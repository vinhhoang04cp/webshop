<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InventoryAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'adjustment_type' => 'required|in:in,out',
            'quantity' => 'required|integer|min:1',
            'note' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'adjustment_type.required' => 'Loại điều chỉnh là bắt buộc',
            'adjustment_type.in' => 'Loại điều chỉnh phải là nhập kho hoặc xuất kho',
            'quantity.required' => 'Số lượng là bắt buộc',
            'quantity.integer' => 'Số lượng phải là số nguyên',
            'quantity.min' => 'Số lượng phải lớn hơn 0',
            'note.max' => 'Ghi chú không được vượt quá 500 ký tự',
        ];
    }
}
