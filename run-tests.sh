#!/bin/bash

# Script để chạy tests với Laravel Sail
# Sử dụng: ./run-tests.sh

echo "🧪 Chạy Security Middleware Tests với Laravel Sail..."
echo ""

# Kiểm tra Sail có đang chạy không
if ! ./vendor/bin/sail ps | grep -q "Up"; then
    echo "⚠️  Laravel Sail chưa chạy. Đang khởi động..."
    ./vendor/bin/sail up -d
    echo "⏳ Đợi services khởi động..."
    sleep 5
fi

echo "✅ Sail đang chạy"
echo ""

# Chạy migrations trước
echo "📊 Chạy migrations..."
./vendor/bin/sail artisan migrate:fresh --seed --force
echo ""

# Chạy tests
echo "🧪 Chạy Security Middleware Tests..."
echo ""
./vendor/bin/sail test --filter=SecurityMiddlewareTest

echo ""
echo "✅ Tests hoàn tất!"
