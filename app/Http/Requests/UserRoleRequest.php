<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRoleRequest extends FormRequest
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
        // Nếu là update (nhiều roles)
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return [
                'roles' => 'array',
                'roles.*' => 'exists:roles,role_id',
            ];
        }

        // Nếu là assign role (một role)
        return [
            'role_id' => 'required|exists:roles,role_id',
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
            'roles.array' => 'Danh sách vai trò phải là một mảng',
            'roles.*.exists' => 'Vai trò không tồn tại trong hệ thống',
            'role_id.required' => 'Vai trò là bắt buộc',
            'role_id.exists' => 'Vai trò không tồn tại trong hệ thống',
        ];
    }
}
