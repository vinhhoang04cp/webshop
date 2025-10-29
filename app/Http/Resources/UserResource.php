<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Custom message for the response
     */
    protected $message = 'User retrieved successfully';

    /**
     * Custom status code for the response
     */
    protected $statusCode = 200;

    /**
     * Additional data for the response
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
     * Set additional data (like token)
     */
    public function withAdditionalData(array $data)
    {
        $this->additionalData = $data;

        return $this;
    }

    /**
     * Static helper for created response
     */
    public static function created($resource, string $message = 'User created successfully', array $additionalData = [])
    {
        return (new static($resource))
            ->withMessage($message)
            ->withStatusCode(201)
            ->withAdditionalData($additionalData)
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Static helper for retrieved response
     */
    public static function retrieved($resource, string $message = 'User retrieved successfully', array $additionalData = [])
    {
        return (new static($resource))
            ->withMessage($message)
            ->withStatusCode(200)
            ->withAdditionalData($additionalData);
    }

    /**
     * Static helper for updated response
     */
    public static function updated($resource, string $message = 'User updated successfully', array $additionalData = [])
    {
        return (new static($resource))
            ->withMessage($message)
            ->withStatusCode(200)
            ->withAdditionalData($additionalData);
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
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'avatar' => $this->avatar,
            'avatar_url' => $this->avatar ? asset('storage/'.$this->avatar) : null,
            'email_verified_at' => $this->email_verified_at?->format('Y-m-d H:i:s'),
            'roles' => $this->whenLoaded('roles', function () {
                return $this->roles->pluck('name');
            }),
            'is_admin' => $this->when(
                $this->relationLoaded('roles'),
                fn () => $this->roles->contains('name', 'admin')
            ),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get additional data for the response
     */
    public function with(Request $request): array
    {
        return array_merge([
            'status' => true,
            'message' => $this->message,
        ], $this->additionalData);
    }
}
