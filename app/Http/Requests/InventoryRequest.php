<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class InventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $inventoryId = $this->route('id') ?? $this->route('inventory');

        $rules = [
            'product_id' => 'required|exists:products,product_id',
            'stock_in' => 'required|integer|min:0',
            'stock_out' => 'integer|min:0',
            'current_stock' => 'integer|min:0',
        ];

        // For update operation
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['product_id'] = 'sometimes|exists:products,product_id|unique:inventory,product_id,'.$inventoryId.',inventory_id';
            $rules['stock_in'] = 'sometimes|integer|min:0';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'Mã sản phẩm là bắt buộc',
            'product_id.exists' => 'Sản phẩm không tồn tại',
            'product_id.unique' => 'Sản phẩm đã có trong kho',
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

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation error',
            'errors' => $validator->errors(),
        ], 422));
    }
}
