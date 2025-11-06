#!/bin/bash

# Script tự động cài đặt SSL cho WebShop bằng Nginx Proxy Manager
# 
# Usage: ./setup-ssl.sh

set -e  # Exit on error

echo "🔒 WebShop SSL Setup Script"
echo "=============================="
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Functions
success() {
    echo -e "${GREEN}✓ $1${NC}"
}

error() {
    echo -e "${RED}✗ $1${NC}"
}

warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

info() {
    echo -e "→ $1"
}

# Check if running as root
if [[ $EUID -eq 0 ]]; then
   error "Do not run this script as root!"
   exit 1
fi

# Step 1: Check prerequisites
echo "📋 Step 1: Checking prerequisites..."
echo ""

# Check Docker
if ! command -v docker &> /dev/null; then
    error "Docker is not installed. Please install Docker first."
    exit 1
else
    success "Docker is installed"
fi

# Check Docker Compose
if ! command -v docker-compose &> /dev/null; then
    error "Docker Compose is not installed. Please install Docker Compose first."
    exit 1
else
    success "Docker Compose is installed"
fi

# Check if ports 80 and 443 are available
if sudo netstat -tuln | grep -q ':80 '; then
    warning "Port 80 is already in use. You may need to stop the service using it."
    read -p "Do you want to continue? (y/n) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
else
    success "Port 80 is available"
fi

if sudo netstat -tuln | grep -q ':443 '; then
    warning "Port 443 is already in use. You may need to stop the service using it."
    read -p "Do you want to continue? (y/n) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
else
    success "Port 443 is available"
fi

# Step 2: Get domain information
echo ""
echo "📝 Step 2: Domain configuration"
echo ""

read -p "Enter your domain name (e.g., dienthoaicuavinh.name.vn): " DOMAIN_NAME
read -p "Enter your email for Let's Encrypt notifications: " LE_EMAIL

if [[ -z "$DOMAIN_NAME" ]] || [[ -z "$LE_EMAIL" ]]; then
    error "Domain name and email are required!"
    exit 1
fi

# Validate email format
if [[ ! "$LE_EMAIL" =~ ^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$ ]]; then
    error "Invalid email format!"
    exit 1
fi

info "Domain: $DOMAIN_NAME"
info "Email: $LE_EMAIL"

# Check DNS
echo ""
info "Checking DNS configuration..."
SERVER_IP=$(curl -s ifconfig.me)
DOMAIN_IP=$(dig +short "$DOMAIN_NAME" | head -n 1)

if [[ -z "$DOMAIN_IP" ]]; then
    error "Domain $DOMAIN_NAME does not resolve to any IP!"
    warning "Please configure your domain's A record to point to: $SERVER_IP"
    exit 1
elif [[ "$SERVER_IP" != "$DOMAIN_IP" ]]; then
    warning "Domain resolves to $DOMAIN_IP but server IP is $SERVER_IP"
    warning "SSL certificate request may fail!"
    read -p "Do you want to continue anyway? (y/n) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
else
    success "DNS is configured correctly ($DOMAIN_NAME → $SERVER_IP)"
fi

# Step 3: Stop existing containers
echo ""
echo "🛑 Step 3: Stopping existing containers..."
echo ""

if docker ps | grep -q "laravel_app"; then
    info "Stopping Laravel app container..."
    docker-compose down
    success "Laravel app stopped"
else
    info "Laravel app is not running"
fi

# Step 4: Start Nginx Proxy Manager
echo ""
echo "🚀 Step 4: Starting Nginx Proxy Manager..."
echo ""

info "Starting NPM container..."
docker-compose -f docker-compose.npm.yml up -d

# Wait for NPM to be ready
info "Waiting for NPM to be ready..."
sleep 10

if docker ps | grep -q "nginx_proxy_manager"; then
    success "Nginx Proxy Manager started successfully"
else
    error "Failed to start Nginx Proxy Manager"
    docker-compose -f docker-compose.npm.yml logs
    exit 1
fi

