# HƯỚNG DẪN HOÀN CHỈNH TÍCH HỢP VNPAY VÀO LARAVEL

> **Tài liệu này mô tả chi tiết toàn bộ quá trình tích hợp VNPay Payment Gateway vào Laravel WebShop từ đầu đến cuối.**

---

## 🎯 QUICK START (TL;DR)

**Đã hoàn thành tích hợp VNPay! Để sử dụng ngay:**

```bash
# 1. Khởi động services
./vendor/bin/sail up -d

# 2. Cập nhật ngrok URL tự động
./update-ngrok-url.sh

# 3. Mở trình duyệt và test thanh toán
# URL sẽ hiển thị sau bước 2
```

**Test với thẻ VNPay Sandbox:**
- Số thẻ: `9704198526191432198`
- Tên: `NGUYEN VAN A`
- Ngày: `07/15`
- OTP: `123456`

**⚠️ LƯU Ý QUAN TRỌNG:** 
- **PHẢI đăng ký VNPay Sandbox** tại http://sandbox.vnpayment.vn/devreg/ để lấy credentials riêng
- Credentials demo công khai KHÔNG hoạt động
- Sau khi nhận email, cập nhật `VNPAY_TMN_CODE` và `VNPAY_HASH_SECRET` vào `.env`

**Tính năng đã tích hợp:**
- ✅ Thanh toán COD (Thanh toán khi nhận hàng)
- ✅ Thanh toán VNPay (Thẻ ATM, Visa, MasterCard)
- ✅ UI chọn phương thức thanh toán trong giỏ hàng
- ✅ Trang success/failed sau thanh toán
- ✅ Lưu trạng thái thanh toán vào database
- ✅ Auto-update ngrok URL script

**Nếu gặp lỗi "Invalid Signature":**
1. Đăng ký tài khoản VNPay sandbox (bắt buộc!)
2. Cập nhật credentials vào `.env` (không có quotes, không có khoảng trắng)
3. Chạy: `./vendor/bin/sail artisan config:clear`
4. Test lại

📖 **Đọc phần chi tiết bên dưới nếu cần hiểu sâu hơn hoặc troubleshooting.**

---

## 📋 MỤC LỤC

