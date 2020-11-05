<?php

namespace App\Repositories\Order;

use App\Models\Order;
use App\Models\OrderInfo;
use App\Models\OrderLine;
use App\Models\Charge;
use App\Models\OrderLineStatus;
use App\Models\OrderLineTrackingInfo;
use App\Models\Product;
use App\Models\WalmartToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use ApiHelpers;
use App\Models\OrderLineFulfillment;
use App\Models\OrderLineRefund;

class OrderRepository
{

    public function pullOrders(Request $request)
    {
        $token = $request->header('token');
        $wt = WalmartToken::where('token', $token)->first();
        if (!$wt) {
            return response()->json(['message' => 'Error, invalid token'], 400);
        }
        $this->receive($token, null, $wt);
        $orders = Order::paginate(20);
        return response()->json($orders, 200);
    }

    public function receive($token, $cursor = null, $wt)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode($wt->client_id . ':' . Crypt::decryptString($wt->client_secret)),
            'WM_SEC.ACCESS_TOKEN' => $token,
            'WM_SVC.NAME' => 'Walmart Marketplace',
            'WM_QOS.CORRELATION_ID' => uniqid(),
            'Accept' => 'application/json',
        ])->get('https://marketplace.walmartapis.com/v3/orders' . ($cursor !== null ? $cursor : '?createdStartDate=1900-01-01'));

        $items = $response->json();

        if (!isset($items['list'])) return response()->json(['message' => 'Could not fetch the orders, please try again later'], 500);

        $this->storeOrders($items['list']['elements']['order']);
        if (isset($items['list']['meta']['nextCursor'])) {
            if (ApiHelpers::autoRenewToken($token)) {
                $token = ApiHelpers::autoRenewToken($token);
            }

            $this->receive($token, $items['list']['meta']['nextCursor'], $wt);
        }
    }

    public function storeOrders($items)
    {
        foreach ($items as $item) {
            $o = Order::where('purchaseOrderId', $item['purchaseOrderId'])->first();

            if (isset($item['purchaseOrderId'])) {
                if (!$o) {
                    try {
                        $o = new Order;
                        $o->purchaseOrderId = isset($item['purchaseOrderId']) ? $item['purchaseOrderId'] : '';
                        $o->customerOrderId = isset($item['customerOrderId']) ? $item['customerOrderId']  : '';
                        $o->customerEmailId = isset($item['customerEmailId']) ? $item['customerEmailId'] : '';
                        $o->orderDate = $item['orderDate'];
                        $o->shipNodeType = $item['shipNode']['type'];
                        $o->save();
                    } catch (\Exception $e) {
                        return response()->json(['message' => 'Internal server error'], 500);
                    }

                    if (isset($item['shippingInfo'])) {
                        try {
                            $ot = new OrderInfo;
                            $ot->order_id = $o->id;
                            $ot->phone = $item['shippingInfo']['phone'];
                            $ot->estimatedDeliveryDate = $item['shippingInfo']['estimatedDeliveryDate'];
                            $ot->estimatedShipDate = $item['shippingInfo']['estimatedShipDate'];
                            $ot->methodCode = $item['shippingInfo']['methodCode'];
                            $ot->name = $item['shippingInfo']['postalAddress']['name'];
                            $ot->address1 = $item['shippingInfo']['postalAddress']['address1'];
                            $ot->address2 = $item['shippingInfo']['postalAddress']['address2'];
                            $ot->city = $item['shippingInfo']['postalAddress']['city'];
                            $ot->state = $item['shippingInfo']['postalAddress']['state'];
                            $ot->postalCode = $item['shippingInfo']['postalAddress']['postalCode'];
                            $ot->country = $item['shippingInfo']['postalAddress']['country'];
                            $ot->addressType = $item['shippingInfo']['postalAddress']['addressType'];
                            $ot->save();
                        } catch (\Exception $e) {
                            return response()->json(['message' => 'Internal server error'], 500);
                        }
                    }

                    if (isset($item['orderLines'])) {
                        foreach ($item['orderLines']['orderLine'] as $orderLine) {
                            try {
                                $ol = new OrderLine;
                                $ol->lineNumber = $orderLine['lineNumber'];
                                $ol->order_id = $o->id;

                                $p = Product::where('name', $orderLine['item']['productName'])->first();

                                if (!$p) {
                                    $p = new Product;
                                    $p->name = $orderLine['item']['productName'];
                                    $p->sku = $orderLine['item']['sku'];
                                    $p->save();
                                }

                                $ol->product_id = $p->id;
                                $ol->unitOfMeasurement = $orderLine['orderLineQuantity']['unitOfMeasurement'];
                                $ol->amount = $orderLine['orderLineQuantity']['amount'];
                                $ol->statusDate = $orderLine['statusDate'];
                                $ol->save();
                            } catch (\Exception $e) {
                                dd($e, $orderLine);
                                return response()->json(['message' => 'Internal server error'], 500);
                            }

                            if (isset($orderLine['refund'])) {
                                try {
                                    $r = new OrderLineRefund;
                                    $r->refundId = $orderLine['refund']['refundId'];
                                    $r->refundComments = $orderLine['refund']['refundComments'];
                                    $r->order_line_id = $ol->id;
                                    $r->save();

                                    foreach ($orderLine['refund']['refundCharges']['refundCharge'] as $refundCharge) {
                                        $c = new Charge;
                                        $c->order_line_id = $ol->id;
                                        $c->chargeType = $refundCharge['charge']['chargeType'];
                                        $c->chargeName = $refundCharge['charge']['chargeName'];
                                        $c->chargeCurrency = $refundCharge['charge']['chargeAmount']['currency'];
                                        $c->chargeAmount = $refundCharge['charge']['chargeAmount']['amount'];

                                        if (isset($refundCharge['tax'])) {
                                            $c->taxName = $refundCharge['tax']['taxName'];
                                            $c->taxAmount = $refundCharge['tax']['taxAmount']['amount'];
                                            $c->taxCurrency = $refundCharge['tax']['taxAmount']['currency'];
                                        }

                                        $c->refundReason = $refundCharge['refundReason'];
                                        $c->save();
                                    }
                                } catch (\Exception $e) {
                                    return response()->json(['message' => 'Internal server error'], 500);
                                }
                            }


                            foreach ($orderLine['charges']['charge'] as $charge) {
                                try {
                                    $olc = new Charge;
                                    $olc->order_line_id = $ol->id;
                                    $olc->chargeType = $charge['chargeType'];
                                    $olc->chargeName = $charge['chargeName'];
                                    $olc->chargeCurrency = $charge['chargeAmount']['currency'];
                                    $olc->chargeAmount = $charge['chargeAmount']['amount'];

                                    if (isset($charge['tag'])) {
                                        $olc->taxName = $charge['tax']['taxName'];
                                        $olc->taxAmount = $charge['tax']['taxAmount']['amount'];
                                        $olc->taxCurrency = $charge['tax']['taxAmount']['currency'];
                                    }

                                    $olc->save();
                                } catch (\Exception $e) {
                                    return response()->json(['message' => 'Internal server error'], 500);
                                }
                            }

                            foreach ($orderLine['orderLineStatuses']['orderLineStatus'] as $status) {
                                try {
                                    $ols = new OrderLineStatus;
                                    $ols->order_line_id = $ol->id;
                                    $ols->status = $status['status'];
                                    $ols->unitOfMeasurement = $status['statusQuantity']['unitOfMeasurement'];
                                    $ols->amount = $status['statusQuantity']['amount'];
                                    $ols->cancellationReason = $status['cancellationReason'];
                                    $ols->returnCenterAddress = $status['returnCenterAddress'];
                                    $ols->save();
                                } catch (\Exception $e) {
                                    return response()->json(['message' => 'Internal server error'], 500);
                                }
                            }

                            if (isset($orderLine['trackingInfo'])) {
                                try {
                                    $olti = new OrderLineTrackingInfo;
                                    $olti->order_line_id = $ol->id;
                                    $olti->shipDateTime = $orderLine['trackingInfo']['shipDateTime'];
                                    $olti->carrier = $orderLine['trackingInfo']['carrierName']['carrier'];
                                    $olti->otherCarrier = $orderLine['trackingInfo']['carrierName']['otherCarrier'];
                                    $olti->methodCode = $orderLine['trackingInfo']['methodCode'];
                                    $olti->carrierMethodCode = $orderLine['trackingInfo']['carrierMethodCode'];
                                    $olti->trackingNumber = $orderLine['trackingInfo']['trackingNumber'];
                                    $olti->trackingURL = $orderLine['trackingInfo']['trackingURL'];
                                    $olti->save();
                                } catch (\Exception $e) {
                                    return response()->json(['message' => 'Internal server error'], 500);
                                }
                            }

                            if (isset($orderLine['fulfillment'])) {
                                try {
                                    $olf = new OrderLineFulfillment;
                                    $olf->order_line_id = $ol->id;
                                    $olf->fulfillmentOption = $orderLine['fulfillment']['fulfillmentOption'];
                                    $olf->shipMethod = $orderLine['fulfillment']['shipMethod'];
                                    $olf->storeId = $orderLine['fulfillment']['storeId'];
                                    $olf->pickUpDateTime = $orderLine['fulfillment']['pickUpDateTime'];
                                    $olf->pickUpBy = $orderLine['fulfillment']['pickUpBy'];
                                    $olf->shippingProgramType = $orderLine['fulfillment']['shippingProgramType'];
                                    $olf->save();
                                } catch (\Exception $e) {
                                    return response()->json(['message' => 'Internal server error'], 500);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
