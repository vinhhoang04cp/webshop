<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    /**
     * Custom message for the response
     */
    protected $message = 'Cart item retrieved successfully';

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
            ->withMessage('Cart item created successfully')
            ->withStatusCode(201);
    }

    /**
     * Static helper for updated response
     */
    public static function updated($resource)
    {
        return (new static($resource))
            ->withMessage('Cart item updated successfully')
            ->withStatusCode(200);
    }

    /**
     * Static helper for retrieved response
     */
    public static function retrieved($resource)
    {
        return (new static($resource))
            ->withMessage('Cart item retrieved successfully')
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
            'cart_item_id' => $this->cart_item_id,
            'cart_id' => $this->cart_id,
            'product_id' => $this->product_id,
            'quantity' => $this->quantity,
            'price' => $this->price,

            // Product information (when loaded)
            'product' => $this->when($this->relationLoaded('product'), function () {
                return [
                    'product_id' => $this->product->product_id,
                    'name' => $this->product->name,
                    'slug' => $this->product->slug ?? null,
                    'description' => $this->product->description,
                    'price' => $this->product->price,
                    'original_price' => $this->product->original_price,
                    'image' => $this->product->image,
                    'stock_quantity' => $this->product->stock_quantity,
                    'category' => $this->when($this->product->relationLoaded('category'), function () {
                        return [
                            'category_id' => $this->product->category->category_id,
                            'name' => $this->product->category->name,
                        ];
                    }),
                ];
            }),

            // Calculated fields
            'subtotal' => $this->when($this->relationLoaded('product'), function () {
                return $this->quantity * $this->product->price;
            }),

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
