<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CartRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
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

    /**
     * Get item validation rules based on request structure
     */
    private function getItemRules(): array
    {
        if ($this->has('items')) {
            return [
                'items' => ['required', 'array', 'min:1'],
                'items.*.product_id' => ['required', 'integer', 'exists:products,product_id'],
                'items.*.quantity' => ['required', 'integer', 'min:1'],
            ];
        }

        return [
            'product_id' => ['required', 'integer', 'exists:products,product_id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
