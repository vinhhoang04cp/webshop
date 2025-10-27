<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Custom message for the response
     */
    protected $message = 'Category retrieved successfully';

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
            ->withMessage('Category created successfully')
            ->withStatusCode(201);
    }

    /**
     * Static helper for updated response
     */
    public static function updated($resource)
    {
        return (new static($resource))
            ->withMessage('Category updated successfully')
            ->withStatusCode(200);
    }

    /**
     * Static helper for retrieved response
     */
    public static function retrieved($resource)
    {
        return (new static($resource))
            ->withMessage('Category retrieved successfully')
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
            'category_id' => $this->category_id,
            'name' => $this->name,
            'description' => $this->description,

            // Products information (when loaded)
            'products' => $this->when($this->relationLoaded('products'), function () {
                return $this->products->map(function ($product) {
                    return [
                        'product_id' => $product->product_id,
                        'name' => $product->name,
                        'slug' => $product->slug ?? null,
                        'price' => $product->price,
                        'original_price' => $product->original_price,
                        'image' => $product->image,
                        'stock_quantity' => $product->stock_quantity,
                        'is_active' => $product->is_active ?? true,
                    ];
                });
            }),

            // Products count
            'products_count' => $this->when(
                $this->relationLoaded('products') || isset($this->products_count),
                function () {
                    return $this->products_count ?? $this->products->count();
                }
            ),

            // Timestamps
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
