<?php

namespace App\Services\Walmart;

use App\Repositories\Listing\ListingRepository;
use Illuminate\Http\Request;

class ListingService
{

    private $repository;

    public function __construct(ListingRepository $repository)
    {
        $this->repository = $repository;
    }

    public function pullListings(Request $request)
    {
        return $this->repository->get($request);
    }

}
