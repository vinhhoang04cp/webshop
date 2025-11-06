<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id' => 'required|exists:orders,order_id',
        ];
    }

    public function messages(): array
    {
        return [
            'order_id.required' => 'Vui lòng cung cấp mã đơn hàng',
            'order_id.exists' => 'Đơn hàng không tồn tại',
        ];
    }
}
