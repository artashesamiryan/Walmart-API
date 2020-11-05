<?php
namespace App\Services\Walmart;

use App\Repositories\Auth\AuthRepository;
use Illuminate\Http\Request;

class AuthService{
    private $repository;

    public function __construct(AuthRepository $repository)
    {
        $this->repository = $repository;    
    }

    public function authenticate(Request $request){
        return $this->repository->authenticate($request);
    }

    public function refreshToken($token){
        return $this->repository->refreshToken($token);
    }
}