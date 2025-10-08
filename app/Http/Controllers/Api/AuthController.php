<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * AuthController - xử lý xác thực user qua API
 */
class AuthController extends Controller
{
    // Ham dang nhap va tao token
    public function login(Request $request)
    {
        // Validated du lieu dau vao
        // Validate email va password tu request
        // Neu khong hop le, Laravel tu dong tra ve loi 422 Unprocessable Entity
        // Rule:
        // 'required' - bat buoc phai co
        // 'email' - phai dung dinh dang email
        // 'string' - phai la kieu chuoi
        // 'min:8' - do dai toi thieu 8 ky tu
        // 'confirmed' - phai co field password_confirmation giong password
        // 'unique:users' - email phai duy nhat trong bang users
        // 'max:255' - do dai toi da 255 ky tu
        // 'nullable' - field co the khong can phai co (khong bat buoc)
        // 'sometimes' - neu field co trong request thi moi validate
        $request->validate([
            'email' => 'required|email',    // Email bắt buộc và phải đúng định dạng email
            'password' => 'required',       // Password bắt buộc (không có yêu cầu định dạng cụ thể)
        ]);

        // $user la bien chua thong tin user tim duoc
        $user = User::where('email', $request->email)->first(); // Tim user trong DB theo email

        // Kiem tra neu khong co user hoac password khong dung, user khong hop le
        if (! $user || ! Hash::check($request->password, $user->password)) {
            // Tra ve thong bao loi
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'], // Thông báo lỗi cho field email
            ]);
        }

        // Xoa tat ca token cu (neu co)
        // De dam bao moi lan login chi co 1 token hop le
        // tokens() - lay tat ca personal access tokens cua user
        // delete() - xoa tat ca tokens cu
        $user->tokens()->delete();

        // Tao token moi cho user
        $token = $user->createToken('api-token')->plainTextToken;

        // Trả về response JSON thành công
        return response()->json([
            'status' => true,                // Trạng thái thành công
            'message' => 'Login successful', // Thông báo
            'user' => $user,                 // Thông tin user (sẽ tự động loại bỏ password nhờ $hidden)
            'token' => $token,               // Token để client sử dụng cho các request tiếp theo
        ], 200); // HTTP status 200 OK
    }

    /**
     * Dang ky user moi
     */
    public function register(Request $request)
    {
        // Validate du lieu dau vao truyen tu client
        $request->validate([
            'name' => 'required|string|max:255',           // Tên bắt buộc, string, tối đa 255 ký tự
            'email' => 'required|string|email|max:255|unique:users', // Email bắt buộc, unique trong bảng users
            'password' => 'required|string|min:8|confirmed', // Password tối thiểu 8 ký tự, cần confirmation
            'phone' => 'nullable|string|max:20',           // Phone tùy chọn (nullable), tối đa 20 ký tự
            'address' => 'nullable|string|max:500',        // Address tùy chọn, tối đa 500 ký tự
        ]);

        // Tao moi user trong database bang ham create()
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // Hash password trước khi lưu vào DB
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        // Tao token moi cho user
        $token = $user->createToken('api-token')->plainTextToken;

        // Tra ve response thanh cong voi thong tin user va token
        return response()->json([
            'status' => true,                     // Trạng thái thành công
            'message' => 'Registration successful', // Thông báo
            'user' => $user,                      // Thông tin user vừa tạo
            'token' => $token,                    // Token để client có thể sử dụng ngay
        ], 201); // HTTP status 201 Created - tài nguyên mới được tạo
    }

    /**
     * Ham dang xuat va xoa token
     */
    public function logout(Request $request)
    {
        // $request là request hiện tại có chứa token trong header Authorization
        // $request->user() - lấy user đã authenticated từ middleware auth:sanctum
        // currentAccessToken() - lấy token hiện tại đang được sử dụng cho request này
        // delete() - xóa token khỏi database
        $request->user()->currentAccessToken()->delete();

        // Bước 2: Trả về response xác nhận đăng xuất thành công
        return response()->json([
            'status' => true,               // Trạng thái thành công
            'message' => 'Logout successful', // Thông báo đăng xuất thành công
        ], 200); // HTTP status 200 OK
    }
}
