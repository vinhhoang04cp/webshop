<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CategoryRequest extends FormRequest
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
        $rules = [
            'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:1000',
        ];

        // Add unique validation rule for updates
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            // Try to get category ID from route parameters
            $categoryId = $this->route('id') ?? $this->route('category');
            $rules['name'] = 'required|string|max:150|unique:categories,name,'.$categoryId.',category_id';
        } else {
            $rules['name'] = 'required|string|max:150|unique:categories,name';
        }

        return $rules;
    }

    /**
     * Handle a failed validation attempt.
     *
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        // If this is an API request, return JSON response
        if ($this->expectsJson() || $this->is('api/*')) {
            throw new HttpResponseException(response()->json([
                'status' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422));
        }
        
        // For web requests, use the parent method (redirect back with errors)
        parent::failedValidation($validator);
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name.required' => 'Tên danh mục là bắt buộc.',
            'name.string' => 'Tên danh mục phải là chuỗi ký tự.',
            'name.max' => 'Tên danh mục không được vượt quá 150 ký tự.',
            'name.unique' => 'Tên danh mục này đã tồn tại. Vui lòng chọn tên khác.',
            'description.string' => 'Mô tả phải là chuỗi ký tự.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'name' => 'tên danh mục',
            'description' => 'mô tả',
        ];
    }
}
