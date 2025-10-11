<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Thu vien Auth dung de xac thuc
use Illuminate\Support\Facades\Hash; // Thu vien Hash dung de ma hoa password

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
        
        // Neu chua dang nhap thi hien thi form login
        return view('auth.login');
    }

    /**
     * Hiển thị form register
     */
    public function showRegister() // Ham hien thi form register
    {
        if (Auth::check()) {
            return redirect()->route('dashboard'); // Neu da dang nhap thi chuyen huong ve dashboard
        } else {
            return view('auth.register'); // Neu chua dang nhap thi hien thi form register
        }
    }

    /**
     * Xử lý login thông qua web form
     */
    public function login(Request $request) // (Request $request) la tham so truyen vao ham , duoc gui tu form login
    {
        $request->validate([ // Validate du lieu dau vao
            'email' => 'required|email', // email bat buoc va phai dung dinh dang email
            'password' => 'required', // password bat buoc
        ]);

        // Tìm user theo email
        $user = User::where('email', $request->email)->first(); // User lay tu model User, tim user dau tien co email giong voi email tu form

        // Kiểm tra credentials
        if (! $user || ! Hash::check($request->password, $user->password)) { // !user neu khong tim thay user hoac password khong dung, Hash::check de kiem tra password neu khong trung
            return back()->withErrors([ // Quay lai trang truoc do voi loi
                'email' => 'Thông tin đăng nhập không chính xác.', // Thong bao loi
            ])->withInput(); // withInput de giu lai du lieu nguoi dung da nhap
        }

        // Kiểm tra role - chỉ cho phép admin và manager
        if (! $user->hasRole('admin') && ! $user->hasRole('manager')) { // ! $user->hasRole neu user khong co role admin va manager
            return back()->withErrors([ // Quay lai trang truoc do voi loi
                'email' => 'Bạn không có quyền truy cập vào hệ thống quản lý.', //
            ])->withInput(); // withInput de giu lai du lieu nguoi dung da nhap
        }

        // Đăng nhập user
        Auth::login($user); // Auth::login de dang nhap user

        return redirect()->route('dashboard')->with('success', 'Đăng nhập thành công!'); // Chuyen huong ve dashboard voi thong bao thanh cong
    }

    /**
     * Xử lý register thông qua web form
     */
    public function register(Request $request) // (request $request) la tham so truyen vao ham , duoc gui tu form register
    {
        $request->validate([ // Validate du lieu dau vao tu form register
            'name' => 'required|string|max:255', // name bat buoc, kieu chuoi, do dai toi da 255 ky tu
            'email' => 'required|string|email|max:255|unique:users', // email bat buoc, kieu chuoi, dung dinh dang email, do dai toi da 255 ky tu, phai duy nhat trong bang users
            'password' => 'required|string|min:8|confirmed', // password bat buoc, kieu chuoi, do dai toi thieu 8 ky tu, phai giong voi password_confirmation
            'phone' => 'nullable|string|max:20', // phone khong bat buoc, kieu chuoi, do dai toi da 20 ky tu
            'address' => 'nullable|string|max:500', // address khong bat buoc, kieu chuoi, do dai toi da 500 ky tu
        ]);

        // Tạo user mới
        $user = User::create([
            'name' => $request->name, // Lay name tu form
            'email' => $request->email, // Lay email tu form
            'password' => Hash::make($request->password), // Ma hoa password truoc khi luu vao database
            'phone' => $request->phone, // Lay phone tu form
            'address' => $request->address, // Lay address tu form
        ]);

        // Note: User mới tạo sẽ không có role admin/manager
        // Cần admin phân quyền sau

        return redirect()->route('login')->with('success', 'Đăng ký thành công! Vui lòng liên hệ admin để được phân quyền truy cập.'); // Chuyen huong ve trang login voi thong bao thanh cong
    }

    /**
     * Đăng xuất
     */
    public function logout()
    {
        Auth::logout(); // Ham Auth::logout de dang xuat user hien tai

        return redirect()->route('login')->with('success', 'Đăng xuất thành công!'); // Chuyen huong ve trang login voi thong bao thanh cong
    }

    /**
     * Hiển thị dashboard
     */
    public function dashboard()
    {
        $user = Auth::user(); // Lấy user hiện tại

        // Double check role
        if (! $user->hasRole('admin') && ! $user->hasRole('manager')) { // Neu user khong co role admin va manager
            Auth::logout(); // Dang xuat user

            return redirect()->route('login')->withErrors([
                'email' => 'Bạn không có quyền truy cập.',
            ]);
        }

        try {
            // truy van truc tiep vao db qua model bang eloquent
            // Dem so luong products, orders, users
            $productsCount = \App\Models\Product::count(); // $productsCount dem so luong products trong bang products
            $ordersCount = \App\Models\Order::count(); // $ordersCount dem so luong orders trong bang orders
            $usersCount = \App\Models\User::count(); // $usersCount dem so luong users trong bang users

            // Tính tổng doanh thu từ orders
            $totalRevenue = \App\Models\Order::where('status', '!=', 'cancelled')->sum('total_amount');

            // Lấy 5 orders gần nhất với user relationship
            $recentOrders = \App\Models\Order::with('user')
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
            // Fallback to zero values nếu có lỗi
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
