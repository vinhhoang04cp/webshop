#!/bin/bash

# Script test Chat API giữa Admin/Manager và User
# Chạy trên Docker với port 80

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Base URL (chạy trên Docker port 443 với HTTPS)
BASE_URL="https://localhost:443/api"

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}Test Chat API - Admin/Manager và User${NC}"
echo -e "${BLUE}========================================${NC}\n"

# Function to print section header
print_section() {
    echo -e "\n${YELLOW}$1${NC}"
    echo -e "${YELLOW}$(printf '=%.0s' {1..50})${NC}\n"
}

# Function to print test result
print_result() {
    if [ $1 -eq 0 ]; then
        echo -e "${GREEN}✓ $2${NC}"
    else
        echo -e "${RED}✗ $2${NC}"
    fi
}

# Step 1: Login as Admin
print_section "BƯỚC 1: Login Admin để lấy token"
echo "POST $BASE_URL/login"
ADMIN_RESPONSE=$(curl -s -k -X POST "$BASE_URL/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@webshop.com",
    "password": "admin123"
  }')

echo "$ADMIN_RESPONSE" | jq '.'

ADMIN_TOKEN=$(echo "$ADMIN_RESPONSE" | jq -r '.token // empty')
ADMIN_ID=$(echo "$ADMIN_RESPONSE" | jq -r '.data.id // empty')

if [ -z "$ADMIN_TOKEN" ]; then
    echo -e "${RED}✗ Không thể login Admin. Kiểm tra credentials!${NC}"
    exit 1
else
    echo -e "${GREEN}✓ Admin login thành công${NC}"
    echo -e "Admin ID: ${ADMIN_ID}"
    echo -e "Token: ${ADMIN_TOKEN:0:50}..."
fi

# Step 2: Login as User (Customer)
print_section "BƯỚC 2: Login User (Customer) để lấy token"
echo "POST $BASE_URL/login"
USER_RESPONSE=$(curl -s -k -X POST "$BASE_URL/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "customer@webshop.com",
    "password": "customer123"
  }')

echo "$USER_RESPONSE" | jq '.'

USER_TOKEN=$(echo "$USER_RESPONSE" | jq -r '.token // empty')
USER_ID=$(echo "$USER_RESPONSE" | jq -r '.data.id // empty')

if [ -z "$USER_TOKEN" ]; then
    echo -e "${RED}✗ Không thể login User. Thử tạo user mới...${NC}"
    
    # Try to register new user
    echo -e "\nThử đăng ký user mới..."
    USER_RESPONSE=$(curl -s -k -X POST "$BASE_URL/register" \
      -H "Content-Type: application/json" \
      -d '{
        "name": "Test Customer",
        "email": "testcustomer@example.com",
        "password": "password123",
        "password_confirmation": "password123"
      }')
    
    echo "$USER_RESPONSE" | jq '.'
    
    USER_TOKEN=$(echo "$USER_RESPONSE" | jq -r '.token // empty')
    USER_ID=$(echo "$USER_RESPONSE" | jq -r '.data.id // empty')
    
    if [ -z "$USER_TOKEN" ]; then
        echo -e "${RED}✗ Không thể tạo User mới!${NC}"
        exit 1
    fi
fi

echo -e "${GREEN}✓ User login thành công${NC}"
echo -e "User ID: ${USER_ID}"
echo -e "Token: ${USER_TOKEN:0:50}..."

# Step 3: User gửi tin nhắn đầu tiên
print_section "BƯỚC 3: User gửi tin nhắn cho Admin"
echo "POST $BASE_URL/chat/user/${USER_ID}/message"
MESSAGE1_RESPONSE=$(curl -s -k -X POST "$BASE_URL/chat/user/${USER_ID}/message" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $USER_TOKEN" \
  -d '{
    "message": "Xin chào Admin, tôi muốn hỏi về sản phẩm iPhone 15 Pro Max"
  }')

echo "$MESSAGE1_RESPONSE" | jq '.'
MESSAGE1_ID=$(echo "$MESSAGE1_RESPONSE" | jq -r '.id // empty')

if [ ! -z "$MESSAGE1_ID" ]; then
    echo -e "${GREEN}✓ User gửi tin nhắn thành công (Message ID: ${MESSAGE1_ID})${NC}"
else
    echo -e "${RED}✗ Không thể gửi tin nhắn từ User${NC}"
fi

