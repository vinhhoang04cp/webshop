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

            return view('dashboard.reports.index', compact(
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
    public function revenue(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $groupBy = $request->get('group_by', 'day'); // day, week, month

        // Doanh thu theo khoảng thời gian
        $revenueData = $this->getRevenueByPeriod($startDate, $endDate, $groupBy);

        // Tổng doanh thu trong khoảng thời gian
        $totalRevenue = Order::where('status', '!=', Order::STATUS_CANCELLED)
            ->whereBetween('order_date', [$startDate, $endDate])
            ->sum('total_amount');

        // Số đơn hàng trong khoảng thời gian
        $totalOrders = Order::where('status', '!=', Order::STATUS_CANCELLED)
            ->whereBetween('order_date', [$startDate, $endDate])
            ->count();

        // Giá trị đơn hàng trung bình
        $averageOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        return view('dashboard.reports.revenue', compact(
            'revenueData',
            'totalRevenue',
            'totalOrders',
            'averageOrderValue',
            'startDate',
            'endDate',
            'groupBy'
        ));
    }

    /**
     * Báo cáo sản phẩm
     */
    public function products(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        // Top sản phẩm bán chạy
        $topProducts = $this->getTopSellingProducts(20, $startDate, $endDate);

        // Sản phẩm theo danh mục
        $productsByCategory = OrderItem::select(
            'categories.name as category_name',
            DB::raw('SUM(order_items.quantity) as total_quantity'),
            DB::raw('SUM(order_items.quantity * order_items.price) as total_revenue')
        )
            ->join('products', 'order_items.product_id', '=', 'products.product_id')
            ->join('categories', 'products.category_id', '=', 'categories.category_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.order_id')
            ->where('orders.status', '!=', Order::STATUS_CANCELLED)
            ->whereBetween('orders.order_date', [$startDate, $endDate])
            ->groupBy('categories.category_id', 'categories.name')
            ->orderBy('total_revenue', 'desc')
            ->get();

        return view('dashboard.reports.products', compact(
            'topProducts',
            'productsByCategory',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Báo cáo khách hàng
     */
    public function customers(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        // Top khách hàng theo doanh thu
        $topCustomers = Order::select(
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
            ->limit(20)
            ->get();

        // Khách hàng mới
        $newCustomers = User::whereHas('roles', function ($query) {
            $query->where('role_name', 'customer');
        })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        // Khách hàng có đơn hàng
        $activeCustomers = Order::where('status', '!=', Order::STATUS_CANCELLED)
            ->whereBetween('order_date', [$startDate, $endDate])
            ->distinct('user_id')
            ->count();

        return view('dashboard.reports.customers', compact(
            'topCustomers',
            'newCustomers',
            'activeCustomers',
            'startDate',
            'endDate'
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
