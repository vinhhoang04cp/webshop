#!/bin/bash
# Generate self-signed SSL certificate for local development

CERT_DIR="docker/nginx/ssl"
CERT_FILE="$CERT_DIR/cert.pem"
KEY_FILE="$CERT_DIR/key.pem"

# Create directory if not exists
mkdir -p "$CERT_DIR"

# Check if certificate already exists
if [ -f "$CERT_FILE" ] && [ -f "$KEY_FILE" ]; then
    echo "✓ SSL certificates already exist in $CERT_DIR"
    exit 0
fi

echo "Generating self-signed SSL certificate..."

# Generate self-signed certificate
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout "$KEY_FILE" \
    -out "$CERT_FILE" \
    -subj "/C=VN/ST=HoChiMinh/L=HoChiMinh/O=Webshop/OU=Development/CN=localhost" \
    -addext "subjectAltName=DNS:localhost,DNS:*.localhost,IP:127.0.0.1"

# Set permissions
chmod 644 "$CERT_FILE"
chmod 600 "$KEY_FILE"

echo "✓ SSL certificate generated successfully!"
echo "  Certificate: $CERT_FILE"
echo "  Private Key: $KEY_FILE"
echo ""
echo "⚠️  This is a self-signed certificate for development only."
echo "    Your browser will show a security warning - this is normal."
echo ""
echo "To trust this certificate in your browser:"
echo "  Chrome/Edge: Visit https://localhost and click 'Advanced' > 'Proceed to localhost (unsafe)'"
echo "  Firefox: Visit https://localhost and click 'Advanced' > 'Accept the Risk and Continue'"