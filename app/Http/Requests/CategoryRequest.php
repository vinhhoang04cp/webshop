<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

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

    public function messages()
    {
        return [
            'name.required' => 'Tên danh mục không được để trống.',
            'name.max' => 'Tên danh mục không được vượt quá 150 ký tự.',
            'name.unique' => 'Tên danh mục đã tồn tại.',
        ];
    }

    public function attributes()
    {
        return [
            'name' => 'tên danh mục',
            'description' => 'mô tả',
        ];
    }
}
