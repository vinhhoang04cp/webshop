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
    public function index(Request $request) // hiển thị danh sách coupon
    {
        try {
            $query = Coupon::with('product'); // query coupon voi relationship product

            // Tìm kiếm theo code
            if ($request->has('search') && $request->search) { // neu request truyen len co tham so search va search khac rong
                $query->where('code', 'like', '%'.$request->search.'%'); // tim kiem theo code
            }

            $coupons = $query->orderBy('created_at', 'desc')->paginate(15); // phan trang 15 coupon/trang

            return view('dashboard.coupons.index', [
                'coupons' => $coupons, // truyen coupons vao view
                'pagination' => $coupons, // truyen pagination vao view
            ]);

        } catch (\Exception $e) {
            \Log::error('Coupon index error: '.$e->getMessage()); // log loi neu co

            return view('dashboard.coupons.index', [
                'coupons' => collect(),
                'pagination' => null, // truyen pagination vao view
                'error' => 'Lỗi khi tải danh sách coupon: '.$e->getMessage(), // truyen error vao view
            ]);
        }
    }

    /**
     * Hiển thị form tạo coupon mới
     */
    public function create(Request $request) // (Request $request) de lay du lieu tu Http request
    {
        try {
            $products = Product::orderBy('name')->get(); // lay tat ca products, orderby() la ham de sap xep theo name
            $selectedProductId = $request->input('product_id', null); // lay product_id tu request

            return view('dashboard.coupons.create', compact('products', 'selectedProductId')); // truyen products va selectedProductId vao view
        } catch (\Exception $e) {
            return redirect()->route('dashboard.coupons.index') // chuyen huong ve trang danh sach coupon
                ->with('error', 'Lỗi khi tải form: '.$e->getMessage()); // truyen error vao view
        }
    }

    /**
     * Lưu coupon mới
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code', // code la truong bat buoc, string, max 50 ky tu, unique trong bang coupons, code
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0', // discount_value la truong bat buoc, numeric, min 0
            'product_id' => 'nullable|exists:products,product_id', // product_id la truong nullable, exists trong bang products, product_id
            'start_date' => 'required|date', // start_date la truong bat buoc, date
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'boolean',
        ]);

        try {
            // Validate giá trị phần trăm
            if ($request->discount_type === 'percentage' && $request->discount_value > 100) { // neu gia tri cua discount_type la phan tram va discount_value lon hon 100 thi tra ve loi
                return redirect()->back() // tra ve trang truoc do
                    ->withInput() // truyen input vao view
                    ->withErrors(['discount_value' => 'Giá trị phần trăm không được vượt quá 100%']); // truyen error vao view
            }

            $coupon = Coupon::create([
                'code' => strtoupper($request->code), // code la truong bat buoc, string, max 50 ky tu, unique trong bang coupons, code
                'discount_type' => $request->discount_type,
                'discount_value' => $request->discount_value, // discount_value la truong bat buoc, numeric, min 0
                'product_id' => $request->product_id, // product_id la truong nullable, exists trong bang products, product_id
                'start_date' => $request->start_date, // start_date la truong bat buoc, date
                'end_date' => $request->end_date,
                'is_active' => $request->has('is_active'),
            ]);

            // Nếu coupon active và áp dụng cho sản phẩm cụ thể, cập nhật giá sản phẩm
            if ($coupon->is_active && $request->product_id) {
                $this->applyDiscountToProduct($coupon);
            }

            return redirect()->route('dashboard.coupons.index') // chuyen huong ve trang danh sach coupon
                ->with('success', 'Coupon đã được tạo thành công!'); // truyen success vao view

        } catch (\Exception $e) { // bat loi neu co
            return redirect()->back() // tra ve trang truoc do
                ->withInput()
                ->with('error', 'Lỗi khi tạo coupon: '.$e->getMessage()); // truyen error vao view
        }
    }

    /**
     * Hiển thị chi tiết coupon
     */
    public function show($id) // show($id) la ham de hien thi chi tiet coupon
    {
        try {
            $coupon = Coupon::with('product')->findOrFail($id); // findOrFail la ham de tim kiem coupon theo id, with('product') la ham de lay relationship product

            return view('dashboard.coupons.show', compact('coupon')); // truyen coupon vao view
        } catch (\Exception $e) {
            return redirect()->route('dashboard.coupons.index') // chuyen huong ve trang danh sach coupon
                ->with('error', 'Không tìm thấy coupon.'); // truyen error vao view
        }
    }

    /**
     * Hiển thị form chỉnh sửa
     */
    public function edit($id) // edit($id) la ham de hien thi form chinh sua coupon
    {
        try {
            $coupon = Coupon::findOrFail($id); // findOrFail la ham de tim kiem coupon theo id
            $products = Product::orderBy('name')->get();

            return view('dashboard.coupons.edit', compact('coupon', 'products')); // truyen coupon va products vao view
        } catch (\Exception $e) {
            return redirect()->route('dashboard.coupons.index') // chuyen huong ve trang danh sach coupon
                ->with('error', 'Không tìm thấy coupon.'); // truyen error vao view
        }
    }

    /**
     * Cập nhật coupon
     */
    public function update(Request $request, $id) // update(Request $request, $id) la ham de cap nhat coupon
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code,'.$id.',coupon_id', // code la truong bat buoc, string, max 50 ky tu, unique trong bang coupons, code
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0', // discount_value la truong bat buoc, numeric, min 0
            'product_id' => 'nullable|exists:products,product_id', // product_id la truong nullable, exists trong bang products, product_id
            'start_date' => 'required|date', // start_date la truong bat buoc, date
            'end_date' => 'required|date|after:start_date', // end_date la truong bat buoc, date, after:start_date
            'is_active' => 'boolean',
        ]);

        try {
            $coupon = Coupon::findOrFail($id); // findOrFail la ham de tim kiem coupon theo id
            $oldProductId = $coupon->product_id; // Lưu lại product_id cũ
            $oldIsActive = $coupon->is_active; // Lưu lại trạng thái cũ

            // Validate giá trị phần trăm
            if ($request->discount_type === 'percentage' && $request->discount_value > 100) { // neu gia tri cua discount_type la phan tram va discount_value lon hon 100 thi tra ve loi
                return redirect()->back() // tra ve trang truoc do
                    ->withInput() // truyen input vao view
                    ->withErrors(['discount_value' => 'Giá trị phần trăm không được vượt quá 100%']); // truyen error vao view
            }

            // Nếu sản phẩm cũ có áp dụng giảm giá, khôi phục giá gốc
            if ($oldIsActive && $oldProductId) {
                $this->restoreProductPrice($oldProductId);
            }

            $coupon->update([ // update coupon
                'code' => strtoupper($request->code), // code la truong bat buoc, string, max 50 ky tu, unique trong bang coupons, code
                'discount_type' => $request->discount_type, // discount_type la truong bat buoc, in:percentage,fixed
                'discount_value' => $request->discount_value, // discount_value la truong bat buoc, numeric, min 0
                'product_id' => $request->product_id, // product_id la truong nullable, exists trong bang products, product_id
                'start_date' => $request->start_date, // start_date la truong bat buoc, date
                'end_date' => $request->end_date, // end_date la truong bat buoc, date, after:start_date
                'is_active' => $request->has('is_active'), // is_active la truong bat buoc, boolean
            ]);

            // Nếu coupon mới active và áp dụng cho sản phẩm cụ thể, áp dụng giảm giá
            if ($coupon->is_active && $request->product_id) {
                $this->applyDiscountToProduct($coupon);
            }

            return redirect()->route('dashboard.coupons.index') // chuyen huong ve trang danh sach coupon
                ->with('success', 'Coupon đã được cập nhật!'); // truyen success vao view

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Lỗi khi cập nhật: '.$e->getMessage()); // truyen error vao view
        }
    }

    /**
     * Xóa coupon
     */
    public function destroy($id) // destroy($id) la ham de xoa coupon
    {
        try {
            $coupon = Coupon::findOrFail($id); // findOrFail la ham de tim kiem coupon theo id

            // Nếu coupon đang active và áp dụng cho sản phẩm cụ thể, khôi phục giá gốc
            if ($coupon->is_active && $coupon->product_id) {
                $this->restoreProductPrice($coupon->product_id);
            }

            $coupon->delete(); // delete la ham de xoa coupon

            return redirect()->route('dashboard.coupons.index') // chuyen huong ve trang danh sach coupon
                ->with('success', 'Coupon đã được xóa!'); // truyen success vao view

        } catch (\Exception $e) {
            return redirect()->route('dashboard.coupons.index')
                ->with('error', 'Lỗi khi xóa coupon: '.$e->getMessage()); // truyen error vao view
        }
    }

    /**
     * Toggle trạng thái
     */
    public function toggleStatus($id) // toggleStatus($id) la ham de chuyen trang thai coupon
    {
        try {
            $coupon = Coupon::findOrFail($id); // findOrFail la ham de tim kiem coupon theo id

            // Nếu đang active và có product_id, khôi phục giá gốc trước khi toggle
            if ($coupon->is_active && $coupon->product_id) {
                $this->restoreProductPrice($coupon->product_id);
            }

            $coupon->update(['is_active' => ! $coupon->is_active]); // update la ham de cap nhat coupon

            // Nếu sau khi toggle mà active và có product_id, áp dụng giảm giá
            if ($coupon->is_active && $coupon->product_id) {
                $this->applyDiscountToProduct($coupon);
            }

            $status = $coupon->is_active ? 'kích hoạt' : 'vô hiệu hóa'; // status la truong bat buoc, boolean

            return redirect()->route('dashboard.coupons.index') // chuyen huong ve trang danh sach coupon
                ->with('success', "Coupon đã được {$status}!"); // truyen success vao view

        } catch (\Exception $e) { // bat loi neu co
            return redirect()->route('dashboard.coupons.index') // chuyen huong ve trang danh sach coupon
                ->with('error', 'Lỗi: '.$e->getMessage()); // truyen error vao view
        }
    }

    /**
     * Áp dụng giảm giá cho sản phẩm
     */
    private function applyDiscountToProduct($coupon)
    {
        if (! $coupon->product_id) {
            return;
        }

        $product = Product::find($coupon->product_id);
        if (! $product) {
            return;
        }

        // Lưu giá gốc nếu chưa có
        if ($product->original_price === null) {
            $product->original_price = $product->price;
        }

        // Tính giá sau khi giảm
        $discountedPrice = $product->original_price - $coupon->calculateDiscount($product->original_price);

        // Cập nhật giá sản phẩm
        $product->price = max(0, $discountedPrice); // Đảm bảo giá không âm
        $product->save();
    }

    /**
     * Khôi phục giá gốc cho sản phẩm
     */
    private function restoreProductPrice($productId)
    {
        $product = Product::find($productId);
        if (! $product || $product->original_price === null) {
            return;
        }

        // Khôi phục giá gốc
        $product->price = $product->original_price;
        $product->original_price = null;
        $product->save();
    }
}
