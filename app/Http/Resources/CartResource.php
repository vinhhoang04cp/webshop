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
            //'items' => CartItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'items_count' => $this->whenLoaded('items', function () {
                return $this->items->count();
            }),
            'total_quantity' => $this->whenLoaded('items', function () {
                return $this->items->sum('quantity');
            }),
            'total_price' => $this->whenLoaded('items', function () {
                return $this->items->sum(function ($item) {
                    return $item->quantity * $item->product->price;
                });
            }),

        ];

    }
}
