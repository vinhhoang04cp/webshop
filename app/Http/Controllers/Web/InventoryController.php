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
    public function index(Request $request)
    {
        try {
            // Query inventory with product relationship
            $query = Inventory::with('product.category');

            // Search by product name
            if ($request->has('search') && $request->search) {
                $searchTerm = $request->search;
                $query->whereHas('product', function ($q) use ($searchTerm) {
                    $q->where('name', 'like', '%'.$searchTerm.'%');
                });
            }

            // Filter by stock status
            if ($request->has('stock_status') && $request->stock_status !== '') {
                $status = $request->stock_status;
                if ($status === 'low') {
                    // Low stock: current_stock < 10
                    $query->where('current_stock', '<', 10);
                } elseif ($status === 'out') {
                    // Out of stock: current_stock = 0
                    $query->where('current_stock', '=', 0);
                } elseif ($status === 'available') {
                    // Available: current_stock >= 10
                    $query->where('current_stock', '>=', 10);
                }
            }

            // Sort by
            $sortBy = $request->get('sort_by', 'updated_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = 15;
            $inventories = $query->paginate($perPage);

            return view('dashboard.inventory.index', [
                'paginatedInventory' => $inventories->items(),
                'pagination' => $inventories,
                'search' => $request->search,
                'stock_status' => $request->stock_status,
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
            ]);

        } catch (\Exception $e) {
            return view('dashboard.inventory.index', [
                'paginatedInventory' => [],
                'pagination' => null,
                'error' => 'Lỗi khi tải danh sách tồn kho: '.$e->getMessage(),
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

            return view('dashboard.inventory.edit', compact('inventory'));
        } catch (\Exception $e) {
            return redirect()->route('dashboard.inventory.index')
                ->with('error', 'Không thể tải form chỉnh sửa: '.$e->getMessage());
        }
    }

    /**
     * Update the specified inventory record.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'stock_in' => 'required|integer|min:0',
            'stock_out' => 'required|integer|min:0',
            'current_stock' => 'required|integer|min:0',
        ]);

        try {
            $inventory = Inventory::findOrFail($id);

            $inventory->update([
                'stock_in' => $request->stock_in,
                'stock_out' => $request->stock_out,
                'current_stock' => $request->current_stock,
            ]);

            return redirect()->route('dashboard.inventory.show', $id)
                ->with('success', 'Cập nhật tồn kho thành công!');

        } catch (\Exception $e) {
            return back()
                ->with('error', 'Lỗi khi cập nhật tồn kho: '.$e->getMessage())
                ->withInput();
        }
    }

    /**
     * Adjust stock (add or remove stock).
     */
    public function adjustStock(Request $request, $id)
    {
        $request->validate([
            'adjustment_type' => 'required|in:in,out',
            'quantity' => 'required|integer|min:1',
            'note' => 'nullable|string|max:500',
        ]);

        try {
            $inventory = Inventory::findOrFail($id);

            if ($request->adjustment_type === 'in') {
                // Stock in - nhập kho
                $inventory->stock_in += $request->quantity;
                $inventory->current_stock += $request->quantity;
            } else {
                // Stock out - xuất kho
                if ($inventory->current_stock < $request->quantity) {
                    return back()->with('error', 'Số lượng xuất kho vượt quá tồn kho hiện tại!');
                }
                $inventory->stock_out += $request->quantity;
                $inventory->current_stock -= $request->quantity;
            }

            $inventory->save();

            $action = $request->adjustment_type === 'in' ? 'nhập' : 'xuất';
            return redirect()->route('dashboard.inventory.show', $id)
                ->with('success', "Đã {$action} kho thành công {$request->quantity} sản phẩm!");

        } catch (\Exception $e) {
            return back()
                ->with('error', 'Lỗi khi điều chỉnh tồn kho: '.$e->getMessage());
        }
    }
}
