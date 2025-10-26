<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Lấy thống kê tổng quan
     */
    public function getOverviewStats()
    {
        return [
            'total_revenue' => Order::where('status', '!=', Order::STATUS_CANCELLED)->sum('total_amount'),
            'total_orders' => Order::where('status', '!=', Order::STATUS_CANCELLED)->count(),
            'total_customers' => User::whereHas('roles', function ($query) {
                $query->where('role_name', 'customer');
            })->count(),
            'total_products' => Product::count(),
            'today_revenue' => Order::where('status', '!=', Order::STATUS_CANCELLED)
                ->whereDate('order_date', Carbon::today())
                ->sum('total_amount'),
            'this_month_revenue' => Order::where('status', '!=', Order::STATUS_CANCELLED)
                ->whereMonth('order_date', Carbon::now()->month)
                ->whereYear('order_date', Carbon::now()->year)
                ->sum('total_amount'),
        ];
    }

    /**
     * Lấy doanh thu theo tháng (12 tháng gần nhất)
     */
    public function getMonthlyRevenue()
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
    public function getTopSellingProducts($limit = 10, $startDate = null, $endDate = null)
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
     * Lấy đơn hàng theo trạng thái
     */
    public function getOrdersByStatus()
    {
        return Order::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();
    }

    /**
     * Lấy doanh thu theo khoảng thời gian
     */
    public function getRevenueByPeriod($startDate, $endDate, $groupBy = 'day')
    {
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
     * Lấy thống kê doanh thu trong khoảng thời gian
     */
    public function getRevenueStats($startDate, $endDate)
    {
        $totalRevenue = Order::where('status', '!=', Order::STATUS_CANCELLED)
            ->whereBetween('order_date', [$startDate, $endDate])
            ->sum('total_amount');

        $totalOrders = Order::where('status', '!=', Order::STATUS_CANCELLED)
            ->whereBetween('order_date', [$startDate, $endDate])
            ->count();

        $averageOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        return [
            'total_revenue' => $totalRevenue,
            'total_orders' => $totalOrders,
            'average_order_value' => $averageOrderValue,
        ];
    }

    /**
     * Lấy sản phẩm theo danh mục
     */
    public function getProductsByCategory($startDate, $endDate)
    {
        return OrderItem::select(
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
    }

    /**
     * Lấy top khách hàng theo doanh thu
     */
    public function getTopCustomers($startDate, $endDate, $limit = 20)
    {
        return Order::select(
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
            ->limit($limit)
            ->get();
    }

    /**
     * Lấy thống kê khách hàng
     */
    public function getCustomerStats($startDate, $endDate)
    {
        $newCustomers = User::whereHas('roles', function ($query) {
            $query->where('role_name', 'customer');
        })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $activeCustomers = Order::where('status', '!=', Order::STATUS_CANCELLED)
            ->whereBetween('order_date', [$startDate, $endDate])
            ->distinct('user_id')
            ->count();

        return [
            'new_customers' => $newCustomers,
            'active_customers' => $activeCustomers,
        ];
    }

    /**
     * Export dữ liệu doanh thu
     */
    public function exportRevenueData($startDate, $endDate)
    {
        return $this->getRevenueByPeriod($startDate, $endDate, 'day');
    }

    /**
     * Export dữ liệu sản phẩm
     */
    public function exportProductsData($startDate, $endDate)
    {
        return $this->getTopSellingProducts(100, $startDate, $endDate);
    }

    /**
     * Export dữ liệu khách hàng
     */
    public function exportCustomersData($startDate, $endDate)
    {
        return $this->getTopCustomers($startDate, $endDate, 1000);
    }
}
