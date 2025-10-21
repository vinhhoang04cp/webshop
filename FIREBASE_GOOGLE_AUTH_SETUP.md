# Firebase Google Authentication Setup

## Tổng quan

Tính năng đăng nhập Google với Firebase đã được tích hợp thành công vào project Laravel. Hệ thống hoạt động theo luồng sau:

1. **Frontend**: User click "Sign in with Google" → Firebase Authentication
2. **Firebase**: Trả về ID token
3. **Frontend**: Gửi ID token lên Laravel API
4. **Laravel**: Verify token với Firebase Admin SDK → Tạo/tìm user → Trả về Sanctum token
5. **Frontend**: Lưu Sanctum token để sử dụng cho các API calls tiếp theo

## Files đã tạo/cập nhật

### Backend (Laravel)
- `config/firebase.php` - Cấu hình Firebase
- `app/Http/Middleware/FirebaseAuth.php` - Middleware xác thực Firebase token
- `app/Services/FirebaseAuthService.php` - Service xử lý Firebase authentication
- `app/Http/Controllers/Api/AuthController.php` - Thêm method `loginWithGoogle()`
- `database/migrations/*_add_google_oauth_fields_to_users_table.php` - Migration thêm fields
- `app/Models/User.php` - Cập nhật fillable fields
- `routes/api.php` - Thêm route `/api/auth/google`
- `bootstrap/app.php` - Đăng ký middleware `firebase.auth`

### Frontend (Example)
- `resources/js/firebase/auth.js` - Firebase Auth Service class
- `public/firebase-demo.html` - Demo page để test authentication

## API Endpoints

### 1. Google Login
```
POST /api/auth/google
Content-Type: application/json

Body:
{
    "firebase_token": "eyJhbGciOiJSUzI1NiIs..."
}

Response (Success):
{
    "status": true,
    "message": "Google login successful",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "provider": "google",
            "avatar": "https://lh3.googleusercontent.com/...",
            "email_verified_at": "2025-10-21T01:55:12.000000Z"
        },
        "token": "1|abc123...",
        "token_type": "Bearer"
    }
}
```

### 2. Get Profile
```
GET /api/profile
Authorization: Bearer 1|abc123...

Response:
{
    "status": true,
    "message": "Profile retrieved successfully",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "phone": null,
            "address": null,
            "provider": "google",
            "avatar": "https://lh3.googleusercontent.com/...",
            "email_verified_at": "2025-10-21T01:55:12.000000Z",
            "created_at": "2025-10-21T01:55:12.000000Z",
            "updated_at": "2025-10-21T01:55:12.000000Z"
        }
    }
}
```

## Setup Instructions

### 1. Firebase Console Setup

