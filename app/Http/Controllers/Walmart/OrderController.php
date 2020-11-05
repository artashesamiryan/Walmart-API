<?php

namespace App\Http\Controllers\Walmart;

use App\Http\Controllers\Controller;
use App\Services\Walmart\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{

    private $service;

    public function __construct(OrderService $service)
    {
        $this->service = $service;
    }

    public function pullOrders(Request $request)
    {
        return $this->service->pullOrders($request);
    }

    public function orderUpdate()
    {
        return $this->service->orderUpdate();
    }
}
