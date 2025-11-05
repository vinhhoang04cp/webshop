#!/bin/bash

# Script để clear cache và config trong Docker container
# Sử dụng: ./clear-cache.sh

echo "🧹 Clearing Laravel cache and config in Docker container..."

# Tên container (thay đổi nếu cần)
CONTAINER_NAME="laravel_app"

# Kiểm tra container có đang chạy không
if ! docker ps | grep -q $CONTAINER_NAME; then
    echo "❌ Error: Container '$CONTAINER_NAME' is not running!"
    echo "Please start the container first with: docker compose up -d"
    exit 1
fi

echo "📦 Container found: $CONTAINER_NAME"

# Clear cache, config, route, view
echo "🔄 Clearing application cache..."
docker exec $CONTAINER_NAME php artisan cache:clear

echo "🔄 Clearing config cache..."
docker exec $CONTAINER_NAME php artisan config:clear

echo "🔄 Clearing route cache..."
docker exec $CONTAINER_NAME php artisan route:clear

echo "🔄 Clearing view cache..."
docker exec $CONTAINER_NAME php artisan view:clear

echo "🔄 Clearing compiled classes..."
docker exec $CONTAINER_NAME php artisan clear-compiled

# Optimize lại để performance tốt hơn (optional)
echo "⚡ Optimizing application..."
docker exec $CONTAINER_NAME php artisan config:cache
docker exec $CONTAINER_NAME php artisan route:cache

echo "✅ Cache cleared successfully!"
echo ""
echo "💡 Tip: If you're still having issues, try restarting the container:"
echo "   docker compose restart app"
