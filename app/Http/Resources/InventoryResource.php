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
            'stock_status' => $this->getStockStatus(),
            'stock_status_text' => $this->getStockStatusText(),
            'is_low_stock' => $this->current_stock > 0 && $this->current_stock < 10,
            'is_out_of_stock' => $this->current_stock == 0,
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'product' => $this->whenLoaded('product', function () {
                return [
                    'product_id' => $this->product->product_id,
                    'name' => $this->product->name,
                    'slug' => $this->product->slug,
                    'price' => $this->product->price,
                    'original_price' => $this->product->original_price,
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
            'stock_value' => $this->whenLoaded('product', function () {
                return $this->current_stock * ($this->product->price ?? 0);
            }),
        ];
    }

    /**
     * Get stock status code
     */
    protected function getStockStatus(): string
    {
        if ($this->current_stock == 0) {
            return 'out';
        } elseif ($this->current_stock < 10) {
            return 'low';
        }

        return 'available';
    }

    /**
     * Get stock status text
     */
    protected function getStockStatusText(): string
    {
        if ($this->current_stock == 0) {
            return 'Hết hàng';
        } elseif ($this->current_stock < 10) {
            return 'Tồn kho thấp';
        }

        return 'Còn hàng';
    }
}
