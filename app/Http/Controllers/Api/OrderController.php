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
        $orders = Order::all(); // $orders lay tu database, chua toan bo don hang , Order::all() tra ve toan bo don hang
        if ($request->has('with_user') && $request->get('with_user')) { // Neu co tham so with_user=true trong query string, thi load quan he user, tham so o day la ?with_user=true
            $orders->load('user'); // load quan he 'user' neu co tham so with_user=true
        } else {
            $orders->loadCount('products'); // Neu khong co tham so with_user, chi load count san pham trong moi don hang
        }

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
