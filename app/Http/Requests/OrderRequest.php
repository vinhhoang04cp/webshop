<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class OrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Nếu là request update status (PUT/PATCH), chỉ validate status
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return [
                'status' => ['required', 'string', 'in:pending,processing,shipped,delivered,cancelled'],
            ];
        }

        // Nếu là request tạo order mới (POST)
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'order_date' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,product_id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages()
    {
        return [
            'user_id.required' => 'User ID is required',
            'user_id.exists' => 'User not found',
            'order_date.required' => 'Order date is required',
            'items.required' => 'Order items are required',
            'items.min' => 'At least one order item is required',
            'items.*.product_id.required' => 'Product ID is required for each item',
            'items.*.product_id.exists' => 'Product not found for item',
            'items.*.quantity.required' => 'Quantity is required for each item',
            'items.*.quantity.min' => 'Quantity must be at least 1 for each item',
            'status.required' => 'Trạng thái đơn hàng là bắt buộc',
            'status.in' => 'Trạng thái phải là một trong: pending, processing, shipped, delivered, cancelled',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        if ($this->expectsJson() || $this->is('api/*')) {
            throw new HttpResponseException(response()->json([
                'status' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422));
        }
        parent::failedValidation($validator);
    }
}