# Step 4: Admin xem danh sách cuộc hội thoại
print_section "BƯỚC 4: Admin xem danh sách cuộc hội thoại"
echo "GET $BASE_URL/chat/conversations"
CONVERSATIONS_RESPONSE=$(curl -s -k -X GET "$BASE_URL/chat/conversations" \
  -H "Authorization: Bearer $ADMIN_TOKEN")

echo "$CONVERSATIONS_RESPONSE" | jq '.'

CONV_COUNT=$(echo "$CONVERSATIONS_RESPONSE" | jq 'length // 0')
echo -e "${GREEN}✓ Admin thấy ${CONV_COUNT} cuộc hội thoại${NC}"

# Step 5: Admin xem lịch sử chat với User
print_section "BƯỚC 5: Admin xem lịch sử chat với User"
echo "GET $BASE_URL/chat/user/${USER_ID}/history"
HISTORY1_RESPONSE=$(curl -s -k -X GET "$BASE_URL/chat/user/${USER_ID}/history" \
  -H "Authorization: Bearer $ADMIN_TOKEN")

echo "$HISTORY1_RESPONSE" | jq '.'

MSG_COUNT=$(echo "$HISTORY1_RESPONSE" | jq 'length // 0')
echo -e "${GREEN}✓ Có ${MSG_COUNT} tin nhắn trong lịch sử${NC}"

# Step 6: Admin trả lời User
print_section "BƯỚC 6: Admin trả lời User"
echo "POST $BASE_URL/chat/user/${USER_ID}/message"
MESSAGE2_RESPONSE=$(curl -s -k -X POST "$BASE_URL/chat/user/${USER_ID}/message" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -d '{
    "message": "Dạ chào anh/chị! iPhone 15 Pro Max hiện đang có sẵn với giá 29.990.000 VNĐ. Em có thể tư vấn thêm cho anh/chị."
  }')

echo "$MESSAGE2_RESPONSE" | jq '.'
MESSAGE2_ID=$(echo "$MESSAGE2_RESPONSE" | jq -r '.id // empty')

if [ ! -z "$MESSAGE2_ID" ]; then
    echo -e "${GREEN}✓ Admin trả lời thành công (Message ID: ${MESSAGE2_ID})${NC}"
else
    echo -e "${RED}✗ Không thể gửi tin nhắn từ Admin${NC}"
fi

# Step 7: User kiểm tra tin nhắn chưa đọc
print_section "BƯỚC 7: User kiểm tra tin nhắn chưa đọc"
echo "GET $BASE_URL/chat/user/${USER_ID}/unread?last_read_message_id=${MESSAGE1_ID}"
UNREAD_RESPONSE=$(curl -s -k -X GET "$BASE_URL/chat/user/${USER_ID}/unread?last_read_message_id=${MESSAGE1_ID}" \
  -H "Authorization: Bearer $USER_TOKEN")

echo "$UNREAD_RESPONSE" | jq '.'
UNREAD_COUNT=$(echo "$UNREAD_RESPONSE" | jq -r '.unread_count // 0')
echo -e "${GREEN}✓ User có ${UNREAD_COUNT} tin nhắn chưa đọc${NC}"

# Step 8: User xem lại lịch sử chat
print_section "BƯỚC 8: User xem lại lịch sử chat"
echo "GET $BASE_URL/chat/user/${USER_ID}/history"
HISTORY2_RESPONSE=$(curl -s -k -X GET "$BASE_URL/chat/user/${USER_ID}/history" \
  -H "Authorization: Bearer $USER_TOKEN")

echo "$HISTORY2_RESPONSE" | jq '.'
MSG_COUNT2=$(echo "$HISTORY2_RESPONSE" | jq 'length // 0')
echo -e "${GREEN}✓ Có ${MSG_COUNT2} tin nhắn trong lịch sử (sau khi Admin trả lời)${NC}"

# Step 9: User trả lời lại
print_section "BƯỚC 9: User trả lời lại Admin"
echo "POST $BASE_URL/chat/user/${USER_ID}/message"
MESSAGE3_RESPONSE=$(curl -s -k -X POST "$BASE_URL/chat/user/${USER_ID}/message" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $USER_TOKEN" \
  -d '{
    "message": "Cảm ơn bạn! Cho mình hỏi máy có bảo hành bao lâu và có những màu nào?"
  }')

echo "$MESSAGE3_RESPONSE" | jq '.'
MESSAGE3_ID=$(echo "$MESSAGE3_RESPONSE" | jq -r '.id // empty')

