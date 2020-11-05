<?php

namespace App\Repositories\Category;

use App\Repositories\Category\CategoryRepositoryInterface;
use Illuminate\Http\Request;
use App\Models\WalmartToken;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use App\Models\Category;
use App\Models\Subcategory;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function get(Request $request)
    {
        $token = $request->header('token');
        $wt = WalmartToken::where('token', $token)->first();

        if (!$wt) {
            return response()->json(['message' => 'Error, invalid token'], 400);
        }

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode($wt->client_id . ':' . Crypt::decryptString($wt->client_secret)),
            'WM_SEC.ACCESS_TOKEN' => $token,
            'WM_SVC.NAME' => 'Walmart Marketplace',
            'WM_QOS.CORRELATION_ID' => uniqid(),
            'Accept' => 'application/json',
        ])->get('https://marketplace.walmartapis.com/v3/items/taxonomy');



        $items = $response->json()['payload'];

        try {
            foreach ($items as $item) {
                $c = Category::where('name', $item['category'])->first();

                if (!$c) {
                    $c = new Category;
                    $c->name = $item['category'];
                    $c->save();
                }

                foreach ($item['subcategory'] as $subcategory) {
                    $s = Subcategory::where('name', $subcategory['subCategoryName'])->first();
                    if (!$s) {
                        $s = new Subcategory;
                        $s->category_id = $c->id;
                        $s->name = $subcategory['subCategoryName'];
                        $s->subcategory_id = $subcategory['subCategoryId'];
                        $s->save();
                    }
                }
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Internal server error'], 500);
        }

        return response(Category::paginate(20), 200);
    }
}
