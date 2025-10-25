<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $couponId = $this->route('id');

        $rules = [
            'code' => [
                'required',
                'string',
                'max:50',
                'unique:coupons,code'.($couponId ? ','.$couponId.',coupon_id' : ''),
            ],
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'product_id' => 'nullable|exists:products,product_id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'boolean',
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Mã coupon là bắt buộc',
            'code.unique' => 'Mã coupon đã tồn tại',
            'code.max' => 'Mã coupon không được vượt quá 50 ký tự',
            'discount_type.required' => 'Loại giảm giá là bắt buộc',
            'discount_type.in' => 'Loại giảm giá phải là phần trăm hoặc cố định',
            'discount_value.required' => 'Giá trị giảm giá là bắt buộc',
            'discount_value.numeric' => 'Giá trị giảm giá phải là số',
            'discount_value.min' => 'Giá trị giảm giá phải lớn hơn hoặc bằng 0',
            'product_id.exists' => 'Sản phẩm không tồn tại',
            'start_date.required' => 'Ngày bắt đầu là bắt buộc',
            'start_date.date' => 'Ngày bắt đầu không hợp lệ',
            'end_date.required' => 'Ngày kết thúc là bắt buộc',
            'end_date.date' => 'Ngày kết thúc không hợp lệ',
            'end_date.after' => 'Ngày kết thúc phải sau ngày bắt đầu',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->discount_type === 'percentage' && $this->discount_value > 100) {
                $validator->errors()->add('discount_value', 'Giá trị phần trăm không được vượt quá 100%');
            }
        });
    }
}
