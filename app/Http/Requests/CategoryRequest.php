<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator; // Interface để làm việc với validator instance
use Illuminate\Foundation\Http\FormRequest;   // Lớp cơ sở cho custom Request (có sẵn trong Laravel)
use Illuminate\Http\Exceptions\HttpResponseException; // Exception để trả về JSON khi validation fail trong API

class CategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Trả về true nghĩa là cho phép mọi user đều được thực hiện request này.
        // Nếu bạn muốn phân quyền (ví dụ: chỉ admin mới được tạo/cập nhật),
        // có thể viết logic check role ở đây.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Quy định các rule chung cho create/update.
        // name: bắt buộc, là chuỗi, tối đa 150 ký tự.
        // description: có thể null, nếu có phải là chuỗi, tối đa 1000 ký tự.
        $rules = [
            'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:1000',
        ];

        // Nếu là request UPDATE (PUT hoặc PATCH),
        // thì cần thêm điều kiện unique nhưng bỏ qua ID hiện tại.
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            // Lấy ID từ route (có thể đặt tên param khác nhau: id hoặc category).
            $categoryId = $this->route('id') ?? $this->route('category');

            // Rule unique:
            // - Bảng categories
            // - Cột name phải unique
            // - Bỏ qua record có id = $categoryId (để tránh báo trùng chính nó)
            // - 'category_id' trong đoạn unique: ... là tên cột khoá chính trong DB (tuỳ DB bạn đặt)
            $rules['name'] = 'required|string|max:150|unique:categories,name,'.$categoryId.',category_id';
        } else {
            // Nếu là request CREATE thì chỉ cần unique bình thường.
            $rules['name'] = 'required|string|max:150|unique:categories,name';
        }

        return $rules;
    }

    /**
     * Handle a failed validation attempt.
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        // Nếu request là API (client mong đợi JSON),
        // hoặc URL thuộc nhóm api/* thì trả về JSON lỗi.
        if ($this->expectsJson() || $this->is('api/*')) {
            throw new HttpResponseException(response()->json([
                'status' => false,
                'message' => 'Validation errors', // Thông điệp chung
                'errors' => $validator->errors(), // Chi tiết từng field lỗi
            ], 422)); // 422 Unprocessable Entity: chuẩn REST cho lỗi validate
        }

        // Nếu là request web (form submit),
        // gọi lại method gốc của FormRequest để redirect về form + kèm error bag.
        parent::failedValidation($validator);
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        // Tuỳ chỉnh thông báo lỗi cho từng rule cụ thể.
        // Giúp thân thiện với người dùng thay vì thông báo mặc định của Laravel.
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
        // Định nghĩa lại tên field khi hiển thị lỗi,
        // ví dụ thay vì "The name field is required" -> "Tên danh mục là bắt buộc".
        return [
            'name' => 'tên danh mục',
            'description' => 'mô tả',
        ];
    }
}
