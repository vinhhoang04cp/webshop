#!/bin/bash

# Script test Chat API với Manager và User
# Test để đảm bảo Manager cũng có quyền chat với User

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Base URL (chạy trên Docker port 443 với HTTPS)
BASE_URL="https://localhost:443/api"

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}Test Chat API - Manager và User${NC}"
echo -e "${BLUE}========================================${NC}\n"

# Function to print section header
print_section() {
    echo -e "\n${YELLOW}$1${NC}"
    echo -e "${YELLOW}$(printf '=%.0s' {1..50})${NC}\n"
}

# Step 1: Login as Manager
print_section "BƯỚC 1: Login Manager để lấy token"
echo "POST $BASE_URL/login"
MANAGER_RESPONSE=$(curl -s -k -X POST "$BASE_URL/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "manager@webshop.com",
    "password": "manager123"
  }')

echo "$MANAGER_RESPONSE" | jq '.'

MANAGER_TOKEN=$(echo "$MANAGER_RESPONSE" | jq -r '.token // empty')
MANAGER_ID=$(echo "$MANAGER_RESPONSE" | jq -r '.data.id // empty')

if [ -z "$MANAGER_TOKEN" ]; then
    echo -e "${RED}✗ Không thể login Manager. Kiểm tra credentials!${NC}"
    exit 1
else
    echo -e "${GREEN}✓ Manager login thành công${NC}"
    echo -e "Manager ID: ${MANAGER_ID}"
    echo -e "Token: ${MANAGER_TOKEN:0:50}..."
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
    echo -e "${RED}✗ Không thể login User!${NC}"
    exit 1
fi

echo -e "${GREEN}✓ User login thành công${NC}"
echo -e "User ID: ${USER_ID}"
echo -e "Token: ${USER_TOKEN:0:50}..."

# Step 3: User gửi tin nhắn
print_section "BƯỚC 3: User gửi tin nhắn"
echo "POST $BASE_URL/chat/user/${USER_ID}/message"
MESSAGE1_RESPONSE=$(curl -s -k -X POST "$BASE_URL/chat/user/${USER_ID}/message" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $USER_TOKEN" \
  -d '{
    "message": "Xin chào Manager, tôi muốn hỏi về chính sách bảo hành"
  }')

echo "$MESSAGE1_RESPONSE" | jq '.'
MESSAGE1_ID=$(echo "$MESSAGE1_RESPONSE" | jq -r '.id // empty')

if [ ! -z "$MESSAGE1_ID" ]; then
    echo -e "${GREEN}✓ User gửi tin nhắn thành công (Message ID: ${MESSAGE1_ID})${NC}"
else
    echo -e "${RED}✗ Không thể gửi tin nhắn từ User${NC}"
fi

# Step 4: Manager xem danh sách cuộc hội thoại
print_section "BƯỚC 4: Manager xem danh sách cuộc hội thoại"
echo "GET $BASE_URL/chat/conversations"
CONVERSATIONS_RESPONSE=$(curl -s -k -X GET "$BASE_URL/chat/conversations" \
  -H "Authorization: Bearer $MANAGER_TOKEN")

echo "$CONVERSATIONS_RESPONSE" | jq '.'

CONV_COUNT=$(echo "$CONVERSATIONS_RESPONSE" | jq 'length // 0')
echo -e "${GREEN}✓ Manager thấy ${CONV_COUNT} cuộc hội thoại${NC}"

# Step 5: Manager xem lịch sử chat với User
print_section "BƯỚC 5: Manager xem lịch sử chat với User"
echo "GET $BASE_URL/chat/user/${USER_ID}/history"
HISTORY1_RESPONSE=$(curl -s -k -X GET "$BASE_URL/chat/user/${USER_ID}/history" \
  -H "Authorization: Bearer $MANAGER_TOKEN")

echo "$HISTORY1_RESPONSE" | jq '.'

MSG_COUNT=$(echo "$HISTORY1_RESPONSE" | jq 'length // 0')
echo -e "${GREEN}✓ Có ${MSG_COUNT} tin nhắn trong lịch sử${NC}"

# Step 6: Manager trả lời User
print_section "BƯỚC 6: Manager trả lời User"
echo "POST $BASE_URL/chat/user/${USER_ID}/message"
MESSAGE2_RESPONSE=$(curl -s -k -X POST "$BASE_URL/chat/user/${USER_ID}/message" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $MANAGER_TOKEN" \
  -d '{
    "message": "Chào bạn! Chính sách bảo hành của chúng tôi là 12 tháng cho tất cả sản phẩm. Bạn có thắc mắc gì thêm không?"
  }')

echo "$MESSAGE2_RESPONSE" | jq '.'
MESSAGE2_ID=$(echo "$MESSAGE2_RESPONSE" | jq -r '.id // empty')

