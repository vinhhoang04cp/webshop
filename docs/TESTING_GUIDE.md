# 🧪 Hướng dẫn chạy Tests

## Cách 1: Chạy với Sail (Khuyến nghị)

### Chạy tất cả tests
```bash
./vendor/bin/sail test
```

### Chạy chỉ Security Middleware Tests
```bash
./vendor/bin/sail test --filter=SecurityMiddlewareTest
```

### Sử dụng script tự động
```bash
./run-tests.sh
```

Script này sẽ:
1. Kiểm tra và khởi động Sail nếu chưa chạy
2. Chạy migrations
3. Chạy Security Middleware Tests

---

## Cách 2: Chạy tests với SQLite (Nhanh hơn)

Tests đã được cấu hình sử dụng SQLite in-memory database trong `phpunit.xml`:

```bash
# Ngoài Sail container
./vendor/bin/sail artisan test --filter=SecurityMiddlewareTest

# Hoặc bên trong Sail container
./vendor/bin/sail bash
php artisan test --filter=SecurityMiddlewareTest
```

---

## Test Cases

### 1. ✅ test_sanitize_middleware_removes_html_tags
Kiểm tra middleware loại bỏ HTML tags khỏi input:
- Input: `<script>alert("XSS")</script>Test User`
- Output: `alert(&quot;XSS&quot;)Test User`

### 2. ✅ test_sanitize_middleware_escapes_special_characters
Kiểm tra middleware escape ký tự đặc biệt:
- Input: `User & Company "Test"`
- Output: `User &amp; Company &quot;Test&quot;`

### 3. ✅ test_sanitize_middleware_preserves_passwords
Kiểm tra passwords không bị ảnh hưởng:
- Password với ký tự đặc biệt vẫn hoạt động bình thường

### 4. ✅ test_security_headers_are_present
Kiểm tra các security headers được thêm vào response:
- X-Content-Type-Options
- X-Frame-Options
- X-XSS-Protection
- Referrer-Policy
- Content-Security-Policy
- Permissions-Policy

### 5. ✅ test_content_security_policy_header
Kiểm tra CSP header có đúng format

### 6. ✅ test_permissions_policy_header
Kiểm tra Permissions-Policy header

---

## Troubleshooting

### Lỗi "Connection refused"
Nếu gặp lỗi kết nối MySQL:

1. **Đảm bảo Sail đang chạy:**
   ```bash
   ./vendor/bin/sail up -d
   ```

2. **Kiểm tra containers:**
   ```bash
   ./vendor/bin/sail ps
   ```

3. **Xem logs:**
   ```bash
   ./vendor/bin/sail logs
   ```

4. **Restart services:**
   ```bash
   ./vendor/bin/sail down
   ./vendor/bin/sail up -d
   ```

### Test sử dụng SQLite
Tests đã được cấu hình để tự động dùng SQLite in-memory, không cần MySQL:

```xml
<!-- phpunit.xml -->
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

---

## CI/CD Integration

### GitHub Actions
```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          
      - name: Install Dependencies
        run: composer install
        
      - name: Run Tests
        run: php artisan test
```

---

## Kết quả mong đợi

```
PASS  Tests\Feature\SecurityMiddlewareTest
✓ sanitize middleware removes html tags
✓ sanitize middleware escapes special characters
✓ sanitize middleware preserves passwords
✓ security headers are present
✓ content security policy header
✓ permissions policy header

Tests:    6 passed
Duration: 2.5s
```

---

**Ngày cập nhật:** 30/10/2025
