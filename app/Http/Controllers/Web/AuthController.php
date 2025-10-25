<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Order;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
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
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'email' => 'Thông tin đăng nhập không chính xác.',
            ])->withInput();
        }

        Auth::login($user);

        if ($user->hasRole('admin') || $user->hasRole('manager')) {
            return redirect()->route('dashboard')->with('success', 'Đăng nhập thành công!');
        } elseif ($user->hasRole('customer')) {
            return redirect()->route('products.index')->with('success', 'Đăng nhập thành công!');
        } else {
            return redirect()->route('home')->with('success', 'Đăng nhập thành công!');
        }
    }

    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        try {
            DB::beginTransaction();

            $customerRole = Role::where('role_name', 'customer')->first();
            if ($customerRole) {
                UserRole::create([
                    'user_id' => $user->id,
                    'role_id' => $customerRole->role_id,
                    'assigned_at' => now(),
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
        }

        return redirect()->route('login')->with('success', 'Đăng ký thành công! Vui lòng đăng nhập để tiếp tục.');
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

        if (! $user->hasRole('admin') && ! $user->hasRole('manager')) {
            Auth::logout();

            return redirect()->route('login')->withErrors([
                'email' => 'Bạn không có quyền truy cập.',
            ]);
        }

        try {
            $productsCount = Product::count();
            $ordersCount = Order::count();
            $usersCount = User::count();
            $totalRevenue = Order::where('status', '!=', 'cancelled')->sum('total_amount');

            $recentOrders = Order::with('user')
                ->orderBy('order_date', 'desc')
                ->limit(5)
                ->get()
                ->toArray();

            return view('dashboard.index', compact(
                'user',
                'productsCount',
                'ordersCount',
                'usersCount',
                'totalRevenue',
                'recentOrders'
            ));
        } catch (\Exception $e) {
            return view('dashboard.index', [
                'user' => $user,
                'productsCount' => 0,
                'ordersCount' => 0,
                'usersCount' => 0,
                'totalRevenue' => 0,
                'recentOrders' => [],
                'error' => 'Không thể tải dữ liệu dashboard: '.$e->getMessage(),
            ]);
        }
    }
}