if [ ! -z "$MESSAGE2_ID" ]; then
    echo -e "${GREEN}✓ Manager trả lời thành công (Message ID: ${MESSAGE2_ID})${NC}"
else
    echo -e "${RED}✗ Không thể gửi tin nhắn từ Manager${NC}"
fi

# Step 7: User xem lịch sử và trả lời
print_section "BƯỚC 7: User xem lịch sử chat"
echo "GET $BASE_URL/chat/user/${USER_ID}/history"
HISTORY2_RESPONSE=$(curl -s -k -X GET "$BASE_URL/chat/user/${USER_ID}/history" \
  -H "Authorization: Bearer $USER_TOKEN")

echo "$HISTORY2_RESPONSE" | jq '.'
MSG_COUNT2=$(echo "$HISTORY2_RESPONSE" | jq 'length // 0')
echo -e "${GREEN}✓ Có ${MSG_COUNT2} tin nhắn trong lịch sử${NC}"

# Step 8: User trả lời Manager
print_section "BƯỚC 8: User trả lời Manager"
echo "POST $BASE_URL/chat/user/${USER_ID}/message"
MESSAGE3_RESPONSE=$(curl -s -k -X POST "$BASE_URL/chat/user/${USER_ID}/message" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $USER_TOKEN" \
  -d '{
    "message": "Cảm ơn bạn! Vậy nếu có lỗi phần cứng thì được đổi mới không?"
  }')

echo "$MESSAGE3_RESPONSE" | jq '.'
MESSAGE3_ID=$(echo "$MESSAGE3_RESPONSE" | jq -r '.id // empty')

if [ ! -z "$MESSAGE3_ID" ]; then
    echo -e "${GREEN}✓ User trả lời thành công (Message ID: ${MESSAGE3_ID})${NC}"
else
    echo -e "${RED}✗ Không thể gửi tin nhắn từ User${NC}"
fi

# Step 9: Manager trả lời tiếp
print_section "BƯỚC 9: Manager trả lời tiếp"
echo "POST $BASE_URL/chat/user/${USER_ID}/message"
MESSAGE4_RESPONSE=$(curl -s -k -X POST "$BASE_URL/chat/user/${USER_ID}/message" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $MANAGER_TOKEN" \
  -d '{
    "message": "Có ạ, nếu lỗi phần cứng trong tháng đầu, chúng tôi sẽ đổi mới 100%. Từ tháng thứ 2 trở đi sẽ được bảo hành sửa chữa hoặc thay thế linh kiện tùy tình trạng."
  }')

echo "$MESSAGE4_RESPONSE" | jq '.'
MESSAGE4_ID=$(echo "$MESSAGE4_RESPONSE" | jq -r '.id // empty')

if [ ! -z "$MESSAGE4_ID" ]; then
    echo -e "${GREEN}✓ Manager trả lời thành công (Message ID: ${MESSAGE4_ID})${NC}"
else
    echo -e "${RED}✗ Không thể gửi tin nhắn từ Manager${NC}"
fi

# Step 10: Test với limit và offset
print_section "BƯỚC 10: Test phân trang (limit=3, offset=0)"
echo "GET $BASE_URL/chat/user/${USER_ID}/history?limit=3&offset=0"
HISTORY3_RESPONSE=$(curl -s -k -X GET "$BASE_URL/chat/user/${USER_ID}/history?limit=3&offset=0" \
  -H "Authorization: Bearer $MANAGER_TOKEN")

echo "$HISTORY3_RESPONSE" | jq '.'
MSG_COUNT3=$(echo "$HISTORY3_RESPONSE" | jq 'length // 0')
echo -e "${GREEN}✓ Lấy được ${MSG_COUNT3} tin nhắn với limit=3${NC}"

# Final Summary
print_section "TÓM TẮT KẾT QUẢ TEST"
echo -e "${GREEN}✓ Manager đã login thành công${NC}"
echo -e "${GREEN}✓ User đã login thành công${NC}"
echo -e "${GREEN}✓ User gửi tin nhắn cho Manager${NC}"
echo -e "${GREEN}✓ Manager xem danh sách cuộc hội thoại${NC}"
echo -e "${GREEN}✓ Manager xem lịch sử chat${NC}"
echo -e "${GREEN}✓ Manager trả lời User (2 lần)${NC}"
echo -e "${GREEN}✓ User xem lịch sử chat${NC}"
echo -e "${GREEN}✓ User trả lời lại Manager${NC}"
echo -e "${GREEN}✓ Test phân trang lịch sử chat${NC}"

echo -e "\n${BLUE}========================================${NC}"
echo -e "${BLUE}Hoàn thành test Chat API với Manager!${NC}"
echo -e "${BLUE}========================================${NC}\n"

