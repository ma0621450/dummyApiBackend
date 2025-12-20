<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ProductResource;
use App\Models\Product;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'category' => 'nullable|string',
            'brand' => 'nullable|string',
            'q' => 'nullable|string|max:100',
            'sort' => 'nullable|in:price_asc,price_desc,name_asc,name_desc,newest',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:50',
            'price_min' => 'nullable|numeric|min:0',
            'price_max' => 'nullable|numeric|min:0',
        ]);

        if (isset($validated['price_min']) && isset($validated['price_max'])) {
            if ($validated['price_min'] > $validated['price_max']) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => [
                        'price_min' => ['The price_min must be less than or equal to price_max.']
                    ]
                ], 422);
            }
        }

        $perPage = $validated['per_page'] ?? 12;
        $query = Product::with(['brand', 'category', 'variants']);

        if (!empty($validated['category'])) {
            $query->whereHas('category', function ($q) use ($validated) {
                $q->where('slug', $validated['category']);
            });
        }

        if (!empty($validated['brand'])) {
            $query->whereHas('brand', function ($q) use ($validated) {
                $q->where('slug', $validated['brand']);
            });
        }

        if (isset($validated['price_min'])) {
            $query->where('price', '>=', $validated['price_min']);
        }
        if (isset($validated['price_max'])) {
            $query->where('price', '<=', $validated['price_max']);
        }

        if (!empty($validated['q'])) {
            $term = $validated['q'];
            $query->where(function ($q) use ($term) {
                $q->where('name', 'ilike', "%{$term}%")
                    ->orWhere('description', 'ilike', "%{$term}%")
                    ->orWhereHas('brand', function ($bq) use ($term) {
                        $bq->where('name', 'ilike', "%{$term}%");
                    });
            });
        }

        $sort = $validated['sort'] ?? 'newest';
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $paginator = $query->paginate($perPage);

        if ($request->page > $paginator->lastPage()) {
            return response()->json([
                'data' => [],
                'links' => [
                    'first' => $paginator->url(1),
                    'last' => $paginator->url($paginator->lastPage()),
                    'prev' => $paginator->lastPage() > 0 ? $paginator->url($paginator->lastPage()) : null,
                    'next' => null,
                ],
                'meta' => [
                    'current_page' => (int) $request->page,
                    'from' => 0,
                    'last_page' => $paginator->lastPage(),
                    'path' => $paginator->path(),
                    'per_page' => $paginator->perPage(),
                    'to' => 0,
                    'total' => $paginator->total(),
                ],
                'message' => "Page not found"
            ], 404);
        }

        if ($paginator->isEmpty() && !empty($validated['q'])) {
            return response()->json([
                'data' => [],
                'meta' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'total' => 0,
                    'from' => 0,
                    'to' => 0
                ],
                'message' => "No products found for your search"
            ]);
        }

        if (!empty($validated['q'])) {
            return ProductResource::collection($paginator)->additional([
                'message' => "Found {$paginator->total()} products for your search"
            ]);
        }

        return ProductResource::collection($paginator);
    }

    public function show($slug)
    {
        $product = \Illuminate\Support\Facades\Cache::remember("product_{$slug}", 300, function () use ($slug) {
            return Product::where('slug', $slug)
                ->with(['brand', 'category', 'variants'])
                ->first();
        });

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return new ProductResource($product);
    }

    public function variants($id)
    {
        $product = Product::with('variants')->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json([
            'data' => \App\Http\Resources\VariantResource::collection($product->variants)
        ]);
    }

    public function variantBySku($id, $sku)
    {
        $product = Product::with('variants')->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $variant = $product->variants->where('sku', $sku)->first();

        if (!$variant) {
            return response()->json(['message' => 'Variant not found'], 404);
        }

        return response()->json([
            'data' => new \App\Http\Resources\VariantResource($variant)
        ]);
    }
}
