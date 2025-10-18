<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            $oldStatus = $order->status;
            $newStatus = $request->status;

            // Kiểm tra xem có thể chuyển đổi trạng thái không
            if (! $order->canTransitionTo($newStatus)) {
                return redirect()->route('dashboard.orders.edit', $id)
                    ->with('error', 'Không thể chuyển đổi trạng thái đơn hàng từ "'.$this->getStatusLabel($oldStatus).'" sang "'.$this->getStatusLabel($newStatus).'"');
            }

            // Sử dụng transaction để đảm bảo tính toàn vẹn dữ liệu
            DB::transaction(function () use ($order, $newStatus, $oldStatus) {
                // Cập nhật trạng thái đơn hàng
                $order->update([
                    'status' => $newStatus,
                ]);

                // Nếu đơn hàng chuyển sang "Đã giao hàng", tự động cập nhật inventory
                if ($newStatus === Order::STATUS_DELIVERED && $oldStatus !== Order::STATUS_DELIVERED) {
                    $this->updateInventoryOnDelivered($order);
                }

                // Nếu đơn hàng bị hủy, hoàn trả số lượng vào kho
                if ($newStatus === Order::STATUS_CANCELLED && $oldStatus !== Order::STATUS_CANCELLED) {
                    $this->restoreInventoryOnCancelled($order);
                }
            });

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
            if (! in_array($order->status, [Order::STATUS_CANCELLED, Order::STATUS_DELIVERED])) {
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
    private function getAvailableStatuses($currentStatus) // $currentStatus lay tu bang orders
    {
        $allStatuses = [ // khoi tao mang allStatuses
            Order::STATUS_PENDING => 'Chờ xử lý', // key la trang thai, value la ten trang thai
            Order::STATUS_PROCESSING => 'Đang xử lý', // key la trang thai, value la ten trang thai
            Order::STATUS_SHIPPED => 'Đã gửi hàng', // key la trang thai, value la ten trang thai
            Order::STATUS_DELIVERED => 'Đã giao hàng', // key la trang thai, value la ten trang thai
            Order::STATUS_CANCELLED => 'Đã hủy', // key la trang thai, value la ten trang thai
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
    private function getStatusLabel($status) // $status lay tu bang orders
    {
        $labels = [ // khoi tao mang labels
            Order::STATUS_PENDING => 'Chờ xử lý', // key la trang thai, value la ten trang thai
            Order::STATUS_PROCESSING => 'Đang xử lý', // key la trang thai, value la ten trang thai
            Order::STATUS_SHIPPED => 'Đã gửi hàng', // key la trang thai, value la ten trang thai
            Order::STATUS_DELIVERED => 'Đã giao hàng', // key la trang thai, value la ten trang thai
            Order::STATUS_CANCELLED => 'Đã hủy', // key la trang thai, value la ten trang thai
        ];

        return $labels[$status] ?? $status; // neu co labels thi tra ve labels, khong co thi tra ve status
    }

    /**
     * Cập nhật inventory khi đơn hàng được giao thành công
     * LƯU Ý: Tồn kho đã được trừ khi đặt hàng (checkout)
     * Hàm này CHỈ để tracking và logging, KHÔNG trừ tồn kho nữa
     */
    private function updateInventoryOnDelivered(Order $order)
    {
        // Tồn kho đã được giảm khi khách đặt hàng (checkout)
        // Khi delivered, chúng ta chỉ cần log hoặc cập nhật trạng thái
        // KHÔNG cần trừ stock_quantity và inventory nữa vì đã trừ rồi

        // Nếu cần logging:
        \Log::info("Order #{$order->order_id} delivered successfully. Stock was already deducted at checkout.");
    }

    /**
     * Hoàn trả inventory khi đơn hàng bị hủy
     */
    private function restoreInventoryOnCancelled(Order $order)
    {
        // Lấy tất cả items trong đơn hàng
        $orderItems = $order->items()->with('product')->get();

        foreach ($orderItems as $item) {
            if (! $item->product) {
                continue; // Bỏ qua nếu sản phẩm không tồn tại
            }

            $product = $item->product;
            $quantity = $item->quantity;

            // Tăng lại stock_quantity trong bảng products
            $product->increment('stock_quantity', $quantity);

            // Cập nhật inventory
            $inventory = Inventory::firstOrCreate(
                ['product_id' => $product->product_id],
                [
                    'stock_in' => 0,
                    'stock_out' => 0,
                    'current_stock' => 0,
                ]
            );

            // Giảm số lượng xuất kho và tăng tồn kho hiện tại
            if ($inventory->stock_out >= $quantity) {
                $inventory->decrement('stock_out', $quantity);
            }
            $inventory->increment('current_stock', $quantity);
        }
    }
}
