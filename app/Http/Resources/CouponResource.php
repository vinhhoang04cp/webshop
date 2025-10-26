<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CouponResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'coupon_id' => $this->coupon_id,
            'code' => $this->code,
            'name' => $this->name,
            'discount_type' => $this->discount_type,
            'discount_value' => $this->discount_value,
            'discount_display' => $this->discount_display,
            'min_order_amount' => $this->min_order_amount,
            'max_discount_amount' => $this->max_discount_amount,
            'usage_limit' => $this->usage_limit,
            'used_count' => $this->used_count,
            'remaining_usage' => $this->usage_limit !== null
                ? max(0, $this->usage_limit - $this->used_count)
                : null,
            'product_id' => $this->product_id,
            'product' => $this->when($this->relationLoaded('product') && $this->product, function () {
                return [
                    'product_id' => $this->product->product_id,
                    'name' => $this->product->name,
                    'slug' => $this->product->slug,
                    'price' => $this->product->price,
                    'image' => $this->product->image,
                ];
            }),
            'scope_display' => $this->scope_display,
            'start_date' => $this->start_date?->format('Y-m-d H:i:s'),
            'end_date' => $this->end_date?->format('Y-m-d H:i:s'),
            'is_active' => $this->is_active,
            'status_display' => $this->status_display,
            'is_valid' => $this->isValid()['valid'],
            'validation_message' => $this->isValid()['message'],
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
