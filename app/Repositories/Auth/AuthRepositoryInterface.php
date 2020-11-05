<?php

namespace App\Repositories\Auth;

use Illuminate\Http\Request;

interface AuthRepositoryInterface
{
    public function authenticate(Request $request);

    public function store($id, $secret, $token, $expiry);

    public function refreshToken($client_id);
}
