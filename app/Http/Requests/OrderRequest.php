<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

class OrderRequest extends FormRequest
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
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'order_date' => ['required', 'date'],
            // total_amount sẽ được tính tự động từ items
            // 'items' la mot mang chua cac san pham trong don hang, lay tu request
            'items' => ['required', 'array', 'min:1'],
            // 'items.*.product_id' => ['required', 'integer', 'exists:products,id'] lay tu request
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
            'order_date.date' => 'Order date must be a valid date',
            // total_amount messages removed as it's calculated automatically
            'items.required' => 'Order items are required',
            'items.array' => 'Order items must be an array',
            'items.min' => 'At least one order item is required',
            'items.*.product_id.required' => 'Product ID is required for each item',
            'items.*.product_id.exists' => 'Product not found for each item',
            'items.*.quantity.required' => 'Quantity is required for each item',
            'items.*.quantity.integer' => 'Quantity must be an integer for each item',
            'items.*.quantity.min' => 'Quantity must be at least 1 for each item',
            // Đã loại bỏ price validation messages vì sẽ lấy từ database
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
            ], 422));
        }
        parent::failedValidation($validator);
    }
}
