<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductDetailResource extends JsonResource
{
    public function with($request)
    {
        return [
            'success' => true,
            'message' => 'Product detail retrieved successfully',
        ];
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'size' => $this->size,
            'color' => $this->color,
            'material' => $this->material,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
