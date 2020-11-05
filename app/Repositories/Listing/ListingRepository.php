<?php

namespace App\Repositories\Listing;

use App\Models\Fitment;
use App\Models\WalmartToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use App\Models\ProductType;
use App\Models\Product;
use App\Models\ProductAsset;
use ApiHelpers;

class ListingRepository
{
    public function get(Request $request)
    {
        $token = $request->header('token');
        $wt = WalmartToken::where('token', $token)->first();
        if (!$wt) {
            return response()->json(['message' => 'Error, invalid token'], 400);
        }
        $this->receive($token, null, $wt);
        $products = Product::paginate(20);
        return response()->json($products, 200);
    }

    public function receive($token, $cursor = null, $wt)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode($wt->client_id . ':' . Crypt::decryptString($wt->client_secret)),
            'WM_SEC.ACCESS_TOKEN' => $token,
            'WM_SVC.NAME' => 'Walmart Marketplace',
            'WM_QOS.CORRELATION_ID' => uniqid(),
            'Accept' => 'application/json',
        ])->get('https://marketplace.walmartapis.com/v3/items', ['nextCursor' => $cursor ? $cursor : '', 'limit' => 50]);

        $items = $response->json();

        $this->storeProducts($items);

        if (isset($items['nextCursor'])) {
            if (ApiHelpers::autoRenewToken($token)) {
                $token = ApiHelpers::autoRenewToken($token);
            }
            $this->receive($token, $items['nextCursor'], $wt);
        }
    }

    public function storeProducts($items)
    {
        if (isset($items['ItemResponse'])) {

            foreach ($items['ItemResponse'] as $item) {
                $pt = ProductType::where('name', $item['productType'])->first();
                if (!$pt) {
                    $pt = new ProductType;
                    $pt->name = $item['productType'];
                    $pt->save();
                }
                $p = Product::where('wpid', $item['wpid'])->first();
                if (!$p) {
                    try {
                        $p = new Product;
                        $p->mart = $item['mart'];
                        $p->sku = $item['sku'];
                        $p->wpid = $item['wpid'];
                        $p->upc = $item['upc'];
                        $p->gtin = $item['gtin'];
                        $p->name = $item['productName'];
                        $p->published_status = $item['publishedStatus'];
                        $p->lifecycle_status = $item['lifecycleStatus'];
                        $p->product_type_id = $pt->id;
                        $p->save();
                    } catch (\Exception $e) {
                        return response()->json(['message' => 'Internal server error'], 500);
                    }
                    foreach (json_decode($item['shelf']) as $fitment) {
                        try {
                            $f = Fitment::where('name', $fitment)->first();
                            if (!$f) {
                                $f = new Fitment;
                                $f->name = $fitment;
                                $f->save();
                            }
                            $p->fitments()->attach($f->id);
                        } catch (\Exception $e) {
                            return response()->json(['message' => 'Internal server error'], 500);
                        }
                    }
                    $pa = ProductAsset::where('product_id', $p->id)->first();
                    if (!$pa) {
                        try {
                            $pa = new ProductAsset;
                            $pa->currency = $item['price']['currency'];
                            $pa->amount = $item['price']['amount'];
                            $pa->product_id = $p->id;
                            $pa->save();
                        } catch (\Exception $e) {
                            return response()->json(['message' => 'Internal server error'], 500);
                        }
                    }
                }
            }
        }
    }
}
