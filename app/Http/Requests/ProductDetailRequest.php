<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProductDetailRequest extends FormRequest
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
        return [
            'product_id' => ['required', 'exists:products,product_id'],
            'color' => ['nullable', 'string', 'max:50'],
            'storage' => ['nullable', 'string', 'max:20'], // 128GB, 256GB, 512GB
            'ram' => ['nullable', 'string', 'max:20'], // 4GB, 8GB, 12GB, 16GB
            'screen_size' => ['nullable', 'string', 'max:20'], // 6.1 inch, 6.7 inch
            'chip' => ['nullable', 'string', 'max:100'], // Apple A17 Pro, Snapdragon 8 Gen 3
            'battery' => ['nullable', 'string', 'max:50'], // 5000 mAh, 4422 mAh
            'camera_main' => ['nullable', 'string', 'max:100'], // 48MP Main + 12MP Ultra Wide
            'camera_front' => ['nullable', 'string', 'max:100'], // 12MP, 32MP
            'os' => ['nullable', 'string', 'max:50'], // iOS 17, Android 14
            'special_features' => ['nullable', 'string'], // Dynamic Island, Galaxy AI, etc.
        ];
    }

    public function messages()
    {
        return [
            'product_id.required' => 'Product ID is required',
            'product_id.exists' => 'Product not found',
            'color.max' => 'Color must not exceed 50 characters',
            'storage.max' => 'Storage must not exceed 20 characters',
            'ram.max' => 'RAM must not exceed 20 characters',
            'screen_size.max' => 'Screen size must not exceed 20 characters',
            'chip.max' => 'Chip must not exceed 100 characters',
            'battery.max' => 'Battery must not exceed 50 characters',
            'camera_main.max' => 'Main camera must not exceed 100 characters',
            'camera_front.max' => 'Front camera must not exceed 100 characters',
            'os.max' => 'Operating system must not exceed 50 characters',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'errors' => $validator->errors(),
        ], 422));
    }
}
