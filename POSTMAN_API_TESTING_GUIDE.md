# 📮 Postman API Testing Guide - Complete

## 🚀 Setup Postman

### 1. Tạo Environment
**Name**: `Webshop API`

**Variables**:
```
base_url = http://localhost:8000/api
token = (sẽ được set tự động sau khi login)
user_id = (sẽ được set tự động)
product_id = 1
category_id = 1
order_id = 1
cart_id = 1
coupon_id = 1
inventory_id = 1
```

### 2. Pre-request Script (Global)
Thêm vào Collection settings:
```javascript
// Automatically add token to headers if exists
if (pm.environment.get("token")) {
    pm.request.headers.add({
        key: 'Authorization',
        value: 'Bearer ' + pm.environment.get("token")
    });
}
```

### 3. Test Script (Global)
Thêm vào Collection settings:
```javascript
// Automatically save token from login/register
if (pm.response.json().token) {
    pm.environment.set("token", pm.response.json().token);
    console.log("Token saved:", pm.response.json().token);
}

// Automatically save user_id
if (pm.response.json().data && pm.response.json().data.id) {
    pm.environment.set("user_id", pm.response.json().data.id);
}
```

---

## 📁 API Collection Structure

```
Webshop API
├── 1. Authentication
├── 2. Profile
├── 3. Password Reset
├── 4. Social Auth
├── 5. Categories
├── 6. Products
├── 7. Product Details
├── 8. Product Ratings
├── 9. Coupons
├── 10. Cart
├── 11. Orders
├── 12. Inventory
```

---

## 1. 🔐 Authentication APIs

### 1.1 Register
```
POST {{base_url}}/register
Content-Type: application/json
```

**Body (raw JSON)**:
```json
{
  "name": "Test User",
  "email": "test@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Response**:
```json
{
  "data": {
    "id": 1,
    "name": "Test User",
    "email": "test@example.com",
    "phone": null,
    "address": null,
    "avatar": null,
    "avatar_url": null,
    "email_verified_at": null,
    "roles": ["customer"],
    "is_admin": false,
    "created_at": "2025-10-26 15:00:00",
    "updated_at": "2025-10-26 15:00:00"
  },
  "status": true,
  "message": "Registration successful",
  "token": "1|abc123xyz..."
}
```

**Test Script**:
```javascript
pm.test("Status is 201", function() {
    pm.response.to.have.status(201);
});
pm.test("Token is present", function() {
    pm.expect(pm.response.json().token).to.exist;
});
```

---

### 1.2 Login
```
POST {{base_url}}/login
Content-Type: application/json
```

**Body (raw JSON)**:
```json
{
  "email": "test@example.com",
  "password": "password123"
}
```

**Response**: Same as Register

---

### 1.3 Logout
```
POST {{base_url}}/logout
Authorization: Bearer {{token}}
```

**No Body**

**Response**:
```json
{
  "status": true,
  "message": "Logout successful"
}
```

---

### 1.4 Get Profile
```
GET {{base_url}}/profile
Authorization: Bearer {{token}}
```

**Response**: Same as Login (without token)

---

### 1.5 Dashboard (Admin Only)
```
GET {{base_url}}/dashboard
Authorization: Bearer {{token}}
```

**Response**:
```json
{
  "status": true,
  "message": "Dashboard data retrieved successfully",
  "data": {
    "total_users": 150,
    "total_products": 200,
    "total_orders": 500,
    "total_revenue": 125000000,
    "recent_orders": [...]
  }
}
```

---

### 1.6 Check Auth
```
GET {{base_url}}/check-auth
Authorization: Bearer {{token}}
```

**Response**:
```json
{
  "data": {
    "id": 1,
    "name": "Test User",
    "email": "test@example.com",
    "roles": ["customer"],
    "is_admin": false
  },
  "status": true,
  "message": "Authenticated",
  "authenticated": true
}
```

---

## 2. 👤 Profile APIs

### 2.1 Get Profile
```
GET {{base_url}}/profile
Authorization: Bearer {{token}}
```

---

### 2.2 Update Profile
```
PUT {{base_url}}/profile
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body (raw JSON)**:
```json
{
  "name": "Updated Name",
  "phone": "0123456789",
  "address": "123 ABC Street, Hanoi"
}
```

