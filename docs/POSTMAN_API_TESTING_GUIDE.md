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
payment_url = (sẽ được set tự động)
txn_ref = (sẽ được set tự động)
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

## 📋 API Response Format

### ✅ Success Response Structure
Tất cả success responses sử dụng `SuccessResource` hoặc các Resource classes với format chuẩn:

```json
{
  "status": true,
  "message": "Operation successful",
  "data": {
    // Resource data here
  }
}
```

**Các loại Success Response:**
- `200 OK` - Thành công (retrieve, update, delete)
- `201 Created` - Tạo mới thành công
- `202 Accepted` - Request được chấp nhận (async operations)
- `204 No Content` - Thành công nhưng không có content

### ❌ Error Response Structure
Tất cả error responses sử dụng `ErrorResource` với format chuẩn:

```json
{
  "status": false,
  "message": "Error description",
  "data": {
    // Optional additional error details
  }
}
```

**Các loại Error Response:**
- `400 Bad Request` - Request không hợp lệ
- `401 Unauthorized` - Chưa xác thực hoặc token không hợp lệ
- `403 Forbidden` - Không có quyền truy cập
- `404 Not Found` - Resource không tồn tại
- `422 Unprocessable Entity` - Validation error
- `500 Internal Server Error` - Lỗi server

---

## 🧪 Testing Error Scenarios

### Common Test Cases

#### 1. Authentication Errors
```javascript
// Test 401 Unauthorized
pm.test("Returns 401 when token is invalid", function() {
    pm.response.to.have.status(401);
    pm.expect(pm.response.json().status).to.be.false;
});
```

#### 2. Authorization Errors
```javascript
// Test 403 Forbidden
pm.test("Returns 403 when user lacks permission", function() {
    pm.response.to.have.status(403);
    pm.expect(pm.response.json().status).to.be.false;
    pm.expect(pm.response.json().message).to.include("Forbidden");
});
```

#### 3. Not Found Errors
```javascript
// Test 404 Not Found
pm.test("Returns 404 when resource not found", function() {
    pm.response.to.have.status(404);
    pm.expect(pm.response.json().status).to.be.false;
    pm.expect(pm.response.json().message).to.include("not found");
});
```

#### 4. Validation Errors
```javascript
// Test 422 Validation Error
pm.test("Returns 422 for invalid data", function() {
    pm.response.to.have.status(422);
    pm.expect(pm.response.json().message).to.exist;
    pm.expect(pm.response.json().errors).to.exist;
});
```

#### 5. Server Errors
```javascript
// Test 500 Server Error
pm.test("Returns 500 for server errors", function() {
    pm.response.to.have.status(500);
    pm.expect(pm.response.json().status).to.be.false;
});
```

### Universal Test Script
Thêm vào Collection settings để test tất cả responses:

