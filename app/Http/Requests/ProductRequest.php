<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest; // 
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProductRequest extends FormRequest
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
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
            'category_id' => 'required|exists:categories,category_id',
            'image_url' => 'nullable|string|max:255',
        ];

        // For update requests, make fields optional with 'sometimes'
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $productId = $this->route('id') ?? $this->route('product');
            $rules = [
                'name' => 'sometimes|required|string|max:255|unique:products,name,' . $productId . ',product_id',
                'description' => 'sometimes|nullable|string|max:2000',
                'price' => 'sometimes|required|numeric|min:0',
                'stock_quantity' => 'sometimes|nullable|integer|min:0',
                'category_id' => 'sometimes|required|exists:categories,category_id',
                'image_url' => 'sometimes|nullable|string|max:255',
            ];
        } else {
            // For create, add unique validation for name
            $rules['name'] = 'required|string|max:255|unique:products,name';
        }

        return $rules; 
    }
    /**
     * Handle a failed validation attempt.
     *
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        // If this is an API request, return JSON response      
        if ($this->expectsJson() || $this->is('api/*')) {
            throw new HttpResponseException(response()->json([
                'status' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422));
        }
        // For web requests, use the default behavior (redirect back with errors)
        parent::failedValidation($validator);
    }

    public function messages()
    {
        return [
            'name.required' => 'The product name is required.',
            'name.string' => 'The product name must be a string.',
            'name.max' => 'The product name may not be greater than 255 characters.',
            'name.unique' => 'The product name has already been taken.',
            'description.string' => 'The description must be a string.',
            'description.max' => 'The description may not be greater than 2000 characters.',
            'price.required' => 'The price is required.',
            'price.numeric' => 'The price must be a number.',
            'price.min' => 'The price must be at least 0.',
            'stock_quantity.integer' => 'The stock quantity must be an integer.',
            'stock_quantity.min' => 'The stock quantity must be at least 0.',
            'category_id.required' => 'The category is required.',
            'category_id.exists' => 'The selected category is invalid.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'product name',
            'description' => 'product description',
            'price' => 'product price',
            'stock_quantity' => 'product stock quantity',
            'category_id' => 'category',
        ];
    }
}