**For file upload (form-data)**:
```
name: Updated Name
phone: 0123456789
address: 123 ABC Street, Hanoi
avatar: [file] (image file)
```

---

### 2.3 Change Password
```
PUT {{base_url}}/profile/password
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body**:
```json
{
  "current_password": "password123",
  "new_password": "newpassword123",
  "new_password_confirmation": "newpassword123"
}
```

---

### 2.4 Delete Avatar
```
DELETE {{base_url}}/profile/avatar
Authorization: Bearer {{token}}
```

---

## 3. 🔑 Password Reset APIs

### 3.1 Forgot Password
```
POST {{base_url}}/forgot-password
Content-Type: application/json
```

**Body**:
```json
{
  "email": "test@example.com"
}
```

**For SPA/Mobile**:
```json
{
  "email": "test@example.com",
  "reset_url": "https://yourfrontend.com/reset-password"
}
```

---

### 3.2 Reset Password
```
POST {{base_url}}/reset-password
Content-Type: application/json
```

**Body**:
```json
{
  "email": "test@example.com",
  "token": "token_from_email",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

---

### 3.3 Validate Token
```
POST {{base_url}}/validate-reset-token
Content-Type: application/json
```

**Body**:
```json
{
  "email": "test@example.com",
  "token": "token_from_email"
}
```

---

## 4. 🌐 Social Auth APIs

### 4.1 Redirect to Provider
```
GET {{base_url}}/auth/google/redirect
```

**Response**:
```json
{
  "status": true,
  "message": "Redirect URL generated successfully",
  "redirect_url": "https://accounts.google.com/o/oauth2/..."
}
```

---

### 4.2 Handle Callback
```
GET {{base_url}}/auth/google/callback?code=xxx&state=xxx
```

---

### 4.3 Login with Token (Mobile/SPA)
```
POST {{base_url}}/auth/social/token
Content-Type: application/json
```

**Body**:
```json
{
  "provider": "google",
  "access_token": "provider_access_token_here"
}
```

**Response**: Same as Login

---

## 5. 📂 Categories APIs

### 5.1 List Categories (Public)
```
GET {{base_url}}/categories
```

**Query Params**:
```
?search=smartphone
&per_page=20
&page=1
```

**Response**:
```json
{
  "data": [
    {
      "category_id": 1,
      "name": "Smartphone",
      "slug": "smartphone",
      "description": "Điện thoại thông minh",
      "image": "categories/smartphone.jpg",
      "products_count": 50,
      "created_at": "2025-10-26 15:00:00"
    }
  ],
  "status": true,
  "message": "Categories retrieved successfully",
  "meta": {...}
}
```

---

### 5.2 Get Category (Public)
```
GET {{base_url}}/categories/{{category_id}}
```

**Query Params**:
```
?with_products=1
```

---

### 5.3 Create Category (Admin)
```
POST {{base_url}}/categories
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body**:
```json
{
  "name": "Laptop",
  "slug": "laptop",
  "description": "Máy tính xách tay",
  "image": "https://example.com/laptop.jpg"
}
```

---

### 5.4 Update Category (Admin)
```
PUT {{base_url}}/categories/{{category_id}}
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body**: Same as Create

---

### 5.5 Delete Category (Admin)
```
DELETE {{base_url}}/categories/{{category_id}}
Authorization: Bearer {{token}}
```

---

## 6. 📱 Products APIs

### 6.1 List Products (Public)
```
GET {{base_url}}/products
```

**Query Params**:
```
?category=1
&search=iphone
&min_price=10000000
&max_price=50000000
&stock_status=in_stock
&has_discount=1
&sort_by=price
&sort_order=desc
&per_page=20
&page=1
```

**Response**:
```json
{
  "data": [
    {
      "product_id": 1,
      "name": "iPhone 15 Pro Max",
      "slug": "iphone-15-pro-max",
      "price": "29990000",
      "original_price": "34990000",
      "has_discount": true,
      "discount_percentage": 14.3,
      "stock_quantity": 50,
      "is_in_stock": true,
      "stock_status": "in_stock",
      "image": "/images/iphone-15.jpg",
      "category": {
        "category_id": 1,
        "name": "Smartphone",
        "slug": "smartphone"
      },
      "average_rating": 4.5,
      "total_ratings": 120
    }
  ],
  "status": true,
  "message": "Products retrieved successfully",
  "meta": {...}
}
```

---

### 6.2 Get Product (Public)
```
GET {{base_url}}/products/{{product_id}}
```

**Response**: Full product details with category, details, inventory, ratings

---

### 6.3 Product Statistics (Public)
```
GET {{base_url}}/products/stats
```

**Response**:
```json
{
  "status": true,
  "message": "Product statistics retrieved successfully",
  "data": {
    "total_products": 150,
    "in_stock": 120,
    "low_stock": 20,
    "out_of_stock": 10,
    "total_value": 4500000000,
    "with_discount": 35
  }
}
```

---

### 6.4 Create Product (Admin)
```
POST {{base_url}}/products
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body**:
```json
{
  "name": "iPhone 15 Pro Max",
  "description": "Flagship Apple phone with A17 Pro chip",
  "price": 29990000,
  "original_price": 34990000,
  "category_id": 1,
  "stock_quantity": 100,
  "image_url": "https://example.com/iphone-15.jpg"
}
```

---

### 6.5 Update Product (Admin)
```
PUT {{base_url}}/products/{{product_id}}
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body**: Same as Create

---

### 6.6 Delete Product (Admin)
```
DELETE {{base_url}}/products/{{product_id}}
Authorization: Bearer {{token}}
```

---

## 7. 📋 Product Details APIs

### 7.1 List Product Details
```
GET {{base_url}}/product-details
Authorization: Bearer {{token}}
```

**Query Params**:
```
?product_id=1
&color=Black
&storage=256GB
&ram=8GB
```

---

### 7.2 Get Product Detail
```
GET {{base_url}}/product-details/{{detail_id}}
Authorization: Bearer {{token}}
```

---

### 7.3 Create Product Detail (Admin)
```
POST {{base_url}}/product-details
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body**:
```json
{
  "product_id": 1,
  "color": "Natural Titanium",
  "storage": "256GB",
  "ram": "8GB",
  "screen_size": "6.7 inch",
  "chip": "Apple A17 Pro",
  "battery": "4422 mAh",
  "camera_main": "48MP Main + 12MP Ultra Wide",
  "camera_front": "12MP TrueDepth",
  "os": "iOS 17",
  "special_features": "Dynamic Island, Always-On Display, Action Button"
}
```

---

### 7.4 Update Product Detail (Admin)
```
PUT {{base_url}}/product-details/{{detail_id}}
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body**: Same as Create

---

### 7.5 Delete Product Detail (Admin)
```
DELETE {{base_url}}/product-details/{{detail_id}}
Authorization: Bearer {{token}}
```

---

## 8. ⭐ Product Ratings APIs

### 8.1 Get Product Ratings (Public)
```
GET {{base_url}}/products/{{product_id}}/ratings
```

**Response**:
```json
{
  "data": [
    {
      "id": 1,
      "product_id": 1,
      "rating": 5,
      "review": "Sản phẩm tuyệt vời!",
      "user": {
        "id": 1,
        "name": "Test User",
        "email": "test@example.com"
      },
      "created_at": "2025-10-26 15:00:00"
    }
  ],
  "status": true,
  "message": "Ratings retrieved successfully",
  "average_rating": 4.5,
  "total_ratings": 120
}
```

---

### 8.2 Add Rating (Auth)
```
POST {{base_url}}/products/{{product_id}}/ratings
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body**:
```json
{
  "rating": 5,
  "review": "Sản phẩm rất tốt, giao hàng nhanh!"
}
```

---

### 8.3 Update Rating (Own)
```
PUT {{base_url}}/products/{{product_id}}/ratings/{{rating_id}}
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body**:
```json
{
  "rating": 4,
  "review": "Updated review: Good but pricey"
}
```

---

### 8.4 Delete Rating (Own/Admin)
```
DELETE {{base_url}}/products/{{product_id}}/ratings/{{rating_id}}
Authorization: Bearer {{token}}
```

---

## 9. 🎫 Coupons APIs

### 9.1 List Coupons (Public)
```
GET {{base_url}}/coupons
```

**Query Params**:
```
?code=WELCOME10
&is_active=1
&discount_type=percentage
&product_id=1
&per_page=20
```

**Response**:
```json
{
  "data": [
    {
      "coupon_id": 1,
      "code": "WELCOME10",
      "name": "Welcome Discount",
      "discount_type": "percentage",
      "discount_value": 10,
      "discount_display": "10%",
      "min_order_amount": 1000000,
      "max_discount_amount": 500000,
      "usage_limit": 100,
      "used_count": 25,
      "remaining_usage": 75,
      "product_id": null,
      "scope_display": "Tất cả sản phẩm",
      "start_date": "2025-01-01 00:00:00",
      "end_date": "2025-12-31 23:59:59",
      "is_active": true,
      "status_display": "Đang hoạt động",
      "is_valid": true,
      "validation_message": "Mã giảm giá hợp lệ"
    }
  ],
  "status": true,
  "message": "Coupons retrieved successfully"
}
```

---

### 9.2 Get Coupon (Public)
```
GET {{base_url}}/coupons/{{coupon_id}}
```

---

### 9.3 Validate Coupon (Public)
```
POST {{base_url}}/coupons/validate
Content-Type: application/json
```

**Body**:
```json
{
  "code": "WELCOME10",
  "order_amount": 5000000
}
```

**Response**:
```json
{
  "data": {
    "coupon_id": 1,
    "code": "WELCOME10",
    "discount_value": 10,
    "discount_type": "percentage",
    "is_valid": true
  },
  "status": true,
  "message": "Coupon is valid",
  "discount_amount": 500000,
  "final_amount": 4500000
}
```

---

### 9.4 Create Coupon (Admin)
```
POST {{base_url}}/coupons
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body**:
```json
{
  "code": "SUMMER2025",
  "name": "Summer Sale 2025",
  "discount_type": "percentage",
  "discount_value": 15,
  "min_order_amount": 2000000,
  "max_discount_amount": 1000000,
  "usage_limit": 500,
  "product_id": null,
  "start_date": "2025-06-01 00:00:00",
  "end_date": "2025-08-31 23:59:59",
  "is_active": true
}
```

---

### 9.5 Update Coupon (Admin)
```
PUT {{base_url}}/coupons/{{coupon_id}}
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body**: Same as Create

---

### 9.6 Delete Coupon (Admin)
```
DELETE {{base_url}}/coupons/{{coupon_id}}
Authorization: Bearer {{token}}
```

---

### 9.7 Toggle Coupon Status (Admin)
```
POST {{base_url}}/coupons/{{coupon_id}}/toggle-status
Authorization: Bearer {{token}}
```

---

## 10. 🛒 Cart APIs

### 10.1 Get Current Cart (User)
```
GET {{base_url}}/cart
Authorization: Bearer {{token}}
```

**Response**:
```json
{
  "data": {
    "cart_id": 1,
    "user_id": 1,
    "items": [
      {
        "cart_item_id": 1,
        "product_id": 1,
        "quantity": 2,
        "price": "29990000",
        "subtotal": 59980000,
        "product": {
          "product_id": 1,
          "name": "iPhone 15 Pro Max",
          "slug": "iphone-15-pro-max",
          "price": "29990000",
          "image": "/images/iphone-15.jpg",
          "stock_quantity": 50,
          "category": {
            "category_id": 1,
            "name": "Smartphone"
          }
        }
      }
    ],
    "items_count": 1,
    "total_quantity": 2,
    "total_amount": 59980000
  },
  "status": true,
  "message": "Cart retrieved successfully"
}
```

---

### 10.2 Add Product to Cart (User)
```
POST {{base_url}}/cart/add/{{product_id}}
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body**:
```json
{
  "quantity": 2
}
```

---

### 10.3 Update Cart Item (User)
```
PUT {{base_url}}/cart/items/{{cart_item_id}}
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body**:
```json
{
  "quantity": 3
}
```

---

### 10.4 Remove Cart Item (User)
```
DELETE {{base_url}}/cart/items/{{cart_item_id}}
Authorization: Bearer {{token}}
```

---

### 10.5 Clear Cart (User)
```
DELETE {{base_url}}/cart/clear
Authorization: Bearer {{token}}
```

---

### 10.6 Validate Coupon (User)
```
POST {{base_url}}/cart/validate-coupon
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body**:
```json
{
  "coupon_code": "WELCOME10"
}
```

---

### 10.7 Checkout (User)
```
POST {{base_url}}/cart/checkout
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body**:
```json
{
  "shipping_name": "Nguyễn Văn A",
  "shipping_phone": "0123456789",
  "shipping_address": "123 ABC Street, Hanoi, Vietnam",
  "payment_method": "vnpay",
  "coupon_code": "WELCOME10",
  "note": "Gọi trước khi giao hàng"
}
```

**Response**:
```json
{
  "status": true,
  "message": "Order created successfully",
  "order": {
    "order_id": 123,
    "total_amount": 59980000,
    "status": "pending"
  },
  "payment": {
    "method": "vnpay",
    "redirect_url": "https://sandbox.vnpayment.vn/..."
  }
}
```

---

### 10.8 List All Carts (Admin)
```
GET {{base_url}}/carts
Authorization: Bearer {{token}}
```

**Query Params**:
```
?user_id=1
&product_id=1
&per_page=20
```

---

### 10.9 Get Cart by ID (Admin)
```
GET {{base_url}}/carts/{{cart_id}}
Authorization: Bearer {{token}}
```

---

### 10.10 Create Cart (Admin)
```
POST {{base_url}}/carts
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body**:
```json
{
  "user_id": 1
}
```

---

### 10.11 Update Cart (Admin)
```
PUT {{base_url}}/carts/{{cart_id}}
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body**:
```json
{
  "user_id": 2
}
```

---

### 10.12 Delete Cart (Admin)
```
DELETE {{base_url}}/carts/{{cart_id}}
Authorization: Bearer {{token}}
```

---

## 11. 📦 Orders APIs

### 11.1 List Orders (User/Admin)
```
GET {{base_url}}/orders
Authorization: Bearer {{token}}
```

**Query Params (User)**:
```
?status=processing
&search=laptop
&sort_by=order_date
&sort_order=desc
&per_page=20
```

**Query Params (Admin)**:
```
?user_id=1
&status=pending
&min_date=2025-01-01
&max_date=2025-12-31
&min_total=1000000
&max_total=50000000
&sort_by=total_amount
&per_page=20
```

**Response**:
```json
{
  "data": [
    {
      "order_id": 1,
      "user_id": 1,
      "order_date": "2025-10-26 15:00:00",
      "total_amount": 59980000,
      "status": "processing",
      "status_text": "Đang xử lý",
      "can_cancel": true,
      "available_transitions": ["shipped", "cancelled"],
      "shipping_name": "Nguyễn Văn A",
      "shipping_phone": "0123456789",
      "shipping_address": "123 ABC Street",
      "payment_status": "paid",
      "payment_method": "vnpay",
      "user": {
        "id": 1,
        "name": "Test User",
        "email": "test@example.com"
      },
      "items": [...],
      "items_count": 2,
      "total_quantity": 3
    }
  ],
  "status": true,
  "message": "Orders retrieved successfully",
  "meta": {...}
}
```

---

### 11.2 Get Order (User/Admin)
```
GET {{base_url}}/orders/{{order_id}}
Authorization: Bearer {{token}}
```

---

### 11.3 Get Order Statuses
```
GET {{base_url}}/orders/statuses
Authorization: Bearer {{token}}
```

**Response**:
```json
{
  "status": true,
  "message": "Statuses retrieved successfully",
  "data": {
    "pending": "Chờ xử lý",
    "processing": "Đang xử lý",
    "shipped": "Đã gửi hàng",
    "delivered": "Đã giao hàng",
    "cancelled": "Đã hủy"
  }
}
```

---

### 11.4 Get Order Statistics
```
GET {{base_url}}/orders/stats
Authorization: Bearer {{token}}
```

**Response (User)**:
```json
{
  "status": true,
  "message": "Order statistics retrieved successfully",
  "data": {
    "total_orders": 12,
    "pending": 2,
    "processing": 3,
    "shipped": 2,
    "delivered": 4,
    "cancelled": 1,
    "total_revenue": 25000000
  }
}
```

**Response (Admin)**:
```json
{
  "data": {
    "total_orders": 150,
    "pending": 20,
    "processing": 30,
    "shipped": 25,
    "delivered": 70,
    "cancelled": 5,
    "total_revenue": 450000000,
    "pending_value": 50000000,
    "processing_value": 75000000
  }
}
```

---

### 11.5 Create Order (API)
```
POST {{base_url}}/orders
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body**:
```json
{
  "user_id": 1,
  "shipping_name": "Nguyễn Văn A",
  "shipping_phone": "0123456789",
  "shipping_address": "123 ABC Street, Hanoi",
  "payment_method": "cod",
  "items": [
    {
      "product_id": 1,
      "quantity": 2
    },
    {
      "product_id": 2,
      "quantity": 1
    }
  ]
}
```

---

### 11.6 Update Order
```
PUT {{base_url}}/orders/{{order_id}}
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body (User - limited)**:
```json
{
  "shipping_name": "Updated Name",
  "shipping_phone": "0987654321",
  "shipping_address": "New Address"
}
```

**Body (Admin - full control)**:
```json
{
  "status": "shipped",
  "shipping_name": "Updated Name",
  "shipping_phone": "0987654321",
  "items": [...]
}
```

---

### 11.7 Delete Order
```
DELETE {{base_url}}/orders/{{order_id}}
Authorization: Bearer {{token}}
```

---

### 11.8 Change Order Status (Admin)
```
POST {{base_url}}/orders/{{order_id}}/change-status
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body**:
```json
{
  "status": "shipped"
}
```

**Response**:
```json
{
  "data": {
    "order_id": 1,
    "status": "shipped",
    "status_text": "Đã gửi hàng",
    "can_cancel": false,
    "available_transitions": ["delivered"]
  },
  "status": true,
  "message": "Order status updated successfully"
}
```

---

## 12. 📊 Inventory APIs (Admin Only)

### 12.1 List Inventories
```
GET {{base_url}}/inventories
Authorization: Bearer {{token}}
```

**Query Params**:
```
?stock_status=low_stock
&search=iphone
&sort_by=current_stock
&sort_order=asc
&per_page=20
```

**Response**:
```json
{
  "data": [
    {
      "inventory_id": 1,
      "product_id": 1,
      "stock_in": 100,
      "stock_out": 50,
      "current_stock": 50,
      "stock_status": "in_stock",
      "stock_status_text": "Còn hàng",
      "is_low_stock": false,
      "is_out_of_stock": false,
      "product": {
        "product_id": 1,
        "name": "iPhone 15 Pro Max",
        "price": "29990000",
        "category": {
          "category_id": 1,
          "name": "Smartphone"
        }
      },
      "stock_value": 1499500000
    }
  ],
  "status": true,
  "message": "Inventory retrieved successfully"
}
```

---

### 12.2 Get Inventory
```
GET {{base_url}}/inventories/{{inventory_id}}
Authorization: Bearer {{token}}
```

---

### 12.3 Create Inventory
```
POST {{base_url}}/inventories
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body**:
```json
{
  "product_id": 1,
  "stock_in": 100,
  "stock_out": 0,
  "current_stock": 100
}
```

---

### 12.4 Update Inventory
```
PUT {{base_url}}/inventories/{{inventory_id}}
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body**:
```json
{
  "stock_in": 150,
  "stock_out": 20,
  "current_stock": 130
}
```

---

### 12.5 Delete Inventory
```
DELETE {{base_url}}/inventories/{{inventory_id}}
Authorization: Bearer {{token}}
```

---

### 12.6 Update Stock
```
PUT {{base_url}}/inventories/{{inventory_id}}/update-stock
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body (Stock In)**:
```json
{
  "type": "in",
  "quantity": 50
}
```

**Body (Stock Out)**:
```json
{
  "type": "out",
  "quantity": 20
}
```

---

### 12.7 Upsert Inventory
```
POST {{base_url}}/inventories/upsert
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body**:
```json
{
  "product_id": 1,
  "stock_in": 100,
  "stock_out": 0,
  "current_stock": 100
}
```

---

### 12.8 Low Stock Items
```
GET {{base_url}}/inventories/low-stock/list
Authorization: Bearer {{token}}
```

**Query Params**:
```
?threshold=10
```

---

### 12.9 Out of Stock Items
```
GET {{base_url}}/inventories/out-of-stock/list
Authorization: Bearer {{token}}
```

---

### 12.10 Inventory Statistics
```
GET {{base_url}}/inventories/stats
Authorization: Bearer {{token}}
```

**Response**:
```json
{
  "status": true,
  "message": "Inventory statistics retrieved successfully",
  "data": {
    "total_products": 150,
    "in_stock": 120,
    "low_stock": 20,
    "out_of_stock": 10,
    "total_stock_value": 4500000000,
    "total_stock_in": 5000,
    "total_stock_out": 2000,
    "total_current_stock": 3000
  }
}
```

---

## 🧪 Testing Flow (Recommended Order)

### 1. Authentication Flow
```
1. Register new user
2. Login
3. Get profile
4. Check auth
5. Logout
```

### 2. Admin Setup (if needed)
```
1. Login as admin
2. Access dashboard
3. Verify admin role
```

### 3. Categories
```
1. List categories
2. Create category (admin)
3. Get category
4. Update category (admin)
```

### 4. Products
```
1. List products with filters
2. Create product (admin)
3. Get product details
4. Get product stats
5. Create product details
6. Update product
```

### 5. Ratings
```
1. Add rating (authenticated user)
2. Get product ratings
3. Update own rating
4. Delete own rating
```

### 6. Coupons
```
1. List coupons
2. Create coupon (admin)
3. Validate coupon
4. Toggle coupon status
```

### 7. Cart & Checkout
```
1. Add product to cart
2. Get current cart
3. Update cart item quantity
4. Validate coupon
5. Checkout (creates order)
6. Clear cart
```

### 8. Orders
```
1. List user orders
2. Get order details
3. Get order stats
4. Change order status (admin)
```

### 9. Inventory (Admin)
```
1. List inventories
2. Get low stock items
3. Get out of stock items
4. Update stock
5. Get inventory stats
```

### 10. Profile Management
```
1. Get profile
2. Update profile
3. Change password
4. Delete avatar
```

---

## 🔒 Common Headers

### For JSON requests:
```
Content-Type: application/json
```

### For authenticated requests:
```
Authorization: Bearer {{token}}
```

### For file uploads:
```
Content-Type: multipart/form-data
```

---

## ⚠️ Common Error Responses

### 401 Unauthorized
```json
{
  "status": false,
  "message": "Unauthenticated."
}
```

### 403 Forbidden
```json
{
  "status": false,
  "message": "Access denied. Admin only."
}
```

### 404 Not Found
```json
{
  "status": false,
  "message": "Resource not found"
}
```

### 422 Validation Error
```json
{
  "status": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

### 500 Server Error
```json
{
  "status": false,
  "message": "Internal server error",
  "error": "Error message details"
}
```

---

## 📝 Notes

1. **Token Management**: Token tự động được lưu sau login/register
2. **Admin Routes**: Cần token của user có role admin
3. **Pagination**: Mặc định 15 items/page, có thể thay đổi bằng `per_page`
4. **Filters**: Combine nhiều filters trong query string
5. **Dates**: Format `Y-m-d H:i:s` (2025-10-26 15:00:00)
6. **Money**: Tất cả giá trị tiền tệ là VND (số nguyên)
7. **Stock Status**: `in_stock`, `low_stock`, `out_of_stock`
8. **Order Status**: `pending`, `processing`, `shipped`, `delivered`, `cancelled`

---

## 🚀 Quick Test Collections

### Postman Collection Import
Tạo file `webshop-api.postman_collection.json` với tất cả endpoints trên.

### Environment Import
Tạo file `webshop-api.postman_environment.json`:
```json
{
  "name": "Webshop API",
  "values": [
    {"key": "base_url", "value": "http://localhost:8000/api", "enabled": true},
    {"key": "token", "value": "", "enabled": true},
    {"key": "user_id", "value": "", "enabled": true}
  ]
}
```

**Happy Testing! 🎉**

