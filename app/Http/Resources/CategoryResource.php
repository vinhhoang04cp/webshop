<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'category_id' => $this->category_id,
            'name' => $this->name,
            'description' => $this->description,

            // Products information (when loaded)
            'products' => $this->when($this->relationLoaded('products'), function () {
                return $this->products->map(function ($product) {
                    return [
                        'product_id' => $product->product_id,
                        'name' => $product->name,
                        'slug' => $product->slug ?? null,
                        'price' => $product->price,
                        'original_price' => $product->original_price,
                        'image' => $product->image,
                        'stock_quantity' => $product->stock_quantity,
                        'is_active' => $product->is_active ?? true,
                    ];
                });
            }),

            // Products count
            'products_count' => $this->when(
                $this->relationLoaded('products') || isset($this->products_count),
                function () {
                    return $this->products_count ?? $this->products->count();
                }
            ),

            // Timestamps
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
