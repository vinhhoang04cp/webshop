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
            'detail_id' => $this->detail_id,
            'product_id' => $this->product_id,
            'color' => $this->color,
            'storage' => $this->storage, // 128GB, 256GB, 512GB
            'ram' => $this->ram, // 4GB, 8GB, 12GB, 16GB
            'screen_size' => $this->screen_size, // 6.1 inch, 6.7 inch
            'chip' => $this->chip, // Apple A17 Pro, Snapdragon 8 Gen 3
            'battery' => $this->battery, // 5000 mAh, 4422 mAh
            'camera_main' => $this->camera_main, // 48MP Main + 12MP Ultra Wide
            'camera_front' => $this->camera_front, // 12MP, 32MP
            'os' => $this->os, // iOS 17, Android 14
            'special_features' => $this->special_features, // Dynamic Island, Galaxy AI
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
