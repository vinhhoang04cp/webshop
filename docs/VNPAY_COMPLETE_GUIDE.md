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
- Cần phải cập nhật VNPAY_TMN_CODE và UELMZJAAMYUZYNJDVUOMVXXOZNJGYSOR lấy từ mail của VNpay sau 1 thời gian nhất định hoặc khi reset

**Tính năng đã tích hợp:**
- ✅ Thanh toán COD (Thanh toán khi nhận hàng)
- ✅ Thanh toán VNPay (Thẻ ATM, Visa, MasterCard)
- ✅ UI chọn phương thức thanh toán trong giỏ hàng
- ✅ Trang success/failed sau thanh toán
- ✅ Lưu trạng thái thanh toán vào database
- ✅ Auto-update ngrok URL script

**Nếu gặp lỗi "Invalid Signature hay Sai Chữ kí Thì phải cập Nhật các key trong .env như đã ghi chú ở phần Lưu Ý Quan Trọng"** 
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

### 1.4. Luồng hoạt động chi tiết bằng tiếng Việt

#### 🔄 LUỒNG THANH TOÁN COD (Thanh toán khi nhận hàng)

```
Bước 1: Khách hàng vào trang giỏ hàng
└─> Xem danh sách sản phẩm trong giỏ
    └─> Kiểm tra tổng tiền

Bước 2: Điền thông tin giao hàng
├─> Họ tên người nhận
├─> Số điện thoại
├─> Địa chỉ giao hàng
└─> Ghi chú (không bắt buộc)

Bước 3: Chọn phương thức thanh toán
└─> Click radio button "Thanh toán khi nhận hàng (COD)"
    └─> Nút "Đặt hàng (COD)" màu xanh dương hiện ra

Bước 4: Xác nhận đặt hàng
└─> Click nút "Đặt hàng (COD)"
    └─> CustomerCartController::checkout() được gọi
        ├─> Validate dữ liệu form
        ├─> Tạo Order mới (status=pending, payment_status=pending, payment_method=cod)
        ├─> Tạo OrderItems từ CartItems
        ├─> Xóa giỏ hàng
        └─> Redirect về trang giỏ hàng với thông báo thành công

Kết quả:
✅ Đơn hàng được tạo
✅ Giỏ hàng được xóa
✅ Khách hàng nhận thông báo: "Đặt hàng thành công! Bạn sẽ thanh toán khi nhận hàng."
```

#### 💳 LUỒNG THANH TOÁN VNPAY (Thanh toán trực tuyến)

