<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'cart_id' => $this->cart_id,
            'user_id' => $this->user_id,

            // Cart items với đầy đủ thông tin product
            'items' => CartItemResource::collection($this->whenLoaded('items')),

            // Summary information
            'items_count' => $this->whenLoaded('items', function () {
                return $this->items->count();
            }),
            'total_quantity' => $this->whenLoaded('items', function () {
                return $this->items->sum('quantity');
            }),
            'total_amount' => $this->whenLoaded('items', function () {
                return $this->items->sum(function ($item) {
                    return $item->quantity * ($item->product->price ?? $item->price);
                });
            }),

            // Timestamps
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
