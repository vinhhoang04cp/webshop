<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    /**
     * Hiển thị danh sách coupon
     */
    public function index(Request $request)
    {
        try {
            $query = Coupon::with('product');

            // Tìm kiếm theo code
            if ($request->has('search') && $request->search) {
                $query->where('code', 'like', '%'.$request->search.'%');
            }

            $coupons = $query->orderBy('created_at', 'desc')->paginate(15);

            return view('dashboard.coupons.index', [
                'coupons' => $coupons,
                'pagination' => $coupons,
            ]);

        } catch (\Exception $e) {
            \Log::error('Coupon index error: '.$e->getMessage());

            return view('dashboard.coupons.index', [
                'coupons' => collect(),
                'pagination' => null,
                'error' => 'Lỗi khi tải danh sách coupon: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Hiển thị form tạo coupon mới
     */
    public function create(Request $request)
    {
        try {
            $products = Product::orderBy('name')->get();
            $selectedProductId = $request->input('product_id', null);

            return view('dashboard.coupons.create', compact('products', 'selectedProductId'));
        } catch (\Exception $e) {
            return redirect()->route('dashboard.coupons.index')
                ->with('error', 'Lỗi khi tải form: '.$e->getMessage());
        }
    }

    /**
     * Lưu coupon mới
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'product_id' => 'nullable|exists:products,product_id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'boolean',
        ]);

        try {
            // Validate giá trị phần trăm
            if ($request->discount_type === 'percentage' && $request->discount_value > 100) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['discount_value' => 'Giá trị phần trăm không được vượt quá 100%']);
            }

            Coupon::create([
                'code' => strtoupper($request->code),
                'discount_type' => $request->discount_type,
                'discount_value' => $request->discount_value,
                'product_id' => $request->product_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'is_active' => $request->has('is_active'),
            ]);

            return redirect()->route('dashboard.coupons.index')
                ->with('success', 'Coupon đã được tạo thành công!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Lỗi khi tạo coupon: '.$e->getMessage());
        }
    }

    /**
     * Hiển thị chi tiết coupon
     */
    public function show($id)
    {
        try {
            $coupon = Coupon::with('product')->findOrFail($id);

            return view('dashboard.coupons.show', compact('coupon'));
        } catch (\Exception $e) {
            return redirect()->route('dashboard.coupons.index')
                ->with('error', 'Không tìm thấy coupon.');
        }
    }

    /**
     * Hiển thị form chỉnh sửa
     */
    public function edit($id)
    {
        try {
            $coupon = Coupon::findOrFail($id);
            $products = Product::orderBy('name')->get();

            return view('dashboard.coupons.edit', compact('coupon', 'products'));
        } catch (\Exception $e) {
            return redirect()->route('dashboard.coupons.index')
                ->with('error', 'Không tìm thấy coupon.');
        }
    }

    /**
     * Cập nhật coupon
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code,'.$id.',coupon_id',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'product_id' => 'nullable|exists:products,product_id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'boolean',
        ]);

        try {
            $coupon = Coupon::findOrFail($id);

            // Validate giá trị phần trăm
            if ($request->discount_type === 'percentage' && $request->discount_value > 100) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['discount_value' => 'Giá trị phần trăm không được vượt quá 100%']);
            }

            $coupon->update([
                'code' => strtoupper($request->code),
                'discount_type' => $request->discount_type,
                'discount_value' => $request->discount_value,
                'product_id' => $request->product_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'is_active' => $request->has('is_active'),
            ]);

            return redirect()->route('dashboard.coupons.index')
                ->with('success', 'Coupon đã được cập nhật!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Lỗi khi cập nhật: '.$e->getMessage());
        }
    }

    /**
     * Xóa coupon
     */
    public function destroy($id)
    {
        try {
            $coupon = Coupon::findOrFail($id);
            $coupon->delete();

            return redirect()->route('dashboard.coupons.index')
                ->with('success', 'Coupon đã được xóa!');

        } catch (\Exception $e) {
            return redirect()->route('dashboard.coupons.index')
                ->with('error', 'Lỗi khi xóa coupon: '.$e->getMessage());
        }
    }

    /**
     * Toggle trạng thái
     */
    public function toggleStatus($id)
    {
        try {
            $coupon = Coupon::findOrFail($id);
            $coupon->update(['is_active' => ! $coupon->is_active]);

            $status = $coupon->is_active ? 'kích hoạt' : 'vô hiệu hóa';

            return redirect()->route('dashboard.coupons.index')
                ->with('success', "Coupon đã được {$status}!");

        } catch (\Exception $e) {
            return redirect()->route('dashboard.coupons.index')
                ->with('error', 'Lỗi: '.$e->getMessage());
        }
    }
}