```
GIAI ĐOẠN 1: KHỞI TẠO THANH TOÁN
═══════════════════════════════════

Bước 1: Khách hàng vào trang giỏ hàng
└─> URL: /cart
    └─> Xem danh sách sản phẩm
        └─> Tổng tiền hiển thị

Bước 2: Điền thông tin giao hàng
├─> Form validation:
│   ├─> shipping_name (bắt buộc)
│   ├─> shipping_phone (bắt buộc)
│   ├─> shipping_address (bắt buộc)
│   └─> note (tùy chọn)

Bước 3: Chọn phương thức thanh toán VNPay
└─> Click radio button "Thanh toán Online qua VNPay"
    ├─> JavaScript selectPaymentMethod('vnpay') được gọi
    ├─> Nút chuyển sang màu xanh lá: "Thanh toán với VNPay"
    └─> Radio button được highlight

Bước 4: Submit form thanh toán
└─> Click "Thanh toán với VNPay"
    └─> POST /cart/checkout
        └─> CustomerCartController::checkout()
            ├─> Validate: payment_method = 'vnpay'
            ├─> Kiểm tra giỏ hàng không rỗng
            ├─> Tính tổng tiền từ CartItems
            │
            ├─> TẠO ĐƠN HÀNG:
            │   └─> Order::create([
            │       'user_id' => auth()->id(),
            │       'total_price' => $totalPrice,
            │       'status' => 'pending',
            │       'payment_status' => 'pending',
            │       'payment_method' => 'vnpay',
            │       'shipping_name' => ...,
            │       'shipping_phone' => ...,
            │       'shipping_address' => ...,
            │   ])
            │
            ├─> TẠO ORDER ITEMS:
            │   └─> Foreach CartItem:
            │       └─> OrderItem::create([
            │           'order_id' => $order->order_id,
            │           'product_id' => ...,
            │           'quantity' => ...,
            │           'price' => ... (lock giá tại thời điểm đặt)
            │       ])
            │
            ├─> LƯU ORDER_ID VÀO SESSION:
            │   └─> session(['pending_order_id' => $order->order_id])
            │
            └─> REDIRECT ĐẾN PAYMENT CONTROLLER:
                └─> return redirect()->route('payment.create.get')


GIAI ĐOẠN 2: TẠO URL THANH TOÁN VNPAY
════════════════════════════════════════

Bước 5: PaymentController::createPayment()
└─> Lấy order_id từ session
    ├─> Kiểm tra session tồn tại
    │   └─> Nếu không có: redirect về giỏ hàng với lỗi
    │
    ├─> Lấy thông tin Order từ database
    │   └─> Order::findOrFail($orderId)
    │
    ├─> CHUẨN BỊ DỮ LIỆU VNPAY:
    │   ├─> vnp_TmnCode: Lấy từ config
    │   ├─> vnp_HashSecret: Lấy từ config
    │   ├─> vnp_Url: https://sandbox.vnpayment.vn/paymentv2/vpcpay.html
    │   ├─> vnp_ReturnUrl: URL callback sau khi thanh toán
    │   ├─> vnp_TxnRef: {order_id}_{timestamp} (unique ID)
    │   ├─> vnp_OrderInfo: "Thanh toán đơn hàng #{order_id}"
    │   ├─> vnp_Amount: $order->total_price * 100 (VNPay tính đơn vị đồng)
    │   └─> vnp_IpAddr: IP khách hàng
    │
    ├─> TẠO MẢNG THAM SỐ:
    │   └─> $inputData = [
    │       "vnp_Version" => "2.1.0",
    │       "vnp_TmnCode" => ...,
    │       "vnp_Amount" => ...,
    │       "vnp_Command" => "pay",
    │       "vnp_CreateDate" => YmdHis,
    │       "vnp_CurrCode" => "VND",
    │       "vnp_IpAddr" => ...,
    │       "vnp_Locale" => "vn",
    │       "vnp_OrderInfo" => ...,
    │       "vnp_OrderType" => "billpayment",
    │       "vnp_ReturnUrl" => ...,
    │       "vnp_TxnRef" => ...,
    │   ]
    │
    ├─> SẮP XẾP THEO ALPHABET:
    │   └─> ksort($inputData)
    │
    ├─> TẠO CHUỖI HASH (BẢO MẬT):
    │   ├─> Foreach $inputData:
    │   │   └─> $hashdata .= urlencode($key) . "=" . urlencode($value) . "&"
    │   │
    │   └─> $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret)
    │       └─> Hash HMAC-SHA512 để VNPay verify không bị giả mạo
    │
    ├─> TẠO QUERY STRING:
    │   └─> $query = urlencode(key)=urlencode(value)&...
    │
    ├─> TẠO URL HOÀN CHỈNH:
    │   └─> $vnp_Url = $vnp_Url . "?" . $query . "vnp_SecureHash=" . $vnpSecureHash
    │
    ├─> GHI LOG:
    │   └─> Log::info('VNPay Payment URL: ' . $vnp_Url)
    │
    └─> REDIRECT KHÁCH HÀNG ĐẾN VNPAY:
        └─> return redirect($vnp_Url)
            └─> Trình duyệt chuyển sang trang VNPay


GIAI ĐOẠN 3: KHÁCH HÀNG THANH TOÁN TRÊN VNPAY
═══════════════════════════════════════════════

Bước 6: Trang thanh toán VNPay
└─> URL: https://sandbox.vnpayment.vn/paymentv2/vpcpay.html?...
    │
    ├─> VNPAY HIỂN THỊ:
    │   ├─> Thông tin đơn hàng
    │   ├─> Số tiền cần thanh toán
    │   ├─> Danh sách ngân hàng
    │   └─> Form nhập thông tin thẻ
    │
    ├─> KHÁCH HÀNG CHỌN NGÂN HÀNG:
    │   └─> Click vào logo ngân hàng (VCB, TCB, MB, ...)
    │
    ├─> KHÁCH HÀNG NHẬP THÔNG TIN THẺ:
    │   ├─> Số thẻ: 9704198526191432198 (sandbox)
    │   ├─> Tên chủ thẻ: NGUYEN VAN A
    │   ├─> Ngày phát hành: 07/15
    │   └─> Click "Thanh toán"
    │
    ├─> VNPAY XÁC THỰC OTP:
    │   ├─> Hiển thị form nhập OTP
    │   ├─> Khách hàng nhập: 123456 (sandbox)
    │   └─> Click "Xác nhận"
    │
    └─> VNPAY XỬ LÝ GIAO DỊCH:
        ├─> Kiểm tra số dư tài khoản
        ├─> Trừ tiền (sandbox: giả lập)
        ├─> Tạo mã giao dịch: vnp_TransactionNo
        └─> Xác định kết quả:
            ├─> vnp_ResponseCode = '00' (Thành công)
            └─> hoặc mã lỗi khác (Thất bại)


GIAI ĐOẠN 4: VNPAY CALLBACK VỀ WEBSITE
════════════════════════════════════════

Bước 7A: VNPay Return URL (Người dùng thấy)
└─> VNPay redirect browser về:
    └─> GET /payment/vnpay-return?vnp_Amount=...&vnp_ResponseCode=...&vnp_SecureHash=...
        │
        └─> PaymentController::vnpayReturn(Request $request)
            │
            ├─> LẤY DỮ LIỆU TỪ VNPAY:
            │   ├─> $inputData = $request->all()
            │   ├─> $vnp_SecureHash = $inputData['vnp_SecureHash']
            │   └─> unset các field không cần hash
            │
            ├─> VERIFY CHỮ KÝ (QUAN TRỌNG):
            │   ├─> ksort($inputData) - Sắp xếp alphabet
            │   ├─> Tạo $hashData từ $inputData
            │   ├─> $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret)
            │   └─> So sánh: $secureHash === $vnp_SecureHash
            │       ├─> Nếu SAI → Redirect /payment/failed (Bị giả mạo!)
            │       └─> Nếu ĐÚNG → Tiếp tục xử lý
            │
            ├─> PARSE THÔNG TIN:
            │   ├─> $vnpTxnRef = $request->vnp_TxnRef
            │   │   └─> Format: "{order_id}_{timestamp}"
            │   │   └─> explode('_', $vnpTxnRef)[0] → Lấy order_id
            │   │
            │   ├─> $vnpResponseCode = $request->vnp_ResponseCode
            │   │   └─> '00' = Thành công
            │   │   └─> Khác = Thất bại
            │   │
            │   └─> $vnpTransactionNo = $request->vnp_TransactionNo
            │       └─> Mã giao dịch từ VNPay
            │
            ├─> TÌM ORDER:
            │   └─> Order::where('order_id', $orderId)->first()
            │       └─> Nếu không tìm thấy → Redirect /payment/failed
            │
            ├─> KIỂM TRA KẾT QUẢ:
            │   │
            │   ├─> NẾU THÀNH CÔNG (vnp_ResponseCode == '00'):
            │   │   │
            │   │   ├─> CẬP NHẬT ORDER:
            │   │   │   └─> $order->update([
            │   │   │       'payment_status' => 'paid',
            │   │   │       'transaction_id' => $vnpTransactionNo,
            │   │   │       'paid_at' => now(),
            │   │   │   ])
            │   │   │
            │   │   ├─> XÓA GIỎ HÀNG:
            │   │   │   ├─> Tìm Cart của user
            │   │   │   └─> CartItem::where('cart_id', $cart->cart_id)->delete()
            │   │   │
            │   │   ├─> XÓA SESSION:
            │   │   │   └─> session()->forget('pending_order_id')
            │   │   │
            │   │   ├─> GHI LOG:
            │   │   │   └─> Log::info('VNPay Payment Success: Order #...')
            │   │   │
            │   │   └─> REDIRECT ĐẾN TRANG THÀNH CÔNG:
            │   │       └─> return redirect()->route('payment.success', [
            │   │           'order_id' => $order->order_id
            │   │       ])
            │   │
            │   └─> NẾU THẤT BẠI:
            │       │
            │       ├─> CẬP NHẬT ORDER:
            │       │   └─> $order->update(['payment_status' => 'failed'])
            │       │
            │       ├─> GHI LOG:
            │       │   └─> Log::warning('VNPay Payment Failed...')
            │       │
            │       └─> REDIRECT ĐẾN TRANG THẤT BẠI:
            │           └─> return redirect()->route('payment.failed', [
            │               'order_id' => $order->order_id
            │           ])

Bước 7B: VNPay IPN URL (Server-to-Server - Backup)
└─> VNPay gọi API (không qua browser):
    └─> POST /payment/vnpay-ipn
        │
        └─> PaymentController::vnpayIPN(Request $request)
            │
            ├─> VERIFY CHỮ KÝ (giống Return URL)
            ├─> TÌM ORDER
            ├─> CẬP NHẬT ORDER (nếu chưa update)
            └─> TRẢ VỀ JSON:
                ├─> Success: {"RspCode": "00", "Message": "Confirm Success"}
                └─> Error: {"RspCode": "97", "Message": "Invalid signature"}

    💡 LƯU Ý: IPN là backup, đảm bảo giao dịch được ghi nhận
              ngay cả khi user đóng browser trước khi Return URL chạy


GIAI ĐOẠN 5: HIỂN THỊ KẾT QUẢ CHO KHÁCH HÀNG
═══════════════════════════════════════════════

Bước 8A: Trang thanh toán thành công
└─> URL: /payment/success/{order_id}
    └─> PaymentController::success($order_id)
        │
        ├─> LẤY THÔNG TIN ORDER:
        │   └─> Order::with('orderItems.product')
        │       ->where('order_id', $order_id)
        │       ->first()
        │
        └─> HIỂN THỊ VIEW: payment/success.blade.php
            │
            ├─> ✅ Icon tick xanh lớn
            ├─> Tiêu đề: "Thanh toán thành công!"
            ├─> Thông báo: "Cảm ơn bạn đã đặt hàng..."
            │
            ├─> THÔNG TIN ĐƠN HÀNG:
            │   ├─> Mã đơn hàng: #{order_id}
            │   ├─> Tổng tiền: {total_price} VNĐ
            │   ├─> Phương thức: VNPay
            │   ├─> Trạng thái: Badge "Đã thanh toán" (xanh lá)
            │   ├─> Mã giao dịch: {transaction_id}
            │   └─> Thời gian: {paid_at}
            │
            ├─> DANH SÁCH SẢN PHẨM:
            │   └─> Table hiển thị:
            │       ├─> Tên sản phẩm
            │       ├─> Số lượng
            │       ├─> Đơn giá
            │       └─> Thành tiền
            │
            └─> CÁC NÚT HÀNH ĐỘNG:
                ├─> "Về trang chủ" (btn-primary)
                └─> "Xem đơn hàng của tôi" (btn-outline-primary)

Bước 8B: Trang thanh toán thất bại
└─> URL: /payment/failed/{order_id}
    └─> PaymentController::failed($order_id)
        │
        ├─> LẤY THÔNG TIN ORDER (nếu có)
        │
        └─> HIỂN THỊ VIEW: payment/failed.blade.php
            │
            ├─> ❌ Icon X đỏ lớn
            ├─> Tiêu đề: "Thanh toán thất bại!"
            ├─> Thông báo: "Rất tiếc, giao dịch không thành công..."
            │
            ├─> THÔNG TIN ĐƠN HÀNG (nếu có):
            │   ├─> Mã đơn hàng: #{order_id}
            │   ├─> Tổng tiền: {total_price} VNĐ
            │   └─> Trạng thái: Badge "Thanh toán thất bại" (đỏ)
            │
            ├─> CẢNH BÁO:
            │   └─> "Đơn hàng vẫn được lưu. Bạn có thể thử lại..."
            │
            └─> CÁC NÚT HÀNH ĐỘNG:
                ├─> "Thử lại thanh toán VNPay" (btn-primary)
                │   └─> POST /payment/create với order_id
                │
                └─> "Quay lại giỏ hàng" (btn-outline-secondary)
```

