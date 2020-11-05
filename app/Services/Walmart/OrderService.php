<?php

namespace App\Services\Walmart;

use App\Repositories\Order\OrderRepository;
use Illuminate\Http\Request;

class OrderService
{
    private $repository;

    public function __construct(OrderRepository $repository)
    {
        $this->repository = $repository;
    }

    public function orderUpdate()
    {
        return $this->repository->orderUpdate();
    }

    public function pullOrders(Request $request)
    {
        return $this->repository->pullOrders($request);
    }
}
