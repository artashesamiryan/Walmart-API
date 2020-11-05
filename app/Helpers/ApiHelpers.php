<?php

namespace App\Helpers;

use App\Models\WalmartToken;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

class ApiHelpers
{
    public static function autoRenewToken($token)
    {

        $wt = WalmartToken::where('token', $token)->first();

        if (!$wt) {
            return response()->json(['message' => 'Invalid token'], 400);
        }

        if ($wt->expires_at->lte(Carbon::now())) {


            $response = Http::asForm()
                ->withBasicAuth($wt->client_id, Crypt::decryptString($wt->client_secret))
                ->withHeaders([
                    'WM_SVC.NAME' => 'Walmart Marketplace',
                    'WM_QOS.CORRELATION_ID' => uniqid(),
                    'Accept' => 'application/json',
                    'WM_SVC.VERSION' => '1.0.0',
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ])->post('https://marketplace.walmartapis.com/v3/token', [
                    'grant_type' => 'client_credentials'
                ]);


            if ($response->successful()) {
                $token = $response->json()['access_token'];
                $expiry = $response->json()['expires_in'];

                try {
                    $wt->token = $token;
                    $wt->expires_at = Carbon::now()->addSeconds($expiry);
                    $wt->save();

                    return $token;
                } catch (\Exception $e) {
                    return false;
                }
            } else {
                return response($response->json)->status($response->status());
            }
        } else {
            return false;
        }
    }

    public static function tokenExpired()
    {
    }
}
