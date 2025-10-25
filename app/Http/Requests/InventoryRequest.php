<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InventoryRequest extends FormRequest
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
            'stock_in' => 'required|integer|min:0',
            'stock_out' => 'required|integer|min:0',
            'current_stock' => 'required|integer|min:0',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'stock_in.required' => 'Số lượng nhập kho là bắt buộc',
            'stock_in.integer' => 'Số lượng nhập kho phải là số nguyên',
            'stock_in.min' => 'Số lượng nhập kho phải lớn hơn hoặc bằng 0',
            'stock_out.required' => 'Số lượng xuất kho là bắt buộc',
            'stock_out.integer' => 'Số lượng xuất kho phải là số nguyên',
            'stock_out.min' => 'Số lượng xuất kho phải lớn hơn hoặc bằng 0',
            'current_stock.required' => 'Tồn kho hiện tại là bắt buộc',
            'current_stock.integer' => 'Tồn kho hiện tại phải là số nguyên',
            'current_stock.min' => 'Tồn kho hiện tại phải lớn hơn hoặc bằng 0',
        ];
    }
}
