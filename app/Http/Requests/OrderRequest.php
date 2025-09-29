<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

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
        if ($this->isMethod('put') || $this->isMethod('patch')) { // Neu la PUT hoac PATCH (update)
            $orderId = $this->route('id') ?? $this->route('order'); // Lay ID don hang tu route parameter
            $this->merge(['order_id' => $orderId]); // Them order_id vao request de su dung trong rule unique
        }

        return [
            'user_id' => ['required', 'exists:users,id'], // Kiem tra user_id phai ton tai trong bang users
            'order_date' => ['required', 'date'],
            'status' => ['required', 'string', Rule::in(['pending', 'processing', 'completed', 'cancelled'])],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'], // items phai la mang, it nhat phai co 1 item
            'items.*.product_id' => ['required', 'exists:products,product_id'], // Kiem tra product_id phai ton tai trong bang products
            'items.*.quantity' => ['required', 'integer', 'min:1'], // quantity phai la so nguyen, it nhat la 1
            'items.*.price' => ['required', 'numeric', 'min:0'], // price phai la so, it nhat la 0
        ];
    }

    public function messages()
    {
        return [
            'user_id.required' => 'User ID is required.',
            'user_id.exists' => 'The specified user does not exist.',
            'order_date.required' => 'Order date is required.',
            'order_date.date' => 'Order date must be a valid date.',
            'status.required' => 'Order status is required.',
            'status.in' => 'Order status must be one of: pending, processing, completed, cancelled.',
            'total_amount.required' => 'Total amount is required.',
            'total_amount.numeric' => 'Total amount must be a number.',
            'total_amount.min' => 'Total amount must be at least 0.',
            'items.required' => 'Order items are required.',
            'items.array' => 'Order items must be an array.',
            'items.min' => 'At least one order item is required.',
            'items.*.product_id.required' => 'Product ID is required for each item.',
            'items.*.product_id.exists' => 'The specified product does not exist.',
            'items.*.quantity.required' => 'Quantity is required for each item.',
            'items.*.quantity.integer' => 'Quantity must be an integer for each item.',
            'items.*.quantity.min' => 'Quantity must be at least 1 for each item.',
            'items.*.price.required' => 'Price is required for each item.',
            'items.*.price.numeric' => 'Price must be a number for each item.',
            'items.*.price.min' => 'Price must be at least 0 for each item.',
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