#### 📊 TỔNG KẾT LUỒNG DỮ LIỆU

```
DATABASE CHANGES:
═════════════════

1. Tạo Order (Bước 4):
   orders table:
   ├─> order_id: auto increment
   ├─> user_id: ID người dùng
   ├─> total_price: Tổng tiền
   ├─> status: 'pending'
   ├─> payment_status: 'pending'
   ├─> payment_method: 'vnpay'
   ├─> transaction_id: NULL
   ├─> paid_at: NULL
   ├─> shipping_name: Họ tên
   ├─> shipping_phone: SĐT
   ├─> shipping_address: Địa chỉ
   └─> created_at: Thời gian tạo

2. Tạo OrderItems (Bước 4):
   order_items table (foreach CartItem):
   ├─> order_item_id: auto increment
   ├─> order_id: FK to orders
   ├─> product_id: FK to products
   ├─> quantity: Số lượng
   ├─> price: Giá tại thời điểm đặt (LOCKED)
   └─> created_at: Thời gian tạo

3. Cập nhật Order khi thanh toán thành công (Bước 7A):
   orders table:
   ├─> payment_status: 'pending' → 'paid'
   ├─> transaction_id: NULL → '14211323' (từ VNPay)
   ├─> paid_at: NULL → '2025-10-25 10:30:45'
   └─> updated_at: Thời gian cập nhật

4. Xóa Cart (Bước 7A):
   cart_items table:
   └─> DELETE WHERE cart_id = ...
       └─> Giỏ hàng trống sau khi thanh toán thành công

SESSION CHANGES:
════════════════

1. Lưu pending_order_id (Bước 4):
   Session::put('pending_order_id', 123)
   └─> Dùng để PaymentController biết đơn hàng nào cần thanh toán

2. Xóa pending_order_id (Bước 7A):
   Session::forget('pending_order_id')
   └─> Sau khi thanh toán thành công

LOG ENTRIES:
════════════

1. Log URL thanh toán (Bước 5):
   [INFO] VNPay Payment URL: https://sandbox.vnpayment.vn/...

2. Log thành công (Bước 7A):
   [INFO] VNPay Payment Success: Order #123

3. Log thất bại (Bước 7A):
   [WARNING] VNPay Payment Failed: Order #123 - Code: 24

4. Log lỗi chữ ký (Bước 7A/7B):
   [ERROR] VNPay Return: Invalid signature
```

