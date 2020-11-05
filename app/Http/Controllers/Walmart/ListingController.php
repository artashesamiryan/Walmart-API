<?php

namespace App\Http\Controllers\Walmart;

use App\Http\Controllers\Controller;
use App\Services\Walmart\ListingService;
use Illuminate\Http\Request;

class ListingController extends Controller
{

    private $service;

    public function __construct(ListingService $service)
    {
        $this->service = $service;
    }

    public function pullListings(Request $request)
    {
        return $this->service->pullListings($request);
    }
}
