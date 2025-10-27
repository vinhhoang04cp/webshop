<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductDetailResource extends JsonResource
{
    /**
     * Custom message for the response
     */
    protected $message = 'Product detail retrieved successfully';

    /**
     * Custom status code for the response
     */
    protected $statusCode = 200;

    /**
     * Set custom message
     */
    public function withMessage(string $message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Set custom status code
     */
    public function withStatusCode(int $statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * Static helper for created response
     */
    public static function created($resource)
    {
        return (new static($resource))
            ->withMessage('Product detail created successfully')
            ->withStatusCode(201);
    }

    /**
     * Static helper for updated response
     */
    public static function updated($resource)
    {
        return (new static($resource))
            ->withMessage('Product detail updated successfully')
            ->withStatusCode(200);
    }

    /**
     * Static helper for retrieved response
     */
    public static function retrieved($resource)
    {
        return (new static($resource))
            ->withMessage('Product detail retrieved successfully')
            ->withStatusCode(200);
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

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'status' => true,
            'message' => $this->message,
        ];
    }

    /**
     * Customize the outgoing response for the resource.
     */
    public function withResponse($request, $response)
    {
        $response->setStatusCode($this->statusCode);
    }
}
