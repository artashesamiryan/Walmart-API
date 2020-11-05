<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\WalmartToken;
use App\Services\Walmart\AuthService;
use Carbon\Carbon;

class WalmartTokenMiddleware
{
    private $service;

    public function __construct(AuthService $service){
        $this->service = $service;
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->header('token');
        
        $wt = WalmartToken::where('token', $token)->first();
        
        if(!$wt){
            return response()->json(['message' => 'Invalid token'], 400);
        }

        if($wt->expires_at->lte(Carbon::now())){
            return response()->json(['message' => 'Your token has expired']);
        }

        return $next($request);
    }
}
