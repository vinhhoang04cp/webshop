<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RatingResource extends JsonResource
{
    /**
     * Custom message for the response
     */
    protected $message = 'Rating retrieved successfully';

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
            ->withMessage('Rating added successfully')
            ->withStatusCode(201);
    }

    /**
     * Static helper for updated response
     */
    public static function updated($resource)
    {
        return (new static($resource))
            ->withMessage('Rating updated successfully')
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
            'id' => $this->id,
            'product_id' => $this->product_id,
            'user_id' => $this->user_id,
            'rating' => $this->rating,
            'review' => $this->review,
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
            'product' => $this->whenLoaded('product', function () {
                return [
                    'product_id' => $this->product->product_id,
                    'name' => $this->product->name,
                    'slug' => $this->product->slug,
                ];
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
}
