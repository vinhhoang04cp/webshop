<?php

namespace App\Http\Controllers\Web;

use App\Contracts\OrderServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderServiceInterface $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Hiển thị danh sách đơn hàng cho admin
     */
    public function index(Request $request)
    {
        try {
            $filters = $request->only(['search', 'status']);
            $orders = $this->orderService->getOrdersForAdmin($filters, 15);
            $statuses = $this->orderService->getAllStatuses();

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
     * Hiển thị chi tiết đơn hàng
     */
    public function show($id)
    {
        try {
            $order = $this->orderService->getOrderDetail($id);
            $availableStatuses = $this->orderService->getAvailableStatuses($order->status);

            return view('dashboard.orders.show', compact('order', 'availableStatuses'));
        } catch (\Exception $e) {
            return redirect()->route('dashboard.orders.index')
                ->with('error', 'Lỗi khi tải chi tiết đơn hàng: '.$e->getMessage());
        }
    }

    /**
     * Hiển thị form chỉnh sửa đơn hàng
     */
    public function edit($id)
    {
        try {
            $order = $this->orderService->getOrderForEdit($id);
            $availableStatuses = $this->orderService->getAvailableStatuses($order->status);

            return view('dashboard.orders.edit', compact('order', 'availableStatuses'));
        } catch (\Exception $e) {
            return redirect()->route('dashboard.orders.index')
                ->with('error', 'Lỗi khi tải thông tin đơn hàng: '.$e->getMessage());
        }
    }

    /**
     * Cập nhật trạng thái đơn hàng
     */
    public function update(OrderRequest $request, $id)
    {
        try {
            $this->orderService->updateOrderStatus($id, $request->status);

            return redirect()->route('dashboard.orders.show', $id)
                ->with('success', 'Trạng thái đơn hàng đã được cập nhật thành công!');
        } catch (\Exception $e) {
            return redirect()->route('dashboard.orders.edit', $id)
                ->with('error', 'Lỗi khi cập nhật đơn hàng: '.$e->getMessage());
        }
    }

    /**
     * Xóa đơn hàng
     */
    public function destroy($id)
    {
        try {
            $this->orderService->deleteOrder($id);

            return redirect()->route('dashboard.orders.index')
                ->with('success', 'Đơn hàng đã được xóa thành công!');
        } catch (\Exception $e) {
            return redirect()->route('dashboard.orders.index')
                ->with('error', 'Lỗi khi xóa đơn hàng: '.$e->getMessage());
        }
    }
}
