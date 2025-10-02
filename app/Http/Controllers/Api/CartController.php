<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CartRequest;
use App\Http\Resources\CartResource;
use App\Models\Cart;
use App\Models\Product;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Cart::with('items.product'); // Load cả items và product để tính tổng giá
        // Loc theo user_id neu co
        if ($request->has('user_id')) { // neu request co user_id , $request co nghia la lay du lieu tu request
            $query->where('user_id', $request->get('user_id')); // them dieu kien loc user_id vao truy van
        }
        if ($request->has('product_id')) {
            $query->where('product_id', $request->get('product_id'));
        }
        $carts = $query->paginate(10); // Phan trang 10 per page by default

        // Tính tổng giá cho mỗi cart
        $cartsData = []; // tao 1 mang luu tru du lieu cart
        $grandTotal = 0; // Tổng giá của tất cả cart

        foreach ($carts as $cart) { // cart la bien luu tru cart hien tai , lap voi moi cart trong carts
            $cartTotalAmount = 0; // khoi tao tong tien cua cart hien tai
            $totalItems = 0; // tong so luong item trong cart

            foreach ($cart->items as $cartItem) { // cartItem la bien luu tru moi 1 item, lap voi moi cart item trong cart
                $cartTotalAmount += $cartItem->product->price * $cartItem->quantity; // tong tien se bang tien cua cart item * so luong
                $totalItems += $cartItem->quantity; // cong don so luong item trong cart
            }

            $cartData = new CartResource($cart); // chuan hoa du lieu cart hien tai
            // them thong tin tong tien va tong so luong item vao cartData
            $cartData->additional([
                'total_amount' => $cartTotalAmount, // tong tien cua cart hien tai , lay tu bien $cartTotalAmount
                'total_items' => $totalItems, // tong so luong item trong cart, lay tu bien $totalItems
            ]);

            $cartsData[] = [ // them du lieu cart vao mang cartsData
                'cart' => $cartData, // chuan hoa du lieu cart hien tai
                'total_amount' => $cartTotalAmount, // tong tien cua cart hien tai , lay tu bien $cartTotalAmount
                'total_items' => $totalItems, // tong so luong item trong cart, lay tu bien $totalItems
            ];

            $grandTotal += $cartTotalAmount; // cong don tong tien cua tat ca cart
        }

        return response()->json([
            'status' => true,
            'message' => 'Carts retrieved successfully',
            'data' => $cartsData,
            'pagination' => [
                'current_page' => $carts->currentPage(),
                'per_page' => $carts->perPage(),
                'total' => $carts->total(),
                'last_page' => $carts->lastPage(),
            ],
            'grand_total' => $grandTotal, // Tổng giá của tất cả cart
            'total_carts' => $carts->total(),
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CartRequest $request) // (Cartrequest $request) la tham so truyen vao, CartRequest la lop request tu tao de validate du lieu
    {
        DB::beginTransaction(); // DB transaction lam viec voi nhieu cau lenh DB, neu co loi thi rollback ve trang thai truoc do
        try {
            $cartData = $request->validated(); // $cartData la 1 mang  duoc luu tru du lieu da duoc validate

            // Xác định user_id
            $userId = auth()->id() ?? $request->user_id ?? 1; // $userId lay tu auth hoac request hoac mac dinh la 1

            // check xem $cartData truyen tu request co cart_id khong
            if (isset($cartData['cart_id'])) { // neu co cart_id thi tim cart theo cart_id va user_id
                $cart = Cart::where('cart_id', $cartData['cart_id']) // query cart theo cart_id
                    ->where('user_id', $userId) // Kiem tra cart thuoc ve user hien tai
                    ->first(); // Lay cart neu co

                if (! $cart) {
                    throw new Exception("Cart with ID {$cartData['cart_id']} not found or does not belong to user"); // thong bao loi rang cart khong ton tai hoac khong thuoc ve user
                } // Neu khong tim thay cart thi throw exception
            } else {
                $cart = Cart::where('user_id', $userId)->first(); // first() lay cart dau tien neu co
            }

            // Nếu chưa có cart thì tạo mới
            if (! $cart) { // neu khong ton tai cart thi tao moi cart
                $cart = Cart::create([
                    'user_id' => $userId, // gan user_id
                ]);
            }

            $totalAmount = 0; // tong tien ban dau la 0
            $itemsToAdd = []; // Khoi tao mang luu tru items can them

            // Xu lay array truyen vao duoi 2 dang items hoac product_id va quantity
            // Neu du lieu truyen vao co items va la mang , bien itemsToAdd se luu tru cac items can them
            /*
             Test
            {
              "user_id": 1,
              "product_id": 1,
              "quantity": 2
            }
            */
            if (isset($cartData['items']) && is_array($cartData['items'])) {
                $itemsToAdd = $cartData['items'];  // gan cac items duoc truyen vao mang itemsToAdd
            }
            // Xu ly single item neu co product_id va quantity, mang truyen vao se duoc luu tru trong itemsToAdd
            /*
                Test
                {
                  "user_id": 1,
                  "items": [
                    {
                      "product_id": 1,
                      "quantity": 2
                    },
                    {
                      "product_id": 2,
                      "quantity": 1
                    }
                  ]
                }
            */
            // neu du lieu truyen vao co product_id va quantity
            elseif (isset($cartData['product_id']) && isset($cartData['quantity'])) { // $cartData la du lieu truyen vao tu request
                $itemsToAdd[] = [ // them phan tu vao mang itemsToAdd
                    'product_id' => $cartData['product_id'], // gan product_id va quantity tu request vao mang itemsToAdd
                    'quantity' => $cartData['quantity'],
                ];
            }

            // Thêm hoặc cập nhật cart items
            foreach ($itemsToAdd as $item) { // lap voi moi item trong mang itemsToAdd
                $product = Product::find($item['product_id']); // tim product theo product_id

                if (! $product) { // neu khong tim thay product thi throw exception
                    throw new Exception("Product with ID {$item['product_id']} not found");
                }

                // Kiểm tra xem sản phẩm đã có trong cart chưa
                $cartItem = $cart->items()->where('product_id', $item['product_id'])->first();

                if ($cartItem) { // $cartitem la bien luu tru cart item
                    // Nếu đã có thì cập nhật số lượng (cộng dồn)
                    $cartItem->quantity += $item['quantity']; // cong don so luong
                    $cartItem->save(); // luu thay doi
                } else {
                    // Nếu chưa có thì tạo mới
                    $cart->items()->create([ // tao moi cart item
                        'product_id' => $item['product_id'], // gan product_id
                        'quantity' => $item['quantity'], // gan quantity
                    ]);
                }

                // Tính tổng tiền: giá sản phẩm * số lượng
                $itemTotal = $product->price * $item['quantity']; // tonm tien = gia san pham * so luong cong don lai
                $totalAmount += $itemTotal;
            }

            DB::commit(); // luu thay doi neu khong co loi xay ra

            // Load lại cart với relationships
            $cart = Cart::with('items.product')->find($cart->cart_id); // load lai cart voi items va product theo cart_id

            // Tính tổng tiền của toàn bộ cart (bao gồm cả items cũ)
            $cartTotalAmount = 0; // tong tien cua cart ban dau la 0
            foreach ($cart->items as $cartItem) { // cartItem la bien luu tru cart item, lap voi moi cart item trong cart
                $cartTotalAmount += $cartItem->product->price * $cartItem->quantity; // Tong tien se bang tien cua cart item * so luong
            }

            // Trả về dữ liệu đã chuẩn hoá sử dụng CartResource với thông tin tổng tiền
            return response()->json([
                'status' => true,
                'message' => 'Items added to cart successfully',
                'data' => new CartResource($cart),
                'total_amount' => $cartTotalAmount,
                'total_items' => $cart->items->sum('quantity'), // Tong so luong item trong cart
                'items_added' => count($itemsToAdd), // So luong item da them vao cart
            ], 201);
        } catch (Exception $e) { // bat loi
            DB::rollback();

            return response()->json([ // tra ve duoi dang json
                'status' => false,
                'message' => 'Failed to add items to cart',
                'error' => $e->getMessage(),
            ], 500);
        }
        \DB::reorderIds(); // Goi ham reorderIds de sap xep lai ID sau khi tao moi

        return (new CartResource($cart))
            ->response()
            ->setStatusCode(201); // Trả về 201 Created với dữ liệu đã chuẩn hoá
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CartRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $cartData = $request->validated();

            // Tìm cart theo ID
            $cart = Cart::find($id);

            if (! $cart) {
                throw new Exception("Cart with ID {$id} not found");
            }

            // Kiểm tra quyền sở hữu cart
            $userId = auth()->id() ?? $request->user_id ?? 1;
            if ($cart->user_id !== $userId) {
                throw new Exception('Unauthorized to update this cart');
            }

            $itemsToUpdate = [];

            // Xử lý items array nếu có
            if (isset($cartData['items']) && is_array($cartData['items'])) {
                $itemsToUpdate = $cartData['items'];
            }
            // Xử lý single item nếu có product_id và quantity
            elseif (isset($cartData['product_id']) && isset($cartData['quantity'])) {
                $itemsToUpdate[] = [
                    'product_id' => $cartData['product_id'],
                    'quantity' => $cartData['quantity'],
                ];
            }

            // Cập nhật cart items
            foreach ($itemsToUpdate as $item) {
                $product = Product::find($item['product_id']);

                if (! $product) {
                    throw new Exception("Product with ID {$item['product_id']} not found");
                }

                // Tìm cart item
                $cartItem = $cart->items()->where('product_id', $item['product_id'])->first();

                if ($cartItem) {
                    // Cập nhật số lượng (ghi đè, không cộng dồn)
                    $cartItem->quantity = $item['quantity'];
                    $cartItem->save();
                } else {
                    // Nếu chưa có thì tạo mới
                    $cart->items()->create([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                    ]);
                }
            }

            DB::commit(); // commit neu khong co loi xay ra

            // Load lại cart với relationships
            $cart = Cart::with('items.product')->find($cart->cart_id);

            // Tính tổng tiền của toàn bộ cart
            $cartTotalAmount = 0;
            foreach ($cart->items as $cartItem) {
                $cartTotalAmount += $cartItem->product->price * $cartItem->quantity;
            }

            // Trả về dữ liệu đã chuẩn hoá sử dụng CartResource
            return response()->json([
                'status' => true,
                'message' => 'Cart updated successfully',
                'data' => new CartResource($cart),
                'total_amount' => $cartTotalAmount,
                'total_items' => $cart->items->sum('quantity'),
            ], 200);
        } catch (Exception $e) {
            DB::rollback();

            return response()->json([
                'status' => false,
                'message' => 'Failed to update cart',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Xóa cart
        $cart = Cart::find($id);
        if (! $cart) {
            return response()->json([
                'status' => false,
                'message' => "Cart with ID {$id} not found",
            ], 404);
        }

        // Kiểm tra quyền sở hữu cart
        $userId = auth()->id() ?? 1;
        if ($cart->user_id !== $userId) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized to delete this cart',
            ], 403);
        }

        // Xóa cart
        $cart->delete();
        \DB::reorderIds(); // Goi ham reorderIds de sap xep lai ID sau khi xoa

        return response()->json([
            'status' => true,
            'message' => 'Cart deleted successfully',
        ], 200);
    }
}
