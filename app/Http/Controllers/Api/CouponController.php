<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Coupon::query();

        if ($request->has('code')) {
            $query->where('code', 'LIKE', '%'.$request->get('code').'%');
        }

        if ($request->has('description')) {
            $query->where('description', 'LIKE', '%'.$request->get('description').'%');
        }

        $coupons = $query->paginate(15);

        return response()->json([
            'status' => true,
            'data' => $coupons,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $coupon = Coupon::create([
            'code' => $request->code,
            'name' => $request->name,
            'discount_type' => $request->discount_type,
            'discount_value' => $request->discount_value,
            'min_order_amount' => $request->min_order_amount,
            'max_discount_amount' => $request->max_discount_amount,
            'usage_limit' => $request->usage_limit,
            'product_id' => $request->product_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'is_active' => $request->is_active,
        ]);

        $coupon = $coupon->fresh();

        return response()->json([
            'status' => true,
            'data' => $coupon,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