# Step 5: Start Laravel app (without exposed ports)
echo ""
echo "🚀 Step 5: Starting Laravel application..."
echo ""

info "Starting Laravel app..."
docker-compose up -d

sleep 5

if docker ps | grep -q "laravel_app"; then
    success "Laravel app started successfully"
else
    error "Failed to start Laravel app"
    docker-compose logs app
    exit 1
fi

# Step 6: Update .env
echo ""
echo "⚙️  Step 6: Updating .env configuration..."
echo ""

if [ -f .env ]; then
    # Backup .env
    cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
    success "Backed up .env"
    
    # Update APP_URL
    if grep -q "^APP_URL=" .env; then
        sed -i "s|^APP_URL=.*|APP_URL=https://$DOMAIN_NAME|" .env
        success "Updated APP_URL to https://$DOMAIN_NAME"
    else
        echo "APP_URL=https://$DOMAIN_NAME" >> .env
        success "Added APP_URL=https://$DOMAIN_NAME"
    fi
    
    # Update SESSION_SECURE_COOKIE
    if grep -q "^SESSION_SECURE_COOKIE=" .env; then
        sed -i "s|^SESSION_SECURE_COOKIE=.*|SESSION_SECURE_COOKIE=true|" .env
    else
        echo "SESSION_SECURE_COOKIE=true" >> .env
    fi
    success "Set SESSION_SECURE_COOKIE=true"
    
    # Update TRUSTED_PROXIES
    if grep -q "^TRUSTED_PROXIES=" .env; then
        sed -i "s|^TRUSTED_PROXIES=.*|TRUSTED_PROXIES=*|" .env
    else
        echo "TRUSTED_PROXIES=*" >> .env
    fi
    success "Set TRUSTED_PROXIES=*"
    
    # Clear Laravel cache
    info "Clearing Laravel cache..."
    docker-compose exec -T app php artisan config:cache
    docker-compose exec -T app php artisan route:cache
    docker-compose exec -T app php artisan view:cache
    success "Cache cleared"
    
else
    error ".env file not found!"
    exit 1
fi

# Step 7: Instructions for NPM configuration
echo ""
echo "✅ Setup completed!"
echo ""
echo "=============================="
echo "📋 Next Steps (Manual):"
echo "=============================="
echo ""
echo "1. Open Nginx Proxy Manager Admin UI:"
echo "   → http://$SERVER_IP:81"
echo ""
echo "2. Login with default credentials:"
echo "   Email: admin@example.com"
echo "   Password: changeme"
echo ""
echo "3. Change your password immediately!"
echo ""
echo "4. Add a new Proxy Host:"
echo "   ✓ Domain: $DOMAIN_NAME"
echo "   ✓ Scheme: http"
echo "   ✓ Forward Hostname: laravel_app"
echo "   ✓ Forward Port: 80"
echo "   ✓ Enable: Cache Assets"
echo "   ✓ Enable: Block Common Exploits"
echo "   ✓ Enable: Websockets Support"
echo ""
echo "5. In the SSL tab:"
echo "   ✓ Request a new SSL Certificate"
echo "   ✓ Email: $LE_EMAIL"
echo "   ✓ Enable: Force SSL"
echo "   ✓ Enable: HTTP/2 Support"
echo "   ✓ Enable: HSTS Enabled"
echo ""
echo "6. Click Save and wait 30-60 seconds"
echo ""
echo "7. Test your site:"
echo "   → https://$DOMAIN_NAME"
echo ""
echo "=============================="
echo "📚 Documentation:"
echo "=============================="
echo ""
echo "Full guide: docs/SSL_SETUP_NGINX_PROXY_MANAGER.md"
echo ""
echo "Useful commands:"
echo "  • View NPM logs: docker-compose -f docker-compose.npm.yml logs -f"
echo "  • View app logs: docker-compose logs -f app"
echo "  • Restart NPM: docker-compose -f docker-compose.npm.yml restart"
echo "  • Restart app: docker-compose restart app"
echo ""
echo "🎉 Happy deploying!"
