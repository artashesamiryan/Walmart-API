<?php 
namespace App\Services\Walmart;

use App\Repositories\Category\CategoryRepository;
use Illuminate\Http\Request;

class CategoryService{

    private $repository;

    public function __construct(CategoryRepository $repository)
    {
        $this->repository = $repository;
    }

    public function pullCategores(Request $request){
        return $this->repository->get($request);
    }
}