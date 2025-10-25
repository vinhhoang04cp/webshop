<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\CouponRequest;
use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Coupon::with('product');

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

    public function store(CouponRequest $request)
    {
        try {
            $coupon = Coupon::create([
                'code' => strtoupper($request->code),
                'discount_type' => $request->discount_type,
                'discount_value' => $request->discount_value,
                'product_id' => $request->product_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'is_active' => $request->has('is_active'),
            ]);

            if ($coupon->is_active && $request->product_id) {
                $this->applyDiscountToProduct($coupon);
            }

            return redirect()->route('dashboard.coupons.index')
                ->with('success', 'Coupon đã được tạo thành công!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Lỗi khi tạo coupon: '.$e->getMessage());
        }
    }

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

    public function update(CouponRequest $request, $id)
    {
        try {
            $coupon = Coupon::findOrFail($id);
            $oldProductId = $coupon->product_id;
            $oldIsActive = $coupon->is_active;

            $coupon->update([
                'code' => strtoupper($request->code),
                'discount_type' => $request->discount_type,
                'discount_value' => $request->discount_value,
                'product_id' => $request->product_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'is_active' => $request->has('is_active'),
            ]);

            if ($coupon->is_active && $request->product_id) {
                $this->applyDiscountToProduct($coupon);
            }

            return redirect()->route('dashboard.coupons.index')
                ->with('success', 'Coupon đã được cập nhật!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Lỗi khi cập nhật: '.$e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $coupon = Coupon::findOrFail($id);

            if ($coupon->is_active && $coupon->product_id) {
                $this->restoreProductPrice($coupon->product_id);
            }

            $coupon->delete();

            return redirect()->route('dashboard.coupons.index')
                ->with('success', 'Coupon đã được xóa!');

        } catch (\Exception $e) {
            return redirect()->route('dashboard.coupons.index')
                ->with('error', 'Lỗi khi xóa coupon: '.$e->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        try {
            $coupon = Coupon::findOrFail($id);

            if ($coupon->is_active && $coupon->product_id) {
                $this->restoreProductPrice($coupon->product_id);
            }

            $coupon->update(['is_active' => ! $coupon->is_active]);

            if ($coupon->is_active && $coupon->product_id) {
                $this->applyDiscountToProduct($coupon);
            }

            $status = $coupon->is_active ? 'kích hoạt' : 'vô hiệu hóa';

            return redirect()->route('dashboard.coupons.index')
                ->with('success', "Coupon đã được {$status}!");

        } catch (\Exception $e) {
            return redirect()->route('dashboard.coupons.index')
                ->with('error', 'Lỗi: '.$e->getMessage());
        }
    }

    private function applyDiscountToProduct($coupon)
    {
        if (! $coupon->product_id) {
            return;
        }

        $product = Product::find($coupon->product_id);
        if (! $product) {
            return;
        }

        if ($product->original_price === null) {
            $product->original_price = $product->price;
        }

        $discountedPrice = $product->original_price - $coupon->calculateDiscount($product->original_price);

        $product->price = max(0, $discountedPrice);
        $product->save();
    }

    private function restoreProductPrice($productId)
    {
        $product = Product::find($productId);
        if (! $product || $product->original_price === null) {
            return;
        }

        $product->price = $product->original_price;
        $product->original_price = null;
        $product->save();
    }
}
