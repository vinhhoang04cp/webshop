# Hướng dẫn Setup và Test Coupon System

## ⚠️ Vấn đề đã Fix

Bạn đã báo: **"Có vẻ như giá sau khi áp dụng coupon không thay đổi"**

**Nguyên nhân**: Logic áp dụng coupon chưa được implement trong hàm `checkout()`.

**Đã sửa**: 
- ✅ Thêm các trường còn thiếu vào database (`name`, `min_order_amount`, `max_discount_amount`, `usage_limit`, `used_count`)
- ✅ Cập nhật Model `Coupon` với validation đầy đủ
- ✅ Thêm logic áp dụng coupon vào `CustomerCartController@checkout`
- ✅ Thêm API preview coupon `CustomerCartController@applyCoupon`
- ✅ Tạo route `/cart/apply-coupon`

---

## 🚀 Các bước Setup

### Bước 1: Chạy Migration (Thêm các trường mới)

```bash
# Start MySQL nếu chưa chạy
sudo service mysql start

# Chạy migration
php artisan migrate
```

Migration sẽ thêm các trường:
- `name` - Tên coupon
- `min_order_amount` - Đơn hàng tối thiểu
- `max_discount_amount` - Giảm tối đa (cho % discount)
- `usage_limit` - Giới hạn số lần dùng
- `used_count` - Số lần đã dùng

### Bước 2: Tạo dữ liệu mẫu

```bash
php artisan db:seed --class=UpdateCouponSeeder
```

Seeder sẽ tạo 4 coupon mẫu:
- **WELCOME10**: Giảm 10%, đơn tối thiểu 500k, giảm tối đa 100k
- **SALE50K**: Giảm 50k cho đơn từ 1 triệu
- **FLASHSALE**: Giảm 20%, đơn tối thiểu 300k, giảm tối đa 200k
- **VIP500**: Giảm 500k cho đơn từ 5 triệu

---

## 🧪 Test Coupon

### Test 1: Coupon Percentage với Max Discount

**Scenario**: Mua đơn hàng 2 triệu, áp mã **WELCOME10**

**Kết quả mong đợi**:
- Tổng tiền gốc: 2,000,000 VND
- Giảm giá: 100,000 VND (10% = 200k nhưng max chỉ 100k)
- Thanh toán: 1,900,000 VND

### Test 2: Coupon Fixed Amount

**Scenario**: Mua đơn hàng 1.5 triệu, áp mã **SALE50K**

**Kết quả mong đợi**:
- Tổng tiền gốc: 1,500,000 VND
- Giảm giá: 50,000 VND
- Thanh toán: 1,450,000 VND

### Test 3: Đơn hàng chưa đủ điều kiện

**Scenario**: Mua đơn hàng 400k, áp mã **SALE50K**

**Kết quả mong đợi**:
- ❌ Lỗi: "Đơn hàng chưa đạt giá trị tối thiểu 1,000,000 VND"

### Test 4: Coupon hết lượt

**Scenario**: Sử dụng coupon đã hết usage_limit

**Kết quả mong đợi**:
- ❌ Lỗi: "Mã giảm giá đã hết lượt sử dụng"

---

## 📝 Cách sử dụng trong Frontend

### 1. Thêm input coupon vào form checkout

```html
<form action="{{ route('cart.checkout') }}" method="POST">
    @csrf
    
    <!-- Thông tin giao hàng -->
    <input type="text" name="shipping_name" required>
    <input type="text" name="shipping_phone" required>
    <textarea name="shipping_address" required></textarea>
    <textarea name="note"></textarea>
    
    <!-- Input mã coupon -->
    <div class="coupon-section">
        <label>Mã giảm giá (tùy chọn)</label>
        <input type="text" 
               name="coupon_code" 
               id="coupon_code" 
               placeholder="Nhập mã giảm giá"
               maxlength="50">
        <button type="button" id="apply-coupon-btn">Áp dụng</button>
    </div>
    
    <!-- Hiển thị kết quả -->
    <div id="coupon-result" style="display:none;">
        <p>Giảm giá: <span id="discount-amount"></span></p>
        <p>Tổng thanh toán: <span id="final-amount"></span></p>
    </div>
    
    <button type="submit">Đặt hàng</button>
</form>
```

