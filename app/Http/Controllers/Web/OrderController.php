<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Models\Inventory;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Hiển thị danh sách đơn hàng cho admin
     *
     * Chức năng: Hiển thị tất cả đơn hàng với tính năng tìm kiếm và lọc
     * Hoạt động:
     * - Query orders với eager loading user, items, và product
     * - Tìm kiếm theo order_id hoặc thông tin user (name, email)
     * - Lọc theo trạng thái đơn hàng (pending, processing, shipped, delivered, cancelled)
     * - Sắp xếp theo order_date giảm dần (mới nhất trước)
     * - Phân trang 15 đơn hàng mỗi trang
     * - Lấy danh sách trạng thái đơn hàng để hiển thị filter
     * - Trả về view với orders và statuses
     * - Xử lý exception và trả về danh sách rỗng nếu có lỗi
     *
     * @param  \Illuminate\Http\Request  $request  Chứa tham số search và filter
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        try {
            // Lấy danh sách orders với search và filter
            $query = Order::with(['user', 'items.product']);

            // Nếu có search, filter dữ liệu
            if ($request->has('search') && $request->search) { // neu request co tham so search va search khac rong
                $searchTerm = $request->search; // searchTerm la bien chua lay gia tri search tu request
                $query->where('order_id', 'LIKE', "%{$searchTerm}%") // tim kiem theo order_id
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
     * Hiển thị chi tiết đơn hàng
     *
     * Chức năng: Hiển thị thông tin chi tiết của một đơn hàng cụ thể
     * Hoạt động:
     * - Tìm order theo ID với eager loading user, items, product, productDetail
     * - Throw exception nếu không tìm thấy
     * - Lấy danh sách trạng thái có thể chuyển đổi dựa trên trạng thái hiện tại
     * - Trả về view chi tiết với order và availableStatuses
     * - Redirect về danh sách với thông báo lỗi nếu có exception
     *
     * @param  int  $id  ID của order cần hiển thị
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
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
     * Hiển thị form chỉnh sửa đơn hàng
     *
     * Chức năng: Hiển thị form để chỉnh sửa trạng thái đơn hàng
     * Hoạt động:
     * - Tìm order theo ID với eager loading user, items, product
     * - Lấy danh sách trạng thái có thể chuyển đổi từ trạng thái hiện tại
     * - Trả về view form edit với order và availableStatuses
     * - Redirect về danh sách với thông báo lỗi nếu không tìm thấy
     *
     * @param  int  $id  ID của order cần chỉnh sửa
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
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
     * Cập nhật trạng thái đơn hàng
     *
     * Chức năng: Xử lý thay đổi trạng thái của đơn hàng
     * Hoạt động:
     * - Validate trạng thái mới (phải thuộc: pending, processing, shipped, delivered, cancelled)
     * - Tìm order theo ID
     * - Kiểm tra có thể chuyển từ trạng thái cũ sang trạng thái mới không
     * - Sử dụng database transaction:
     *   + Cập nhật trạng thái đơn hàng
     *   + Nếu chuyển sang 'delivered': tự động cập nhật inventory
     *   + Nếu chuyển sang 'cancelled': hoàn trả số lượng vào kho
     * - Redirect về trang chi tiết với thông báo thành công
     * - Redirect về form edit với thông báo lỗi nếu có exception
     *
     * @param  \Illuminate\Http\Request  $request  Trạng thái mới
     * @param  int  $id  ID của order cần cập nhật
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(OrderRequest $request, $id)
    {
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
     * Xóa đơn hàng
     *
     * Chức năng: Xóa đơn hàng khỏi hệ thống
     * Hoạt động:
     * - Tìm order theo ID
     * - Kiểm tra trạng thái: chỉ cho phép xóa đơn đã hủy hoặc đã giao
     * - Nếu trạng thái không hợp lệ, trả về lỗi
     * - Xóa order khỏi database
     * - Redirect về danh sách với thông báo thành công
     * - Xử lý exception và hiển thị lỗi nếu có
     *
     * @param  int  $id  ID của order cần xóa
     * @return \Illuminate\Http\RedirectResponse
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
     * Lấy danh sách trạng thái có thể chuyển đổi
     *
     * Chức năng: Xác định các trạng thái mà đơn hàng có thể chuyển sang
     * Hoạt động:
     * - Lấy tất cả các trạng thái có thể có
     * - Dựa vào trạng thái hiện tại, lấy danh sách trạng thái hợp lệ từ STATUS_TRANSITIONS
     * - Trả về mảng các trạng thái có thể chuyển đổi với label tiếng Việt
     *
     * @param  string  $currentStatus  Trạng thái hiện tại của đơn hàng
     * @return array Danh sách trạng thái có thể chuyển đổi
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

        $availableTransitions = Order::STATUS_TRANSITIONS[$currentStatus] ?? []; // lay cac trang thai co the chuyen

        $result = [];
        foreach ($availableTransitions as $status) {
            $result[$status] = $allStatuses[$status];
        }

        return $result;
    }

    /**
     * Lấy nhãn trạng thái bằng tiếng Việt
     *
     * Chức năng: Chuyển đổi mã trạng thái sang tên tiếng Việt
     * Hoạt động:
     * - Map các mã trạng thái (pending, processing, ...) sang tên tiếng Việt
     * - Trả về tên tiếng Việt nếu có, hoặc trả về mã trạng thái nếu không tìm thấy
     *
     * @param  string  $status  Mã trạng thái của đơn hàng
     * @return string Tên trạng thái bằng tiếng Việt
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
     *
     * Chức năng: Cập nhật số liệu tồn kho khi đơn hàng chuyển sang trạng thái "Đã giao hàng"
     * Hoạt động:
     * - Không thực hiện gì vì đã trừ tồn kho khi đặt hàng
     * - Chỉ để placeholder cho logic nghiệp vụ tương lai
     *
     * Lưu ý: Hệ thống đã trừ tồn kho ngay khi khách đặt hàng (giữ hàng cho khách)
     *        Hàm này CHỈ để tracking và logging, KHÔNG trừ tồn kho nữa
     *
     * @param  Order  $order  Đơn hàng đã được giao
     * @return void
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
     *
     * Chức năng: Hoàn trả số lượng sản phẩm vào kho khi khách hủy đơn
     * Hoạt động:
     * - Lấy tất cả order items với thông tin product
     * - Duyệt qua từng item trong đơn hàng:
     *   + Bỏ qua nếu product không tồn tại
     *   + Tăng stock_quantity của product
     *   + Tìm hoặc tạo inventory cho product
     *   + Giảm stock_out (nếu đủ)
     *   + Tăng current_stock
     * - Đảm bảo tồn kho được phục hồi đúng như trước khi đặt hàng
     *
     * @param  Order  $order  Đơn hàng bị hủy
     * @return void
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
