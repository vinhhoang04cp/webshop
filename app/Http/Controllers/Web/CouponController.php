<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\CouponRequest;
use App\Services\CouponService;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    protected $couponService;

    // injections dependeces cho CouponService
    public function __construct(CouponService $couponService)
    {
        $this->couponService = $couponService;
    }

    public function index(Request $request)
    {
        try {
            $filters = $request->only(['search']); // Lấy bộ lọc từ request voi key 'search'
            $coupons = $this->couponService->getCouponsForAdmin($filters, 15);
            //goi den phuong thuc trong service de lay danh sach coupon voi bo loc va phan trang

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
            $products = $this->couponService->getProductsForDropdown();
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
            $data = $request->validated();
            $data['is_active'] = $request->has('is_active');

            $this->couponService->createCoupon($data);

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
            $coupon = $this->couponService->getCouponDetail($id);

            return view('dashboard.coupons.show', compact('coupon'));
        } catch (\Exception $e) {
            return redirect()->route('dashboard.coupons.index')
                ->with('error', 'Không tìm thấy coupon.');
        }
    }

    public function edit($id)
    {
        try {
            $coupon = $this->couponService->getCouponById($id);
            $products = $this->couponService->getProductsForDropdown();

            return view('dashboard.coupons.edit', compact('coupon', 'products'));
        } catch (\Exception $e) {
            return redirect()->route('dashboard.coupons.index')
                ->with('error', 'Không tìm thấy coupon.');
        }
    }

    public function update(CouponRequest $request, $id)
    {
        try {
            $data = $request->validated();
            $data['is_active'] = $request->has('is_active');

            $this->couponService->updateCoupon($id, $data);

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
            $this->couponService->deleteCoupon($id);

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
            $coupon = $this->couponService->toggleCouponStatus($id);

            $status = $coupon->is_active ? 'kích hoạt' : 'vô hiệu hóa';

            return redirect()->route('dashboard.coupons.index')
                ->with('success', "Coupon đã được {$status}!");
        } catch (\Exception $e) {
            return redirect()->route('dashboard.coupons.index')
                ->with('error', 'Lỗi: '.$e->getMessage());
        }
    }
}
