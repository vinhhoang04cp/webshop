#!/bin/bash

# Script tự động cập nhật .env với ngrok URL
# Sử dụng: ./update-ngrok-url.sh

echo "🔍 Đang lấy ngrok URL..."

# Lấy ngrok URL từ API
NGROK_URL=$(curl -s http://localhost:4040/api/tunnels | grep -o '"public_url":"https://[^"]*' | grep -o 'https://[^"]*' | head -1)

if [ -z "$NGROK_URL" ]; then
    echo "❌ Không thể lấy ngrok URL. Đảm bảo ngrok đang chạy!"
    echo "   Truy cập http://localhost:4040 để kiểm tra."
    exit 1
fi

echo "✅ Ngrok URL: $NGROK_URL"

# Backup file .env
cp .env .env.backup
echo "📋 Đã backup .env thành .env.backup"

# Cập nhật APP_URL
sed -i "s|APP_URL=.*|APP_URL=$NGROK_URL|g" .env

# Cập nhật VNPAY_RETURN_URL
sed -i "s|VNPAY_RETURN_URL=.*|VNPAY_RETURN_URL=$NGROK_URL/payment/vnpay-return|g" .env

# Cập nhật VNPAY_IPN_URL
sed -i "s|VNPAY_IPN_URL=.*|VNPAY_IPN_URL=$NGROK_URL/payment/vnpay-ipn|g" .env

echo "✅ Đã cập nhật file .env với:"
echo "   APP_URL=$NGROK_URL"
echo "   VNPAY_RETURN_URL=$NGROK_URL/payment/vnpay-return"
echo "   VNPAY_IPN_URL=$NGROK_URL/payment/vnpay-ipn"

# Clear Laravel cache
echo ""
echo "🧹 Đang clear cache..."
./vendor/bin/sail artisan config:clear
./vendor/bin/sail artisan cache:clear

echo ""
echo "✨ Hoàn tất! Bạn có thể test VNPay payment ngay bây giờ."
echo "🌐 Truy cập ứng dụng tại: $NGROK_URL"
