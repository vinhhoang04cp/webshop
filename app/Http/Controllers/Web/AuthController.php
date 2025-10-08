<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Hiển thị form login
     */
    public function showLogin() // Ham hien thi form login
    {
        if (Auth::check()) { // Auth - kiem tra user da dang nhap chua
            // Neu da dang nhap thi chuyen huong ve dashboard
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    /**
     * Hiển thị form register
     */
    public function showRegister() // Ham hien thi form register
    {
        if (Auth::check()) { // Auth - kiem tra user da dang nhap chua
            return redirect()->route('dashboard'); // Neu da dang nhap thi chuyen huong ve dashboard
        }
        return view('auth.register');
    }

    /**
     * Xử lý login thông qua web form
     */
    public function login(Request $request) //(Request $request) la tham so truyen vao ham , duoc gui tu form login
    {
        $request->validate([ // Validate du lieu dau vao
            'email' => 'required|email', // email bat buoc va phai dung dinh dang email
            'password' => 'required', // password bat buoc
        ]);

        // Tìm user theo email
        $user = User::where('email', $request->email)->first();

        // Kiểm tra credentials
        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'email' => 'Thông tin đăng nhập không chính xác.',
            ])->withInput();
        }

        // Kiểm tra role - chỉ cho phép admin và manager
        if (!$user->hasRole('admin') && !$user->hasRole('manager')) {
            return back()->withErrors([
                'email' => 'Bạn không có quyền truy cập vào hệ thống quản lý.',
            ])->withInput();
        }

        // Đăng nhập user
        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Đăng nhập thành công!');
    }

    /**
     * Xử lý register thông qua web form
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

        // Tạo user mới
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        // Note: User mới tạo sẽ không có role admin/manager
        // Cần admin phân quyền sau
        
        return redirect()->route('login')->with('success', 'Đăng ký thành công! Vui lòng liên hệ admin để được phân quyền truy cập.');
    }

    /**
     * Đăng xuất
     */
    public function logout()
    {
        Auth::logout();
        return redirect()->route('login')->with('success', 'Đăng xuất thành công!');
    }

    /**
     * Hiển thị dashboard
     */
    public function dashboard()
    {
        $user = Auth::user();
        
        // Double check role
        if (!$user->hasRole('admin') && !$user->hasRole('manager')) {
            Auth::logout();
            return redirect()->route('login')->withErrors([
                'email' => 'Bạn không có quyền truy cập.',
            ]);
        }

        // Statistics for dashboard
        $productsCount = \App\Models\Product::count();
        $ordersCount = \App\Models\Order::count();
        $usersCount = \App\Models\User::count();

        // Total revenue (sum of total_amount in orders). Use 0 if null
        $totalRevenue = \App\Models\Order::sum('total_amount') ?? 0;

        // Recent orders (latest 5)
        $recentOrders = \App\Models\Order::with('user')
            ->orderBy('order_date', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact(
            'user',
            'productsCount',
            'ordersCount',
            'usersCount',
            'totalRevenue',
            'recentOrders'
        ));
    }
}