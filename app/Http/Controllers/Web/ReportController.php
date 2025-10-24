<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Hiển thị trang báo cáo tổng quan
     */
    public function index()
    {
        try {
            // Thống kê tổng quan
            $totalRevenue = Order::where('status', '!=', Order::STATUS_CANCELLED)->sum('total_amount');
            // $totalRevenue la tong doanh thu cua cac don hang co trang thai khac voi STATUS_CANCELLED
            $totalOrders = Order::where('status', '!=', Order::STATUS_CANCELLED)->count();
            // $totalOrders la tong so don hang co trang thai khac voi STATUS_CANCELLED
            $totalCustomers = User::whereHas('roles', function ($query) { // su dung ham whereHas de dem so luong khach hang co vai tro la 'customer' bang callback function
                $query->where('role_name', 'customer');
            })->count();
            $totalProducts = Product::count(); // dem tong so san pham trong he thong

            // Doanh thu theo tháng (12 tháng gần nhất)
            $monthlyRevenue = $this->getMonthlyRevenue(); // goi phuong thuc getMonthlyRevenue de lay du lieu doanh thu theo thang

            // Top sản phẩm bán chạy
            $topProducts = $this->getTopSellingProducts(10); // lay 10 san pham ban chay nhat

            // Đơn hàng theo trạng thái
            $ordersByStatus = Order::select('status', DB::raw('count(*) as count'))  // su dung eloquent va ham raw de dem so luong don hang theo tung trang thai
                ->groupBy('status')
                ->get();

            // Doanh thu hôm nay
            $todayRevenue = Order::where('status', '!=', Order::STATUS_CANCELLED) // loc cac don hang khong bi huy
                ->whereDate('order_date', Carbon::today()) // ham Carbon.today() tra ve ngay hien tai
                ->sum('total_amount'); // tinh tong doanh thu trong ngay hom nay

            // Doanh thu tháng này
            $thisMonthRevenue = Order::where('status', '!=', Order::STATUS_CANCELLED)
                ->whereMonth('order_date', Carbon::now()->month)
                ->whereYear('order_date', Carbon::now()->year)
                ->sum('total_amount');

            return view('dashboard.reports.index', compact( // su dung ham compact de truyen du lieu den view
                'totalRevenue',
                'totalOrders',
                'totalCustomers',
                'totalProducts',
                'monthlyRevenue',
                'topProducts',
                'ordersByStatus',
                'todayRevenue',
                'thisMonthRevenue'
            ));
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra: '.$e->getMessage());
        }
    }

    /**
     * Báo cáo doanh thu chi tiết
     */
    public function revenue(Request $request) // su dung Request de lay du lieu tu yeu cau HTTP
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d')); // Carbon::now()->startOfMonth()->format('Y-m-d') tra ve ngay dau tien cua thang hien tai dinh dang 'Y-m-d'
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d')); // Carbon::now()->endOfMonth()->format('Y-m-d') tra ve ngay cuoi cung cua thang hien tai dinh dang 'Y-m-d'
        $groupBy = $request->get('group_by', 'day'); // day, week, month la cac lua chon nhom du lieu

        // Doanh thu theo khoảng thời gian
        $revenueData = $this->getRevenueByPeriod($startDate, $endDate, $groupBy); // goi phuong thuc getRevenueByPeriod de lay du lieu doanh thu theo khoang thoi gian va cach nhom du lieu

        // Tổng doanh thu trong khoảng thời gian
        $totalRevenue = Order::where('status', '!=', Order::STATUS_CANCELLED) // loc cac don hang khong bi huy
            ->whereBetween('order_date', [$startDate, $endDate]) // loc cac don hang trong khoang thoi gian tu startDate den endDate
            ->sum('total_amount'); // tinh tong doanh thu trong khoang thoi gian

        // Số đơn hàng trong khoảng thời gian
        $totalOrders = Order::where('status', '!=', Order::STATUS_CANCELLED) // loc cac don hang khong bi huy
            ->whereBetween('order_date', [$startDate, $endDate]) // loc cac don hang trong khoang thoi gian tu startDate den endDate
            ->count(); // dem so don hang trong khoang thoi gian

        // Giá trị đơn hàng trung bình
        $averageOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0; // ? la toan tu dieu kien, neu totalOrders > 0 thi tinh gia tri don hang trung binh, nguoc lai tra ve 0

        return view('dashboard.reports.revenue', compact( // su dung ham compact de truyen du lieu den view
            'revenueData', // du lieu doanh thu theo khoang thoi gian
            'totalRevenue', // tong doanh thu trong khoang thoi gian
            'totalOrders', // tong so don hang trong khoang thoi gian
            'averageOrderValue', // gia tri don hang trung binh
            'startDate', // ngay bat dau
            'endDate', // ngay ket thuc
            'groupBy' // phuong thuc nhom du lieu
        ));
    }

    /**
     * Báo cáo sản phẩm
     */
    public function products(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        // ham Carbon la ham xu ly thoi gian trong PHP, Carbon::now() tra ve thoi gian hien tai, startOfMonth() tra ve ngay dau tien cua thang hien tai, format('Y-m-d') dinh dang lai thanh chuoi theo dinh dang 'Y-m-d'
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        // ham Carbon la ham xu ly thoi gian trong PHP, Carbon::now() tra ve thoi gian hien tai, endOfMonth() tra ve ngay cuoi cung cua thang hien tai, format('Y-m-d') dinh dang lai thanh chuoi theo dinh dang 'Y-m-d'

        // Top sản phẩm bán chạy
        $topProducts = $this->getTopSellingProducts(20, $startDate, $endDate);
        // goi den ham getTopSellingProducts lay 20 san pham ban nhieu nhat trong khoang thoi gian tu startDate den endDate

        // Sản phẩm theo danh mục
        $productsByCategory = OrderItem::select( // su dung Eloquent de lay du lieu tu bang OrderItem
            'categories.name as category_name', // lay ten danh muc san pham
            DB::raw('SUM(order_items.quantity) as total_quantity'), // ham raw de viet cau truy van SQL thuan, SUM tinh tong so luong san pham da ban
            DB::raw('SUM(order_items.quantity * order_items.price) as total_revenue') // SUM tinh tong doanh thu cua san pham theo danh muc
        )
            ->join('products', 'order_items.product_id', '=', 'products.product_id') // ket noi bang products voi order_items qua truong product_id
            ->join('categories', 'products.category_id', '=', 'categories.category_id') // ket noi bang categories voi products qua truong category_id
            ->join('orders', 'order_items.order_id', '=', 'orders.order_id') // ket noi bang orders voi order_items qua truong order_id
            ->where('orders.status', '!=', Order::STATUS_CANCELLED) // loc cac don hang khong bi huy
            ->whereBetween('orders.order_date', [$startDate, $endDate]) // loc cac don hang trong khoang thoi gian duoc chon
            ->groupBy('categories.category_id', 'categories.name') // nhom ket qua theo danh muc san pham
            ->orderBy('total_revenue', 'desc') // sap xep giam dan theo tong doanh thu
            ->get();

        return view('dashboard.reports.products', compact( // su dung ham compact de truyen du lieu den view
            'topProducts',
            'productsByCategory',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Báo cáo khách hàng
     */
    public function customers(Request $request) // su dung Request de lay du lieu tu yeu cau HTTP
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d')); // lay ngay bat dau tu yeu cau, neu khong co thi mac dinh la ngay dau tien cua thang hien tai
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d')); // lay ngay ket thuc tu yeu cau, neu khong co thi mac dinh la ngay cuoi cung cua thang hien tai

        // Top khách hàng theo doanh thu
        $topCustomers = Order::select( // select la ham de chon cac truong can thiet tu bang orders
            'users.name', // lay ten khach hang tu bang users
            'users.email', // lay email khach hang tu bang users
            DB::raw('COUNT(orders.order_id) as total_orders'), // raw la ham de viet cau truy van SQL thuan, COUNT dem so luong don hang cua moi khach hang
            DB::raw('SUM(orders.total_amount) as total_spent') // SUM tinh tong so tien ma moi khach hang da chi tieu
        )
            ->join('users', 'orders.user_id', '=', 'users.id') // ket noi bang users voi orders qua truong user_id va id
            ->where('orders.status', '!=', Order::STATUS_CANCELLED) // loc cac don hang khong bi huy
            ->whereBetween('orders.order_date', [$startDate, $endDate]) // loc cac don hang trong khoang thoi gian duoc chon
            ->groupBy('users.id', 'users.name', 'users.email') // nhom ket qua theo khach hang
            ->orderBy('total_spent', 'desc') // sap xep giam dan theo tong chi tieu
            ->limit(20) // gioi han 20 khach hang dau tien
            ->get(); // thuc thi cau truy van va lay ket qua

        // Khách hàng mới
        $newCustomers = User::whereHas('roles', function ($query) { // su dung ham whereHas de dem so luong khach hang co vai tro la 'customer' bang callback function
            $query->where('role_name', 'customer'); // loc nhung nguoi dung co vai tro la 'customer'
        })
            ->whereBetween('created_at', [$startDate, $endDate]) // loc nhung khach hang duoc tao trong khoang thoi gian duoc chon
            ->count(); // dem so luong khach hang moi

        // Khách hàng có đơn hàng
        $activeCustomers = Order::where('status', '!=', Order::STATUS_CANCELLED) // loc cac don hang khong bi huy
            ->whereBetween('order_date', [$startDate, $endDate]) // loc cac don hang trong khoang thoi gian duoc chon
            ->distinct('user_id') // chon nhung user_id khac nhau
            ->count(); // dem so luong khach hang co don hang

        return view('dashboard.reports.customers', compact( // su dung ham compact de truyen du lieu den view
            'topCustomers', // du lieu top khach hang theo doanh thu
            'newCustomers', // so luong khach hang moi
            'activeCustomers',  // so luong khach hang co don hang
            'startDate', // ngay bat dau
            'endDate' // ngay ket thuc
        ));
    }

    /**
     * Lấy doanh thu theo tháng (12 tháng gần nhất)
     */
    private function getMonthlyRevenue()
    {
        return Order::select(
            DB::raw('YEAR(order_date) as year'),
            DB::raw('MONTH(order_date) as month'),
            DB::raw('SUM(total_amount) as revenue')
        )
            ->where('status', '!=', Order::STATUS_CANCELLED)
            ->where('order_date', '>=', Carbon::now()->subMonths(11)->startOfMonth())
            ->groupBy(DB::raw('YEAR(order_date)'), DB::raw('MONTH(order_date)'))
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                $item->period = Carbon::createFromDate($item->year, $item->month, 1)->format('M Y');

                return $item;
            });
    }

    /**
     * Lấy top sản phẩm bán chạy
     */
    private function getTopSellingProducts($limit = 10, $startDate = null, $endDate = null)
    {
        $query = OrderItem::select(
            'products.name as product_name',
            'products.price as product_price',
            DB::raw('SUM(order_items.quantity) as total_sold'),
            DB::raw('SUM(order_items.quantity * order_items.price) as total_revenue')
        )
            ->join('products', 'order_items.product_id', '=', 'products.product_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.order_id')
            ->where('orders.status', '!=', Order::STATUS_CANCELLED);

        if ($startDate && $endDate) {
            $query->whereBetween('orders.order_date', [$startDate, $endDate]);
        }

        return $query->groupBy('products.product_id', 'products.name', 'products.price')
            ->orderBy('total_sold', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Lấy doanh thu theo khoảng thời gian
     */
    private function getRevenueByPeriod($startDate, $endDate, $groupBy = 'day')
    {
        $dateFormat = match ($groupBy) {
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            default => '%Y-%m-%d'
        };

        $selectFormat = match ($groupBy) {
            'week' => "CONCAT(YEAR(order_date), '-W', LPAD(WEEK(order_date), 2, '0'))",
            'month' => "DATE_FORMAT(order_date, '%Y-%m')",
            default => 'DATE(order_date)'
        };

        return Order::select(
            DB::raw("$selectFormat as period"),
            DB::raw('SUM(total_amount) as revenue'),
            DB::raw('COUNT(*) as order_count')
        )
            ->where('status', '!=', Order::STATUS_CANCELLED)
            ->whereBetween('order_date', [$startDate, $endDate])
            ->groupBy(DB::raw($selectFormat))
            ->orderBy('period')
            ->get();
    }

    /**
     * Export dữ liệu báo cáo
     */
    public function export(Request $request)
    {
        $type = $request->get('type', 'revenue');
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        switch ($type) {
            case 'revenue':
                return $this->exportRevenue($startDate, $endDate);
            case 'products':
                return $this->exportProducts($startDate, $endDate);
            case 'customers':
                return $this->exportCustomers($startDate, $endDate);
            default:
                return back()->with('error', 'Loại báo cáo không hợp lệ');
        }
    }

    private function exportRevenue($startDate, $endDate)
    {
        $data = $this->getRevenueByPeriod($startDate, $endDate, 'day');

        $filename = "revenue_report_{$startDate}_to_{$endDate}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Ngày', 'Doanh thu', 'Số đơn hàng']);

            foreach ($data as $row) {
                fputcsv($file, [
                    $row->period,
                    number_format($row->revenue, 0, ',', '.'),
                    $row->order_count,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportProducts($startDate, $endDate)
    {
        $data = $this->getTopSellingProducts(100, $startDate, $endDate);

        $filename = "products_report_{$startDate}_to_{$endDate}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Tên sản phẩm', 'Đã bán', 'Doanh thu']);

            foreach ($data as $row) {
                fputcsv($file, [
                    $row->product_name,
                    $row->total_sold,
                    number_format($row->total_revenue, 0, ',', '.'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportCustomers($startDate, $endDate)
    {
        $data = Order::select(
            'users.name',
            'users.email',
            DB::raw('COUNT(orders.order_id) as total_orders'),
            DB::raw('SUM(orders.total_amount) as total_spent')
        )
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->where('orders.status', '!=', Order::STATUS_CANCELLED)
            ->whereBetween('orders.order_date', [$startDate, $endDate])
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderBy('total_spent', 'desc')
            ->get();

        $filename = "customers_report_{$startDate}_to_{$endDate}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Tên khách hàng', 'Email', 'Số đơn hàng', 'Tổng chi tiêu']);

            foreach ($data as $row) {
                fputcsv($file, [
                    $row->name,
                    $row->email,
                    $row->total_orders,
                    number_format($row->total_spent, 0, ',', '.'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