1. Truy cập [Firebase Console](https://console.firebase.google.com)
2. Tạo project mới hoặc chọn project existing
3. Vào **Authentication** → **Sign-in method**
4. Bật **Google** provider
5. Thêm domain của bạn vào **Authorized domains**
6. Vào **Project Settings** → **Service accounts**
7. Click **Generate new private key** để download service account JSON
8. Vào **Project Settings** → **General** để lấy Firebase config

### 2. Laravel Environment Setup

1. Copy nội dung service account JSON vào file `.env`:
```env
FIREBASE_PROJECT_ID=your-project-id
FIREBASE_TYPE=service_account
FIREBASE_PRIVATE_KEY_ID=your-private-key-id
FIREBASE_PRIVATE_KEY="-----BEGIN PRIVATE KEY-----\nYour private key here\n-----END PRIVATE KEY-----\n"
FIREBASE_CLIENT_EMAIL=firebase-adminsdk-xxxxx@your-project.iam.gserviceaccount.com
FIREBASE_CLIENT_ID=your-client-id
FIREBASE_AUTH_URI=https://accounts.google.com/o/oauth2/auth
FIREBASE_TOKEN_URI=https://oauth2.googleapis.com/token
FIREBASE_AUTH_PROVIDER_X509_CERT_URL=https://www.googleapis.com/oauth2/v1/certs
FIREBASE_CLIENT_X509_CERT_URL=https://www.googleapis.com/robot/v1/metadata/x509/your-service-account-email
```

2. Chạy migration:
```bash
./vendor/bin/sail artisan migrate
```

### 3. Frontend Setup

1. Cài đặt Firebase SDK (nếu dùng npm/yarn):
```bash
npm install firebase
```

2. Cập nhật Firebase config trong `resources/js/firebase/auth.js`:
```javascript
const firebaseConfig = {
    apiKey: "your-api-key",
    authDomain: "your-project.firebaseapp.com",
    projectId: "your-project-id",
    storageBucket: "your-project.appspot.com",
    messagingSenderId: "your-sender-id",
    appId: "your-app-id"
};
```

### 4. Test Authentication

1. Mở `http://localhost/firebase-demo.html` trong trình duyệt
2. Click "Sign in with Google"
3. Hoàn thành OAuth flow
4. Kiểm tra API response và database

## Cách sử dụng trong Frontend

### Vanilla JavaScript
```javascript
import { firebaseAuthService } from './firebase/auth.js';

// Sign in
try {
    const result = await firebaseAuthService.signInWithGoogle();
    console.log('Login successful:', result);
    // result.data.token chứa Laravel Sanctum token
    // result.data.user chứa thông tin user
} catch (error) {
    console.error('Login failed:', error);
}

// Sign out
await firebaseAuthService.signOut();

// Check authentication status
if (firebaseAuthService.isAuthenticated()) {
    const user = firebaseAuthService.getStoredUser();
    const token = firebaseAuthService.getStoredToken();
    // Use token for API calls
}
```

### React Example
```jsx
import { useState, useEffect } from 'react';
import { firebaseAuthService } from '../firebase/auth.js';

function LoginComponent() {
    const [user, setUser] = useState(null);
    const [loading, setLoading] = useState(false);

    const handleGoogleLogin = async () => {
        setLoading(true);
        try {
            const result = await firebaseAuthService.signInWithGoogle();
            setUser(result.data.user);
        } catch (error) {
            console.error('Login failed:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleLogout = async () => {
        try {
            await firebaseAuthService.signOut();
            setUser(null);
        } catch (error) {
            console.error('Logout failed:', error);
        }
    };

    useEffect(() => {
        // Check stored auth on component mount
        if (firebaseAuthService.isAuthenticated()) {
            setUser(firebaseAuthService.getStoredUser());
        }
    }, []);

    if (user) {
        return (
            <div>
                <img src={user.avatar} alt="Avatar" />
                <h3>{user.name}</h3>
                <p>{user.email}</p>
                <button onClick={handleLogout}>Sign Out</button>
            </div>
        );
    }

    return (
        <button onClick={handleGoogleLogin} disabled={loading}>
            {loading ? 'Signing in...' : 'Sign in with Google'}
        </button>
    );
}
```

## Security Features

1. **Firebase ID Token Verification**: Laravel verify token với Firebase Admin SDK
2. **Rate Limiting**: API có rate limiting 60 requests/phút
3. **Sanctum Token**: Sử dụng Laravel Sanctum cho session management
4. **HTTPS Required**: Firebase Authentication yêu cầu HTTPS trong production
5. **Token Expiry**: Firebase ID tokens tự động expire sau 1 giờ

## Database Schema

Các fields đã thêm vào bảng `users`:
- `google_id` (nullable, unique): Google User ID
- `firebase_uid` (nullable, unique): Firebase User ID  
- `provider` (default 'email'): Loại provider (email, google, facebook, etc.)
- `avatar` (nullable): URL avatar từ provider

## Troubleshooting

### Common Issues:

1. **"Invalid Firebase token"**: 
   - Kiểm tra Firebase config
   - Đảm bảo service account key đúng
   - Verify project ID

2. **CORS errors**: 
   - Thêm domain vào Authorized domains trong Firebase Console
   - Kiểm tra Laravel CORS config

3. **Token expiry**: 
   - Firebase ID tokens expire sau 1 giờ
   - Implement token refresh mechanism

4. **Database connection errors**:
   - Đảm bảo Laravel Sail đang chạy: `./vendor/bin/sail up -d`
   - Kiểm tra database credentials trong `.env`

## Next Steps

1. Implement token refresh mechanism
2. Add support for other providers (Facebook, Twitter)
3. Add email verification workflow
4. Implement user profile management
5. Add admin dashboard for user management

Tính năng đăng nhập Google với Firebase đã được tích hợp hoàn tất! 🚀