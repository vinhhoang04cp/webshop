<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Order::query();
        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }
        if ($request->has('user_id')) {
            $query->where('user_id', $request->get('user_id'));
        }
        if ($request->has('min_date')) {
            $query->where('order_date', '>=', $request->get('min_date'));
        }
        if ($request->has('max_date')) {
            $query->where('order_date', '<=', $request->get('max_date'));
        }
        if ($request->has('min_total')) {
            $query->where('total_amount', '>=', $request->get('min_total'));
        }
        if ($request->has('max_total')) {
            $query->where('total_amount', '<=', $request->get('max_total'));
        }

        $orders = $query->get();
        $orders = $query->paginate(10); // Paginate results, 10 per page

        return response()->json($orders);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
        $order = Order::findOrFail($id);

        return response()->json($order);
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
