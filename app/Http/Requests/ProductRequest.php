<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
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
     */
    public function rules(): array
    {
        $productId = null;
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            // Lấy product_id từ route parameter
            $productId = $this->route('id') ?? $this->route('product');
        }

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'name')->ignore($productId, 'product_id'),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999999.99'],
            'stock_quantity' => ['required', 'integer', 'min:0', 'max:999999999'],
            'category_id' => ['required', 'exists:categories,category_id'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'image_url' => ['nullable', 'url', 'max:2048'],
            // ProductDetail fields
            'color' => ['nullable', 'string', 'max:100'],
            'storage' => ['nullable', 'string', 'max:100'],
            'ram' => ['nullable', 'string', 'max:100'],
            'screen_size' => ['nullable', 'string', 'max:100'],
            'chip' => ['nullable', 'string', 'max:100'],
            'battery' => ['nullable', 'string', 'max:100'],
            'camera_main' => ['nullable', 'string', 'max:100'],
            'camera_front' => ['nullable', 'string', 'max:100'],
            'os' => ['nullable', 'string', 'max:100'],
            'special_features' => ['nullable', 'string'],
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        if ($this->expectsJson() || $this->is('api/*')) {
            throw new HttpResponseException(response()->json([
                'status' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422));
        }
    }

    public function messages()
    {
        return [
            'name.required' => 'Tên sản phẩm không được để trống.',
            'name.max' => 'Tên sản phẩm không được vượt quá 255 ký tự.',
            'name.unique' => 'Tên sản phẩm đã tồn tại.',
            'description.max' => 'Mô tả sản phẩm không được vượt quá 2000 ký tự.',
            'price.required' => 'Giá sản phẩm không được để trống.',
            'price.numeric' => 'Giá sản phẩm phải là số.',
            'price.min' => 'Giá sản phẩm phải lớn hơn hoặc bằng 0.',
            'price.max' => 'Giá sản phẩm không được vượt quá 999,999,999.99.',
            'stock_quantity.required' => 'Số lượng tồn kho không được để trống.',
            'stock_quantity.integer' => 'Số lượng tồn kho phải là số nguyên.',
            'stock_quantity.min' => 'Số lượng tồn kho phải lớn hơn hoặc bằng 0.',
            'stock_quantity.max' => 'Số lượng tồn kho không được vượt quá 999,999,999.',
            'category_id.required' => 'Danh mục không được để trống.',
            'category_id.exists' => 'Danh mục đã chọn không tồn tại.',
            'image.image' => 'File phải là hình ảnh.',
            'image.mimes' => 'Hình ảnh phải có định dạng: jpeg, png, jpg, gif.',
            'image.max' => 'Kích thước hình ảnh không được vượt quá 2MB.',
            'image_url.url' => 'URL hình ảnh không hợp lệ.',
            'image_url.max' => 'URL hình ảnh không được vượt quá 2048 ký tự.',
            // ProductDetail messages
            'color.max' => 'Màu sắc không được vượt quá 100 ký tự.',
            'storage.max' => 'Bộ nhớ không được vượt quá 100 ký tự.',
            'ram.max' => 'RAM không được vượt quá 100 ký tự.',
            'screen_size.max' => 'Kích thước màn hình không được vượt quá 100 ký tự.',
            'chip.max' => 'Chip không được vượt quá 100 ký tự.',
            'battery.max' => 'Pin không được vượt quá 100 ký tự.',
            'camera_main.max' => 'Camera chính không được vượt quá 100 ký tự.',
            'camera_front.max' => 'Camera trước không được vượt quá 100 ký tự.',
            'os.max' => 'Hệ điều hành không được vượt quá 100 ký tự.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'tên sản phẩm',
            'description' => 'mô tả sản phẩm',
            'price' => 'giá sản phẩm',
            'stock_quantity' => 'số lượng tồn kho',
            'category_id' => 'danh mục',
            'image' => 'hình ảnh',
            'image_url' => 'URL hình ảnh',
            'color' => 'màu sắc',
            'storage' => 'bộ nhớ',
            'ram' => 'RAM',
            'screen_size' => 'kích thước màn hình',
            'chip' => 'chip',
            'battery' => 'pin',
            'camera_main' => 'camera chính',
            'camera_front' => 'camera trước',
            'os' => 'hệ điều hành',
            'special_features' => 'tính năng đặc biệt',
        ];
    }
}
