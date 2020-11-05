<?php

namespace App\Http\Controllers\Walmart;

use App\Http\Controllers\Controller;
use App\Services\Walmart\FitmentService;
use Illuminate\Http\Request;

class FitmentController extends Controller
{
    private $service;

    public function __construct(FitmentService $service)
    {
        $this->service = $service;
    }

    public function pullFitments()
    {
        return $this->service->pullFitments();
    }
}
