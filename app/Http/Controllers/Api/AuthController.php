<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Đăng nhập và trả về token
     */
    public function login(Request $request) // Ham login
    {
        $request->validate([ // Xac thuc du lieu dau vao
            'email' => 'required|email', // email bat buoc va phai dung dinh dang email
            'password' => 'required', // password bat buoc (khong co yeu cau dinh dang)
        ]);

        $user = User::where('email', $request->email)->first(); // // $user la bien chua thong tin user tim thay theo email , first() lay ban ghi dau tien

        if (!$user || !Hash::check($request->password, $user->password)) { // Kiem tra neu khong tim thay user hoac mat khau khong dung
            throw ValidationException::withMessages([ // Neu khong dung thi tra ve loi xac thuc
                'email' => ['The provided credentials are incorrect.'], // Thong bao loi
            ]);
        }

        // Xóa tất cả token cũ của user
        $user->tokens()->delete(); 

        // Tạo token mới
        $token = $user->createToken('api-token')->plainTextToken; //

        return response()->json([
            'status' => true,
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token,
        ], 200);
    }

    /**
     * Đăng ký user mới
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        // Tạo token cho user mới
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Registration successful',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    /**
     * Đăng xuất và xóa token
     */
    public function logout(Request $request)
    {
        // Xóa token hiện tại
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Logout successful',
        ], 200);
    }
}