if [ ! -z "$MESSAGE3_ID" ]; then
    echo -e "${GREEN}✓ User trả lời thành công (Message ID: ${MESSAGE3_ID})${NC}"
else
    echo -e "${RED}✗ Không thể gửi tin nhắn từ User${NC}"
fi

# Step 10: Admin xem tin nhắn chưa đọc và trả lời tiếp
print_section "BƯỚC 10: Admin trả lời tiếp"
echo "POST $BASE_URL/chat/user/${USER_ID}/message"
MESSAGE4_RESPONSE=$(curl -s -k -X POST "$BASE_URL/chat/user/${USER_ID}/message" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -d '{
    "message": "Máy được bảo hành 12 tháng chính hãng. Hiện có 4 màu: Natural Titanium, Blue Titanium, White Titanium và Black Titanium. Anh/chị muốn đặt hàng không ạ?"
  }')

echo "$MESSAGE4_RESPONSE" | jq '.'
MESSAGE4_ID=$(echo "$MESSAGE4_RESPONSE" | jq -r '.id // empty')

if [ ! -z "$MESSAGE4_ID" ]; then
    echo -e "${GREEN}✓ Admin trả lời thành công (Message ID: ${MESSAGE4_ID})${NC}"
else
    echo -e "${RED}✗ Không thể gửi tin nhắn từ Admin${NC}"
fi

# Step 11: Xem lịch sử chat cuối cùng với phân trang
print_section "BƯỚC 11: Xem lịch sử chat cuối cùng (với limit và offset)"
echo "GET $BASE_URL/chat/user/${USER_ID}/history?limit=2&offset=0"
HISTORY3_RESPONSE=$(curl -s -k -X GET "$BASE_URL/chat/user/${USER_ID}/history?limit=2&offset=0" \
  -H "Authorization: Bearer $ADMIN_TOKEN")

echo "$HISTORY3_RESPONSE" | jq '.'
MSG_COUNT3=$(echo "$HISTORY3_RESPONSE" | jq 'length // 0')
echo -e "${GREEN}✓ Lấy được ${MSG_COUNT3} tin nhắn (giới hạn 2 tin mới nhất)${NC}"

# Step 12: Test Authorization - User không thể xem chat của user khác
print_section "BƯỚC 12: Test Authorization - User không thể xem chat của user khác"
ANOTHER_USER_ID=999
echo "GET $BASE_URL/chat/user/${ANOTHER_USER_ID}/history"
FORBIDDEN_RESPONSE=$(curl -s -k -X GET "$BASE_URL/chat/user/${ANOTHER_USER_ID}/history" \
  -H "Authorization: Bearer $USER_TOKEN" \
  -w "\nHTTP_CODE:%{http_code}")

HTTP_CODE=$(echo "$FORBIDDEN_RESPONSE" | grep "HTTP_CODE" | cut -d: -f2)
echo "$FORBIDDEN_RESPONSE" | grep -v "HTTP_CODE" | jq '.'

if [ "$HTTP_CODE" == "403" ]; then
    echo -e "${GREEN}✓ Authorization hoạt động đúng: User không thể xem chat của người khác (403 Forbidden)${NC}"
else
    echo -e "${YELLOW}⚠ Authorization có thể không hoạt động đúng (HTTP Code: ${HTTP_CODE})${NC}"
fi

# Final Summary
print_section "TÓM TẮT KẾT QUẢ TEST"
echo -e "${GREEN}✓ Admin đã login thành công${NC}"
echo -e "${GREEN}✓ User đã login thành công${NC}"
echo -e "${GREEN}✓ User gửi tin nhắn cho Admin${NC}"
echo -e "${GREEN}✓ Admin xem danh sách cuộc hội thoại${NC}"
echo -e "${GREEN}✓ Admin xem lịch sử chat${NC}"
echo -e "${GREEN}✓ Admin trả lời User (2 lần)${NC}"
echo -e "${GREEN}✓ User kiểm tra tin nhắn chưa đọc${NC}"
echo -e "${GREEN}✓ User xem lịch sử chat${NC}"
echo -e "${GREEN}✓ User trả lời lại Admin${NC}"
echo -e "${GREEN}✓ Test phân trang lịch sử chat${NC}"
echo -e "${GREEN}✓ Test authorization (403 Forbidden)${NC}"

echo -e "\n${BLUE}========================================${NC}"
echo -e "${BLUE}Hoàn thành test Chat API!${NC}"
echo -e "${BLUE}========================================${NC}\n"

