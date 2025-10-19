<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /**
     * Display a listing of inventory records for admin UI.
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
     * Display the specified inventory record.
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
     * Show the form for editing the specified inventory record.
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
     * Update the specified inventory record.
     */
    public function update(Request $request, $id)
    {
        $request->validate([ // validate du lieu tu request
            'stock_in' => 'required|integer|min:0', // stock_in bat buoc phai la so nguyen lon hon hoac bang 0
            'stock_out' => 'required|integer|min:0', // stock_out bat buoc phai la so nguyen lon hon hoac bang 0
            'current_stock' => 'required|integer|min:0', // current_stock bat buoc phai la so nguyen lon hon hoac bang 0
        ]);

        try {
            $inventory = Inventory::findOrFail($id); // tim kiem inventory theo id, neu khong tim thay thi throw exception su dung eloquent

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
     * ham dieu chinh ton kho (nhap kho / xuat kho)
     */
    public function adjustStock(Request $request, $id)
    {
        $request->validate([ // validate du lieu tu request
            'adjustment_type' => 'required|in:in,out', // adjustment_type la truong du lieu bieu thi loai dieu chinh (in: nhap kho, out: xuat kho)
            'quantity' => 'required|integer|min:1', // quantity bat buoc phai la so nguyen lon hon 0
            'note' => 'nullable|string|max:500', // note khong bat buoc, la chuoi ky tu, toi da 500 ky tu
        ]);

        try {
            $inventory = Inventory::findOrFail($id); // tim kiem inventory theo id, neu khong tim thay thi throw exception su dung eloquent

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
