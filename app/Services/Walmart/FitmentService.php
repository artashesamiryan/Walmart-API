<?php 
namespace App\Services\Walmart;

use App\Repositories\Fitment\FitmentRepository;

class FitmentService{

    private $repository;

    public function __construct(FitmentRepository $repository)
    {
        $this->repository = $repository;
    }

    public function pullFitments(){
        return $this->repository->get();
    }
}