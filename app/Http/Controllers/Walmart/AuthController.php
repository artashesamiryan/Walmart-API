<?php

namespace App\Http\Controllers\Walmart;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use App\Services\Walmart\AuthService;

class AuthController extends Controller
{

    private $service;

    public function __construct(AuthService $service)
    {
        $this->service = $service;
    }

    public function authenticate(Request $request)
    {
        return $this->service->authenticate($request);
    }

    public function refreshToken(Request $request)
    {
        return $this->service->refreshToken($request->header('token'));
    }
}
