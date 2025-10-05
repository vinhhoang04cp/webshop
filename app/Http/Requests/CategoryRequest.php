<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

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
     */
    public function rules(): array
    {
        $categoryId = null;
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $categoryId = $this->route('id') ?? $this->route('category');
        }

        return [
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('categories')->ignore($categoryId),
            ],
            'description' => ['nullable', 'string'],
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
        parent::failedValidation($validator);
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages()
    {
        return [
            'name.required' => 'Tên danh mục không được để trống.',
            'name.max' => 'Tên danh mục không được vượt quá 150 ký tự.',
            'name.unique' => 'Tên danh mục đã tồn tại.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes()
    {
        return [
            'name' => 'tên danh mục',
            'description' => 'mô tả',
        ];
    }
}
