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
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Cart::with('items.product'); // $query la bien de thuc hien query den Bang Cart thong qua model, with('items.product') la tham chieu den relationship items va product trong model Cart
        // Loc theo user_id neu co
        if ($request->has('user_id')) { // neu request co user_id , $request co nghia la lay du lieu tu request
            $query->where('user_id', $request->get('user_id')); // them dieu kien loc user_id vao truy van
        }
        if ($request->has('product_id')) {
            $query->where('product_id', $request->get('product_id'));
        }
        $carts = $query->paginate(10); // Phan trang 10 per page by default

        // Tinh tong tien va tong so luong item cho moi cart
        $cartsData = []; // tao 1 mang luu tru du lieu cart
        $grandTotal = 0; // Tổng giá của tất cả cart

        foreach ($carts as $cart) { // moi 1 cart trong carts se duoc lap qua, cart la bien luu tru cart hien tai cart la 1 gio hang con carts la tap hop cac cart
            $cartTotalAmount = 0; // khoi tao tong tien cua moi cart hien tai la 0
            $totalItems = 0; // tong so luong item trong moi cart hien tai la 0

            foreach ($cart->items as $cartItem) { // lap voi moi cart item trong cart, moi 1 cart co nhieu cart item
                $cartTotalAmount += $cartItem->product->price * $cartItem->quantity; // Tong tien cua cart se bang gia san pham * so luong cua cart item
                $totalItems += $cartItem->quantity; // Tong so luong cart item se bang cong don so luong cua cart item
            }

            $cartData = new CartResource($cart); // $cartData la bien luu tru du lieu cart hien tai, CartResource la lop resource de chuan hoa du lieu cart
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
                'current_page' => $carts->currentPage(), // ham currentPage() lay trang hien tai
                'per_page' => $carts->perPage(), // ham perPage() lay so luong item tren 1 trang
                'total' => $carts->total(), // ham total() lay tong so item
                'last_page' => $carts->lastPage(), // ham lastPage() lay trang cuoi cung
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
            $cartData = $request->validated(); // $cartData la 1 mang  duoc luu tru du lieu da duoc validate truyen tu request

            // Xác định user_id
            $userId = auth()->id() ?? $request->user_id ?? 1; // $userId lay tu auth hoac request hoac mac dinh la 1

            // check xem $cartData truyen tu request co cart_id khong
            if (isset($cartData['cart_id'])) { // neu co cart_id thi tim cart theo cart_id va user_id // ham isset() kiem tra bien 'cart_id' co ton tai trong mang $cartData hay khong
                $cart = Cart::where('cart_id', $cartData['cart_id']) // neu co cart_id thi tim cart theo cart_id va user_id
                    ->where('user_id', $userId) // Kiem tra cart thuoc ve user hien tai
                    ->first(); // ham first() lay cart dau tien neu co

                if (! $cart) { // neu khong tim thay cart thi throw exception
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

            $totalAmount = 0; // khoi tao tong tien ban dau la 0
            $itemsToAdd = []; // khoi tao mang luu tru cac items can them vao cart

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
            if (isset($cartData['items']) && is_array($cartData['items'])) { // ham is_array() kiem tra xem 'items' co phai la mang hay khong
                $itemsToAdd = $cartData['items'];  // gan cac items duoc truyen vao mang itemsToAdd, 'items' la gia tri truyen vao tu request truyen vao la mang
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
            elseif (isset($cartData['product_id']) && isset($cartData['quantity'])) { // ham isset() kiem tra xem 'product_id' va 'quantity' co ton tai trong mang $cartData hay khong
                $itemsToAdd[] = [ // them phan tu vao mang itemsToAdd
                    'product_id' => $cartData['product_id'], // gan product_id va quantity tu request vao mang itemsToAdd
                    'quantity' => $cartData['quantity'],
                ];
            }

            // Thêm hoặc cập nhật cart items
            foreach ($itemsToAdd as $item) { // moi $item trong itemsToAdd se duoc lap qua, la cac cart item can them vao cart
                $product = Product::find($item['product_id']); // ham find() tim product theo product_id duoc truyen vao tu request, $item la bien luu tru cart item hien tai

                if (! $product) { // neu khong tim thay product thi throw exception
                    throw new Exception("Product with ID {$item['product_id']} not found");
                }

                // Kiểm tra xem sản phẩm đã có trong cart chưa
                $cartItem = $cart->items()->where('product_id', $item['product_id'])->first(); // ham first() lay cart item dau tien de kiem tra xem san pham da co trong cart chua

                if ($cartItem) { // $cartitem la bien luu tru cart item
                    // Neu co roi thi cong don so luong
                    $cartItem->quantity += $item['quantity']; // So luong cua cart item se bang cong don so luong hien tai voi so luong duoc truyen vao
                    $cartItem->save(); // luu thay doi
                } else {
                    // neu chua co thi tao moi
                    $cart->items()->create([ // ham items() truy cap vao relationship items trong model cart, ham create() tao moi cart item
                        'product_id' => $item['product_id'], // gan product_id
                        'quantity' => $item['quantity'], // gan quantity
                    ]);
                }

                // Tính tổng tiền: giá sản phẩm * số lượng
                $itemTotal = $product->price * $item['quantity']; // tong tien cua cart item se bang gia san pham * so luong duoc truyen vao
                $totalAmount += $itemTotal; // so tien cua cart se bang cong don voi tong tien cua cart item hien tai
                Log::info("Added product ID {$item['product_id']} with quantity {$item['quantity']} to cart ID {$cart->cart_id}. Item total: {$itemTotal}. Cart total so far: {$totalAmount}"); // ghi log thong tin san pham duoc them vao
            }

            DB::commit(); // luu thay doi neu khong co loi xay ra

            // Load lại cart với relationships
            $cart = Cart::with('items.product')->find($cart->cart_id); // loa lai cart voi relationship items va product, ham find() tim cart theo cart_id

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

        try {
            Cart::reOrderIds();
        } catch (\Exception $reorderException) {
            // Log error nhưng không làm fail request chính
            \Log::warning('Failed to reorder Cart IDs after delete: '.$reorderException->getMessage());
        }

        return response()->json([
            'status' => true,
            'message' => 'Cart deleted successfully',
        ], 200);
    }
}
