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
    public function toArray(Request $request): array  // Tra ve mot mang chua thong tin cua category
    {
        return [
            'id' => $this->category_id,
            'name' => $this->name,
            'description' => $this->description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'products_count' => $this->whenLoaded('products', function () {
                return $this->products->count();
            }),
        ];
    }
}
