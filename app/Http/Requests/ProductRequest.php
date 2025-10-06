<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

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
     */
    public function rules(): array
    {
        $productId = null;
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $productId = $this->route('id') ?? $this->route('product');
        }

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products')->ignore($productId),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999999.99'],
            'stock_quantity' => ['nullable', 'integer', 'min:0', 'max:999999999'],
            'category_id' => ['required', 'exists:categories,category_id'],
            'image_url' => ['nullable', 'max:2048'],
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        if ($this->expectsJson() || $this->is('api/*')) {
            throw new HttpResponseException(response()->json([
                'status' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422));
        }
    }

    public function messages()
    {
        return [
            'name.required' => 'Product name is required.',
            'name.max' => 'Product name must not exceed 255 characters.',
            'name.unique' => 'Product name must be unique.',
            'description.max' => 'Product description must not exceed 2000 characters.',
            'price.required' => 'Product price is required.',
            'price.min' => 'Product price must be at least 0.',
            'stock_quantity.min' => 'Stock quantity must be at least 0.',
            'category_id.required' => 'Category is required.',
            'category_id.exists' => 'Selected category does not exist.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'product name',
            'description' => 'product description',
            'price' => 'product price',
            'stock_quantity' => 'stock quantity',
            'category_id' => 'category',
        ];
    }
}
