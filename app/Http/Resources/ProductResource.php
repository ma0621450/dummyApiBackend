<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $totalStock = $this->total_stock;
        $discountedPrice = $this->price * 0.9;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => (float) $this->price,
            'discounted_price' => round($discountedPrice, 2),
            'total_stock' => $totalStock,
            'is_in_stock' => $totalStock > 0,
            'image' => "https://picsum.photos/seed/{$this->slug}/400/300",
            'brand' => new BrandResource($this->whenLoaded('brand')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'variants' => VariantResource::collection($this->whenLoaded('variants')),
        ];
    }
}
