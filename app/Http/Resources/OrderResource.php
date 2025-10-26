<?php

namespace App\Http\Resources;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'order_id' => $this->order_id,
            'user_id' => $this->user_id,
            'order_date' => $this->order_date?->format('Y-m-d H:i:s'),
            'total_amount' => $this->total_amount,
            'status' => $this->status,
            'status_text' => $this->getStatusText(),
            'can_cancel' => $this->canTransitionTo(Order::STATUS_CANCELLED),
            'available_transitions' => Order::STATUS_TRANSITIONS[$this->status] ?? [],

            // Shipping information
            'shipping_name' => $this->shipping_name,
            'shipping_phone' => $this->shipping_phone,
            'shipping_address' => $this->shipping_address,
            'note' => $this->note,

            // Payment information
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method,
            'transaction_id' => $this->transaction_id,
            'paid_at' => $this->paid_at?->format('Y-m-d H:i:s'),

            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),

            // User information
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                    'phone' => $this->user->phone,
                ];
            }),

            // Order items
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'items_count' => $this->whenLoaded('items', function () {
                return $this->items->count();
            }),
            'total_quantity' => $this->whenLoaded('items', function () {
                return $this->items->sum('quantity');
            }),

            // Legacy support for products relationship
            'products' => ProductResource::collection($this->whenLoaded('products')),
            'products_count' => $this->whenLoaded('products', function () {
                return $this->products->count();
            }),
        ];
    }

    /**
     * Get status text in Vietnamese
     */
    protected function getStatusText(): string
    {
        $statusTexts = [
            Order::STATUS_PENDING => 'Chờ xử lý',
            Order::STATUS_PROCESSING => 'Đang xử lý',
            Order::STATUS_SHIPPED => 'Đã gửi hàng',
            Order::STATUS_DELIVERED => 'Đã giao hàng',
            Order::STATUS_CANCELLED => 'Đã hủy',
        ];

        return $statusTexts[$this->status] ?? $this->status;
    }
}
