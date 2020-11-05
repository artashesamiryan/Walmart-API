<?php

namespace App\Http\Controllers\Walmart;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Walmart\CategoryService;

class CategoryController extends Controller
{
    private $service;

    public function __construct(CategoryService $service)
    {
        $this->service = $service;
    }

    public function pullCategories(Request $request)
    {
        return $this->service->pullCategores($request);
    }
}
