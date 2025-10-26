<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    protected $authService; // Dịch vụ xác thực

    // Khởi tạo với dịch vụ xác thực
    public function __construct(AuthService $authService) // __construct la phương thức khởi tạo
    {
        $this->authService = $authService;
    }

    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        } else {
            return view('auth.register');
        }
    }

    public function login(LoginRequest $request)
    {
        $user = $this->authService->authenticate($request->email, $request->password);

        if (! $user) {
            return back()->withErrors([
                'email' => 'Thông tin đăng nhập không chính xác.',
            ])->withInput();
        }

        Auth::login($user);

        $redirectRoute = $this->authService->getRedirectRoute($user);

        return redirect()->route($redirectRoute)->with('success', 'Đăng nhập thành công!');
    }

    public function register(RegisterRequest $request)
    {
        try {
            $this->authService->register($request->validated());

            return redirect()->route('login')->with('success', 'Đăng ký thành công! Vui lòng đăng nhập để tiếp tục.');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra: '.$e->getMessage());
        }
    }

    public function logout()
    {
        Auth::logout();

        return redirect()->route('login')->with('success', 'Đăng xuất thành công!');
    }

    public function dashboard()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (! $this->authService->canAccessDashboard($user)) {
            Auth::logout();

            return redirect()->route('login')->withErrors([
                'email' => 'Bạn không có quyền truy cập.',
            ]);
        }

        $dashboardData = $this->authService->getDashboardData();

        return view('dashboard.index', array_merge(
            ['user' => $user],
            [
                'productsCount' => $dashboardData['products_count'],
                'ordersCount' => $dashboardData['orders_count'],
                'usersCount' => $dashboardData['users_count'],
                'totalRevenue' => $dashboardData['total_revenue'],
                'recentOrders' => $dashboardData['recent_orders'],
            ],
            isset($dashboardData['error']) ? ['error' => $dashboardData['error']] : []
        ));
    }
}
