<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException; // HttpResponseException la thu vien de xu ly loi validation thanh JSON
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class CategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool  // Tra ve true de cho phep tat ca nguoi dung su dung request nay
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array  // Tra ve mang chua cac quy tac validation
    {
        if ($this->isMethod('put') || $this->isMethod('patch')) { // Neu la PUT hoac PATCH (update)
            $categoryId = $this->route('id') ?? $this->route('category'); // Lay ID danh muc tu route parameter
            $this->merge(['category_id' => $categoryId]); // Them category_id vao request de su dung trong rule unique
        }

        return [
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('categories')->ignore($this->category_id), // Kiem tra unique trong bang categories, bo qua ban ghi hien tai khi update
            ],
            'description' => ['nullable', 'string'],
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        if ($this->expectsJson() || $this->is('api/*')) {  // $this->is('api/*') kiem tra xem URL co bat dau bang 'api/' khong
            throw new HttpResponseException(response()->json([  // throw new HttpResponseException lam cho viec tra ve loi validation thanh JSON
                'status' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422));
        }

        parent::failedValidation($validator);
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return [
                'name.required' => 'Tên danh mục không được để trống.',
                'name.string' => 'Tên danh mục phải là chuỗi ký tự.',
                'name.max' => 'Tên danh mục không được vượt quá 150 ký tự.',
                'name.unique' => 'Tên danh mục đã tồn tại.',
                'description.string' => 'Mô tả danh mục phải là chuỗi ký tự.',
            ];
        } else {
            return [
                'name.required' => 'Tên danh mục không được để trống.',
                'name.string' => 'Tên danh mục phải là chuỗi ký tự.',
                'name.max' => 'Tên danh mục không được vượt quá 150 ký tự.',
                'name.unique' => 'Tên danh mục đã tồn tại.',
                'description.string' => 'Mô tả danh mục phải là chuỗi ký tự.',
            ];
        }
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
