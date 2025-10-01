<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'cart_id' => ['nullable', 'integer', Rule::exists('carts', 'id')->where(function ($query) {
                $query->where('user_id', auth()->id());
            })],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }
    public function messages()
    {
        return [
            'product_id.required' => 'Product ID is required',
            'product_id.integer' => 'Product ID must be an integer',
            'product_id.exists' => 'Product not found',
            'quantity.required' => 'Quantity is required',
            'quantity.integer' => 'Quantity must be an integer',
            'quantity.min' => 'Quantity must be at least 1',
            'cart_id.integer' => 'Cart ID must be an integer',
            'cart_id.exists' => 'Cart not found for the user',
            'items.required' => 'Cart items are required',
            'items.array' => 'Cart items must be an array',
            'items.min' => 'At least one cart item is required',
            'items.*.product_id.required' => 'Product ID is required for each item',
            'items.*.product_id.exists' => 'Product not found for each item',
            'items.*.quantity.required' => 'Quantity is required for each item',
            'items.*.quantity.integer' => 'Quantity must be an integer for each item',
            'items.*.quantity.min' => 'Quantity must be at least 1 for each item',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();

        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'data' => $errors
        ], 422));
            parent::failedValidation($validator);
    }
}
