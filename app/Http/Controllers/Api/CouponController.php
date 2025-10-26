<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CouponRequest;
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
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['code', 'description']);
        $coupons = $this->couponService->getCoupons($filters);

        return response()->json([
            'status' => true,
            'data' => $coupons,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CouponRequest $request)
    {
        $coupon = $this->couponService->createCouponFull($request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Coupon created successfully',
            'data' => $coupon,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $coupon = $this->couponService->findCoupon($id);

        if (! $coupon) {
            return response()->json([
                'status' => false,
                'message' => 'Coupon not found',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Coupon retrieved successfully',
            'data' => $coupon,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CouponRequest $request, string $id)
    {
        $coupon = $this->couponService->findCoupon($id);

        if (! $coupon) {
            return response()->json([
                'status' => false,
                'message' => 'Coupon not found',
            ], 404);
        }

        $coupon = $this->couponService->updateCouponFull($id, $request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Coupon updated successfully',
            'data' => $coupon,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $coupon = $this->couponService->findCoupon($id);

        if (! $coupon) {
            return response()->json([
                'status' => false,
                'message' => 'Coupon not found',
            ], 404);
        }

        $this->couponService->deleteCoupon($id);

        return response()->json([
            'status' => true,
            'message' => 'Coupon deleted successfully',
        ], 200);
    }
}
