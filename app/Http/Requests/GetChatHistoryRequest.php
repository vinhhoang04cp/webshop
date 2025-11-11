<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class GetChatHistoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = Auth::user();
        $userId = $this->route('userId'); // Lấy userId từ route parameter

        // Admin/Manager có thể xem lịch sử chat của bất kỳ ai
        if ($user->canAccessDashboard()) {
            return true;
        }

        // Customer chỉ có thể xem lịch sử chat của chính mình
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
            'limit' => 'sometimes|integer|min:1|max:100', // Optional: giới hạn số tin nhắn trả về
            'offset' => 'sometimes|integer|min:0', // Optional: phân trang
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
            'limit.integer' => 'Limit phải là số nguyên.',
            'limit.min' => 'Limit phải lớn hơn hoặc bằng 1.',
            'limit.max' => 'Limit không được vượt quá 100.',
            'offset.integer' => 'Offset phải là số nguyên.',
            'offset.min' => 'Offset phải lớn hơn hoặc bằng 0.',
        ];
    }

    /**
     * Handle a failed authorization attempt.
     */
    protected function failedAuthorization()
    {
        throw new \Illuminate\Auth\Access\AuthorizationException(
            'Bạn không có quyền xem lịch sử chat này.'
        );
    }

    /**
     * Get validated data with defaults.
     */
    public function validatedWithDefaults(): array
    {
        $validated = $this->validated();

        return [
            'limit' => $validated['limit'] ?? 50, // Mặc định 50 tin nhắn
            'offset' => $validated['offset'] ?? 0,
        ];
    }
}