### 2. JavaScript để preview coupon (AJAX)

```javascript
document.getElementById('apply-coupon-btn').addEventListener('click', function() {
    const couponCode = document.getElementById('coupon_code').value;
    
    if (!couponCode) {
        alert('Vui lòng nhập mã giảm giá');
        return;
    }
    
    fetch('/cart/apply-coupon', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ coupon_code: couponCode })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Hiển thị kết quả
            document.getElementById('discount-amount').textContent = data.data.discount_display;
            document.getElementById('final-amount').textContent = data.data.final_display;
            document.getElementById('coupon-result').style.display = 'block';
            alert(data.message);
        } else {
            alert(data.message);
            document.getElementById('coupon-result').style.display = 'none';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Có lỗi xảy ra!');
    });
});
```

---

## 🎯 Kiểm tra trong Database

### Xem danh sách coupon

```sql
SELECT * FROM coupons;
```

### Xem coupon đã áp dụng bao nhiêu lần

```sql
SELECT code, name, used_count, usage_limit 
FROM coupons 
ORDER BY used_count DESC;
```

### Xem đơn hàng với tổng tiền sau giảm giá

```sql
SELECT order_id, user_id, total_amount, status, order_date 
FROM orders 
ORDER BY order_date DESC 
LIMIT 10;
```

---

## 📊 Logic Nghiệp vụ Coupon

### 1. Validation Rules

| Rule | Kiểm tra |
|------|----------|
| **is_active** | Coupon có đang hoạt động không |
| **start_date/end_date** | Trong thời gian hiệu lực |
| **used_count vs usage_limit** | Còn lượt sử dụng không |
| **min_order_amount** | Đơn hàng đủ điều kiện chưa |

### 2. Tính toán giảm giá

**Percentage Discount**:
```
discount = (total * discount_value) / 100
if (max_discount_amount) {
    discount = min(discount, max_discount_amount)
}
```

**Fixed Discount**:
```
discount = discount_value
```

**Final Amount**:
```
final_amount = total_amount - min(discount, total_amount)
```

### 3. Workflow

```
1. Khách nhập mã coupon
2. System validate coupon (isValid)
3. System tính discount (calculateDiscount)
4. Hiển thị preview (applyCoupon API)
5. Khách xác nhận checkout
6. System tạo đơn hàng với total_amount đã trừ coupon
7. System tăng used_count của coupon
```

---

## 🐛 Troubleshooting

### Lỗi: "Column not found: name"

**Nguyên nhân**: Chưa chạy migration mới

**Giải pháp**:
```bash
php artisan migrate
```

### Lỗi: "Connection refused"

**Nguyên nhân**: MySQL chưa chạy

**Giải pháp**:
```bash
sudo service mysql start
```

### Coupon không giảm giá

**Kiểm tra**:
1. Đơn hàng có đủ `min_order_amount` không?
2. Coupon có `is_active = true` không?
3. Coupon có trong thời gian hiệu lực không?
4. Coupon còn lượt sử dụng không?

---

## ✅ Checklist Test

- [ ] Migration chạy thành công
- [ ] Seeder tạo 4 coupon mẫu
- [ ] Test coupon percentage (WELCOME10)
- [ ] Test coupon fixed (SALE50K)
- [ ] Test min_order_amount validation
- [ ] Test max_discount_amount limit
- [ ] Test usage_limit
- [ ] Test coupon hết hạn
- [ ] Test coupon inactive
- [ ] Kiểm tra used_count tăng sau mỗi đơn
- [ ] Kiểm tra total_amount trong orders đã trừ discount

---

**Ngày cập nhật**: 22/10/2025  
**Phiên bản**: 1.0 - Complete Coupon System

