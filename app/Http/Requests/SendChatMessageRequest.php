<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SendChatMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = Auth::user();
        $userId = $this->route('userId'); // Lấy userId từ route parameter

        // Admin/Manager có thể gửi tin nhắn cho bất kỳ ai
        if ($user->canAccessDashboard()) {
            return true;
        }

        // Customer chỉ có thể gửi tin nhắn vào phòng của chính mình
        return $user->id == $userId;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'message' => [
                'required',
                'string',
                'max:5000', // Giới hạn độ dài tin nhắn
                'min:1',
            ],
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
            'message.required' => 'Tin nhắn không được để trống.',
            'message.string' => 'Tin nhắn phải là chuỗi ký tự.',
            'message.max' => 'Tin nhắn không được vượt quá 5000 ký tự.',
            'message.min' => 'Tin nhắn phải có ít nhất 1 ký tự.',
        ];
    }

    /**
     * Handle a failed authorization attempt.
     */
    protected function failedAuthorization()
    {
        throw new \Illuminate\Auth\Access\AuthorizationException(
            'Bạn không có quyền gửi tin nhắn vào phòng chat này.'
        );
    }
}
