<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Hiển thị form đăng nhập
     *
     * Chức năng: Hiển thị giao diện form đăng nhập cho người dùng
     * Hoạt động:
     * - Kiểm tra xem người dùng đã đăng nhập chưa
     * - Nếu đã đăng nhập, chuyển hướng về trang dashboard
     * - Nếu chưa đăng nhập, hiển thị view form đăng nhập
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function showLogin() // Ham hien thi form login - tra ve view login.blade.php
    {
        if (Auth::check()) { // Auth - kiem tra user da dang nhap chua
            // Neu da dang nhap thi chuyen huong ve dashboard
            return redirect()->route('dashboard');
        }

        // Neu chua dang nhap thi hien thi form login
        return view('auth.login');
    }

    /**
     * Hiển thị form đăng ký
     *
     * Chức năng: Hiển thị giao diện form đăng ký tài khoản mới
     * Hoạt động:
     * - Kiểm tra trạng thái đăng nhập của người dùng
     * - Nếu đã đăng nhập, chuyển hướng về dashboard
     * - Nếu chưa đăng nhập, hiển thị view form đăng ký
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
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
     * Xử lý đăng nhập qua web form
     *
     * Chức năng: Xác thực thông tin đăng nhập và cho phép người dùng truy cập hệ thống
     * Hoạt động:
     * - Validate dữ liệu đầu vào (email, password)
     * - Tìm kiếm user trong database theo email
     * - So sánh mật khẩu đã mã hóa với password người dùng nhập
     * - Nếu thông tin chính xác, thực hiện đăng nhập
     * - Chuyển hướng user dựa trên vai trò (admin/manager -> dashboard, customer -> products, other -> home)
     * - Nếu thông tin sai, trả về lỗi và giữ lại dữ liệu đã nhập
     *
     * @param  \Illuminate\Http\Request  $request  Dữ liệu từ form đăng nhập
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'email' => 'Thông tin đăng nhập không chính xác.',
            ])->withInput();
        }

        // Đăng nhập user
        Auth::login($user); // Auth::login de dang nhap user

        // Chuyển hướng dựa trên vai trò của user
        if ($user->hasRole('admin') || $user->hasRole('manager')) { // neu nguoi dung co role admin hoac manager
            // Admin và Manager vào dashboard
            return redirect()->route('dashboard')->with('success', 'Đăng nhập thành công!'); // Chuyen huong ve trang dashboard voi thong bao thanh cong
        } elseif ($user->hasRole('customer')) { // neu nguoi dung co role customer
            // Customer vào trang sản phẩm
            return redirect()->route('products.index')->with('success', 'Đăng nhập thành công!'); // Chuyen huong ve trang products.index voi thong bao thanh cong
        } else { // neu nguoi dung khong co role nao
            // User thông thường vào trang chủ
            return redirect()->route('home')->with('success', 'Đăng nhập thành công!'); // Chuyen huong ve trang home voi thong bao thanh cong
        }
    }

    /**
     * Xử lý đăng ký tài khoản mới qua web form
     *
     * Chức năng: Tạo tài khoản người dùng mới trong hệ thống
     * Hoạt động:
     * - Validate dữ liệu đầu vào (name, email, password, phone, address)
     * - Kiểm tra email đã tồn tại chưa (unique)
     * - Mã hóa mật khẩu trước khi lưu vào database
     * - Tạo user mới với Eloquent
     * - Tự động gán role 'customer' cho user mới
     * - Đăng nhập user ngay sau khi đăng ký thành công
     * - Chuyển hướng đến trang danh sách sản phẩm
     * - Tạo giỏ hàng trống cho user mới
     *
     * @param  \Illuminate\Http\Request  $request  Dữ liệu từ form đăng ký
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name, // Lay name tu form
            'email' => $request->email, // Lay email tu form
            'password' => Hash::make($request->password), // Ma hoa password truoc khi luu vao database
            'phone' => $request->phone, // Lay phone tu form
            'address' => $request->address, // Lay address tu form
        ]);

        // Tự động gán role customer cho user mới đăng ký
        try {
            DB::beginTransaction(); // Bat dau giao dich database

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
            // Log lỗi nhưng vẫn cho user đăng ký thành công
        }

        return redirect()->route('login')->with('success', 'Đăng ký thành công! Vui lòng đăng nhập để tiếp tục.'); // Chuyen huong ve trang login voi thong bao thanh cong
    }

    /**
     * Đăng xuất người dùng
     *
     * Chức năng: Kết thúc phiên đăng nhập của người dùng hiện tại
     * Hoạt động:
     * - Hủy session đăng nhập của user
     * - Xóa thông tin authentication
     * - Chuyển hướng về trang đăng nhập
     * - Hiển thị thông báo đăng xuất thành công
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout()
    {
        Auth::logout(); // Ham Auth::logout de dang xuat user hien tai

        return redirect()->route('login')->with('success', 'Đăng xuất thành công!'); // Chuyen huong ve trang login voi thong bao thanh cong
    }

    /**
     * Hiển thị trang dashboard cho admin và manager
     *
     * Chức năng: Hiển thị trang quản trị với thống kê tổng quan
     * Hoạt động:
     * - Lấy thông tin user đang đăng nhập
     * - Kiểm tra quyền truy cập (phải có role admin hoặc manager)
     * - Nếu không có quyền, đăng xuất và chuyển về trang login
     * - Tính toán các thống kê: tổng sản phẩm, đơn hàng, người dùng, doanh thu
     * - Lấy danh sách đơn hàng gần nhất
     * - Hiển thị view dashboard với dữ liệu thống kê
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function dashboard()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user(); // Lấy user hiện tại

        // Check xem user có role admin hoặc manager không
        if (! $user->hasRole('admin') && ! $user->hasRole('manager')) { // Neu user khong co role admin va manager
            Auth::logout(); // Dang xuat user

            return redirect()->route('login')->withErrors([ // Chuyen huong ve trang login voi loi
                'email' => 'Bạn không có quyền truy cập.', // Thong bao loi
            ]);
        }

        try {
            // truy van truc tiep vao db qua model bang eloquent
            // Dem so luong products, orders, users
            $productsCount = \App\Models\Product::count(); // $productsCount goi den model Product de dem so luong products trong bang products
            $ordersCount = \App\Models\Order::count(); // $ordersCount goi den model Order de dem so luong orders trong bang orders
            $usersCount = \App\Models\User::count(); // $usersCount goi den model User de dem so luong users trong bang users

            // $totalRevenue tinh tong doanh thu tu cac orders khong bi huy
            $totalRevenue = \App\Models\Order::where('status', '!=', 'cancelled')->sum('total_amount'); // $totalRevenue goi den model Order de tinh tong cot total_amount trong bang orders voi dieu kien status khac cancelled

            // Lấy 5 orders gần nhất với user relationship
            $recentOrders = \App\Models\Order::with('user') // with('user') de load quan he user voi order
                ->orderBy('order_date', 'desc') // Sap xep giam dan theo order_date
                ->limit(5) // Gioi han 5 order
                ->get() // Lay du lieu
                ->toArray(); // Chuyen doi du lieu sang mang de truyen vao view

            return view('dashboard.index', compact( // tra ve view dashboard.index voi cac bien duoc truyen vao bang compact
                'user', // user hien tai
                'productsCount', // so luong products
                'ordersCount', // so luong orders
                'usersCount', // so luong users
                'totalRevenue', // tong doanh thu
                'recentOrders' // 5 orders gan nhat
            ));
        } catch (\Exception $e) {
            // Fallback to zero values nếu có lỗi
            return view('dashboard.index', [ // tra ve view dashboard.index voi cac bien duoc truyen vao bang mang
                'user' => $user, // user hien tai
                'productsCount' => 0, // so luong products bang 0
                'ordersCount' => 0, // so luong orders bang 0
                'usersCount' => 0, // so luong users bang 0
                'totalRevenue' => 0, // tong doanh thu bang 0
                'recentOrders' => [], // 5 orders gan nhat bang mang rong
                'error' => 'Không thể tải dữ liệu dashboard: '.$e->getMessage(), // Thong bao loi
            ]);
        }
    }
}
