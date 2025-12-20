<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Resources\BrandResource;
use App\Models\Brand;

use Illuminate\Support\Facades\Cache;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Cache::remember('brands_list', 3600, function () {
            return Brand::all();
        });

        return BrandResource::collection($brands);
    }
}
