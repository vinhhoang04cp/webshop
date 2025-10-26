<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Hiển thị trang báo cáo tổng quan
     */
    public function index()
    {
        try {
            $stats = $this->reportService->getOverviewStats();
            $monthlyRevenue = $this->reportService->getMonthlyRevenue();
            $topProducts = $this->reportService->getTopSellingProducts(10);
            $ordersByStatus = $this->reportService->getOrdersByStatus();

            return view('dashboard.reports.index', [
                'totalRevenue' => $stats['total_revenue'],
                'totalOrders' => $stats['total_orders'],
                'totalCustomers' => $stats['total_customers'],
                'totalProducts' => $stats['total_products'],
                'todayRevenue' => $stats['today_revenue'],
                'thisMonthRevenue' => $stats['this_month_revenue'],
                'monthlyRevenue' => $monthlyRevenue,
                'topProducts' => $topProducts,
                'ordersByStatus' => $ordersByStatus,
            ]);
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
        $groupBy = $request->get('group_by', 'day');

        $revenueData = $this->reportService->getRevenueByPeriod($startDate, $endDate, $groupBy);
        $stats = $this->reportService->getRevenueStats($startDate, $endDate);

        return view('dashboard.reports.revenue', [
            'revenueData' => $revenueData,
            'totalRevenue' => $stats['total_revenue'],
            'totalOrders' => $stats['total_orders'],
            'averageOrderValue' => $stats['average_order_value'],
            'startDate' => $startDate,
            'endDate' => $endDate,
            'groupBy' => $groupBy,
        ]);
    }

    /**
     * Báo cáo sản phẩm
     */
    public function products(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $topProducts = $this->reportService->getTopSellingProducts(20, $startDate, $endDate);
        $productsByCategory = $this->reportService->getProductsByCategory($startDate, $endDate);

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

        $topCustomers = $this->reportService->getTopCustomers($startDate, $endDate);
        $customerStats = $this->reportService->getCustomerStats($startDate, $endDate);

        return view('dashboard.reports.customers', [
            'topCustomers' => $topCustomers,
            'newCustomers' => $customerStats['new_customers'],
            'activeCustomers' => $customerStats['active_customers'],
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
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
        $data = $this->reportService->exportRevenueData($startDate, $endDate);
        $filename = "revenue_report_{$startDate}_to_{$endDate}.csv";

        return $this->generateCsvResponse($data, $filename, function ($row) {
            return [
                $row->period,
                number_format($row->revenue, 0, ',', '.'),
                $row->order_count,
            ];
        }, ['Ngày', 'Doanh thu', 'Số đơn hàng']);
    }

    private function exportProducts($startDate, $endDate)
    {
        $data = $this->reportService->exportProductsData($startDate, $endDate);
        $filename = "products_report_{$startDate}_to_{$endDate}.csv";

        return $this->generateCsvResponse($data, $filename, function ($row) {
            return [
                $row->product_name,
                $row->total_sold,
                number_format($row->total_revenue, 0, ',', '.'),
            ];
        }, ['Tên sản phẩm', 'Đã bán', 'Doanh thu']);
    }

    private function exportCustomers($startDate, $endDate)
    {
        $data = $this->reportService->exportCustomersData($startDate, $endDate);
        $filename = "customers_report_{$startDate}_to_{$endDate}.csv";

        return $this->generateCsvResponse($data, $filename, function ($row) {
            return [
                $row->name,
                $row->email,
                $row->total_orders,
                number_format($row->total_spent, 0, ',', '.'),
            ];
        }, ['Tên khách hàng', 'Email', 'Số đơn hàng', 'Tổng chi tiêu']);
    }

    private function generateCsvResponse($data, $filename, $rowCallback, $headers)
    {
        $responseHeaders = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($data, $rowCallback, $headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);

            foreach ($data as $row) {
                fputcsv($file, $rowCallback($row));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $responseHeaders);
    }
}