```javascript
// Test response structure
pm.test("Response has status field", function() {
    pm.expect(pm.response.json()).to.have.property('status');
});

pm.test("Response has message field", function() {
    pm.expect(pm.response.json()).to.have.property('message');
});

// For success responses
if (pm.response.code < 400) {
    pm.test("Success status is true", function() {
        pm.expect(pm.response.json().status).to.be.true;
    });
}

// For error responses
if (pm.response.code >= 400) {
    pm.test("Error status is false", function() {
        pm.expect(pm.response.json().status).to.be.false;
    });
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
├── 12. Payment (VNPay)
├── 13. Inventory
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

**Success Response (200)**: Same as Register (nhưng status code là 200 thay vì 201)

**Error Response - Invalid credentials (401)**:
```json
{
  "status": false,
  "message": "The provided credentials are incorrect."
}
```

**Error Response - Validation error (422)**:
```json
{
  "message": "The email field is required.",
  "errors": {
    "email": ["The email field is required."]
  }
}
```

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

**Success Response (200)**:
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

**Error Response - Forbidden (403)**:
```json
{
  "status": false,
  "message": "Unauthorized. Only admin or manager can access dashboard."
}
```

---

### 1.6 Check Auth
```
GET {{base_url}}/check-auth
Authorization: Bearer {{token}}
```

**Success Response (200)**:
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

**Error Response - Not authenticated (401)**:
```json
{
  "status": false,
  "message": "Not authenticated",
  "authenticated": false
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

**Success Response (200)**:
```json
{
  "status": true,
  "message": "Coupon is valid",
  "data": {
    "valid": true,
    "coupon_code": "WELCOME10",
    "discount_type": "percentage",
    "discount_value": 10,
    "original_amount": 59980000,
    "discount_amount": 5998000,
    "final_amount": 53982000,
    "savings": 5998000
  }
}
```

**Error Response - Coupon not found (404)**:
```json
{
  "status": false,
  "message": "Coupon not found",
  "data": {
    "valid": false
  }
}
```

**Error Response - Invalid coupon (400)**:
```json
{
  "status": false,
  "message": "Coupon has expired",
  "data": {
    "valid": false
  }
}
```

**Error Response - Cart empty (400)**:
```json
{
  "status": false,
  "message": "Cart is empty"
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

**Payment Methods**: `cod` (Cash on Delivery) hoặc `vnpay` (VNPay)

**Success Response - COD (201)**:
```json
{
  "status": true,
  "message": "Order placed successfully",
  "data": {
    "order": {
      "order_id": 123,
      "total_amount": 53982000,
      "status": "pending",
      "shipping_name": "Nguyễn Văn A",
      "shipping_phone": "0123456789",
      "shipping_address": "123 ABC Street, Hanoi, Vietnam",
      "note": "Gọi trước khi giao hàng",
      "order_date": "2025-10-29 10:30:00"
    },
    "discount_amount": 5998000,
    "payment_method": "cod"
  }
}
```

**Success Response - VNPay (201)**:
```json
{
  "status": true,
  "message": "Order created. Please proceed to payment.",
  "data": {
    "order": {
      "order_id": 123,
      "total_amount": 53982000,
      "status": "pending",
      "shipping_name": "Nguyễn Văn A",
      "shipping_phone": "0123456789",
      "shipping_address": "123 ABC Street, Hanoi, Vietnam",
      "note": "Gọi trước khi giao hàng",
      "order_date": "2025-10-29 10:30:00"
    },
    "discount_amount": 5998000,
    "payment_method": "vnpay",
    "payment_redirect": true,
    "order_id_for_payment": 123
  }
}
```

**Lưu ý**: 
- Với VNPay, client cần gọi tiếp endpoint `/api/payment/vnpay/create` với `order_id_for_payment` để lấy URL thanh toán
- Cart sẽ được xóa sau khi checkout thành công
- Nếu có coupon, discount_amount sẽ được tính và trừ vào tổng tiền

**Error Response - Cart empty (400)**:
```json
{
  "status": false,
  "message": "Failed to process checkout: Cart is empty"
}
```

**Error Response - Out of stock (400)**:
```json
{
  "status": false,
  "message": "Failed to process checkout: Product out of stock"
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

## 12. � Payment APIs (VNPay)

### 12.1 Create Payment URL
```
POST {{base_url}}/payment/create
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body**:
```json
{
  "order_id": "{{order_id}}"
}
```

**Response**:
```json
{
  "success": true,
  "message": "Tạo URL thanh toán thành công",
  "data": {
    "payment_url": "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html?vnp_Amount=...",
    "txn_ref": "ORD123_1698345678",
    "order_id": "ORD123"
  }
}
```

**Test Script**:
```javascript
pm.test("Status is 200", function() {
    pm.response.to.have.status(200);
});

pm.test("Payment URL exists", function() {
    var jsonData = pm.response.json();
    pm.expect(jsonData.data.payment_url).to.exist;
    pm.environment.set("payment_url", jsonData.data.payment_url);
    pm.environment.set("txn_ref", jsonData.data.txn_ref);
});
```

---

### 12.2 VNPay Return Callback
```
GET {{base_url}}/payment/vnpay-return
Authorization: Bearer {{token}}
```

**Query Params** (được VNPay tự động gửi về):
```
?vnp_Amount=5998000000
&vnp_BankCode=NCB
&vnp_BankTranNo=VNP01234567
&vnp_CardType=ATM
&vnp_OrderInfo=Thanh toan don hang #ORD123
&vnp_PayDate=20251026150000
&vnp_ResponseCode=00
&vnp_TmnCode=YOUR_TMN_CODE
&vnp_TransactionNo=14123456
&vnp_TransactionStatus=00
&vnp_TxnRef=ORD123_1698345678
&vnp_SecureHash=abc123xyz...
```

**Response (Success)**:
```json
{
  "success": true,
  "message": "Thanh toán thành công!",
  "data": {
    "order_id": "ORD123",
    "user_id": 1,
    "total_amount": "59980000",
    "payment_status": "paid",
    "payment_method": "vnpay",
    "transaction_id": "14123456",
    "paid_at": "2025-10-26 15:00:00",
    "status": "pending",
    "shipping_address": "123 ABC Street, Hanoi",
    "shipping_phone": "0123456789",
    "notes": "Gọi trước khi giao hàng",
    "created_at": "2025-10-26 14:50:00",
    "updated_at": "2025-10-26 15:00:00",
    "items": [
      {
        "order_item_id": 1,
        "order_id": "ORD123",
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
          "stock_quantity": 48,
          "category": {
            "category_id": 1,
            "name": "Smartphone",
            "slug": "smartphone"
          }
        }
      }
    ],
    "user": {
      "id": 1,
      "name": "Test User",
      "email": "test@example.com",
      "phone": "0123456789",
      "address": "123 ABC Street",
      "avatar": null,
      "avatar_url": null
    }
  }
}
```

**Response (Failed)**:
```json
{
  "success": false,
  "message": "Giao dịch không thành công do: Khách hàng hủy giao dịch",
  "data": {
    "order_id": "ORD123"
  }
}
```

---

### 12.3 VNPay IPN (Instant Payment Notification)
```
POST {{base_url}}/payment/vnpay-ipn
Content-Type: application/json
```

**Note**: Endpoint này được VNPay gọi tự động, không cần Authorization header.

**Body** (VNPay sends as query params, but can be JSON):
```json
{
  "vnp_Amount": "5998000000",
  "vnp_BankCode": "NCB",
  "vnp_ResponseCode": "00",
  "vnp_TmnCode": "YOUR_TMN_CODE",
  "vnp_TransactionNo": "14123456",
  "vnp_TxnRef": "ORD123_1698345678",
  "vnp_SecureHash": "abc123xyz..."
}
```

**Response**:
```json
{
  "RspCode": "00",
  "Message": "Confirm Success"
}
```

**Error Responses**:
```json
// Invalid signature
{
  "RspCode": "97",
  "Message": "Invalid signature"
}

// Order already confirmed
{
  "RspCode": "02",
  "Message": "Order already confirmed"
}

// Invalid amount
{
  "RspCode": "04",
  "Message": "Invalid amount"
}

// Order not found
{
  "RspCode": "01",
  "Message": "Order not found"
}

// Unknown error
{
  "RspCode": "99",
  "Message": "Unknown error"
}
```

---

### 12.4 Get Payment Status
```
GET {{base_url}}/payment/status/{{order_id}}
Authorization: Bearer {{token}}
```

**Response**:
```json
{
  "success": true,
  "data": {
    "order_id": "ORD123",
    "payment_status": "paid",
    "payment_method": "vnpay",
    "transaction_id": "14123456",
    "total_amount": "59980000",
    "paid_at": "2025-10-26 15:00:00"
  }
}
```

---

### 12.5 Get Payment Success Details
```
GET {{base_url}}/payment/success/{{order_id}}
Authorization: Bearer {{token}}
```

**Response**:
```json
{
  "success": true,
  "message": "Thanh toán thành công",
  "data": {
    // Full order details with items and user (same as 12.2 success response)
  }
}
```

---

### 12.6 Get Payment Failed Details
```
GET {{base_url}}/payment/failed/{{order_id}}
Authorization: Bearer {{token}}
```

**Response**:
```json
{
  "success": false,
  "message": "Thanh toán thất bại",
  "data": {
    "order_id": "ORD123",
    "payment_status": "failed",
    "total_amount": "59980000"
  }
}
```

---

### 12.7 VNPay Error Codes Reference

| Code | Meaning |
|------|---------|
| 00 | Giao dịch thành công |
| 07 | Trừ tiền thành công. Giao dịch bị nghi ngờ (liên quan tới lừa đảo, giao dịch bất thường) |
| 09 | Thẻ/Tài khoản chưa đăng ký dịch vụ InternetBanking |
| 10 | Khách hàng xác thực thông tin thẻ/tài khoản không đúng quá 3 lần |
| 11 | Đã hết hạn chờ thanh toán |
| 12 | Thẻ/Tài khoản bị khóa |
| 13 | Sai mật khẩu xác thực giao dịch (OTP) |
| 24 | Khách hàng hủy giao dịch |
| 51 | Tài khoản không đủ số dư |
| 65 | Tài khoản đã vượt quá hạn mức giao dịch trong ngày |
| 75 | Ngân hàng thanh toán đang bảo trì |
| 79 | KH nhập sai mật khẩu thanh toán quá số lần quy định |
| 99 | Các lỗi khác |

---

## 13. �📊 Inventory APIs (Admin Only)

### 13.1 List Inventories
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

### 13.2 Get Inventory
```
GET {{base_url}}/inventories/{{inventory_id}}
Authorization: Bearer {{token}}
```

---

### 13.3 Create Inventory
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

### 13.4 Update Inventory
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

### 13.5 Delete Inventory
```
DELETE {{base_url}}/inventories/{{inventory_id}}
Authorization: Bearer {{token}}
```

---

### 13.6 Update Stock
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

### 13.7 Upsert Inventory
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

### 13.8 Low Stock Items
```
GET {{base_url}}/inventories/low-stock/list
Authorization: Bearer {{token}}
```

**Query Params**:
```
?threshold=10
```

---

### 13.9 Out of Stock Items
```
GET {{base_url}}/inventories/out-of-stock/list
Authorization: Bearer {{token}}
```

---

### 13.10 Inventory Statistics
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

### 9. Payment Flow (VNPay)
```
1. Create payment URL for an order
2. Simulate VNPay callback (return)
3. Get payment status
4. Get payment success/failed details
5. Test IPN endpoint (optional)
```

**Detailed Payment Testing Steps**:

#### Step 1: Create an Order First
```
POST {{base_url}}/cart/checkout
Authorization: Bearer {{token}}

Body:
{
  "shipping_name": "Nguyễn Văn A",
  "shipping_phone": "0123456789",
  "shipping_address": "123 ABC Street, Hanoi",
  "payment_method": "vnpay",
  "note": "Test payment"
}

// Save the order_id from response
```

#### Step 2: Create Payment URL
```
POST {{base_url}}/payment/create
Authorization: Bearer {{token}}

Body:
{
  "order_id": "ORD123"
}

// Copy payment_url from response
// In real scenario, redirect user to this URL
```

#### Step 3: Simulate VNPay Return (Success)
```
GET {{base_url}}/payment/vnpay-return?vnp_Amount=5998000000&vnp_BankCode=NCB&vnp_OrderInfo=Thanh%20toan%20don%20hang%20%23ORD123&vnp_ResponseCode=00&vnp_TmnCode=YOUR_TMN_CODE&vnp_TransactionNo=14123456&vnp_TxnRef=ORD123_1698345678&vnp_SecureHash=VALID_HASH
Authorization: Bearer {{token}}

// This simulates successful payment
// vnp_ResponseCode=00 means success
```

#### Step 4: Simulate VNPay Return (Failed)
```
GET {{base_url}}/payment/vnpay-return?vnp_Amount=5998000000&vnp_ResponseCode=24&vnp_TxnRef=ORD123_1698345678&vnp_SecureHash=VALID_HASH
Authorization: Bearer {{token}}

// This simulates failed payment
// vnp_ResponseCode=24 means user cancelled
```

#### Step 5: Check Payment Status
```
GET {{base_url}}/payment/status/ORD123
Authorization: Bearer {{token}}

// Verify payment status updated correctly
```

#### Step 6: Get Success/Failed Details
```
GET {{base_url}}/payment/success/ORD123
or
GET {{base_url}}/payment/failed/ORD123
Authorization: Bearer {{token}}
```

**VNPay Sandbox Testing**:

1. **Test Card Numbers** (VNPay Sandbox):
   - Card Number: 9704198526191432198
   - Cardholder: NGUYEN VAN A
   - Issue Date: 07/15
   - OTP: 123456

2. **Test Bank Accounts**:
   - Bank: NCB
   - Account: 9704198526191432198
   - Password: 123456

3. **Test Scenarios**:
   - Success: Use test card, complete payment
   - Failed: Click "Hủy giao dịch" button
   - Timeout: Wait for timeout (11 minutes)
   - Invalid OTP: Enter wrong OTP 3 times

### 10. Inventory (Admin)
```
1. List inventories
2. Get low stock items
3. Get out of stock items
4. Update stock
5. Get inventory stats
```

### 11. Profile Management
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
9. **Payment Status**: `pending`, `paid`, `failed`
10. **VNPay Testing**: Sử dụng Sandbox credentials để test thanh toán

---

## 💳 Payment Testing Best Practices

### 1. Environment Setup for VNPay
Thêm vào Postman Environment:
```
vnpay_tmn_code = YOUR_TMN_CODE
vnpay_hash_secret = YOUR_HASH_SECRET
vnpay_url = https://sandbox.vnpayment.vn/paymentv2/vpcpay.html
vnpay_return_url = http://localhost:8000/api/payment/vnpay-return
vnpay_ipn_url = http://localhost:8000/api/payment/vnpay-ipn
```

### 2. Pre-request Script for Payment Create
```javascript
// Automatically set order_id from environment if exists
if (pm.environment.get("order_id")) {
    pm.request.body.update({
        mode: 'raw',
        raw: JSON.stringify({
            order_id: pm.environment.get("order_id")
        })
    });
}
```

### 3. Test Script for Payment Create
```javascript
pm.test("Status is 200", function() {
    pm.response.to.have.status(200);
});

pm.test("Payment URL exists", function() {
    var jsonData = pm.response.json();
    pm.expect(jsonData.success).to.be.true;
    pm.expect(jsonData.data.payment_url).to.exist;
    
    // Save to environment
    pm.environment.set("payment_url", jsonData.data.payment_url);
    pm.environment.set("txn_ref", jsonData.data.txn_ref);
    
    console.log("Payment URL:", jsonData.data.payment_url);
    console.log("Transaction Ref:", jsonData.data.txn_ref);
});
```

### 4. Test Script for VNPay Return
```javascript
pm.test("Status is 200", function() {
    pm.response.to.have.status(200);
});

pm.test("Payment processed", function() {
    var jsonData = pm.response.json();
    
    if (jsonData.success) {
        pm.test("Payment successful", function() {
            pm.expect(jsonData.data.payment_status).to.eql("paid");
            pm.expect(jsonData.data.transaction_id).to.exist;
        });
    } else {
        pm.test("Payment failed", function() {
            pm.expect(jsonData.data.order_id).to.exist;
            pm.expect(jsonData.message).to.exist;
        });
    }
});
```

### 5. Complete Payment Test Workflow

**Collection Runner Setup**:
1. Create folder: "Payment Flow"
2. Add requests in order:
   ```
   a. Login
   b. Add Product to Cart
   c. Checkout (creates order)
   d. Create Payment URL
   e. Simulate VNPay Return (Success)
   f. Get Payment Status
   g. Get Payment Success Details
   ```

**Variables to Monitor**:
- `token`: Authentication token
- `order_id`: Created order ID
- `payment_url`: VNPay payment URL
- `txn_ref`: Transaction reference
- `transaction_id`: VNPay transaction ID

### 6. Mock VNPay Callback for Testing

Create a Pre-request Script to generate valid VNPay callback:

```javascript
// Mock VNPay callback parameters
const orderId = pm.environment.get("order_id");
const amount = "5998000000"; // 59,980,000 VND * 100
const txnRef = orderId + "_" + Date.now();

// Set query parameters
pm.request.url.addQueryParams([
    {key: "vnp_Amount", value: amount},
    {key: "vnp_BankCode", value: "NCB"},
    {key: "vnp_BankTranNo", value: "VNP01234567"},
    {key: "vnp_CardType", value: "ATM"},
    {key: "vnp_OrderInfo", value: "Thanh toan don hang #" + orderId},
    {key: "vnp_PayDate", value: "20251026150000"},
    {key: "vnp_ResponseCode", value: "00"}, // 00 = success, 24 = cancelled
    {key: "vnp_TmnCode", value: pm.environment.get("vnpay_tmn_code")},
    {key: "vnp_TransactionNo", value: "14123456"},
    {key: "vnp_TransactionStatus", value: "00"},
    {key: "vnp_TxnRef", value: txnRef}
]);

// Note: In real testing, vnp_SecureHash must be calculated correctly
// For testing purposes, you may need to disable signature validation
// or use the actual VNPay sandbox
```

### 7. Testing Different Payment Scenarios

**Success Scenario**:
```
vnp_ResponseCode=00
vnp_TransactionStatus=00
Result: payment_status = "paid"
```

**User Cancelled**:
```
vnp_ResponseCode=24
Result: payment_status = "failed"
Message: "Khách hàng hủy giao dịch"
```

**Insufficient Balance**:
```
vnp_ResponseCode=51
Result: payment_status = "failed"
Message: "Tài khoản không đủ số dư"
```

**Timeout**:
```
vnp_ResponseCode=11
Result: payment_status = "failed"
Message: "Đã hết hạn chờ thanh toán"
```

### 8. Debugging Payment Issues

**Check these in order**:
1. ✅ Order exists and status is "pending"
2. ✅ VNPay credentials are correct (TMN Code, Hash Secret)
3. ✅ Return URL is accessible
4. ✅ Signature (SecureHash) is calculated correctly
5. ✅ Amount is in correct format (VND * 100)
6. ✅ Timestamp format is YmdHis

**Common Issues**:
- **Invalid Signature**: Check Hash Secret and calculation method
- **Order Not Found**: Verify order_id exists in database
- **Invalid Amount**: Order amount doesn't match vnp_Amount
- **Duplicate Transaction**: Transaction already processed

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

---

## 📝 Changelog - API Response Format Updates

### Version 2.0 - October 29, 2025

**🎯 Chuẩn hóa Response Format**

Tất cả API endpoints giờ đây sử dụng `SuccessResource` và `ErrorResource` để đảm bảo response format nhất quán:

#### ✅ Các Thay Đổi Chính:

**1. Authentication APIs (`AuthController`)**
- ✨ `POST /api/login` - Trả về `ErrorResource::unauthorized()` thay vì `ValidationException`
- ✨ `POST /api/register` - Sử dụng `UserResource::created()` với status 201
- ✨ `POST /api/logout` - Sử dụng `SuccessResource::message()`
- ✨ `GET /api/profile` - Sử dụng `UserResource::retrieved()`
- ✨ `GET /api/dashboard` - Sử dụng `SuccessResource::withData()` và `ErrorResource::forbidden()`
- ✨ `GET /api/check-auth` - Sử dụng `UserResource::retrieved()` và `ErrorResource::unauthorized()`

**2. Cart APIs (`CartController`)**
- ✨ `POST /api/cart/validate-coupon` - Sử dụng `SuccessResource::withData()` cho valid coupon
  - Thêm `ErrorResource::notFound()` cho coupon không tồn tại
  - Thêm `ErrorResource::badRequest()` cho coupon không hợp lệ
- ✨ `POST /api/cart/checkout` - Sử dụng `SuccessResource::withData()` với status 201
  - Cấu trúc response mới với wrapper `data`
  - Thêm các error responses chuẩn hóa

**3. User Resource Enhancement**
- ✨ Thêm các phương thức static:
  - `UserResource::created()` - Cho đăng ký user
  - `UserResource::retrieved()` - Cho lấy thông tin user
  - `UserResource::updated()` - Cho cập nhật user
- ✨ Hỗ trợ `additionalData` để truyền token và metadata khác

**4. Success Resource Enhancement**
- ✨ Thêm phương thức `SuccessResource::withData()` để trả về success kèm data
- ✨ Tất cả success responses đều có format: `{status: true, message: "...", data: {...}}`

#### 📊 Response Format Comparison

**Trước:**
```json
{
  "status": true,
  "message": "Success",
  "order": {...},
  "payment": {...}
}
```

**Sau:**
```json
{
  "status": true,
  "message": "Success",
  "data": {
    "order": {...},
    "payment": {...}
  }
}
```

#### 🎁 Benefits

1. **Nhất quán 100%** - Tất cả endpoints đều có cùng structure
2. **Dễ debug** - Error messages rõ ràng với status codes chuẩn
3. **Type-safe** - Sử dụng resource classes thay vì manual JSON
4. **Dễ test** - Response structure có thể predict được
5. **Maintainable** - Thay đổi format chỉ cần sửa ở resource classes

#### ⚠️ Breaking Changes

Nếu bạn đang sử dụng API version cũ, cần cập nhật code client:

**Cart Checkout Response:**
```javascript
// Old
const orderId = response.order.order_id;

// New
const orderId = response.data.order.order_id;
```

**Validate Coupon Response:**
```javascript
// Old
const discountAmount = response.discount_amount;

// New
const discountAmount = response.data.discount_amount;
```

**Error Handling:**
```javascript
// Old
if (!response.success) { ... }

// New
if (!response.status) { ... }
```

#### 📚 Documentation Updates

- ✅ Thêm section "API Response Format" với giải thích chi tiết
- ✅ Thêm section "Testing Error Scenarios" với test cases
- ✅ Cập nhật tất cả response examples
- ✅ Thêm error response examples cho mọi endpoint quan trọng
- ✅ Thêm Universal Test Script để validate response structure

---

**Happy Testing! 🎉**

