<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'inventory_id' => $this->inventory_id,
            'product_id' => $this->product_id,
            'stock_in' => $this->stock_in,
            'stock_out' => $this->stock_out,
            'current_stock' => $this->current_stock,
            'updated_at' => $this->updated_at,
            'product' => $this->whenLoaded('product', function () {
                return [
                    'product_id' => $this->product->product_id,
                    'product_name' => $this->product->name,
                    'price' => $this->product->price,
                    'stock_quantity' => $this->product->stock_quantity,
                ];
            }),
        ];
    }
}
