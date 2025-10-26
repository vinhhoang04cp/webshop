<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class InventoryAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'stock_in' => 'sometimes|integer|min:0',
            'stock_out' => 'sometimes|integer|min:0',
            'type' => 'required|in:in,out,adjust',
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Loại điều chỉnh là bắt buộc',
            'type.in' => 'Loại điều chỉnh phải là in, out hoặc adjust',
            'stock_in.integer' => 'Số lượng nhập phải là số nguyên',
            'stock_in.min' => 'Số lượng nhập phải lớn hơn hoặc bằng 0',
            'stock_out.integer' => 'Số lượng xuất phải là số nguyên',
            'stock_out.min' => 'Số lượng xuất phải lớn hơn hoặc bằng 0',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation error',
            'errors' => $validator->errors(),
        ], 422));
    }
}
