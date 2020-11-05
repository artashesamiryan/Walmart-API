<?php

namespace App\Repositories\Fulfillment;

use App\Models\WalmartToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class FulfillmentRepository
{

    public function pushFulfillment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'node' => 'required|string',
            'status' => 'required|string',
            'version' => 'string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 400);
        }

        $version = $request->version ? $request->version : "1.2";
        $shipNode = $request->node;
        $status = $request->status;
        $token = $request->header('token');

        $wt = WalmartToken::where('token', $token)->first();
        if (!$wt) {
            return response()->json(['message' => 'Error, invalid token'], 400);
        }

        $response = Http::contentType("application/json")
            ->withHeaders([
                'Authorization' => 'Basic ' . base64_encode($wt->client_id . ':' . Crypt::decryptString($wt->client_secret)),
                'WM_SEC.ACCESS_TOKEN' => $token,
                'WM_SVC.NAME' => 'Walmart Marketplace',
                'WM_QOS.CORRELATION_ID' => uniqid(),
                'Accept' => 'application/json',
            ])->post(
                'https://marketplace.walmartapis.com/v3/settings/shipping/3plshipnodes',
                [
                    "shipNodeHeader" => [
                        "version" => $version
                    ],
                    "shipNode" => [
                        [
                            "shipNode" => $shipNode,
                            "status" => $status
                        ]
                    ]
                ]
            );

        if ($response->successful()) {
            return response()->json(['message' => 'Fulfillment successfully created', 'response' => $response->json()], 200);
        } else {
            return response()->json(['message' => 'Request failed', 'details' => $response->json()], $response->status());
        }
    }
}
