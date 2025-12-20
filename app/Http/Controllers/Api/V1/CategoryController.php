<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Resources\CategoryResource;
use App\Models\Category;

use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Cache::remember('categories_tree', 3600, function () {
            return Category::whereNull('parent_id')
                ->with('children')
                ->get();
        });

        return CategoryResource::collection($categories);
    }
}
