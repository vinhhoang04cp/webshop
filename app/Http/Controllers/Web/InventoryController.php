<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\InventoryAdjustmentRequest;
use App\Http\Requests\InventoryRequest;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function index(Request $request)
    {
        try {
            $filters = $request->only(['search', 'stock_status', 'sort_by', 'sort_order']);
            $inventories = $this->inventoryService->getInventoriesForAdmin($filters, 15);

            return view('dashboard.inventory.index', [
                'paginatedInventory' => $inventories->items(),
                'pagination' => $inventories,
                'search' => $request->search,
                'stock_status' => $request->stock_status,
                'sort_by' => $filters['sort_by'] ?? 'updated_at',
                'sort_order' => $filters['sort_order'] ?? 'desc',
            ]);
        } catch (\Exception $e) {
            return view('dashboard.inventory.index', [
                'paginatedInventory' => [],
                'pagination' => null,
                'error' => 'Lỗi khi tải danh sách tồn kho: '.$e->getMessage(),
            ]);
        }
    }

    public function show($id)
    {
        try {
            $inventory = $this->inventoryService->getInventoryDetail($id);

            return view('dashboard.inventory.show', compact('inventory'));
        } catch (\Exception $e) {
            return redirect()->route('dashboard.inventory.index')
                ->with('error', 'Không tìm thấy bản ghi tồn kho: '.$e->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            $inventory = $this->inventoryService->getInventoryWithProduct($id);

            return view('dashboard.inventory.edit', compact('inventory'));
        } catch (\Exception $e) {
            return redirect()->route('dashboard.inventory.index')
                ->with('error', 'Không thể tải form chỉnh sửa: '.$e->getMessage());
        }
    }

    public function update(InventoryRequest $request, $id)
    {
        try {
            $this->inventoryService->updateInventory($id, $request->validated());

            return redirect()->route('dashboard.inventory.show', $id)
                ->with('success', 'Cập nhật tồn kho thành công!');
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Lỗi khi cập nhật tồn kho: '.$e->getMessage())
                ->withInput();
        }
    }

    public function adjustStock(InventoryAdjustmentRequest $request, $id)
    {
        try {
            $this->inventoryService->adjustStock(
                $id,
                $request->adjustment_type,
                $request->quantity
            );

            $action = $request->adjustment_type === 'in' ? 'nhập' : 'xuất';

            return redirect()->route('dashboard.inventory.show', $id)
                ->with('success', "Đã {$action} kho thành công {$request->quantity} sản phẩm!");
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Lỗi khi điều chỉnh tồn kho: '.$e->getMessage());
        }
    }
}
