<?php

namespace App\Http\Controllers\Walmart;

use App\Http\Controllers\Controller;
use App\Services\Walmart\FulfillmentService;
use Illuminate\Http\Request;

class FulfillmentController extends Controller
{
    private $service;

    public function __construct(FulfillmentService $service)
    {
        $this->service = $service;
    }

    public function pushFulfillment(Request $request){
        return $this->service->pushFulfillment($request);
    }
}
