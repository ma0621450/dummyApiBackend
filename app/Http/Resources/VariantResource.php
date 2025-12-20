<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VariantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'sku' => $this->sku,
            'price' => (float) $this->price,
            'stock' => $this->stock,
            'attributes' => collect($this->attributes)->mapWithKeys(function ($value, $key) {
                return [ucfirst($key) => $value];
            }),
            'image_url' => $this->image_url,
        ];
    }
}
