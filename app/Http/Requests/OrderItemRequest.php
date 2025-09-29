<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

class OrderItemRequest extends FormRequest
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
            'order_id' => ['required', 'integer', 'exists:orders,order_id'],
            'product_id' => ['required', 'integer', 'exists:products,product_id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'numeric', 'min:0'], // Gia phai lon hon hoac bang 0

        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        if ($this->expectsJson() || $this->is('api/*')) {  // $this->is('api/*') kiem tra xem URL co bat dau bang 'api/' khong
            throw new HttpResponseException(response()->json([  // throw new HttpResponseException lam cho viec tra ve loi validation thanh JSON
                'status' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422));   // Tra ve ma loi 422 Unprocessable Entity khi validation khong thanh cong
        }
        parent::failedValidation($validator);   // Goi ham failedValidation cua lop cha de xu ly mac dinh
    }

    public function messages()
    {
        return [
            'order_id.required' => 'Order ID is required',
            'order_id.integer' => 'Order ID must be an integer',
            'order_id.exists' => 'Order not found',
            'product_id.required' => 'Product ID is required',
            'product_id.integer' => 'Product ID must be an integer',
            'product_id.exists' => 'Product not found',
            'quantity.required' => 'Quantity is required',
            'quantity.integer' => 'Quantity must be an integer',
            'quantity.min' => 'Quantity must be at least 1',
            'price.required' => 'Price is required',
            'price.numeric' => 'Price must be a number',
            'price.min' => 'Price must be at least 0', // Thong bao neu gia nho hon 0
        ];
    }
}
