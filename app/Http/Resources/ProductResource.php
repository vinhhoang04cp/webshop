<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Custom message for the response
     */
    protected $message = 'Product retrieved successfully';

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
            ->withMessage('Product created successfully')
            ->withStatusCode(201);
    }

    /**
     * Static helper for updated response
     */
    public static function updated($resource)
    {
        return (new static($resource))
            ->withMessage('Product updated successfully')
            ->withStatusCode(200);
    }

    /**
     * Static helper for retrieved response
     */
    public static function retrieved($resource)
    {
        return (new static($resource))
            ->withMessage('Product retrieved successfully')
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
            'product_id' => $this->product_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->price,
            'original_price' => $this->original_price,
            'has_discount' => $this->original_price && $this->original_price > $this->price,
            'discount_percentage' => $this->original_price && $this->original_price > $this->price
                ? round((($this->original_price - $this->price) / $this->original_price) * 100, 1)
                : 0,
            'category_id' => $this->category_id,
            'stock_quantity' => $this->stock_quantity,
            'is_in_stock' => $this->stock_quantity > 0,
            'stock_status' => $this->getStockStatus(),
            'image' => $this->image,
            'image_url' => $this->image_url,
            'category' => $this->whenLoaded('category', function () {
                return [
                    'category_id' => $this->category->category_id,
                    'name' => $this->category->name,
                    'slug' => $this->category->slug,
                    'image' => $this->category->image,
                ];
            }),
            'details' => $this->whenLoaded('details', function () {
                return new ProductDetailResource($this->details);
            }),
            'inventory' => $this->whenLoaded('inventory', function () {
                return [
                    'inventory_id' => $this->inventory->inventory_id,
                    'stock_in' => $this->inventory->stock_in,
                    'stock_out' => $this->inventory->stock_out,
                    'current_stock' => $this->inventory->current_stock,
                ];
            }),
            'ratings' => RatingResource::collection($this->whenLoaded('ratings')),
            'average_rating' => $this->whenLoaded('ratings', function () {
                return round($this->ratings->avg('rating'), 1);
            }),
            'total_ratings' => $this->whenLoaded('ratings', function () {
                return $this->ratings->count();
            }),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
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

    /**
     * Get stock status
     */
    protected function getStockStatus(): string
    {
        if ($this->stock_quantity == 0) {
            return 'out_of_stock';
        } elseif ($this->stock_quantity < 10) {
            return 'low_stock';
        }

        return 'in_stock';
    }
}
