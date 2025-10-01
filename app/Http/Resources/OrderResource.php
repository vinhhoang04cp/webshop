<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'order_id' => $this->order_id,
            'user_id' => $this->user_id,
            'order_date' => $this->order_date,
            'total_amount' => $this->total_amount,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'products' => ProductResource::collection($this->whenLoaded('products')),
            'products_count' => $this->whenLoaded('products', function () {
                return $this->products->count();
            }),
            'total_quantity' => $this->whenLoaded('products', function () {
                return $this->products->sum('pivot.quantity');
            }),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
