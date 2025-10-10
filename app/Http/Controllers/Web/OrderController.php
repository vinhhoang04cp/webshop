<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of orders for admin UI.
     */
    public function index(Request $request)
    {
        try {
            // Lấy danh sách orders với search và filter
            $query = Order::with(['user', 'items.product']);

            // Nếu có search, filter dữ liệu
            if ($request->has('search') && $request->search) {
                $searchTerm = $request->search;
                $query->where('order_id', 'LIKE', "%{$searchTerm}%")
                    ->orWhereHas('user', function ($q) use ($searchTerm) {
                        $q->where('name', 'LIKE', "%{$searchTerm}%")
                          ->orWhere('email', 'LIKE', "%{$searchTerm}%");
                    });
            }

            // Filter theo trạng thái
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            // Sắp xếp theo ngày tạo mới nhất
            $query->orderBy('order_date', 'desc');

            // Pagination
            $perPage = 15;
            $orders = $query->paginate($perPage);

            // Lấy danh sách trạng thái
            $statuses = [
                Order::STATUS_PENDING => 'Chờ xử lý',
                Order::STATUS_PROCESSING => 'Đang xử lý',
                Order::STATUS_SHIPPED => 'Đã gửi hàng',
                Order::STATUS_DELIVERED => 'Đã giao hàng',
                Order::STATUS_CANCELLED => 'Đã hủy',
            ];

            return view('dashboard.orders.index', compact('orders', 'statuses'));

        } catch (\Exception $e) {
            return view('dashboard.orders.index', [
                'orders' => collect()->paginate(15),
                'statuses' => [],
                'error' => 'Lỗi khi tải danh sách đơn hàng: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Display the specified order.
     */
    public function show($id)
    {
        try {
            $order = Order::with(['user', 'items.product', 'items.productDetail'])
                ->findOrFail($id);

            // Lấy danh sách trạng thái có thể chuyển đổi
            $availableStatuses = $this->getAvailableStatuses($order->status);

            return view('dashboard.orders.show', compact('order', 'availableStatuses'));
        } catch (\Exception $e) {
            return redirect()->route('dashboard.orders.index')
                ->with('error', 'Lỗi khi tải chi tiết đơn hàng: '.$e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified order.
     */
    public function edit($id)
    {
        try {
            $order = Order::with(['user', 'items.product'])->findOrFail($id);

            // Lấy danh sách trạng thái có thể chuyển đổi
            $availableStatuses = $this->getAvailableStatuses($order->status);

            return view('dashboard.orders.edit', compact('order', 'availableStatuses'));
        } catch (\Exception $e) {
            return redirect()->route('dashboard.orders.index')
                ->with('error', 'Lỗi khi tải thông tin đơn hàng: '.$e->getMessage());
        }
    }

    /**
     * Update the specified order.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:pending,processing,shipped,delivered,cancelled',
        ]);

        try {
            $order = Order::findOrFail($id);

            // Kiểm tra xem có thể chuyển đổi trạng thái không
            if (!$order->canTransitionTo($request->status)) {
                return redirect()->route('dashboard.orders.edit', $id)
                    ->with('error', 'Không thể chuyển đổi trạng thái đơn hàng từ "'.$this->getStatusLabel($order->status).'" sang "'.$this->getStatusLabel($request->status).'"');
            }

            // Cập nhật trạng thái đơn hàng
            $order->update([
                'status' => $request->status,
            ]);

            return redirect()->route('dashboard.orders.show', $id)
                ->with('success', 'Trạng thái đơn hàng đã được cập nhật thành công!');

        } catch (\Exception $e) {
            return redirect()->route('dashboard.orders.edit', $id)
                ->with('error', 'Lỗi khi cập nhật đơn hàng: '.$e->getMessage());
        }
    }

    /**
     * Remove the specified order.
     */
    public function destroy($id)
    {
        try {
            $order = Order::findOrFail($id);

            // Chỉ cho phép xóa đơn hàng đã hủy hoặc đã giao
            if (!in_array($order->status, [Order::STATUS_CANCELLED, Order::STATUS_DELIVERED])) {
                return redirect()->route('dashboard.orders.index')
                    ->with('error', 'Chỉ có thể xóa đơn hàng đã hủy hoặc đã giao!');
            }

            $order->delete();

            return redirect()->route('dashboard.orders.index')
                ->with('success', 'Đơn hàng đã được xóa thành công!');

        } catch (\Exception $e) {
            return redirect()->route('dashboard.orders.index')
                ->with('error', 'Lỗi khi xóa đơn hàng: '.$e->getMessage());
        }
    }

    /**
     * Get available statuses for transition
     */
    private function getAvailableStatuses($currentStatus)
    {
        $allStatuses = [
            Order::STATUS_PENDING => 'Chờ xử lý',
            Order::STATUS_PROCESSING => 'Đang xử lý',
            Order::STATUS_SHIPPED => 'Đã gửi hàng',
            Order::STATUS_DELIVERED => 'Đã giao hàng',
            Order::STATUS_CANCELLED => 'Đã hủy',
        ];

        $availableTransitions = Order::STATUS_TRANSITIONS[$currentStatus] ?? [];
        
        $result = [];
        foreach ($availableTransitions as $status) {
            $result[$status] = $allStatuses[$status];
        }

        return $result;
    }

    /**
     * Get status label in Vietnamese
     */
    private function getStatusLabel($status)
    {
        $labels = [
            Order::STATUS_PENDING => 'Chờ xử lý',
            Order::STATUS_PROCESSING => 'Đang xử lý',
            Order::STATUS_SHIPPED => 'Đã gửi hàng',
            Order::STATUS_DELIVERED => 'Đã giao hàng',
            Order::STATUS_CANCELLED => 'Đã hủy',
        ];

        return $labels[$status] ?? $status;
    }
}
