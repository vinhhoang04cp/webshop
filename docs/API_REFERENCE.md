# 📡 API Reference

> **Mục đích**: Quick reference cho tất cả API endpoints

## 📋 Mục lục
1. [Base Information](#base-information)
2. [Authentication](#authentication)
3. [Products](#products)
4. [Categories](#categories)
5. [Cart](#cart)
6. [Orders](#orders)
7. [Users](#users)
8. [Inventory](#inventory)
9. [Status Codes](#status-codes)

---

## 🌐 Base Information

```
Base URL: http://localhost/api
Content-Type: application/json
Accept: application/json
```

### Authentication Header
```
Authorization: Bearer {token}
```

### Pagination
```json
{
  "data": [...],
  "links": {
    "first": "http://localhost/api/products?page=1",
    "last": "http://localhost/api/products?page=5",
    "prev": null,
    "next": "http://localhost/api/products?page=2"
  },
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 75
  }
}
```

---

## 🔐 Authentication

### Register
```http
POST /api/register
```

**Body**:
```json
{
  "name": "Nguyen Van A",
  "email": "user@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "0123456789",
  "address": "Ha Noi"
}
```

**Response (201)**:
```json
{
  "status": true,
  "message": "Registration successful",
  "user": {
    "id": 1,
    "name": "Nguyen Van A",
    "email": "user@example.com"
  },
  "token": "1|abcdefg..."
}
```

---

### Login
```http
POST /api/login
```

**Body**:
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response (200)**:
```json
{
  "status": true,
  "message": "Login successful",
  "user": {...},
  "token": "2|xyz..."
}
```

---

### Logout
```http
POST /api/logout
Authorization: Bearer {token}
```

**Response (200)**:
```json
{
  "status": true,
  "message": "Logout successful"
}
```

---

### Get Current User
```http
GET /api/user
Authorization: Bearer {token}
```

**Response (200)**:
```json
{
  "id": 1,
  "name": "Nguyen Van A",
  "email": "user@example.com",
  "phone": "0123456789",
  "address": "Ha Noi"
}
```

---

## 📦 Products

### List Products
```http
GET /api/products
```

**Query Parameters**:
- `category_id` (optional): Filter by category
- `min_price` (optional): Minimum price
- `max_price` (optional): Maximum price
- `search` (optional): Search by name
- `page` (optional): Page number

**Response (200)**:
```json
{
  "data": [
    {
      "id": 1,
      "name": "iPhone 15 Pro",
      "description": "Latest iPhone model",
      "price": 29990000,
      "category_id": 1,
      "category_name": "Smartphones",
      "image_url": "/images/iphone15.jpg",
      "created_at": "2025-01-01T00:00:00Z"
    }
  ],
  "links": {...},
  "meta": {...}
}
```

---

### Get Product Detail
```http
GET /api/products/{id}
```

**Response (200)**:
```json
{
  "id": 1,
  "name": "iPhone 15 Pro",
  "description": "Latest iPhone model",
  "price": 29990000,
  "category": {
    "id": 1,
    "name": "Smartphones"
  },
  "details": {
    "color": "Titan Blue",
    "ram": "8GB",
    "storage": "256GB",
    "warranty_months": 12
  },
  "inventory": {
    "quantity_available": 50,
    "quantity_reserved": 5
  },
  "image_url": "/images/iphone15.jpg"
}
```

---

### Create Product
```http
POST /api/products
Authorization: Bearer {admin-token}
```

**Body**:
```json
{
  "name": "Samsung Galaxy S24",
  "description": "Latest Samsung flagship",
  "price": 24990000,
  "category_id": 1,
  "image_url": "/images/galaxy-s24.jpg",
  "details": {
    "color": "Phantom Black",
    "ram": "12GB",
    "storage": "512GB"
  },
  "initial_quantity": 100
}
```

**Response (201)**:
```json
{
  "status": true,
  "message": "Product created successfully",
  "data": {...}
}
```

---

### Update Product
```http
PUT /api/products/{id}
Authorization: Bearer {admin/manager-token}
```

**Body** (partial update supported):
```json
{
  "name": "Updated Product Name",
  "price": 19990000
}
```

**Response (200)**:
```json
{
  "status": true,
  "message": "Product updated successfully",
  "data": {...}
}
```

---

### Delete Product
```http
DELETE /api/products/{id}
Authorization: Bearer {admin-token}
```

**Response (200)**:
```json
{
  "status": true,
  "message": "Product deleted successfully"
}
```

---

## 📁 Categories

### List Categories
```http
GET /api/categories
```

**Response (200)**:
```json
{
  "data": [
    {
      "id": 1,
      "name": "Smartphones",
      "description": "Mobile phones and accessories",
      "product_count": 45
    }
  ]
}
```

---

### Get Category Detail
```http
GET /api/categories/{id}
```

**Response (200)**:
```json
{
  "id": 1,
  "name": "Smartphones",
  "description": "Mobile phones and accessories",
  "products": [...]
}
```

---

### Create Category
```http
POST /api/categories
Authorization: Bearer {admin-token}
```

**Body**:
```json
{
  "name": "Laptops",
  "description": "Portable computers"
}
```

**Response (201)**:
```json
{
  "status": true,
  "message": "Category created successfully",
  "data": {...}
}
```

---

### Update Category
```http
PUT /api/categories/{id}
Authorization: Bearer {admin-token}
```

**Body**:
```json
{
  "name": "Gaming Laptops",
  "description": "High-performance gaming laptops"
}
```

---

### Delete Category
```http
DELETE /api/categories/{id}
Authorization: Bearer {admin-token}
```

**Response (200)**:
```json
{
  "status": true,
  "message": "Category deleted successfully"
}
```

---

## 🛒 Cart

### Get Cart
```http
GET /api/cart
Authorization: Bearer {token}
```

**Response (200)**:
```json
{
  "id": 1,
  "user_id": 1,
  "items": [
    {
      "id": 1,
      "product_id": 5,
      "product_name": "iPhone 15 Pro",
      "price": 29990000,
      "quantity": 2,
      "subtotal": 59980000
    }
  ],
  "total": 59980000
}
```

---

### Add to Cart
```http
POST /api/cart/items
Authorization: Bearer {token}
```

**Body**:
```json
{
  "product_id": 5,
  "quantity": 2
}
```

**Response (201)**:
```json
{
  "status": true,
  "message": "Product added to cart",
  "cart": {...}
}
```

---

### Update Cart Item
```http
PUT /api/cart/items/{item_id}
Authorization: Bearer {token}
```

**Body**:
```json
{
  "quantity": 3
}
```

**Response (200)**:
```json
{
  "status": true,
  "message": "Cart updated",
  "cart": {...}
}
```

---

### Remove from Cart
```http
DELETE /api/cart/items/{item_id}
Authorization: Bearer {token}
```

**Response (200)**:
```json
{
  "status": true,
  "message": "Item removed from cart"
}
```

---

### Clear Cart
```http
DELETE /api/cart
Authorization: Bearer {token}
```

**Response (200)**:
```json
{
  "status": true,
  "message": "Cart cleared"
}
```

---

## 📋 Orders

### List Orders
```http
GET /api/orders
Authorization: Bearer {token}
```

**Query Parameters**:
- `status` (optional): pending, processing, shipped, delivered, cancelled
- `from_date` (optional): YYYY-MM-DD
- `to_date` (optional): YYYY-MM-DD

**Response (200)**:
```json
{
  "data": [
    {
      "id": 1,
      "order_number": "ORD-20250119-001",
      "user_id": 1,
      "user_name": "Nguyen Van A",
      "total_amount": 59980000,
      "status": "processing",
      "created_at": "2025-01-19T10:30:00Z"
    }
  ]
}
```

**Note**: 
- Admin/Manager: View all orders
- Customer: View own orders only

---

### Get Order Detail
```http
GET /api/orders/{id}
Authorization: Bearer {token}
```

**Response (200)**:
```json
{
  "id": 1,
  "order_number": "ORD-20250119-001",
  "user": {
    "id": 1,
    "name": "Nguyen Van A",
    "email": "user@example.com"
  },
  "items": [
    {
      "id": 1,
      "product_id": 5,
      "product_name": "iPhone 15 Pro",
      "quantity": 2,
      "price": 29990000,
      "subtotal": 59980000
    }
  ],
  "total_amount": 59980000,
  "status": "processing",
  "shipping_address": "123 Nguyen Trai, Ha Noi",
  "payment_method": "COD",
  "created_at": "2025-01-19T10:30:00Z"
}
```

---

### Create Order (Checkout)
```http
POST /api/orders
Authorization: Bearer {token}
```

**Body**:
```json
{
  "shipping_address": "123 Nguyen Trai, Ha Noi",
  "payment_method": "COD",
  "note": "Deliver in the morning"
}
```

**Response (201)**:
```json
{
  "status": true,
  "message": "Order placed successfully",
  "order": {
    "id": 1,
    "order_number": "ORD-20250119-001",
    "total_amount": 59980000,
    "status": "pending"
  }
}
```

**⚠️ Important**: Stock is deducted IMMEDIATELY when order is created!

---

### Update Order Status
```http
PUT /api/orders/{id}/status
Authorization: Bearer {admin/manager-token}
```

**Body**:
```json
{
  "status": "shipped"
}
```

**Allowed transitions**:
- `pending` → `processing`
- `processing` → `shipped`
- `shipped` → `delivered`
- Any → `cancelled` (but stock NOT restored!)

**Response (200)**:
```json
{
  "status": true,
  "message": "Order status updated",
  "order": {...}
}
```

---

### Cancel Order
```http
POST /api/orders/{id}/cancel
Authorization: Bearer {token}
```

**Response (200)**:
```json
{
  "status": true,
  "message": "Order cancelled"
}
```

**⚠️ Note**: Cancelling does NOT restore stock! Admin must manually adjust inventory.

---

## 👥 Users

### List Users
```http
GET /api/users
Authorization: Bearer {admin-token}
```

**Response (200)**:
```json
{
  "data": [
    {
      "id": 1,
      "name": "Nguyen Van A",
      "email": "user@example.com",
      "phone": "0123456789",
      "roles": ["customer"]
    }
  ]
}
```

---

### Get User Detail
```http
GET /api/users/{id}
Authorization: Bearer {admin-token}
```

**Response (200)**:
```json
{
  "id": 1,
  "name": "Nguyen Van A",
  "email": "user@example.com",
  "phone": "0123456789",
  "address": "Ha Noi",
  "roles": ["customer"],
  "created_at": "2025-01-01T00:00:00Z"
}
```

---

### Update User
```http
PUT /api/users/{id}
Authorization: Bearer {admin-token}
```

**Body**:
```json
{
  "name": "Updated Name",
  "phone": "0987654321"
}
```

---

### Delete User
```http
DELETE /api/users/{id}
Authorization: Bearer {admin-token}
```

---

### Assign Role
```http
POST /api/users/{id}/roles
Authorization: Bearer {admin-token}
```

**Body**:
```json
{
  "role_name": "manager"
}
```

**Response (200)**:
```json
{
  "status": true,
  "message": "Role assigned successfully"
}
```

---

## 📊 Inventory

### Get Inventory Status
```http
GET /api/inventory
Authorization: Bearer {admin/manager-token}
```

**Query Parameters**:
- `low_stock` (optional): true/false (default threshold: 10)
- `product_id` (optional): Filter by product

**Response (200)**:
```json
{
  "data": [
    {
      "product_id": 5,
      "product_name": "iPhone 15 Pro",
      "quantity_available": 50,
      "quantity_reserved": 5,
      "total_quantity": 55,
      "is_low_stock": false
    }
  ]
}
```

---

### Adjust Inventory
```http
POST /api/inventory/adjust
Authorization: Bearer {admin/manager-token}
```

**Body**:
```json
{
  "product_id": 5,
  "quantity": 100,
  "type": "restock",
  "note": "New shipment arrived"
}
```

**Types**:
- `restock`: Add stock
- `damage`: Remove damaged items
- `adjustment`: Manual correction

**Response (200)**:
```json
{
  "status": true,
  "message": "Inventory adjusted successfully",
  "inventory": {
    "product_id": 5,
    "quantity_available": 150
  }
}
```

---

## 📊 Status Codes

| Code | Meaning | Description |
|------|---------|-------------|
| **2xx Success** |
| 200 | OK | Request successful |
| 201 | Created | Resource created successfully |
| 204 | No Content | Success with no response body |
| **4xx Client Errors** |
| 400 | Bad Request | Invalid request format |
| 401 | Unauthorized | Missing or invalid token |
| 403 | Forbidden | Insufficient permissions |
| 404 | Not Found | Resource not found |
| 422 | Unprocessable Entity | Validation errors |
| 429 | Too Many Requests | Rate limit exceeded |
| **5xx Server Errors** |
| 500 | Internal Server Error | Server-side error |
| 503 | Service Unavailable | Server temporarily down |

---

## 🔴 Error Response Format

All errors follow this structure:

```json
{
  "status": false,
  "message": "Error message here",
  "errors": {
    "field_name": [
      "Validation error message"
    ]
  }
}
```

### Examples

**Validation Error (422)**:
```json
{
  "status": false,
  "message": "The given data was invalid.",
  "errors": {
    "email": [
      "The email has already been taken."
    ],
    "password": [
      "The password must be at least 8 characters."
    ]
  }
}
```

**Unauthorized (401)**:
```json
{
  "message": "Unauthenticated."
}
```

**Forbidden (403)**:
```json
{
  "status": false,
  "message": "Access denied. Admin role required."
}
```

**Not Found (404)**:
```json
{
  "status": false,
  "message": "Product not found."
}
```

**Rate Limit (429)**:
```json
{
  "message": "Too Many Attempts."
}
```

---

## 🧪 Testing with cURL

### Complete Workflow Example

```bash
# 1. Register
curl -X POST http://localhost/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'

# 2. Login and save token
TOKEN=$(curl -X POST http://localhost/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }' | jq -r '.token')

# 3. Browse products
curl -X GET "http://localhost/api/products?category_id=1" \
  -H "Accept: application/json"

# 4. Add to cart
curl -X POST http://localhost/api/cart/items \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "product_id": 5,
    "quantity": 2
  }'

# 5. View cart
curl -X GET http://localhost/api/cart \
  -H "Authorization: Bearer $TOKEN"

# 6. Checkout
curl -X POST http://localhost/api/orders \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "shipping_address": "123 Test St",
    "payment_method": "COD"
  }'

# 7. View order
curl -X GET http://localhost/api/orders/1 \
  -H "Authorization: Bearer $TOKEN"

# 8. Logout
curl -X POST http://localhost/api/logout \
  -H "Authorization: Bearer $TOKEN"
```

---

## 📚 Tài liệu liên quan

- **[AUTHENTICATION.md](./AUTHENTICATION.md)** - Authentication & authorization details
- **[BUSINESS_LOGIC.md](./BUSINESS_LOGIC.md)** - Business rules & workflows
- **[ARCHITECTURE.md](./ARCHITECTURE.md)** - System architecture

### Postman Collection
Xem file: `docs/postman/webshop-api.json` (nếu có)

---

**Cập nhật lần cuối**: 19/10/2025  
**Version**: 2.0  
**Author**: Hoàng Quang Vinh