1. [Tổng Quan](#1-tổng-quan)
2. [Yêu Cầu Hệ Thống](#2-yêu-cầu-hệ-thống)
3. [Kiến Trúc Hệ Thống](#3-kiến-trúc-hệ-thống)
4. [Các Bước Triển Khai Chi Tiết](#4-các-bước-triển-khai-chi-tiết)
5. [Code Implementation](#5-code-implementation)
6. [Cấu Hình](#6-cấu-hình)
7. [Testing](#7-testing)
8. [Troubleshooting](#8-troubleshooting)
9. [Best Practices](#9-best-practices)

---

## 1. TỔNG QUAN

### 1.1. Giới thiệu

VNPay là cổng thanh toán trực tuyến hàng đầu tại Việt Nam, cho phép khách hàng thanh toán qua:
- Thẻ ATM nội địa
- Thẻ tín dụng quốc tế (Visa, MasterCard, JCB)
- QR Code
- Ví điện tử

### 1.2. Tại sao cần Ngrok?

Vì project Laravel đang chạy trên localhost (chưa deploy), VNPay không thể callback về `http://localhost`. Giải pháp:
- **Ngrok**: Tạo tunnel công khai từ localhost → Internet
- VNPay có thể callback về URL ngrok → localhost

### 1.3. Luồng thanh toán VNPay

```
┌─────────┐     1. Checkout      ┌──────────────┐
│  User   │ ──────────────────> │   Laravel    │
└─────────┘                      │   WebShop    │
     │                           └──────────────┘
     │                                  │
     │                                  │ 2. Create Order
     │                                  │    (status=pending)
     │                                  ↓
     │                           ┌──────────────┐
     │                           │   Database   │
     │                           └──────────────┘
     │                                  │
     │                                  │ 3. Generate Payment URL
     │                                  │    + Secure Hash
     │                                  ↓
     │     4. Redirect to VNPay  ┌──────────────┐
     │ <──────────────────────── │  Controller  │
     │                           └──────────────┘
     │
     │      5. Enter Card Info
     ↓
┌─────────────┐
│   VNPay     │
│  Payment    │
└─────────────┘
     │
     │ 6. Process Payment
     │
     │     7. Return URL (User sees)
     │ ─────────────────────────────┐
     │                              │
     │ 8. IPN URL (Server-to-Server)│
     │ ─────────────────────────────┤
     │                              ↓
     │                       ┌──────────────┐
     │                       │  Controller  │
     │                       │  - Verify    │
     │                       │  - Update DB │
     │                       └──────────────┘
     │                              │
     │                              ↓
     │                       ┌──────────────┐
     │                       │   Database   │
     │                       │ (status=paid)│
     │                       └──────────────┘
     │                              │
     │      9. Success Page         │
     ↓ <────────────────────────────┘
┌─────────┐
│  User   │
└─────────┘
```

---

## 2. YÊU CẦU HỆ THỐNG

### 2.1. Môi trường phát triển
- **Laravel**: 12.x
- **PHP**: 8.4
- **Database**: MySQL 8.0
- **Docker**: Laravel Sail
- **Ngrok**: Phiên bản mới nhất

### 2.2. Tài khoản VNPay Sandbox
- URL đăng ký: http://sandbox.vnpayment.vn/devreg/
- Credentials cần thiết:
  - Terminal Code (TMN_CODE)
  - Hash Secret Key

---

## 3. KIẾN TRÚC HỆ THỐNG

### 3.1. Cấu trúc thư mục

```
webshop/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── Web/
│   │           ├── CustomerCartController.php  # Xử lý checkout
│   │           └── PaymentController.php       # Xử lý VNPay
│   └── Models/
│       ├── Order.php                          # Model đơn hàng
│       ├── Cart.php
│       └── CartItem.php
│
├── config/
│   └── services.php                           # Config VNPay
│
├── database/
│   └── migrations/
│       └── 2025_10_24_164732_add_payment_fields_to_orders_table.php
│
├── resources/
│   └── views/
│       ├── cart/
│       │   └── index.blade.php                # Trang giỏ hàng
│       └── payment/
│           ├── success.blade.php              # Thanh toán thành công
│           └── failed.blade.php               # Thanh toán thất bại
│
├── routes/
│   └── web.php                                # Payment routes
│
├── compose.yaml                               # Docker services
├── .env                                       # VNPay credentials
└── update-ngrok-url.sh                        # Auto-update script
```

### 3.2. Database Schema

#### Bảng `orders` - Các trường thanh toán mới:

| Trường          | Kiểu dữ liệu | Mô tả                                    |
|-----------------|--------------|------------------------------------------|
| payment_status  | ENUM         | pending, paid, failed, refunded          |
| payment_method  | VARCHAR(50)  | vnpay, cod, bank_transfer                |
| transaction_id  | VARCHAR(255) | Mã giao dịch từ VNPay (vnp_TransactionNo)|
| paid_at         | TIMESTAMP    | Thời điểm thanh toán thành công          |

---

## 4. CÁC BƯỚC TRIỂN KHAI CHI TIẾT

### BƯỚC 1: Thêm Ngrok Service vào Docker Compose

#### 4.1. Đăng ký Ngrok Authtoken

1. Truy cập: https://dashboard.ngrok.com/
2. Đăng ký/Đăng nhập
3. Lấy authtoken tại: https://dashboard.ngrok.com/get-started/your-authtoken
4. Copy authtoken

#### 4.2. Cập nhật `compose.yaml`

Thêm service ngrok vào file `compose.yaml`:

```yaml
services:
  # ... các services khác ...

  ngrok:
    image: ngrok/ngrok:latest
    container_name: "${APP_NAME:-laravel}_ngrok"
    restart: unless-stopped
    command: http laravel.test:80
    environment:
      NGROK_AUTHTOKEN: "${NGROK_AUTHTOKEN}"
    ports:
      - "4040:4040"  # Web interface
    networks:
      - sail
    depends_on:
      - laravel.test

networks:
  sail:
    driver: bridge
```

#### 4.3. Thêm Authtoken vào `.env`

```env
NGROK_AUTHTOKEN=your_authtoken_here
```

#### 4.4. Khởi động Ngrok

```bash
./vendor/bin/sail up -d
```

Truy cập Ngrok dashboard:
```
http://localhost:4040
```

Lấy public URL (ví dụ):
```
https://abc123.ngrok-free.app
```

---

### BƯỚC 2: Tạo Migration cho Payment Fields

#### 2.1. Tạo migration file

```bash
./vendor/bin/sail artisan make:migration add_payment_fields_to_orders_table
```

#### 2.2. Nội dung migration

File: `database/migrations/2025_10_24_164732_add_payment_fields_to_orders_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])
                  ->default('pending')
                  ->after('status');
            
            $table->string('payment_method', 50)
                  ->nullable()
                  ->after('payment_status');
            
            $table->string('transaction_id')
                  ->nullable()
                  ->after('payment_method');
            
            $table->timestamp('paid_at')
                  ->nullable()
                  ->after('transaction_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'payment_status',
                'payment_method',
                'transaction_id',
                'paid_at'
            ]);
        });
    }
};
```

#### 2.3. Chạy migration

```bash
./vendor/bin/sail artisan migrate
```

---

### BƯỚC 3: Cập nhật Order Model

#### 3.1. Thêm fillable fields

File: `app/Models/Order.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $primaryKey = 'order_id';
    
    protected $fillable = [
        'user_id',
        'total_price',
        'status',
        'payment_status',      // ← Thêm mới
        'payment_method',      // ← Thêm mới
        'transaction_id',      // ← Thêm mới
        'paid_at',            // ← Thêm mới
        'shipping_name',
        'shipping_phone',
        'shipping_address',
        'note',
    ];

    protected $casts = [
        'paid_at' => 'datetime',  // ← Thêm mới
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'order_id');
    }
}
```

---

### BƯỚC 4: Tạo PaymentController

#### 4.1. Tạo controller

```bash
./vendor/bin/sail artisan make:controller Web/PaymentController
```

#### 4.2. Implement PaymentController

File: `app/Http/Controllers/Web/PaymentController.php`

```php
<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Tạo URL thanh toán VNPay
     */
    public function createPayment(Request $request)
    {
        // Lấy order_id từ session (đã được set trong CustomerCartController)
        $orderId = session('pending_order_id');
        
        if (!$orderId) {
            return redirect()->route('cart.index')
                ->with('error', 'Không tìm thấy đơn hàng. Vui lòng thử lại.');
        }

        // Lấy thông tin order
        $order = Order::findOrFail($orderId);

        // Cấu hình VNPay
        $vnp_TmnCode = config('services.vnpay.tmn_code');
        $vnp_HashSecret = config('services.vnpay.hash_secret');
        $vnp_Url = config('services.vnpay.url');
        $vnp_ReturnUrl = config('services.vnpay.return_url');

        // Thông tin giao dịch
        $vnp_TxnRef = $order->order_id . '_' . time();
        $vnp_OrderInfo = 'Thanh toán đơn hàng #' . $order->order_id;
        $vnp_OrderType = 'billpayment';
        $vnp_Amount = $order->total_price * 100; // VNPay tính bằng đồng
        $vnp_Locale = 'vn';
        $vnp_IpAddr = $request->ip();

        // Tạo mảng dữ liệu
        $inputData = [
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_ReturnUrl,
            "vnp_TxnRef" => $vnp_TxnRef,
        ];

        // Sắp xếp dữ liệu theo alphabet
        ksort($inputData);

        // Tạo chuỗi hash và query
        $query = "";
        $hashdata = "";
        $i = 0;
        
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        // Tạo secure hash
        $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
        $vnp_Url = $vnp_Url . "?" . $query . 'vnp_SecureHash=' . $vnpSecureHash;

        // Log để debug
        Log::info('VNPay Payment URL: ' . $vnp_Url);

        // Redirect đến VNPay
        return redirect($vnp_Url);
    }

    /**
     * Xử lý callback từ VNPay (Return URL)
     */
    public function vnpayReturn(Request $request)
    {
        $vnp_HashSecret = config('services.vnpay.hash_secret');
        $inputData = $request->all();

        // Lấy secure hash từ VNPay
        $vnp_SecureHash = $inputData['vnp_SecureHash'];
        unset($inputData['vnp_SecureHash']);
        unset($inputData['vnp_SecureHashType']);

        // Sắp xếp dữ liệu
        ksort($inputData);

        // Tạo hash để verify
        $hashData = "";
        $i = 0;
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        // Verify signature
        if ($secureHash !== $vnp_SecureHash) {
            Log::error('VNPay Return: Invalid signature');
            return redirect()->route('payment.failed')
                ->with('error', 'Chữ ký không hợp lệ');
        }

        // Lấy thông tin giao dịch
        $vnpTxnRef = $request->vnp_TxnRef;
        $vnpResponseCode = $request->vnp_ResponseCode;
        $vnpTransactionNo = $request->vnp_TransactionNo;

        // Parse order_id từ vnp_TxnRef (format: {order_id}_{timestamp})
        $orderId = explode('_', $vnpTxnRef)[0];
        $order = Order::where('order_id', $orderId)->first();

        if (!$order) {
            Log::error('VNPay Return: Order not found - ' . $orderId);
            return redirect()->route('payment.failed')
                ->with('error', 'Không tìm thấy đơn hàng');
        }

        // Kiểm tra kết quả thanh toán
        if ($vnpResponseCode == '00') {
            // Thanh toán thành công
            $order->update([
                'payment_status' => 'paid',
                'transaction_id' => $vnpTransactionNo,
                'paid_at' => now(),
            ]);

            // Xóa giỏ hàng
            if ($order->user_id) {
                $cart = Cart::where('user_id', $order->user_id)->first();
                if ($cart) {
                    CartItem::where('cart_id', $cart->cart_id)->delete();
                }
            }

            // Xóa session
            session()->forget('pending_order_id');

            Log::info('VNPay Payment Success: Order #' . $order->order_id);

            return redirect()->route('payment.success', ['order_id' => $order->order_id]);
        } else {
            // Thanh toán thất bại
            $order->update([
                'payment_status' => 'failed',
            ]);

            Log::warning('VNPay Payment Failed: Order #' . $order->order_id . ' - Code: ' . $vnpResponseCode);

            return redirect()->route('payment.failed', ['order_id' => $order->order_id]);
        }
    }

    /**
     * Xử lý IPN từ VNPay (Server-to-Server)
     */
    public function vnpayIPN(Request $request)
    {
        $vnp_HashSecret = config('services.vnpay.hash_secret');
        $inputData = $request->all();

        $vnp_SecureHash = $inputData['vnp_SecureHash'];
        unset($inputData['vnp_SecureHash']);
        unset($inputData['vnp_SecureHashType']);

        ksort($inputData);

        $hashData = "";
        $i = 0;
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        $returnData = [];

        if ($secureHash !== $vnp_SecureHash) {
            $returnData['RspCode'] = '97';
            $returnData['Message'] = 'Invalid signature';
        } else {
            $vnpTxnRef = $request->vnp_TxnRef;
            $orderId = explode('_', $vnpTxnRef)[0];
            $order = Order::where('order_id', $orderId)->first();

            if (!$order) {
                $returnData['RspCode'] = '01';
                $returnData['Message'] = 'Order not found';
            } else {
                if ($request->vnp_ResponseCode == '00') {
                    $order->update([
                        'payment_status' => 'paid',
                        'transaction_id' => $request->vnp_TransactionNo,
                        'paid_at' => now(),
                    ]);
                    $returnData['RspCode'] = '00';
                    $returnData['Message'] = 'Confirm Success';
                } else {
                    $returnData['RspCode'] = '00';
                    $returnData['Message'] = 'Confirm Success';
                }
            }
        }

        return response()->json($returnData);
    }

    /**
     * Trang thanh toán thành công
     */
    public function success($order_id)
    {
        $order = Order::with('orderItems.product')->where('order_id', $order_id)->first();

        if (!$order) {
            return redirect()->route('cart.index')
                ->with('error', 'Không tìm thấy đơn hàng');
        }

        return view('payment.success', compact('order'));
    }

    /**
     * Trang thanh toán thất bại
     */
    public function failed($order_id = null)
    {
        $order = null;
        if ($order_id) {
            $order = Order::where('order_id', $order_id)->first();
        }

        return view('payment.failed', compact('order'));
    }
}
```

---

### BƯỚC 5: Cập nhật CustomerCartController

#### 5.1. Xử lý checkout với payment method

File: `app/Http/Controllers/Web/CustomerCartController.php`

Thêm/cập nhật method `checkout()`:

```php
public function checkout(Request $request)
{
    $request->validate([
        'shipping_name' => 'required|string|max:255',
        'shipping_phone' => 'required|string|max:20',
        'shipping_address' => 'required|string',
        'payment_method' => 'required|in:cod,vnpay',
    ]);

    $userId = auth()->id();
    $cart = Cart::where('user_id', $userId)->first();

    if (!$cart || $cart->cartItems->isEmpty()) {
        return redirect()->route('cart.index')
            ->with('error', 'Giỏ hàng trống');
    }

    // Tính tổng tiền
    $totalPrice = $cart->cartItems->sum(function ($item) {
        return $item->quantity * $item->product->price;
    });

    // Tạo đơn hàng
    $order = Order::create([
        'user_id' => $userId,
        'total_price' => $totalPrice,
        'status' => 'pending',
        'payment_status' => 'pending',
        'payment_method' => $request->payment_method,
        'shipping_name' => $request->shipping_name,
        'shipping_phone' => $request->shipping_phone,
        'shipping_address' => $request->shipping_address,
        'note' => $request->note,
    ]);

    // Tạo order items
    foreach ($cart->cartItems as $item) {
        $order->orderItems()->create([
            'product_id' => $item->product_id,
            'quantity' => $item->quantity,
            'price' => $item->product->price,
        ]);
    }

    // Xử lý theo payment method
    if ($request->payment_method === 'vnpay') {
        // Lưu order_id vào session để sử dụng trong PaymentController
        session(['pending_order_id' => $order->order_id]);
        
        // Redirect đến trang tạo thanh toán VNPay
        return redirect()->route('payment.create.get');
    } else {
        // COD: Xóa giỏ hàng và hoàn tất
        CartItem::where('cart_id', $cart->cart_id)->delete();
        
        return redirect()->route('cart.index')
            ->with('success', 'Đặt hàng thành công! Bạn sẽ thanh toán khi nhận hàng.');
    }
}
```

---

### BƯỚC 6: Thêm Routes

#### 6.1. Cập nhật `routes/web.php`

```php
<?php

use App\Http\Controllers\Web\PaymentController;
// ... các use statements khác ...

// Payment routes
Route::middleware(['auth'])->group(function () {
    // Tạo thanh toán VNPay
    Route::get('/payment/create', [PaymentController::class, 'createPayment'])
        ->name('payment.create.get');
    Route::post('/payment/create', [PaymentController::class, 'createPayment'])
        ->name('payment.create');
    
    // Trang success/failed
    Route::get('/payment/success/{order_id}', [PaymentController::class, 'success'])
        ->name('payment.success');
    Route::get('/payment/failed/{order_id?}', [PaymentController::class, 'failed'])
        ->name('payment.failed');
});

// VNPay callback (không cần auth)
Route::get('/payment/vnpay-return', [PaymentController::class, 'vnpayReturn'])
    ->name('payment.vnpay.return');
Route::post('/payment/vnpay-ipn', [PaymentController::class, 'vnpayIPN'])
    ->name('payment.vnpay.ipn');
```

---

### BƯỚC 7: Cấu hình VNPay

#### 7.1. Thêm config vào `config/services.php`

```php
<?php

return [
    // ... các config khác ...

    'vnpay' => [
        'tmn_code' => env('VNPAY_TMN_CODE'),
        'hash_secret' => env('VNPAY_HASH_SECRET'),
        'url' => env('VNPAY_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'),
        'return_url' => env('VNPAY_RETURN_URL'),
        'ipn_url' => env('VNPAY_IPN_URL'),
    ],
];
```

#### 7.2. Đăng ký VNPay Sandbox

**Bước 1:** Truy cập http://sandbox.vnpayment.vn/devreg/

**Bước 2:** Điền form đăng ký:
- Email: email_cua_ban@gmail.com
- Số điện thoại: 0xxxxxxxxx
- Tên công ty: WebShop Test
- Website: URL ngrok của bạn
- Mô tả: Website thương mại điện tử

**Bước 3:** Submit và check email

**Bước 4:** Lấy credentials từ email:
- Terminal Code (TMN_CODE): XXXXXXXX
- Hash Secret: XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX

#### 7.3. Cập nhật `.env`

```env
# VNPay Configuration
VNPAY_TMN_CODE=XXXXXXXX
VNPAY_HASH_SECRET=XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
VNPAY_URL=https://sandbox.vnpayment.vn/paymentv2/vpcpay.html
VNPAY_RETURN_URL=https://your-ngrok-url.ngrok-free.app/payment/vnpay-return
VNPAY_IPN_URL=https://your-ngrok-url.ngrok-free.app/payment/vnpay-ipn
```

**⚠️ LƯU Ý:**
- KHÔNG có dấu ngoặc kép `""`
- KHÔNG có khoảng trắng thừa
- Thay `your-ngrok-url` bằng URL thật từ ngrok

#### 7.4. Clear cache

```bash
./vendor/bin/sail artisan config:clear
./vendor/bin/sail artisan cache:clear
```

---

### BƯỚC 8: Tạo Views

#### 8.1. Cập nhật Cart View với Payment Method Selection

File: `resources/views/cart/index.blade.php`

Thêm phần chọn phương thức thanh toán:

```blade
<!-- Payment Method Selection -->
<div class="card mt-4">
    <div class="card-header">
        <h5>Phương thức thanh toán</h5>
    </div>
    <div class="card-body">
        <div class="form-check mb-3 p-3 border rounded" 
             style="cursor: pointer; transition: all 0.3s;"
             onmouseover="this.style.backgroundColor='#f8f9fa'"
             onmouseout="if(!this.querySelector('input').checked) this.style.backgroundColor='white'">
            <input class="form-check-input" 
                   type="radio" 
                   name="payment_method" 
                   id="payment_cod" 
                   value="cod" 
                   checked
                   onchange="selectPaymentMethod('cod')">
            <label class="form-check-label w-100" for="payment_cod" style="cursor: pointer;">
                <strong>💵 Thanh toán khi nhận hàng (COD)</strong>
                <p class="mb-0 text-muted small">Bạn sẽ thanh toán bằng tiền mặt khi nhận hàng</p>
            </label>
        </div>

        <div class="form-check mb-3 p-3 border rounded" 
             style="cursor: pointer; transition: all 0.3s;"
             onmouseover="this.style.backgroundColor='#f8f9fa'"
             onmouseout="if(!this.querySelector('input').checked) this.style.backgroundColor='white'">
            <input class="form-check-input" 
                   type="radio" 
                   name="payment_method" 
                   id="payment_vnpay" 
                   value="vnpay"
                   onchange="selectPaymentMethod('vnpay')">
            <label class="form-check-label w-100" for="payment_vnpay" style="cursor: pointer;">
                <strong>💳 Thanh toán Online qua VNPay</strong>
                <p class="mb-0 text-muted small">Thanh toán bằng thẻ ATM, Visa, MasterCard</p>
            </label>
        </div>
    </div>
</div>

<!-- Checkout Button -->
<button type="submit" 
        class="btn btn-primary btn-lg w-100 mt-3" 
        id="checkout-btn">
    Đặt hàng (COD)
</button>

<script>
function selectPaymentMethod(method) {
    const btn = document.getElementById('checkout-btn');
    
    if (method === 'vnpay') {
        btn.textContent = 'Thanh toán với VNPay';
        btn.className = 'btn btn-success btn-lg w-100 mt-3';
    } else {
        btn.textContent = 'Đặt hàng (COD)';
        btn.className = 'btn btn-primary btn-lg w-100 mt-3';
    }
    
    // Highlight selected option
    document.querySelectorAll('.form-check').forEach(el => {
        el.style.backgroundColor = 'white';
        el.style.borderColor = '#dee2e6';
    });
    
    const selected = document.querySelector(`#payment_${method}`).closest('.form-check');
    selected.style.backgroundColor = '#e7f3ff';
    selected.style.borderColor = '#0d6efd';
}
</script>
```

#### 8.2. Tạo Success Page

File: `resources/views/payment/success.blade.php`

```blade
@extends('layouts.app')

@section('title', 'Thanh toán thành công')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-success">
                <div class="card-body text-center py-5">
                    <!-- Success Icon -->
                    <div class="mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" fill="currentColor" class="bi bi-check-circle-fill text-success" viewBox="0 0 16 16">
                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                        </svg>
                    </div>

                    <!-- Success Message -->
                    <h2 class="text-success mb-3">Thanh toán thành công!</h2>
                    <p class="text-muted mb-4">
                        Cảm ơn bạn đã đặt hàng. Đơn hàng của bạn đã được thanh toán và đang được xử lý.
                    </p>

                    <!-- Order Info -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Thông tin đơn hàng</h5>
                            <div class="row text-start">
                                <div class="col-md-6">
                                    <p><strong>Mã đơn hàng:</strong> #{{ $order->order_id }}</p>
                                    <p><strong>Tổng tiền:</strong> {{ number_format($order->total_price) }} VNĐ</p>
                                    <p><strong>Phương thức:</strong> VNPay</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Trạng thái:</strong> 
                                        <span class="badge bg-success">Đã thanh toán</span>
                                    </p>
                                    <p><strong>Mã giao dịch:</strong> {{ $order->transaction_id }}</p>
                                    <p><strong>Thời gian:</strong> {{ $order->paid_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Sản phẩm đã đặt</h5>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Sản phẩm</th>
                                            <th>Số lượng</th>
                                            <th>Đơn giá</th>
                                            <th>Thành tiền</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($order->orderItems as $item)
                                        <tr>
                                            <td>{{ $item->product->name }}</td>
                                            <td>{{ $item->quantity }}</td>
                                            <td>{{ number_format($item->price) }} VNĐ</td>
                                            <td>{{ number_format($item->quantity * $item->price) }} VNĐ</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <a href="{{ route('home') }}" class="btn btn-primary btn-lg">
                            <i class="bi bi-house-door"></i> Về trang chủ
                        </a>
                        <a href="{{ route('customer.orders') }}" class="btn btn-outline-primary btn-lg">
                            <i class="bi bi-list-ul"></i> Xem đơn hàng của tôi
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
```

#### 8.3. Tạo Failed Page

File: `resources/views/payment/failed.blade.php`

```blade
@extends('layouts.app')

@section('title', 'Thanh toán thất bại')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-danger">
                <div class="card-body text-center py-5">
                    <!-- Failed Icon -->
                    <div class="mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" fill="currentColor" class="bi bi-x-circle-fill text-danger" viewBox="0 0 16 16">
                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293 5.354 4.646z"/>
                        </svg>
                    </div>

                    <!-- Failed Message -->
                    <h2 class="text-danger mb-3">Thanh toán thất bại!</h2>
                    <p class="text-muted mb-4">
                        Rất tiếc, giao dịch của bạn không thành công. Vui lòng thử lại hoặc chọn phương thức thanh toán khác.
                    </p>

                    @if($order)
                    <!-- Order Info -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Thông tin đơn hàng</h5>
                            <div class="row text-start">
                                <div class="col-md-6">
                                    <p><strong>Mã đơn hàng:</strong> #{{ $order->order_id }}</p>
                                    <p><strong>Tổng tiền:</strong> {{ number_format($order->total_price) }} VNĐ</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Trạng thái:</strong> 
                                        <span class="badge bg-danger">Thanh toán thất bại</span>
                                    </p>
                                    <p><strong>Thời gian:</strong> {{ now()->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Retry Actions -->
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        Đơn hàng của bạn vẫn được lưu. Bạn có thể thử thanh toán lại hoặc chọn phương thức COD.
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <form action="{{ route('payment.create') }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="order_id" value="{{ $order->order_id }}">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-arrow-repeat"></i> Thử lại thanh toán VNPay
                            </button>
                        </form>
                        <a href="{{ route('cart.index') }}" class="btn btn-outline-secondary btn-lg">
                            <i class="bi bi-cart"></i> Quay lại giỏ hàng
                        </a>
                    </div>
                    @else
                    <!-- No Order Info -->
                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <a href="{{ route('cart.index') }}" class="btn btn-primary btn-lg">
                            <i class="bi bi-cart"></i> Quay lại giỏ hàng
                        </a>
                        <a href="{{ route('home') }}" class="btn btn-outline-primary btn-lg">
                            <i class="bi bi-house-door"></i> Về trang chủ
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
```

---

### BƯỚC 9: Tạo Auto-Update Script cho Ngrok URL

#### 9.1. Tạo file `update-ngrok-url.sh`

```bash
#!/bin/bash

echo "🔄 Updating ngrok URL in .env..."

# Get ngrok URL from API
NGROK_URL=$(curl -s http://localhost:4040/api/tunnels | grep -o 'https://[^"]*\.ngrok-free\.app')

if [ -z "$NGROK_URL" ]; then
    echo "❌ Error: Could not get ngrok URL. Make sure ngrok is running."
    echo "   Check: http://localhost:4040"
    exit 1
fi

echo "✅ Found ngrok URL: $NGROK_URL"

# Backup .env
cp .env .env.backup

# Update .env
sed -i "s|APP_URL=.*|APP_URL=$NGROK_URL|g" .env
sed -i "s|VNPAY_RETURN_URL=.*|VNPAY_RETURN_URL=$NGROK_URL/payment/vnpay-return|g" .env
sed -i "s|VNPAY_IPN_URL=.*|VNPAY_IPN_URL=$NGROK_URL/payment/vnpay-ipn|g" .env

echo "✅ .env updated successfully"

# Clear Laravel cache
./vendor/bin/sail artisan config:clear
./vendor/bin/sail artisan cache:clear

echo "✅ Laravel cache cleared"
echo ""
echo "📌 Your application is now accessible at:"
echo "   $NGROK_URL"
echo ""
echo "🎉 Done! You can now test VNPay payment."
```

#### 9.2. Cấp quyền thực thi

```bash
chmod +x update-ngrok-url.sh
```

#### 9.3. Sử dụng

```bash
./update-ngrok-url.sh
```

---

## 5. CODE IMPLEMENTATION

### 5.1. Giải thích Hash Calculation

VNPay sử dụng HMAC-SHA512 để bảo mật:

```php
// Bước 1: Sắp xếp dữ liệu theo alphabet
ksort($inputData);

// Bước 2: Tạo chuỗi hash (PHẢI urlencode cả key và value)
$hashdata = "";
$i = 0;
foreach ($inputData as $key => $value) {
    if ($i == 1) {
        $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
    } else {
        $hashdata .= urlencode($key) . "=" . urlencode($value);
        $i = 1;
    }
}

// Bước 3: Tạo secure hash
$vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
```

**⚠️ LƯU Ý QUAN TRỌNG:**
- PHẢI `urlencode()` cả key và value trong hashdata
- PHẢI `urlencode()` cả key và value trong query string
- Sắp xếp theo alphabet trước khi hash
- Hash Secret KHÔNG được có khoảng trắng

### 5.2. Primary Key Issue

Order model sử dụng `order_id` thay vì `id`:

```php
// ❌ SAI
$order = Order::find($id);  // Tìm theo 'id'

// ✅ ĐÚNG
$order = Order::where('order_id', $id)->first();  // Tìm theo 'order_id'
```

---

## 6. CẤU HÌNH

### 6.1. Environment Variables

```env
# Application
APP_URL=https://your-ngrok-url.ngrok-free.app

# Ngrok
NGROK_AUTHTOKEN=your_ngrok_authtoken_here

# VNPay Sandbox
VNPAY_TMN_CODE=YOUR_TMN_CODE_FROM_EMAIL
VNPAY_HASH_SECRET=YOUR_HASH_SECRET_FROM_EMAIL
VNPAY_URL=https://sandbox.vnpayment.vn/paymentv2/vpcpay.html
VNPAY_RETURN_URL=https://your-ngrok-url.ngrok-free.app/payment/vnpay-return
VNPAY_IPN_URL=https://your-ngrok-url.ngrok-free.app/payment/vnpay-ipn
```

### 6.2. CSRF Exception (nếu cần)

Nếu VNPay IPN bị lỗi CSRF, thêm vào `app/Http/Middleware/VerifyCsrfToken.php`:

```php
protected $except = [
    'payment/vnpay-ipn',
];
```

---

## 7. TESTING

### 7.1. Quy trình test

**Bước 1:** Khởi động services
```bash
./vendor/bin/sail up -d
```

**Bước 2:** Update ngrok URL
```bash
./update-ngrok-url.sh
```

**Bước 3:** Truy cập ứng dụng
```
https://your-ngrok-url.ngrok-free.app
```

**Bước 4:** Test checkout
1. Đăng nhập
2. Thêm sản phẩm vào giỏ
3. Vào trang giỏ hàng
4. Điền thông tin giao hàng
5. Chọn "Thanh toán Online qua VNPay"
6. Click "Thanh toán với VNPay"

**Bước 5:** Thanh toán với thẻ test

### 7.2. Thẻ Test VNPay Sandbox

#### Thẻ nội địa (ATM):
```
Số thẻ: 9704198526191432198
Tên chủ thẻ: NGUYEN VAN A
Ngày phát hành: 07/15
Mật khẩu OTP: 123456
```

#### Thẻ quốc tế (Visa):
```
Số thẻ: 4456530000001096
Tên chủ thẻ: NGUYEN VAN A
Ngày hết hạn: 12/25
CVV: 123
```

### 7.3. Kiểm tra kết quả

**Trong database:**
```bash
./vendor/bin/sail artisan tinker
```

```php
// Xem đơn hàng mới nhất
$order = App\Models\Order::latest()->first();

// Kiểm tra payment status
$order->payment_status;  // 'paid' hoặc 'failed'

// Kiểm tra transaction ID
$order->transaction_id;

// Kiểm tra thời gian thanh toán
$order->paid_at;
```

**Trong logs:**
```bash
tail -f storage/logs/laravel.log
```

**Trong PhpMyAdmin:**
```
http://localhost:8080
```

---

## 8. TROUBLESHOOTING

### 8.1. Lỗi "Invalid Signature" (Sai chữ ký)

**Nguyên nhân:**
- Credentials không đúng
- Hash Secret có khoảng trắng thừa
- Không urlencode đúng cách

**Giải pháp:**
1. Kiểm tra credentials trong .env (không có quotes, không có khoảng trắng)
2. Clear cache:
```bash
./vendor/bin/sail artisan config:clear
./vendor/bin/sail artisan cache:clear
```
3. Verify hash calculation với tool:
```bash
php verify-vnpay-hash.php
```

### 8.2. VNPay không callback về

**Nguyên nhân:**
- Ngrok không chạy
- URL trong .env sai
- Firewall block

**Giải pháp:**
1. Kiểm tra ngrok: http://localhost:4040
2. Chạy update script:
```bash
./update-ngrok-url.sh
```
3. Kiểm tra logs

### 8.3. Lỗi "Method Not Allowed" (405)

**Nguyên nhân:**
- Route không hỗ trợ method

**Giải pháp:**
Đảm bảo có cả GET và POST route:
```php
Route::get('/payment/create', [PaymentController::class, 'createPayment'])
    ->name('payment.create.get');
Route::post('/payment/create', [PaymentController::class, 'createPayment'])
    ->name('payment.create');
```

### 8.4. Order không update

**Nguyên nhân:**
- Primary key không đúng
- Transaction chưa commit

**Giải pháp:**
Sử dụng `where('order_id', $id)` thay vì `find($id)`

### 8.5. Demo Credentials không hoạt động

**Nguyên nhân:**
- VNPay đã vô hiệu hóa credentials demo công khai

**Giải pháp:**
PHẢI đăng ký tài khoản sandbox riêng tại:
http://sandbox.vnpayment.vn/devreg/

---

## 9. BEST PRACTICES

### 9.1. Security

1. **Luôn verify signature** từ VNPay
2. **Không log sensitive data** (Hash Secret, thông tin thẻ)
3. **Sử dụng HTTPS** (ngrok cung cấp sẵn)
4. **Validate amount** trước khi update order

### 9.2. Error Handling

1. **Log tất cả transactions** để audit
2. **Handle timeout** từ VNPay
3. **Có fallback mechanism** cho IPN
4. **Thông báo rõ ràng** cho user khi lỗi

### 9.3. Performance

1. **Cache config** trong production
2. **Index database** trên transaction_id
3. **Queue email notifications** thay vì sync
4. **Optimize query** với eager loading

### 9.4. User Experience

1. **Loading state** khi redirect VNPay
2. **Clear instructions** cho user
3. **Email confirmation** sau thanh toán
4. **Retry mechanism** cho failed payments

### 9.5. Code Organization

1. **Extract payment logic** vào Service class
2. **Use Repository pattern** cho data access
3. **Implement Events** cho payment success/failed
4. **Write tests** cho critical flows

---

## 10. PRODUCTION DEPLOYMENT

### 10.1. Checklist trước khi production

- [ ] Thay credentials sandbox bằng production credentials
- [ ] Cập nhật VNPAY_URL sang production URL
- [ ] Thay APP_URL bằng domain thật
- [ ] Enable SSL certificate
- [ ] Setup monitoring và alerting
- [ ] Test với thẻ thật (số tiền nhỏ)
- [ ] Configure queue workers cho background jobs
- [ ] Setup backup và disaster recovery
- [ ] Review logs và error handling
- [ ] Load testing với traffic cao

### 10.2. Production URLs

```env
# Production
VNPAY_URL=https://pay.vnpay.vn/vpcpay.html
VNPAY_RETURN_URL=https://yourdomain.com/payment/vnpay-return
VNPAY_IPN_URL=https://yourdomain.com/payment/vnpay-ipn
```

### 10.3. Monitoring

Setup monitoring cho:
- Payment success rate
- Payment failure rate
- Average transaction time
- Error logs
- VNPay API response time

---

## 11. FAQ

### Q1: Ngrok URL thay đổi mỗi khi restart, phải làm gì?

**A:** Chạy script `./update-ngrok-url.sh` sau mỗi lần restart. Hoặc upgrade ngrok để có static domain.

### Q2: VNPay báo lỗi "97 - Invalid Signature", làm sao fix?

**A:** 
1. Kiểm tra credentials trong .env
2. Đảm bảo urlencode đúng cách
3. Clear Laravel cache
4. Verify với tool verify-vnpay-hash.php

### Q3: Có thể test mà không cần đăng ký VNPay sandbox không?

**A:** KHÔNG. Credentials demo công khai không hoạt động. PHẢI đăng ký tài khoản sandbox riêng.

### Q4: IPN và Return URL khác nhau như thế nào?

**A:** 
- **Return URL**: User thấy, có thể bị bypass
- **IPN URL**: Server-to-server, đáng tin cậy hơn
- Nên xử lý cả hai để đảm bảo không miss transaction

### Q5: Làm sao test refund?

**A:** Trong sandbox, vào VNPay merchant portal để tạo refund request manually.

### Q6: Order bị duplicate khi user refresh trang, fix sao?

**A:** Implement idempotency key hoặc check transaction_id trước khi update.

### Q7: Email notification sau thanh toán gửi như thế nào?

**A:** Sử dụng Laravel Events:
```php
// Trong PaymentController
event(new PaymentSuccess($order));

// Tạo Listener gửi email
php artisan make:listener SendPaymentSuccessEmail --event=PaymentSuccess
```

### Q8: Có thể tích hợp nhiều payment gateway không?

**A:** Có. Tạo interface chung:
```php
interface PaymentGateway {
    public function createPayment($order);
    public function handleCallback($request);
}
```

### Q9: Production cần thay đổi gì?

**A:** 
- Credentials production
- URL production
- Enable queue workers
- Setup monitoring
- Enable rate limiting

### Q10: Làm sao debug khi production lỗi?

**A:** 
- Check logs: `storage/logs/laravel.log`
- Enable VNPay merchant portal logging
- Setup Sentry hoặc Bugsnag
- Monitor với New Relic/DataDog

---

## 12. TÀI LIỆU THAM KHẢO

- **VNPay Sandbox:** https://sandbox.vnpayment.vn/
- **VNPay API Documentation:** https://sandbox.vnpayment.vn/apis/
- **Ngrok Documentation:** https://ngrok.com/docs
- **Laravel Documentation:** https://laravel.com/docs
- **Docker Compose:** https://docs.docker.com/compose/

---

## 13. KẾT LUẬN

Bạn đã hoàn thành tích hợp VNPay vào Laravel WebShop với:

✅ **Ngrok** cho public URL  
✅ **PaymentController** xử lý toàn bộ logic VNPay  
✅ **Database migration** cho payment fields  
✅ **Routes** đầy đủ cho payment flow  
✅ **Views** đẹp mắt cho success/failed pages  
✅ **Auto-update script** cho ngrok URL  
✅ **2 phương thức thanh toán**: COD và VNPay  

### Next Steps:

1. Đăng ký VNPay sandbox tại: http://sandbox.vnpayment.vn/devreg/
2. Cập nhật credentials vào `.env`
3. Clear cache Laravel
4. Test với thẻ sandbox
5. Enjoy! 🎉

---

**📞 Support:**
- VNPay Hotline: 1900 55 55 77
- VNPay Email: hotrovnpay@vnpay.vn

**🎊 Chúc bạn tích hợp thành công!**

---

*Tài liệu này được tạo ngày: 25/10/2025*  
*Phiên bản: 1.0*  
*Author: WebShop Development Team*
