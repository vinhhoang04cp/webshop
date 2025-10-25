<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\InventoryAdjustmentRequest;
use App\Http\Requests\InventoryRequest;
use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /**
     * Hiển thị danh sách tồn kho cho admin
     *
     * Chức năng: Hiển thị danh sách inventory của tất cả sản phẩm với tính năng tìm kiếm và lọc
     * Hoạt động:
     * - Query inventory với eager loading product và category
     * - Tìm kiếm theo tên sản phẩm (whereHas với callback)
     * - Lọc theo trạng thái tồn kho:
     *   + 'low': current_stock < 10 (tồn kho thấp)
     *   + 'out': current_stock = 0 (hết hàng)
     *   + 'available': current_stock >= 10 (còn hàng)
     * - Sắp xếp theo sort_by và sort_order (mặc định: updated_at desc)
     * - Phân trang 15 bản ghi mỗi trang
     * - Trả về view với paginatedInventory, pagination, search, filters
     * - Xử lý exception và trả về danh sách rỗng nếu có lỗi
     *
     * @param  \Illuminate\Http\Request  $request  Chứa tham số search, filter, sort
     * @return \Illuminate\View\View
     */
    public function index(Request $request) // Request $request chua cac tham so truyen vao de search, filter, sort
    {
        try {
            // Query inventory with product relationship
            $query = Inventory::with('product.category'); // su dung eloquent de lay du lieu tu bang inventories voi quan he voi bang products va categories
            // ('product.category' la quan he giua inventory va product, category la quan he giua product va category)

            // Search by product name
            if ($request->has('search') && $request->search) { // neu request co tham so search va search khac rong
                $searchTerm = $request->search; // searchTerm la bien chua lay gia tri search tu request
                $query->whereHas('product', function ($q) use ($searchTerm) {  // ham callback de loc theo quan he product
                    // function ($q) use ($searchTerm) { ... } la ham callback, $q la query builder cua product, use ($searchTerm) de su dung bien searchTerm ben ngoai ham
                    $q->where('name', 'like', '%'.$searchTerm.'%'); // tim kiem theo ten san pham
                });
            }

            // Filter by stock status
            if ($request->has('stock_status') && $request->stock_status !== '') { // neu request co tham so stock_status va khac rong
                $status = $request->stock_status; // lay gia tri stock_status tu request
                if ($status === 'low') { // neu status la 'low'. 'low' co nghia la ton kho thap
                    // Low stock: current_stock < 10
                    $query->where('current_stock', '<', 10); // loc nhung ban ghi co current_stock nho hon 10
                } elseif ($status === 'out') { // neu status la 'out'. 'out' co nghia la het hang
                    // Out of stock: current_stock = 0
                    $query->where('current_stock', '=', 0); // loc nhung ban ghi co current_stock bang 0
                } elseif ($status === 'available') { // neu status la 'available'. 'available' co nghia la con hang
                    // Available: current_stock >= 10 // loc nhung ban ghi co current_stock lon hon hoac bang 10
                    $query->where('current_stock', '>=', 10);
                }
            }

            // Sort by
            $sortBy = $request->get('sort_by', 'updated_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = 15; // so ban ghi tren mot trang
            $inventories = $query->paginate($perPage); // phuong thuc paginate de phan trang

            return view('dashboard.inventory.index', [ // tra ve view voi du lieu
                'paginatedInventory' => $inventories->items(), // lay danh sach ban ghi tren trang hien tai
                'pagination' => $inventories, // lay thong tin phan trang
                'search' => $request->search,  // lay gia tri search tu request de hien thi lai tren form
                'stock_status' => $request->stock_status, // lay gia tri stock_status tu request de hien thi lai tren form
                'sort_by' => $sortBy, // lay gia tri sort_by tu request de hien thi lai tren form
                'sort_order' => $sortOrder, // lay gia tri sort_order tu request de hien thi lai tren form
            ]);

        } catch (\Exception $e) { // neu co loi xay ra
            return view('dashboard.inventory.index', [ // tra ve view voi thong bao loi
                'paginatedInventory' => [], // danh sach rong
                'pagination' => null, // thong tin phan trang null
                'error' => 'Lỗi khi tải danh sách tồn kho: '.$e->getMessage(), // hien thi thong bao loi
            ]);
        }
    }

    /**
     * Hiển thị chi tiết một bản ghi tồn kho
     *
     * Chức năng: Hiển thị thông tin chi tiết tồn kho của một sản phẩm cụ thể
     * Hoạt động:
     * - Tìm inventory theo ID với eager loading product và category
     * - Throw exception nếu không tìm thấy
     * - Trả về view chi tiết với thông tin inventory, product, category
     * - Redirect về danh sách với thông báo lỗi nếu có exception
     *
     * @param  int  $id  ID của inventory cần hiển thị
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show($id)
    {
        try {
            $inventory = Inventory::with('product.category')->findOrFail($id);
            // tim kiem inventory theo id voi quan he product va category, neu khong tim thay thi throw exception su dung eloquent

            return view('dashboard.inventory.show', compact('inventory'));
        } catch (\Exception $e) {
            return redirect()->route('dashboard.inventory.index')
                ->with('error', 'Không tìm thấy bản ghi tồn kho: '.$e->getMessage());
        }
    }

    /**
     * Hiển thị form chỉnh sửa tồn kho
     *
     * Chức năng: Hiển thị form để chỉnh sửa thông tin tồn kho
     * Hoạt động:
     * - Tìm inventory theo ID với thông tin product
     * - Throw exception nếu không tìm thấy
     * - Trả về view form edit với dữ liệu inventory hiện tại
     * - Redirect về danh sách với thông báo lỗi nếu có exception
     *
     * @param  int  $id  ID của inventory cần chỉnh sửa
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit($id)
    {
        try {
            $inventory = Inventory::with('product')->findOrFail($id);
            // tim kiem inventory theo id voi quan he product, neu khong tim thay thi throw exception su dung eloquent

            return view('dashboard.inventory.edit', compact('inventory')); // tra ve view voi du lieu inventory
        } catch (\Exception $e) { // neu co loi xay ra
            return redirect()->route('dashboard.inventory.index') // chuyen huong ve trang danh sach inventory
                ->with('error', 'Không thể tải form chỉnh sửa: '.$e->getMessage()); // hien thi thong bao loi
        }
    }

    /**
     * Cập nhật thông tin tồn kho
     *
     * Chức năng: Xử lý cập nhật số liệu tồn kho trong database
     * Hoạt động:
     * - Validate dữ liệu đầu vào (stock_in, stock_out, current_stock phải >= 0)
     * - Tìm inventory theo ID
     * - Cập nhật các giá trị mới vào database
     * - Redirect về trang chi tiết với thông báo thành công
     * - Quay lại form với lỗi và giữ input nếu có exception
     *
     * @param  \Illuminate\Http\Request  $request  Dữ liệu cập nhật
     * @param  int  $id  ID của inventory cần cập nhật
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(InventoryRequest $request, $id)
    {
        try {
            $inventory = Inventory::findOrFail($id);

            $inventory->update([ // cap nhat du lieu inventory
                'stock_in' => $request->stock_in, // cap nhat stock_in tu request
                'stock_out' => $request->stock_out, // cap nhat stock_out tu request
                'current_stock' => $request->current_stock, // cap nhat current_stock tu request
            ]);

            return redirect()->route('dashboard.inventory.show', $id) // chuyen huong ve trang chi tiet inventory
                ->with('success', 'Cập nhật tồn kho thành công!'); // hien thi thong bao thanh cong

        } catch (\Exception $e) { // neu co loi xay ra
            return back() // tro ve trang truoc do
                ->with('error', 'Lỗi khi cập nhật tồn kho: '.$e->getMessage()) // hien thi thong bao loi
                ->withInput(); // giu lai du lieu da nhap tren form
        }
    }

    /**
     * Điều chỉnh tồn kho (nhập/xuất kho)
     *
     * Chức năng: Xử lý việc nhập kho hoặc xuất kho sản phẩm
     * Hoạt động:
     * - Validate dữ liệu: adjustment_type (in/out), quantity (>= 1), note (optional)
     * - Tìm inventory theo ID
     * - Nếu adjustment_type = 'in' (nhập kho):
     *   + Tăng stock_in và current_stock theo quantity
     *   + Cập nhật stock_quantity của product
     * - Nếu adjustment_type = 'out' (xuất kho):
     *   + Kiểm tra current_stock có đủ không
     *   + Tăng stock_out và giảm current_stock theo quantity
     *   + Cập nhật stock_quantity của product
     * - Sử dụng transaction để đảm bảo tính toàn vẹn
     * - Redirect về trang chi tiết với thông báo thành công
     *
     * @param  \Illuminate\Http\Request  $request  Thông tin điều chỉnh
     * @param  int  $id  ID của inventory cần điều chỉnh
     * @return \Illuminate\Http\RedirectResponse
     */
    public function adjustStock(InventoryAdjustmentRequest $request, $id)
    {
        try {
            $inventory = Inventory::findOrFail($id);

            if ($request->adjustment_type === 'in') { // neu adjustment_type la 'in' (nhap kho)
                // Stock in - nhập kho
                $inventory->stock_in += $request->quantity; // cong so luong nhap kho vao stock_in
                $inventory->current_stock += $request->quantity; // cong so luong nhap kho vao current_stock
            } else {
                // Stock out - xuất kho
                if ($inventory->current_stock < $request->quantity) { // neu current_stock nho hon so luong xuat kho
                    return back()->with('error', 'Số lượng xuất kho vượt quá tồn kho hiện tại!'); // hien thi thong bao loi
                }
                $inventory->stock_out += $request->quantity; // cong so luong xuat kho vao stock_out
                $inventory->current_stock -= $request->quantity; // tru so luong xuat kho vao current_stock
            }

            $inventory->save(); // luu thay doi vao database

            $action = $request->adjustment_type === 'in' ? 'nhập' : 'xuất';

            return redirect()->route('dashboard.inventory.show', $id) // chuyen huong ve trang chi tiet inventory
                ->with('success', "Đã {$action} kho thành công {$request->quantity} sản phẩm!"); // hien thi thong bao thanh cong

        } catch (\Exception $e) { // neu co loi xay ra
            return back()
                ->with('error', 'Lỗi khi điều chỉnh tồn kho: '.$e->getMessage()); // hien thi thong bao loi
        }
    }
}
