# 📧 Hướng dẫn Cấu hình Gmail cho Chức năng Quên Mật khẩu

## 🎯 Tổng quan

File `.env` đã được cấu hình sẵn để sử dụng Gmail SMTP. Bạn chỉ cần làm theo 3 bước sau để kích hoạt.

---

## 📝 Bước 1: Tạo App Password từ Google

### 1.1. Truy cập Google Account Security

Mở trình duyệt và truy cập: **https://myaccount.google.com/security**

### 1.2. Bật 2-Step Verification

**Quan trọng**: Bạn PHẢI bật 2-Step Verification trước khi tạo App Password.

1. Tìm mục **"2-Step Verification"** hoặc **"Xác minh 2 bước"**
2. Click vào và làm theo hướng dẫn:
   - Nhập số điện thoại
   - Nhận mã xác nhận
   - Xác nhận và hoàn tất

### 1.3. Tạo App Password

Sau khi bật 2-Step Verification:

1. Quay lại trang **Security** (https://myaccount.google.com/security)
2. Tìm mục **"App passwords"** hoặc **"Mật khẩu ứng dụng"**
3. Click vào **"Generate"** hoặc **"Tạo"**
4. Chọn:
   - **App**: Mail
   - **Device**: Other (Custom name)
   - Nhập tên: `WebShop Laravel App`
5. Click **"Generate"**
6. **Copy mật khẩu 16 ký tự** hiển thị (ví dụ: `abcd efgh ijkl mnop`)

⚠️ **Lưu ý**: 
- Xóa tất cả dấu cách khi paste vào file `.env`
- Mật khẩu chỉ hiển thị 1 lần, lưu lại ngay!

---

## ⚙️ Bước 2: Cập nhật thông tin trong file `.env`

### 2.1. Mở file `.env` trong thư mục gốc dự án

File `.env` đã được cấu hình sẵn như sau:

```env
# Cấu hình Gmail (ĐANG SỬ DỤNG)
MAIL_MAILER=smtp
MAIL_SCHEME=null
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your-email@gmail.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### 2.2. Thay đổi 2 dòng sau:

**Dòng cần sửa 1:**
```env
MAIL_USERNAME=your-email@gmail.com
```
👉 Thay `your-email@gmail.com` bằng **email Gmail thật của bạn**

**Ví dụ:**
```env
MAIL_USERNAME=nguyenvana@gmail.com
```

**Dòng cần sửa 2:**
```env
MAIL_PASSWORD=your-app-password
```
👉 Thay `your-app-password` bằng **App Password 16 ký tự** vừa tạo (XÓA HẾT DẤU CÁCH)

**Ví dụ:**
```env
MAIL_PASSWORD=abcdefghijklmnop
```

**Dòng cần sửa 3 (tùy chọn):**
```env
MAIL_FROM_ADDRESS="your-email@gmail.com"
```
👉 Thay bằng email Gmail của bạn hoặc giữ nguyên

### 2.3. Ví dụ file `.env` sau khi sửa:

```env
# Cấu hình Gmail (ĐANG SỬ DỤNG)
MAIL_MAILER=smtp
MAIL_SCHEME=null
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=nguyenvana@gmail.com
MAIL_PASSWORD=abcdefghijklmnop
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="nguyenvana@gmail.com"
MAIL_FROM_NAME="${APP_NAME}"
```

---

## 🔄 Bước 3: Clear Cache và Test

### 3.1. Clear cache Laravel

Chạy lệnh sau trong terminal:

```bash
php artisan config:clear
php artisan cache:clear
```

### 3.2. Test gửi email

**Cách 1: Test với script tự động**

```bash
./test-password-reset.sh
```

**Cách 2: Test bằng Tinker**

```bash
php artisan tinker
```

Trong Tinker, chạy:

```php
Mail::raw('Test email từ WebShop', function($message) {
    $message->to('email-test@gmail.com');
    $message->subject('Test Email');
});
```

Thay `email-test@gmail.com` bằng email bạn muốn nhận test.

**Cách 3: Test qua trang web**

1. Chạy server: `php artisan serve`
2. Vào: http://localhost:8000/forgot-password
3. Nhập email của user đã tồn tại
4. Submit và kiểm tra email

---

## ✅ Checklist hoàn tất

- [ ] Đã bật 2-Step Verification trên Google Account
- [ ] Đã tạo App Password thành công
- [ ] Đã cập nhật `MAIL_USERNAME` với email Gmail thật
- [ ] Đã cập nhật `MAIL_PASSWORD` với App Password (không có dấu cách)
- [ ] Đã cập nhật `MAIL_FROM_ADDRESS` (tùy chọn)
- [ ] Đã chạy `php artisan config:clear`
- [ ] Đã test gửi email thành công

---

## 🚨 Troubleshooting (Xử lý lỗi)

### Lỗi 1: "Connection refused" hoặc "Connection timeout"

**Nguyên nhân**: Port 587 bị chặn bởi firewall hoặc mạng

**Giải pháp**: Thử port 465 với SSL

```env
MAIL_PORT=465
MAIL_ENCRYPTION=ssl
```

### Lỗi 2: "Invalid credentials" (535 error)

**Nguyên nhân**:
- App Password sai
- Chưa bật 2-Step Verification
- Dùng mật khẩu Gmail thường thay vì App Password

**Giải pháp**:
1. Kiểm tra lại App Password (phải là 16 ký tự)
2. Xóa tất cả dấu cách trong password
3. Tạo App Password mới
4. Chắc chắn đã bật 2-Step Verification

### Lỗi 3: Email vào Spam

**Nguyên nhân**: Gmail đánh dấu email từ ứng dụng là spam

**Giải pháp**:
1. Kiểm tra folder Spam/Junk
2. Đánh dấu "Not Spam"
3. Thêm email sender vào danh bạ
4. Với production, nên dùng domain email riêng + SPF/DKIM

### Lỗi 4: "Address in mailbox given [] does not comply with RFC 2822"

**Nguyên nhân**: `MAIL_FROM_ADDRESS` trống hoặc sai format

**Giải pháp**:
```env
MAIL_FROM_ADDRESS="noreply@webshop.com"
```

Hoặc dùng email Gmail:
```env
MAIL_FROM_ADDRESS="your-email@gmail.com"
```

### Lỗi 5: Gmail block "Less secure apps"

**Nguyên nhân**: Gmail block ứng dụng không an toàn

**Giải pháp**: 
- **PHẢI dùng App Password** (không được dùng mật khẩu Gmail thường)
- App Password chỉ có thể tạo sau khi bật 2-Step Verification

---

## 📊 So sánh Gmail vs Mailtrap vs Log

| Tiêu chí | Gmail | Mailtrap | Log |
|----------|-------|----------|-----|
| **Độ khó setup** | ⭐⭐⭐ (Cần App Password) | ⭐⭐ | ⭐ |
| **Email thật được gửi** | ✅ Có | ❌ Không | ❌ Không |
| **Phù hợp testing** | ⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐ |
| **Phù hợp production** | ⭐⭐⭐ | ❌ | ❌ |
| **Giới hạn** | 500 emails/ngày | 100 emails/tháng | Không |
| **Chi phí** | Miễn phí | Miễn phí | Miễn phí |
| **Xem email** | Gmail inbox | Dashboard đẹp | File log |

**Khuyến nghị**:
- **Development/Testing**: Dùng **Mailtrap** (xem docs/EMAIL_CONFIGURATION.md)
- **Production nhỏ**: Dùng **Gmail** (như đang setup)
- **Production lớn**: Dùng **SendGrid/AWS SES** (tính phí nhưng pro)

---

## 🔐 Bảo mật

### ⚠️ QUAN TRỌNG:

1. **KHÔNG commit file `.env` lên Git**
   - File `.env` đã được thêm vào `.gitignore`
   - Không chia sẻ App Password với ai

2. **Sử dụng Environment Variables**
   - Trên server production, set environment variables trực tiếp
   - Không hardcode password trong code

3. **Rotating App Password**
   - Nên thay đổi App Password định kỳ (3-6 tháng)
   - Xóa App Password cũ khi không dùng

4. **Giới hạn gửi email**
   - Gmail limit: 500 emails/ngày
   - Nếu vượt quá, tài khoản có thể bị khóa tạm thời

---

## 📚 Tài liệu tham khảo

- [Laravel Mail Documentation](https://laravel.com/docs/11.x/mail)
- [Google App Passwords Guide](https://support.google.com/accounts/answer/185833)
- [Tài liệu email configuration chi tiết](./docs/EMAIL_CONFIGURATION.md)
- [Tài liệu password reset](./PASSWORD_RESET.md)

---

## 🎉 Hoàn tất!

Sau khi hoàn thành 3 bước trên, chức năng quên mật khẩu sẽ gửi email qua Gmail của bạn!

**Test ngay**:
1. Vào http://localhost:8000/forgot-password
2. Nhập email của user
3. Kiểm tra Gmail inbox
4. Click link reset password
5. Đặt mật khẩu mới

**Cần hỗ trợ?** 
- Xem file `docs/EMAIL_CONFIGURATION.md` để biết thêm chi tiết
- Chạy `./test-password-reset.sh` để test tự động

---

*Tạo bởi: Hoàng Quang Vinh*  
*Ngày: 04/11/2025*  
*Version: 1.0*
