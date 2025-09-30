<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator; //
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

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
        if ($this->isMethod('put') || $this->isMethod('patch')) { // Neu la PUT hoac PATCH (update)
            $productId = $this->route('id') ?? $this->route('product'); // Lay ID san pham tu route parameter
            $this->merge(['product_id' => $productId]); // Them product_id vao request de su dung trong rule unique
        }

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products')->ignore($this->product_id), // Kiem tra unique trong bang products, bo qua ban ghi hien tai khi update
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
            'category_id' => ['required', 'exists:categories,category_id'], // Kiem tra category_id phai ton tai trong bang categories
        ];
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
        if ($this->expectsJson() || $this->is('api/*')) {  // $this->is('api/*') kiem tra xem URL co bat dau bang 'api/' khong
            throw new HttpResponseException(response()->json([
                'status' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422));
        }
    }

    public function messages()
    {
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $productId = $this->route('id') ?? $this->route('product');
            $this->merge(['product_id' => $productId]);
        }

        return [
            'name.required' => 'Product name is required.',
            'name.string' => 'Product name must be a string.',
            'name.max' => 'Product name must not exceed 255 characters.',
            'name.unique' => 'Product name must be unique.',
            'description.string' => 'Product description must be a string.',
            'description.max' => 'Product description must not exceed 2000 characters.',
            'price.required' => 'Product price is required.',
            'price.numeric' => 'Product price must be a number.',
            'price.min' => 'Product price must be at least 0.',
            'stock_quantity.integer' => 'Stock quantity must be an integer.',
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
