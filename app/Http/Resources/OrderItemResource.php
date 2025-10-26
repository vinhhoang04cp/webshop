<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'order_item_id' => $this->order_item_id,
            'order_id' => $this->order_id,
            'product_id' => $this->product_id,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'subtotal' => $this->quantity * $this->price,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'product' => $this->whenLoaded('product', function () {
                return [
                    'product_id' => $this->product->product_id,
                    'name' => $this->product->name,
                    'slug' => $this->product->slug,
                    'price' => $this->product->price,
                    'image' => $this->product->image,
                    'stock_quantity' => $this->product->stock_quantity,
                    'category' => $this->when(
                        $this->product->relationLoaded('category') && $this->product->category,
                        function () {
                            return [
                                'category_id' => $this->product->category->category_id,
                                'name' => $this->product->category->name,
                                'slug' => $this->product->category->slug,
                            ];
                        }
                    ),
                ];
            }),
        ];
    }
}
