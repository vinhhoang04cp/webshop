#!/bin/bash

# Script test chức năng đặt lại mật khẩu
# Author: Hoàng Quang Vinh
# Date: 03/11/2025

echo "🔑 Test chức năng Đặt lại mật khẩu qua Email"
echo "=============================================="
echo ""

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# API Base URL
API_URL="http://localhost:8000/api"
TEST_EMAIL="test@example.com"

echo "📝 Bước 1: Tạo user test..."
php artisan tinker --execute="
\$user = \App\Models\User::where('email', '$TEST_EMAIL')->first();
if (!\$user) {
    \$user = \App\Models\User::create([
        'name' => 'Test User',
        'email' => '$TEST_EMAIL',
        'password' => \Hash::make('OldPassword@123'),
        'phone' => '0123456789',
        'address' => 'Ha Noi'
    ]);
    \$customerRole = \App\Models\Role::where('name', 'customer')->first();
    if (\$customerRole) {
        \App\Models\UserRole::create([
            'user_id' => \$user->id,
            'role_id' => \$customerRole->id
        ]);
    }
    echo '✅ User created: $TEST_EMAIL / OldPassword@123\n';
} else {
    echo '✅ User already exists: $TEST_EMAIL\n';
}
"

echo ""
echo "📧 Bước 2: Gửi yêu cầu reset password..."

# Gửi request reset password
RESPONSE=$(curl -s -X POST "$API_URL/forgot-password" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d "{\"email\": \"$TEST_EMAIL\"}")

echo "$RESPONSE" | jq '.'

# Check if successful
if echo "$RESPONSE" | jq -e '.status == true' > /dev/null; then
    echo -e "${GREEN}✅ Gửi email reset thành công!${NC}"
else
    echo -e "${RED}❌ Lỗi khi gửi email reset${NC}"
    exit 1
fi

echo ""
echo "📄 Bước 3: Lấy token từ log..."
echo "Đang tìm token trong storage/logs/laravel.log..."

# Lấy dòng cuối cùng chứa reset-password từ log
TOKEN_LINE=$(grep -o 'reset-password/[a-zA-Z0-9]*' storage/logs/laravel.log | tail -1)

if [ -z "$TOKEN_LINE" ]; then
    echo -e "${RED}❌ Không tìm thấy token trong log${NC}"
    echo "Vui lòng kiểm tra file: storage/logs/laravel.log"
    exit 1
fi

# Extract token
TOKEN=$(echo "$TOKEN_LINE" | sed 's/reset-password\///')
echo -e "${GREEN}✅ Token found: $TOKEN${NC}"

echo ""
echo "🔐 Bước 4: Test reset password với token..."

# Reset password
NEW_PASSWORD="NewPassword@123"
RESET_RESPONSE=$(curl -s -X POST "$API_URL/reset-password" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d "{
    \"email\": \"$TEST_EMAIL\",
    \"token\": \"$TOKEN\",
    \"password\": \"$NEW_PASSWORD\",
    \"password_confirmation\": \"$NEW_PASSWORD\"
  }")

echo "$RESET_RESPONSE" | jq '.'

# Check if successful
if echo "$RESET_RESPONSE" | jq -e '.status == true' > /dev/null; then
    echo -e "${GREEN}✅ Reset password thành công!${NC}"
else
    echo -e "${RED}❌ Lỗi khi reset password${NC}"
    exit 1
fi

echo ""
echo "🔑 Bước 5: Test login với mật khẩu mới..."

# Test login với mật khẩu mới
LOGIN_RESPONSE=$(curl -s -X POST "$API_URL/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d "{
    \"email\": \"$TEST_EMAIL\",
    \"password\": \"$NEW_PASSWORD\"
  }")

echo "$LOGIN_RESPONSE" | jq '.'

# Check if successful
if echo "$LOGIN_RESPONSE" | jq -e '.status == true' > /dev/null; then
    echo -e "${GREEN}✅ Login với mật khẩu mới thành công!${NC}"
    AUTH_TOKEN=$(echo "$LOGIN_RESPONSE" | jq -r '.token')
    echo -e "${GREEN}Token: $AUTH_TOKEN${NC}"
else
    echo -e "${RED}❌ Lỗi khi login với mật khẩu mới${NC}"
    exit 1
fi

echo ""
echo "🧪 Bước 6: Test login với mật khẩu cũ (phải thất bại)..."

# Test login với mật khẩu cũ
OLD_LOGIN_RESPONSE=$(curl -s -X POST "$API_URL/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d "{
    \"email\": \"$TEST_EMAIL\",
    \"password\": \"OldPassword@123\"
  }")

echo "$OLD_LOGIN_RESPONSE" | jq '.'

# Check if failed (should fail)
if echo "$OLD_LOGIN_RESPONSE" | jq -e '.status == false' > /dev/null; then
    echo -e "${GREEN}✅ Login với mật khẩu cũ thất bại (đúng như mong đợi)${NC}"
else
    echo -e "${RED}❌ Login với mật khẩu cũ vẫn thành công (không đúng!)${NC}"
    exit 1
fi

echo ""
echo "=============================================="
echo -e "${GREEN}🎉 TẤT CẢ TESTS ĐỀU PASS!${NC}"
echo "=============================================="
echo ""
echo "📊 Tóm tắt kết quả:"
echo "✅ User test được tạo thành công"
echo "✅ Email reset được gửi thành công"
echo "✅ Token được tìm thấy trong log"
echo "✅ Reset password thành công"
echo "✅ Login với mật khẩu mới thành công"
echo "✅ Login với mật khẩu cũ thất bại (đúng)"
echo ""
echo "💡 Thông tin test:"
echo "   Email: $TEST_EMAIL"
echo "   Mật khẩu cũ: OldPassword@123"
echo "   Mật khẩu mới: $NEW_PASSWORD"
echo ""
echo "📚 Xem thêm tài liệu:"
echo "   - docs/PASSWORD_RESET_GUIDE.md"
echo "   - docs/EMAIL_CONFIGURATION.md"
echo "   - docs/PASSWORD_RESET_DEMO.md"

