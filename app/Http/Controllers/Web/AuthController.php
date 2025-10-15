<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Role; // Import model Role de truy cap bang roles
use App\Models\User; // Import model User de truy cap bang users
use App\Models\UserRole; // Import model UserRole de tu dong gan role cho user moi dang ky
use Illuminate\Http\Request; // Thu vien Request dung de lay du lieu tu form
use Illuminate\Support\Facades\Auth; // Thu vien Auth dung de xac thuc
use Illuminate\Support\Facades\DB; // Thu vien DB dung de truy van database
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
        // $request la doi tuong chua cac tham so truyen tu client qua URL den controller se duoc su dung de lay du lieu tu form
        $request->validate([ // Validate du lieu dau vao
            'email' => 'required|email', // email bat buoc va phai dung dinh dang email
            'password' => 'required', // password bat buoc
        ]);

        // Tìm user theo email
        $user = User::where('email', $request->email)->first(); // User lay tu model User, tim user dau tien co email giong voi email tu form, ham first() de lay user dau tien neu co nhieu user trung email

        // Kiểm tra credentials
        if (! $user || ! Hash::check($request->password, $user->password)) { // !user neu khong tim thay user hoac password khong dung, Hash::check de kiem tra password neu khong trung
            return back()->withErrors([ // Quay lai trang truoc do voi loi
                'email' => 'Thông tin đăng nhập không chính xác.', // Thong bao loi
            ])->withInput(); // withInput de giu lai du lieu nguoi dung da nhap
        }

        // Đăng nhập user
        Auth::login($user); // Auth::login de dang nhap user

        // Chuyển hướng dựa trên role
        if ($user->hasRole('admin') || $user->hasRole('manager')) {
            // Admin và Manager vào dashboard
            return redirect()->route('dashboard')->with('success', 'Đăng nhập thành công!');
        } elseif ($user->hasRole('customer')) {
            // Customer vào trang sản phẩm
            return redirect()->route('products.index')->with('success', 'Đăng nhập thành công!');
        } else {
            // User thông thường vào trang chủ
            return redirect()->route('home')->with('success', 'Đăng nhập thành công!');
        }
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

        // Tự động gán role customer cho user mới đăng ký
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
            // Log lỗi nhưng vẫn cho user đăng ký thành công
        }

        return redirect()->route('login')->with('success', 'Đăng ký thành công! Vui lòng đăng nhập để tiếp tục.'); // Chuyen huong ve trang login voi thong bao thanh cong
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