#### ⏱️ TIMELINE DỰ KIẾN

```
00:00 - User click "Thanh toán với VNPay"
00:01 - Order được tạo trong database
00:02 - Redirect đến VNPay
00:03 - User nhập thông tin thẻ
00:45 - User nhập OTP
00:47 - VNPay xử lý giao dịch
00:48 - VNPay callback về website
00:49 - Order được cập nhật (payment_status = paid)
00:50 - User thấy trang "Thanh toán thành công"

💡 Tổng thời gian: ~50 giây (nếu user không chần chừ)
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

### BƯỚC 4: Tạo PaymentService và Controller

#### 4.1. Tạo PaymentService

```bash
./vendor/bin/sail artisan make:service PaymentService
```

**File:** `app/Services/PaymentService.php`

```php
<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    /**
     * Tạo URL thanh toán VNPay
     */
    public function createVNPayPaymentUrl($orderId, $ipAddress)
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

        return [
            'url' => $vnp_Url,
            'txn_ref' => $vnp_TxnRef,
            'order_id' => $order->order_id,
        ];
    }

    /**
     * Xác thực callback từ VNPay
     */
    public function validateVNPayCallback($inputData)
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

        if ($secureHash != $vnp_SecureHash) {
            Log::error('VNPay: Invalid signature', [
                'expected' => $secureHash,
                'received' => $vnp_SecureHash,
            ]);

            return false;
        }

        return true;
    }

    /**
     * Xử lý kết quả thanh toán VNPay
     */
    public function processVNPayReturn($inputData, $userId = null)
    {
        $vnp_ResponseCode = $inputData['vnp_ResponseCode'] ?? '';
        $vnp_TxnRef = $inputData['vnp_TxnRef'] ?? '';
        $vnp_TransactionNo = $inputData['vnp_TransactionNo'] ?? '';

        $orderIdFromTxn = explode('_', $vnp_TxnRef)[0];

        DB::beginTransaction();

        try {
            $order = Order::findOrFail($orderIdFromTxn);

            if ($vnp_ResponseCode == '00') {
                // Giao dịch thành công
                $order->update([
                    'payment_status' => 'paid',
                    'payment_method' => 'vnpay',
                    'transaction_id' => $vnp_TransactionNo,
                    'paid_at' => now(),
                ]);

                // Xóa giỏ hàng
                if ($order->user_id) {
                    $cart = Cart::where('user_id', $order->user_id)->first();
                    if ($cart) {
                        CartItem::where('cart_id', $cart->id)->delete();
                    }
                }

                DB::commit();

                Log::info('VNPay: Payment successful', [
                    'order_id' => $order->order_id,
                    'transaction_id' => $vnp_TransactionNo,
                    'amount' => $inputData['vnp_Amount'] / 100,
                ]);

                return [
                    'success' => true,
                    'order_id' => $order->order_id,
                    'message' => 'Thanh toán thành công!',
                ];
            } else {
                // Giao dịch thất bại
                $order->update([
                    'payment_status' => 'failed',
                    'payment_method' => 'vnpay',
                ]);

                DB::commit();

                $errorMessage = $this->getVNPayErrorMessage($vnp_ResponseCode);

                Log::warning('VNPay: Payment failed', [
                    'order_id' => $order->order_id,
                    'response_code' => $vnp_ResponseCode,
                    'message' => $errorMessage,
                ]);

                return [
                    'success' => false,
                    'order_id' => $order->order_id,
                    'message' => $errorMessage,
                ];
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('VNPay: Error processing payment', [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Lấy thông báo lỗi từ VNPay response code
     */
    private function getVNPayErrorMessage($responseCode)
    {
        $messages = [
            '07' => 'Trừ tiền thành công. Giao dịch bị nghi ngờ (liên quan tới lừa đảo, giao dịch bất thường).',
            '09' => 'Giao dịch không thành công do: Thẻ/Tài khoản của khách hàng chưa đăng ký dịch vụ InternetBanking tại ngân hàng.',
            '10' => 'Giao dịch không thành công do: Khách hàng xác thực thông tin thẻ/tài khoản không đúng quá 3 lần',
            '11' => 'Giao dịch không thành công do: Đã hết hạn chờ thanh toán. Xin quý khách vui lòng thực hiện lại giao dịch.',
            '12' => 'Giao dịch không thành công do: Thẻ/Tài khoản của khách hàng bị khóa.',
            '13' => 'Giao dịch không thành công do Quý khách nhập sai mật khẩu xác thực giao dịch (OTP).',
            '24' => 'Giao dịch không thành công do: Khách hàng hủy giao dịch',
            '51' => 'Giao dịch không thành công do: Tài khoản của quý khách không đủ số dư để thực hiện giao dịch.',
            '65' => 'Giao dịch không thành công do: Tài khoản của Quý khách đã vượt quá hạn mức giao dịch trong ngày.',
            '75' => 'Ngân hàng thanh toán đang bảo trì.',
            '79' => 'Giao dịch không thành công do: KH nhập sai mật khẩu thanh toán quá số lần quy định.',
            '99' => 'Các lỗi khác (lỗi còn lại, không có trong danh sách mã lỗi đã liệt kê)',
        ];

        return $messages[$responseCode] ?? 'Giao dịch không thành công';
    }
}
```

#### 4.2. Tạo PaymentController

```bash
./vendor/bin/sail artisan make:controller Web/PaymentController
```

**File:** `app/Http/Controllers/Web/PaymentController.php`

```php
<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Tạo URL thanh toán VNPay
     */
    public function createPayment(Request $request)
    {
        $orderId = session('pending_order_id');

        if (!$orderId) {
            return redirect()->route('cart.index')
                ->with('error', 'Không tìm thấy đơn hàng. Vui lòng thử lại.');
        }

        $paymentData = $this->paymentService->createVNPayPaymentUrl($orderId, $request->ip());

        return redirect($paymentData['url']);
    }

    /**
     * Xử lý callback từ VNPay
     */
    public function vnpayReturn(Request $request)
    {
        $inputData = $request->all();

        // Validate signature
        if (!$this->paymentService->validateVNPayCallback($inputData)) {
            return redirect()->route('payment.failed')
                ->with('error', 'Chữ ký không hợp lệ');
        }

        // Process payment
        $result = $this->paymentService->processVNPayReturn($inputData);

        if ($result['success']) {
            session()->forget('pending_order_id');
            return redirect()->route('payment.success', ['order_id' => $result['order_id']]);
        } else {
            return redirect()->route('payment.failed', ['order_id' => $result['order_id']]);
        }
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

**💡 Ưu điểm của cách tiếp cận với Service:**
- ✅ **PaymentService** tập trung toàn bộ logic VNPay
- ✅ **Controller gọn gàng**, chỉ xử lý HTTP flow
- ✅ **Dễ test** từng component riêng biệt
- ✅ **Reusable** - có thể tái sử dụng cho API endpoints
- ✅ **Maintainable** - dễ bảo trì và mở rộng
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

## 📝 Changelog

### Version 2.0 - 26/10/2025
**Cập nhật lớn - Refactor theo Service Pattern:**
- ✅ Thêm **PaymentService** để tách business logic VNPay
- ✅ Controllers gọn gàng hơn, chỉ xử lý HTTP flow
- ✅ **Tách methods**: `createVNPayPaymentUrl`, `validateVNPayCallback`, `processVNPayReturn`
- ✅ Thêm **error message mapping** từ VNPay response codes
- ✅ Cải thiện **logging** và **error handling**
- ✅ **Transaction management** trong Service layer
- ✅ Áp dụng **Dependency Injection** pattern
- ✅ Cập nhật tài liệu theo chuẩn mới

### Version 1.0 - 25/10/2025
- Phiên bản ban đầu với logic trong Controllers

---

*Tài liệu được cập nhật: 26/10/2025*  
*Phiên bản: 2.0*  
*Author: Hoàng Quang Vinh*
