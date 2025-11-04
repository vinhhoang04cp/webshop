# 📧 Hướng dẫn Cấu hình Email

> **Mục đích**: Hướng dẫn chi tiết cấu hình email để sử dụng chức năng đặt lại mật khẩu

## 📋 Mục lục
1. [Tổng quan](#tổng-quan)
2. [Cấu hình Gmail](#cấu-hình-gmail)
3. [Cấu hình Mailtrap](#cấu-hình-mailtrap)
4. [Cấu hình Log](#cấu-hình-log)
5. [Kiểm tra cấu hình](#kiểm-tra-cấu-hình)

---

## 🎯 Tổng quan

Để chức năng đặt lại mật khẩu hoạt động, bạn cần cấu hình email trong file `.env`. Có 3 options phổ biến:

1. **Gmail** - Dùng cho production hoặc development thực tế
2. **Mailtrap** - Dùng cho testing (không gửi email thật)
3. **Log** - Lưu email vào log file (không gửi email)

---

## 📮 Option 1: Cấu hình Gmail

### Bước 1: Tạo App Password từ Google

1. Truy cập: https://myaccount.google.com/security
2. Tìm phần **"2-Step Verification"** và bật nó lên (nếu chưa bật)
3. Sau khi bật 2-Step Verification, quay lại trang Security
4. Tìm **"App passwords"** (Mật khẩu ứng dụng)
5. Click **"Generate"** hoặc **"Tạo mật khẩu ứng dụng"**
6. Chọn app: **Mail**, device: **Other (Khác)**
7. Nhập tên: `WebShop Laravel App`
8. Click **"Generate"** và copy mật khẩu 16 ký tự

### Bước 2: Cấu hình trong `.env`

Mở file `.env` và thêm/sửa các dòng sau:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=abcd efgh ijkl mnop
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="WebShop"
```

**⚠️ Lưu ý:**
- `MAIL_USERNAME`: Email Gmail của bạn
- `MAIL_PASSWORD`: Mật khẩu 16 ký tự từ App Password (không phải mật khẩu Gmail)
- `MAIL_FROM_ADDRESS`: Email Gmail của bạn
- Xóa tất cả dấu cách trong App Password khi dán vào `.env`

### Bước 3: Clear cache

```bash
php artisan config:clear
php artisan cache:clear
```

### Ưu và nhược điểm

✅ **Ưu điểm:**
- Email thật được gửi đến user
- Phù hợp cho production
- Miễn phí (trong giới hạn Gmail)

❌ **Nhược điểm:**
- Cần tạo App Password
- Gmail có giới hạn gửi email (500 emails/ngày)
- Email có thể bị đánh dấu spam
- Phức tạp hơn cho testing

---

## 🧪 Option 2: Cấu hình Mailtrap (Khuyến nghị cho Testing)

### Bước 1: Đăng ký Mailtrap

1. Truy cập: https://mailtrap.io
2. Click **"Sign Up"** và đăng ký tài khoản (miễn phí)
3. Verify email và đăng nhập

### Bước 2: Lấy thông tin SMTP

1. Vào **"Email Testing"** > **"Inboxes"**
2. Click vào inbox mặc định (hoặc tạo inbox mới)
3. Vào tab **"SMTP Settings"**
4. Chọn **"Laravel 9+"**
5. Copy thông tin hiển thị

### Bước 3: Cấu hình trong `.env`

```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@webshop.com
MAIL_FROM_NAME="WebShop"
```

**📝 Lưu ý:**
- `MAIL_USERNAME` và `MAIL_PASSWORD` lấy từ Mailtrap dashboard
- `MAIL_FROM_ADDRESS` có thể là bất kỳ email nào (không cần tồn tại)

### Bước 4: Clear cache

```bash
php artisan config:clear
php artisan cache:clear
```

### Bước 5: Kiểm tra email trong Mailtrap

Sau khi gửi email reset password:
1. Vào Mailtrap dashboard
2. Click vào inbox
3. Xem email đã nhận
4. Click vào link reset trong email để test

### Ưu và nhược điểm

✅ **Ưu điểm:**
- **Tốt nhất cho development và testing**
- Email không được gửi thật (tránh spam)
- Xem email trong dashboard đẹp mắt
- Có HTML/Text preview
- Kiểm tra spam score
- Miễn phí (100 emails/tháng)
- Không cần App Password phức tạp

❌ **Nhược điểm:**
- Chỉ dùng cho testing, không dùng production
- Cần đăng ký tài khoản

---

## 📝 Option 3: Cấu hình Log (Đơn giản nhất)

### Bước 1: Cấu hình trong `.env`

```env
MAIL_MAILER=log
MAIL_FROM_ADDRESS=noreply@webshop.com
MAIL_FROM_NAME="WebShop"
```

### Bước 2: Clear cache

```bash
php artisan config:clear
php artisan cache:clear
```

### Bước 3: Xem email trong log

Sau khi gửi email reset password, mở file:

```
storage/logs/laravel.log
```

Tìm email content trong file log. Ví dụ:

```
[2025-11-03 10:30:45] local.DEBUG: 
Subject: Yêu cầu đặt lại mật khẩu
To: user@example.com
Body: ... (HTML content của email)
Reset Link: http://localhost:8000/reset-password/abc123...
```

### Ưu và nhược điểm

✅ **Ưu điểm:**
- **Đơn giản nhất**, không cần cấu hình gì thêm
- Không cần đăng ký service nào
- Tốt cho quick testing

❌ **Nhược điểm:**
- Email không được gửi thật
- Phải mở file log để xem
- Không xem được HTML preview đẹp
- Khó debug hơn Mailtrap

---

## ✅ Kiểm tra cấu hình

### 1. Kiểm tra file config

```bash
php artisan config:show mail
```

Output sẽ hiển thị cấu hình mail hiện tại:

```
mailers.smtp.host ......................................... smtp.gmail.com
mailers.smtp.port ......................................... 587
mailers.smtp.username ..................................... your-email@gmail.com
from.address .............................................. your-email@gmail.com
from.name ................................................. WebShop
```

### 2. Test gửi email với Tinker

```bash
php artisan tinker
```

```php
// Test gửi email đơn giản
Mail::raw('Test email from WebShop', function($message) {
    $message->to('test@example.com');
    $message->subject('Test Email');
});

// Nếu không có lỗi, email đã được gửi (hoặc logged)
```

### 3. Test chức năng reset password

1. Truy cập: http://localhost:8000/forgot-password
2. Nhập email của user đã tồn tại
3. Submit form
4. Kiểm tra:
   - **Gmail**: Kiểm tra hộp thư email
   - **Mailtrap**: Vào Mailtrap dashboard
   - **Log**: Mở file `storage/logs/laravel.log`

---

## 🚨 Troubleshooting

### Lỗi: "Connection refused"

```
Connection could not be established with host "smtp.gmail.com" 
[Connection refused #111]
```

**Nguyên nhân:**
- Server không cho phép kết nối SMTP
- Port bị block bởi firewall

**Giải pháp:**
1. Thử port khác: `MAIL_PORT=465` và `MAIL_ENCRYPTION=ssl`
2. Dùng Mailtrap thay vì Gmail
3. Dùng `MAIL_MAILER=log` tạm thời

### Lỗi: "Invalid credentials"

```
Expected response code "250" but got code "535" with message 
"535-5.7.8 Username and Password not accepted"
```

**Nguyên nhân:**
- App Password sai
- Chưa bật 2-Step Verification
- Dùng mật khẩu Gmail thường thay vì App Password

**Giải pháp:**
1. Kiểm tra lại App Password
2. Tạo App Password mới
3. Xóa tất cả dấu cách trong password
4. Clear config: `php artisan config:clear`

### Lỗi: "Address in mailbox given [] does not comply with RFC 2822"

```
Address in mailbox given [] does not comply with RFC 2822, 3.6.2.
```

**Nguyên nhân:**
- `MAIL_FROM_ADDRESS` trống hoặc không hợp lệ

**Giải pháp:**
```env
MAIL_FROM_ADDRESS=noreply@webshop.com
```

### Email vào spam

**Nguyên nhân:**
- Domain không có SPF/DKIM record
- Gửi từ Gmail miễn phí
- Nội dung email bị đánh dấu spam

**Giải pháp:**
1. Production: Sử dụng email service chuyên nghiệp (SendGrid, AWS SES, Mailgun)
2. Development: Sử dụng Mailtrap (không gửi email thật)
3. Thêm SPF record cho domain
4. Sử dụng email business thay vì Gmail

---

## 📊 So sánh các options

| Tiêu chí | Gmail | Mailtrap | Log |
|----------|-------|----------|-----|
| **Độ khó cài đặt** | ⭐⭐⭐ | ⭐⭐ | ⭐ |
| **Phù hợp cho testing** | ⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐ |
| **Phù hợp cho production** | ⭐⭐⭐ | ❌ | ❌ |
| **Email thật được gửi** | ✅ | ❌ | ❌ |
| **Dễ debug** | ⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐ |
| **Chi phí** | Miễn phí | Miễn phí | Miễn phí |
| **Giới hạn** | 500/ngày | 100/tháng | Không |

### Khuyến nghị

- **Development/Testing**: Dùng **Mailtrap** ⭐⭐⭐⭐⭐
- **Quick Testing**: Dùng **Log** ⭐⭐⭐
- **Production (Small)**: Dùng **Gmail** ⭐⭐⭐
- **Production (Large)**: Dùng SendGrid, AWS SES, Mailgun ⭐⭐⭐⭐⭐

---

## 🚀 Production Email Services (Khuyến nghị)

Cho production app lớn, bạn nên dùng email service chuyên nghiệp:

### 1. SendGrid

**Ưu điểm:**
- 100 emails/ngày miễn phí
- API đơn giản
- Analytics tốt

**Cấu hình:**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your-sendgrid-api-key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
```

### 2. AWS SES (Amazon Simple Email Service)

**Ưu điểm:**
- Rất rẻ ($0.10/1000 emails)
- Tích hợp tốt với AWS
- Không giới hạn

**Cấu hình:**
```env
MAIL_MAILER=ses
AWS_ACCESS_KEY_ID=your-access-key
AWS_SECRET_ACCESS_KEY=your-secret-key
AWS_DEFAULT_REGION=us-east-1
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
```

### 3. Mailgun

**Ưu điểm:**
- 100 emails/ngày miễn phí (sau khi verify domain)
- API tốt
- Tracking detail

**Cấu hình:**
```env
MAIL_MAILER=mailgun
MAILGUN_DOMAIN=mg.yourdomain.com
MAILGUN_SECRET=your-mailgun-secret
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
```

---

## 📚 Tài liệu tham khảo

- [Laravel Mail Documentation](https://laravel.com/docs/11.x/mail)
- [Google App Passwords Guide](https://support.google.com/accounts/answer/185833)
- [Mailtrap Documentation](https://mailtrap.io/docs/)
- [SendGrid Laravel Integration](https://sendgrid.com/docs/for-developers/sending-email/laravel/)

---

**🎉 Hoàn tất!**

Sau khi cấu hình xong, bạn có thể test chức năng đặt lại mật khẩu ngay. Nếu gặp vấn đề, hãy tham khảo phần Troubleshooting hoặc liên hệ team.

