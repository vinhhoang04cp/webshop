<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shipping_name' => 'required|string|max:255',
            'shipping_phone' => 'required|string|max:20',
            'shipping_address' => 'required|string|max:1000',
            'note' => 'nullable|string|max:500',
            'coupon_code' => 'nullable|string|max:50',
            'payment_method' => 'required|in:cod,vnpay',
        ];
    }

    public function messages(): array
    {
        return [
            'shipping_name.required' => 'Vui lòng nhập họ và tên người nhận',
            'shipping_name.max' => 'Họ tên không được vượt quá 255 ký tự',
            'shipping_phone.required' => 'Vui lòng nhập số điện thoại',
            'shipping_phone.max' => 'Số điện thoại không được vượt quá 20 ký tự',
            'shipping_address.required' => 'Vui lòng nhập địa chỉ giao hàng',
            'shipping_address.max' => 'Địa chỉ không được vượt quá 1000 ký tự',
            'note.max' => 'Ghi chú không được vượt quá 500 ký tự',
            'coupon_code.max' => 'Mã giảm giá không được vượt quá 50 ký tự',
            'payment_method.required' => 'Vui lòng chọn phương thức thanh toán',
            'payment_method.in' => 'Phương thức thanh toán không hợp lệ',
        ];
    }
}
