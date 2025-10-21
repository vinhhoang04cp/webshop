<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    /**
     * Hiển thị danh sách coupon cho admin
     */
    public function index(Request $request)
    {
        try {
            // Lấy tất cả coupons đơn giản
            $coupons = Coupon::orderBy('created_at', 'desc')->paginate(15);

            return view('dashboard.coupons.index', [
                'coupons' => $coupons,
                'pagination' => $coupons,
            ]);

        } catch (\Exception $e) {
            \Log::error('Coupon index error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

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
    public function create()
    {
        try {
            return view('dashboard.coupons.create');
        } catch (\Exception $e) {
            return redirect()->route('dashboard.coupons.index')
                ->with('error', 'Lỗi khi tải form tạo coupon: '.$e->getMessage());
        }
    }

    /**
     * Lưu coupon mới vào database
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code',
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'min_order_amount' => 'required|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'boolean',
        ]);

        try {
            // Validate percentage discount
            if ($request->discount_type === 'percentage' && $request->discount_value > 100) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['discount_value' => 'Giá trị giảm giá theo phần trăm không được vượt quá 100%']);
            }

            Coupon::create([
                'code' => strtoupper($request->code),
                'name' => $request->name,
                'description' => $request->description,
                'discount_type' => $request->discount_type,
                'discount_value' => $request->discount_value,
                'min_order_amount' => $request->min_order_amount ?? 0,
                'max_discount_amount' => $request->max_discount_amount,
                'usage_limit' => $request->usage_limit,
                'used_count' => 0,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'is_active' => $request->has('is_active') ? true : false,
            ]);

            return redirect()->route('dashboard.coupons.index')
                ->with('success', 'Coupon đã được tạo thành công!');

        } catch (\Exception $e) {
            return redirect()->route('dashboard.coupons.create')
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
            $coupon = Coupon::findOrFail($id);

            return view('dashboard.coupons.show', compact('coupon'));
        } catch (\Exception $e) {
            return redirect()->route('dashboard.coupons.index')
                ->with('error', 'Không tìm thấy coupon: '.$e->getMessage());
        }
    }

    /**
     * Hiển thị form chỉnh sửa coupon
     */
    public function edit($id)
    {
        try {
            $coupon = Coupon::findOrFail($id);

            return view('dashboard.coupons.edit', compact('coupon'));
        } catch (\Exception $e) {
            return redirect()->route('dashboard.coupons.index')
                ->with('error', 'Không tìm thấy coupon: '.$e->getMessage());
        }
    }

    /**
     * Cập nhật thông tin coupon
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code,'.$id.',coupon_id',
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'min_order_amount' => 'required|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'boolean',
        ]);

        try {
            $coupon = Coupon::findOrFail($id);

            // Validate percentage discount
            if ($request->discount_type === 'percentage' && $request->discount_value > 100) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['discount_value' => 'Giá trị giảm giá theo phần trăm không được vượt quá 100%']);
            }

            $coupon->update([
                'code' => strtoupper($request->code),
                'name' => $request->name,
                'description' => $request->description,
                'discount_type' => $request->discount_type,
                'discount_value' => $request->discount_value,
                'min_order_amount' => $request->min_order_amount ?? 0,
                'max_discount_amount' => $request->max_discount_amount,
                'usage_limit' => $request->usage_limit,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'is_active' => $request->has('is_active') ? true : false,
            ]);

            return redirect()->route('dashboard.coupons.index')
                ->with('success', 'Coupon đã được cập nhật thành công!');

        } catch (\Exception $e) {
            return redirect()->route('dashboard.coupons.edit', $id)
                ->withInput()
                ->with('error', 'Lỗi khi cập nhật coupon: '.$e->getMessage());
        }
    }

    /**
     * Xóa coupon khỏi database
     */
    public function destroy($id)
    {
        try {
            $coupon = Coupon::findOrFail($id);

            // Kiểm tra xem coupon đã được sử dụng chưa
            if ($coupon->used_count > 0) {
                return redirect()->route('dashboard.coupons.index')
                    ->with('error', 'Không thể xóa coupon đã được sử dụng. Bạn có thể vô hiệu hóa nó thay thế.');
            }

            $coupon->delete();

            return redirect()->route('dashboard.coupons.index')
                ->with('success', 'Coupon đã được xóa thành công!');

        } catch (\Exception $e) {
            return redirect()->route('dashboard.coupons.index')
                ->with('error', 'Lỗi khi xóa coupon: '.$e->getMessage());
        }
    }

    /**
     * Toggle trạng thái active/inactive của coupon
     */
    public function toggleStatus($id)
    {
        try {
            $coupon = Coupon::findOrFail($id);
            $coupon->update(['is_active' => ! $coupon->is_active]);

            $status = $coupon->is_active ? 'kích hoạt' : 'vô hiệu hóa';

            return redirect()->route('dashboard.coupons.index')
                ->with('success', "Coupon đã được {$status} thành công!");

        } catch (\Exception $e) {
            return redirect()->route('dashboard.coupons.index')
                ->with('error', 'Lỗi khi thay đổi trạng thái coupon: '.$e->getMessage());
        }
    }
}
