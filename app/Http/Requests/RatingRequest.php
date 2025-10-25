<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RatingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'rating.required' => 'Bạn phải chọn số sao đánh giá',
            'rating.integer' => 'Số sao phải là số nguyên',
            'rating.min' => 'Số sao tối thiểu là 1',
            'rating.max' => 'Số sao tối đa là 5',
            'review.max' => 'Nhận xét không được quá 1000 ký tự',
        ];
    }
}
