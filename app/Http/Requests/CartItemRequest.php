<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CartItemRequest extends FormRequest
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
            'cart_id' => 'required|exists:carts,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0', // Gia phai lon hon hoac bang 0
            'total_price' => 'required|numeric|min:0', // Gia phai lon hon hoac bang 0
        ];
    }

    public function messages()
    {
        return [
            'cart_id.required' => 'Cart ID is required',
            'cart_id.exists' => 'Cart ID does not exist',
            'product_id.required' => 'Product ID is required',
            'product_id.exists' => 'Product ID does not exist',
            'quantity.required' => 'Quantity is required',
            'quantity.integer' => 'Quantity must be an integer',
            'quantity.min' => 'Quantity must be at least 1',
            'price.required' => 'Price is required',
            'price.numeric' => 'Price must be a number',
            'price.min' => 'Price must be at least 0',
            'total_price.required' => 'Total price is required',
            'total_price.numeric' => 'Total price must be a number',
            'total_price.min' => 'Total price must be at least 0',
        ];
    }

    public function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $response = response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'data' => $validator->errors(),
        ], 422);

        throw new \Illuminate\Validation\ValidationException($validator, $response);
    }
}
