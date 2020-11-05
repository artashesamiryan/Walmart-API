<?php 
namespace App\Services\Walmart;

use App\Repositories\Fulfillment\FulfillmentRepository;
use Illuminate\Http\Request;

class FulfillmentService{
    private $repository;

    public function __construct(FulfillmentRepository $repository)
    {
        $this->repository = $repository;
    }

    public function pushFulfillment(Request $request){
        return $this->repository->pushFulfillment($request);
    }
}