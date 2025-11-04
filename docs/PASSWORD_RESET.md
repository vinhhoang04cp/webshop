# 🔑 Đặt lại Mật khẩu qua Email

> **Chức năng đã hoàn thiện 100%! Chỉ cần cấu hình email và sử dụng ngay!**

## 🎯 Quick Start (3 bước - 5 phút)

### Bước 1: Cấu hình Email

**Cách đơn giản nhất (Log):**
```env
# Thêm vào .env
MAIL_MAILER=log
MAIL_FROM_ADDRESS=noreply@webshop.com
MAIL_FROM_NAME="WebShop"
```

**Khuyến nghị cho testing (Mailtrap):**
1. Đăng ký miễn phí: https://mailtrap.io
2. Thêm vào `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@webshop.com
MAIL_FROM_NAME="WebShop"
```

**Gmail cho production:**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password  # Tạo App Password từ Google
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="WebShop"
```

**Clear cache:**
```bash
php artisan config:clear && php artisan cache:clear
```

### Bước 2: Test chức năng

**Web:**
```bash
php artisan serve
# Vào: http://localhost:8000/forgot-password
# Nhập email và test
```

**API:**
```bash
curl -X POST http://localhost:8000/api/forgot-password \
  -H "Content-Type: application/json" \
  -d '{"email": "test@example.com"}'

# Lấy token từ log và reset:
curl -X POST http://localhost:8000/api/reset-password \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "token": "TOKEN_FROM_LOG",
    "password": "NewPassword@123",
    "password_confirmation": "NewPassword@123"
  }'
```

**Test script tự động:**
```bash
./test-password-reset.sh
```

### Bước 3: Xong! 🎉

---

## 📌 URLs & Routes

**Web:**
- `/forgot-password` - Form quên mật khẩu
- `/reset-password/{token}` - Form reset password
- `/login` - Có link "Quên mật khẩu?"

**API:**
- `POST /api/forgot-password` - Gửi link reset
- `POST /api/reset-password` - Reset password
- `POST /api/validate-reset-token` - Validate token

---

## 🔒 Yêu cầu mật khẩu mới

- ✅ Tối thiểu 12 ký tự
- ✅ Có chữ HOA, thường, số, ký tự đặc biệt
- ✅ Ví dụ: `NewPassword@123`

---

## 💻 Luồng hoạt động

```
1. User nhập email → Hệ thống tạo token → Gửi email
2. User click link → Hiển thị form reset
3. User nhập password mới → Token được validate → Password updated
```

**Bảo mật:**
- Token 64 ký tự ngẫu nhiên, được hash
- Hết hạn sau 24 giờ
- Chỉ dùng 1 lần
- Rate limiting chống spam

---

## 🛠️ Có gì trong hệ thống

**Backend:**
- `PasswordResetService` - Logic xử lý
- Controllers cho Web & API
- Validation requests
- Routes với rate limiting

**Frontend:**
- Form forgot password
- Form reset password  
- Email template đẹp

**Database:**
- Table `password_reset_tokens` (đã có sẵn)

---

## 🚨 Troubleshooting

**Email không gửi được:**
```env
MAIL_MAILER=log  # Dùng log tạm thời
```

**Token không tìm thấy:**
```bash
tail -f storage/logs/laravel.log
```

**Mật khẩu không đủ mạnh:**
- Phải min 12 ký tự với chữ hoa, thường, số, ký tự đặc biệt

---

## ⚡ Commands hữu ích

```bash
# Xem routes
php artisan route:list | grep password

# Clear cache
php artisan config:clear

# Xem log real-time
tail -f storage/logs/laravel.log

# Test script
./test-password-reset.sh

# Test email trong Tinker
php artisan tinker
> Mail::raw('Test', fn($m) => $m->to('test@example.com')->subject('Test'));
```

---

## 📊 API Examples

**Request reset link:**
```json
POST /api/forgot-password
{
  "email": "user@example.com"
}

Response:
{
  "status": true,
  "message": "Link đặt lại mật khẩu đã được gửi đến email của bạn."
}
```

**Reset password:**
```json
POST /api/reset-password
{
  "email": "user@example.com",
  "token": "abc123...",
  "password": "NewPassword@123",
  "password_confirmation": "NewPassword@123"
}

Response:
{
  "status": true,
  "message": "Mật khẩu đã được đặt lại thành công."
}
```

---

## 📧 Email Providers Comparison

| Provider | Dễ cài đặt | Phù hợp testing | Phù hợp production | Chi phí |
|----------|------------|-----------------|-------------------|---------|
| **Log** | ⭐⭐⭐⭐⭐ | ⭐⭐⭐ | ❌ | Miễn phí |
| **Mailtrap** | ⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ❌ | Miễn phí |
| **Gmail** | ⭐⭐⭐ | ⭐⭐ | ⭐⭐⭐ | Miễn phí |
| **SendGrid** | ⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐⭐⭐ | $15/tháng |

**Khuyến nghị:**
- Development: Mailtrap
- Testing: Log
- Production: SendGrid/AWS SES

---

## ✅ Checklist

- [ ] Cấu hình email trong `.env`
- [ ] Clear cache
- [ ] Test với user thật
- [ ] Kiểm tra email/log
- [ ] Test reset password
- [ ] Login với mật khẩu mới

---

**🎉 Hoàn tất! Chức năng sẵn sàng sử dụng.**

*Created: 04/11/2025 | Version: 2.0 Unified*