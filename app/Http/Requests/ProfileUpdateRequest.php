<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập họ tên',
            'name.max' => 'Họ tên không được vượt quá 255 ký tự',
            'phone.max' => 'Số điện thoại không được vượt quá 20 ký tự',
            'address.max' => 'Địa chỉ không được vượt quá 500 ký tự',
            'avatar.image' => 'File phải là hình ảnh',
            'avatar.mimes' => 'Hình ảnh phải có định dạng: jpeg, png, jpg, gif',
            'avatar.max' => 'Kích thước hình ảnh không được vượt quá 2MB',
        ];
    }
}
