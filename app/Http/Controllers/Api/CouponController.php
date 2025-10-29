<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CouponRequest;
use App\Http\Resources\CouponResource;
use App\Http\Resources\ErrorResource;
use App\Http\Resources\SuccessResource;
use App\Services\CouponService;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    protected $couponService;

    public function __construct(CouponService $couponService)
    {
        $this->couponService = $couponService;
    }

    /**
     * Hiển thị danh sách mã giảm giá
     */
    public function index(Request $request)
    {
        $filters = $request->only(['code', 'description', 'is_active', 'discount_type', 'product_id']);
        $perPage = $request->input('per_page', 15);
        $coupons = $this->couponService->getCoupons($filters, $perPage);

        return CouponResource::collection($coupons)->additional([
            'status' => true,
            'message' => 'Coupons retrieved successfully',
        ]);
    }

    /**
     * Lưu mã giảm giá mới được tạo
     */
    public function store(CouponRequest $request)
    {
        $coupon = $this->couponService->createCouponFull($request->validated());

        return (new CouponResource($coupon))->additional([
            'status' => true,
            'message' => 'Coupon created successfully',
        ])->response()->setStatusCode(201);
    }

    /**
     * Hiển thị mã giảm giá theo ID
     */
    public function show(string $id)
    {
        $coupon = $this->couponService->findCoupon($id, true);

        if (! $coupon) {
            return ErrorResource::notFound('Coupon not found');
        }

        return (new CouponResource($coupon))->additional([
            'status' => true,
            'message' => 'Coupon retrieved successfully',
        ]);
    }

    /**
     * Cập nhật mã giảm giá theo ID
     */
    public function update(CouponRequest $request, string $id)
    {
        $coupon = $this->couponService->findCoupon($id, false);

        if (! $coupon) {
            return ErrorResource::notFound('Coupon not found');
        }

        $coupon = $this->couponService->updateCouponFull($id, $request->validated());

        return (new CouponResource($coupon))->additional([
            'status' => true,
            'message' => 'Coupon updated successfully',
        ]);
    }

    /**
     * Xóa mã giảm giá theo ID
     */
    public function destroy(string $id)
    {
        $coupon = $this->couponService->findCoupon($id, false);

        if (! $coupon) {
            return ErrorResource::notFound('Coupon not found');
        }

        $this->couponService->deleteCoupon($id);

        return SuccessResource::deleted('Coupon deleted successfully');
    }

    /**
     * Bật/tắt trạng thái hoạt động của mã giảm giá
     */
    public function toggleStatus(string $id)
    {
        $coupon = $this->couponService->findCoupon($id, false);

        if (! $coupon) {
            return ErrorResource::notFound('Coupon not found');
        }

        $coupon = $this->couponService->toggleCouponStatus($id);

        $statusText = $coupon->is_active ? 'activated' : 'deactivated';

        return (new CouponResource($coupon))->additional([
            'status' => true,
            'message' => "Coupon {$statusText} successfully",
        ]);
    }

    /**
     * Kiểm tra tính hợp lệ của mã giảm giá
     */
    public function validate(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'order_amount' => 'nullable|numeric|min:0',
        ]);

        $coupon = \App\Models\Coupon::where('code', strtoupper($request->code))->first();

        if (! $coupon) {
            return ErrorResource::notFound('Coupon not found', [
                'valid' => false,
                'message' => 'Mã giảm giá không tồn tại',
            ]);
        }

        $orderAmount = $request->input('order_amount', 0);
        $validation = $coupon->isValid($orderAmount);

        if ($validation['valid']) {
            $discount = $coupon->calculateDiscount($orderAmount);

            return (new CouponResource($coupon))->additional([
                'status' => true,
                'message' => 'Coupon is valid',
                'discount_amount' => $discount,
                'final_amount' => max(0, $orderAmount - $discount),
            ]);
        }

        return ErrorResource::badRequest($validation['message'], [
            'valid' => false,
            'message' => $validation['message'],
            'coupon' => new CouponResource($coupon),
        ]);
    }
}
