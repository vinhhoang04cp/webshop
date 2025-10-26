<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'cart_item_id' => $this->cart_item_id,
            'cart_id' => $this->cart_id,
            'product_id' => $this->product_id,
            'quantity' => $this->quantity,
            'price' => $this->price,

            // Product information (when loaded)
            'product' => $this->when($this->relationLoaded('product'), function () {
                return [
                    'product_id' => $this->product->product_id,
                    'name' => $this->product->name,
                    'slug' => $this->product->slug ?? null,
                    'description' => $this->product->description,
                    'price' => $this->product->price,
                    'original_price' => $this->product->original_price,
                    'image' => $this->product->image,
                    'stock_quantity' => $this->product->stock_quantity,
                    'category' => $this->when($this->product->relationLoaded('category'), function () {
                        return [
                            'category_id' => $this->product->category->category_id,
                            'name' => $this->product->category->name,
                        ];
                    }),
                ];
            }),

            // Calculated fields
            'subtotal' => $this->when($this->relationLoaded('product'), function () {
                return $this->quantity * $this->product->price;
            }),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
