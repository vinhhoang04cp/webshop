<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    /**
     * Custom message for the response
     */
    protected $message = 'Cart retrieved successfully';

    /**
     * Custom status code for the response
     */
    protected $statusCode = 200;

    /**
     * Additional data to merge into response
     */
    protected $additionalData = [];

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
     * Set additional data
     */
    public function withAdditionalData(array $data)
    {
        $this->additionalData = $data;

        return $this;
    }

    /**
     * Static helper for created response
     */
    public static function created($resource, array $additionalData = [])
    {
        return (new static($resource))
            ->withMessage('Items added to cart successfully')
            ->withStatusCode(201)
            ->withAdditionalData($additionalData);
    }

    /**
     * Static helper for updated response
     */
    public static function updated($resource)
    {
        return (new static($resource))
            ->withMessage('Cart updated successfully')
            ->withStatusCode(200);
    }

    /**
     * Static helper for retrieved response
     */
    public static function retrieved($resource)
    {
        return (new static($resource))
            ->withMessage('Cart retrieved successfully')
            ->withStatusCode(200);
    }

    /**
     * Static helper for current cart response
     */
    public static function current($resource)
    {
        return (new static($resource))
            ->withMessage('Current cart retrieved successfully')
            ->withStatusCode(200);
    }

    /**
     * Static helper for product added response
     */
    public static function productAdded($resource)
    {
        return (new static($resource))
            ->withMessage('Product added to cart successfully')
            ->withStatusCode(200);
    }

    /**
     * Static helper for item updated response
     */
    public static function itemUpdated($resource)
    {
        return (new static($resource))
            ->withMessage('Cart item updated successfully')
            ->withStatusCode(200);
    }

    /**
     * Static helper for item removed response
     */
    public static function itemRemoved($resource)
    {
        return (new static($resource))
            ->withMessage('Item removed from cart successfully')
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
            'cart_id' => $this->cart_id,
            'user_id' => $this->user_id,

            // Cart items với đầy đủ thông tin product
            'items' => CartItemResource::collection($this->whenLoaded('items')),

            // Summary information
            'items_count' => $this->whenLoaded('items', function () {
                return $this->items->count();
            }),
            'total_quantity' => $this->whenLoaded('items', function () {
                return $this->items->sum('quantity');
            }),
            'total_amount' => $this->whenLoaded('items', function () {
                return $this->items->sum(function ($item) {
                    return $item->quantity * ($item->product->price ?? $item->price);
                });
            }),

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
        return array_merge([
            'status' => true,
            'message' => $this->message,
        ], $this->additionalData);
    }

    /**
     * Customize the outgoing response for the resource.
     */
    public function withResponse($request, $response)
    {
        $response->setStatusCode($this->statusCode);
    }
}
