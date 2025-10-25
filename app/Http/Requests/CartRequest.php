<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $commonRules = [
            'cart_id' => ['nullable', 'integer', Rule::exists('carts', 'cart_id')->where(function ($query) {
                $query->where('user_id', auth()->id() ?? request('user_id') ?? 1);
            })],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
        ];

        return array_merge($commonRules, $this->getItemRules());
    }

    public function messages()
    {
        return [
            'product_id.required' => 'Product ID is required',
            'product_id.exists' => 'Product not found',
            'quantity.required' => 'Quantity is required',
            'quantity.min' => 'Quantity must be at least 1',
            'cart_id.exists' => 'Cart not found for the user',
            'items.required' => 'Cart items are required',
            'items.min' => 'At least one cart item is required',
            'items.*.product_id.required' => 'Product ID is required for each item',
            'items.*.product_id.exists' => 'Product not found for item',
            'items.*.quantity.required' => 'Quantity is required for each item',
            'items.*.quantity.min' => 'Quantity must be at least 1 for each item',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'data' => $validator->errors(),
        ], 422));
    }

    private function getItemRules(): array
    {
        // Nếu có items array (bulk operations)
        if ($this->has('items')) {
            return [
                'items' => ['required', 'array', 'min:1'],
                'items.*.product_id' => ['required', 'integer', 'exists:products,product_id'],
                'items.*.quantity' => ['required', 'integer', 'min:1'],
            ];
        }

        // Nếu có product_id trong request (API hoặc form với product_id)
        if ($this->has('product_id')) {
            return [
                'product_id' => ['required', 'integer', 'exists:products,product_id'],
                'quantity' => ['nullable', 'integer', 'min:1'],
            ];
        }

        // Trường hợp add từ route parameter (chỉ cần quantity)
        return [
            'quantity' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
