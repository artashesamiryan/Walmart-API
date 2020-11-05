<?php

namespace App\Repositories\Category;

use Illuminate\Http\Request;

interface CategoryRepositoryInterface
{
    public function get(Request $request);
}
