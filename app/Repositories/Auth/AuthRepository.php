<?php

namespace App\Repositories\Auth;

use App\Repositories\Auth\AuthRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\WalmartToken;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;

class AuthRepository implements AuthRepositoryInterface
{
    public function authenticate(Request $request)
    {
        $response = Http::asForm()
        ->withBasicAuth($request->id, $request->secret)
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
            $wt = $this->store($request->id, $request->secret, $token, $expiry);
            return response()->json(['token' => $wt->token, 'refresh_token' => $wt->refresh_token, 'expires_at' => $wt->expires_at], 200);
        } else {
            $error = json_decode(json_encode($response->json()));
            return response()->json($error, $response->status());
        }
    }

    public function store($id, $secret, $token, $expiry){
        $wt = WalmartToken::where('client_id', $id)->first();
        
        if(!$wt){
            $wt = new WalmartToken;
        }
        
        try{
            $wt->client_secret = Crypt::encryptString($secret);
            $wt->client_id = $id;
            $wt->token = $token;
            $wt->expires_at = Carbon::now()->addSeconds($expiry);
            $wt->refresh_token = $token = bin2hex(openssl_random_pseudo_bytes(512));
            $wt->save();
            return $wt;
        }catch(\Exception $e){
            return response()->json(['message' => 'Internal server error, failed to save the token'], 500);
        }
    }

    public function refreshToken($refresh_token){
        $wt = WalmartToken::where('refresh_token', $refresh_token)->first();
        if(!$wt){
            return response()->json(['message' => 'Invalid refresh token'], 400);
        }

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

            try{
                $wt->token = $token;
                $wt->expires_at = Carbon::now()->addSeconds($expiry);
                $wt->save();

                return response()->json(['token' => $token], 200);
            }catch(\Exception $e){
                return response()->json(['message' => 'Internal server error, failed to save the token'], 500);
            }

        } else {
            return response($response->json)->status($response->status());
        }
    }
}
