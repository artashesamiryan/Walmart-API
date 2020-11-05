<?php

namespace App\Repositories\Order;

use Illuminate\Http\Request;

interface OrderRepositoryInterface
{
    public function pullOrders(Request $request);

    public function receive($token, $cursor = null, $wt);

    public function storeOrders($items);
}
