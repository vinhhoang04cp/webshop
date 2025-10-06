# Bug Fix: Column 'name' not found trong roles table

## Vấn Đề
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'name' in 'where clause'
SQL: select exists(select * from `roles` inner join `user_roles` on `roles`.`role_id` = `user_roles`.`role_id` where `user_roles`.`user_id` = 1 and `name` = admin) as `exists`
```

## Nguyên Nhân
Method `hasRole()` trong `User` model đang tìm kiếm theo cột `name`, nhưng bảng `roles` thực tế sử dụng cột `role_name`.

## Giải Pháp

### File: `app/Models/User.php`

**TRƯỚC:**
```php
public function hasRole(string $roleName): bool
{
    return $this->roles()->where('name', $roleName)->exists();
}
```

**SAU:**
```php
public function hasRole(string $roleName): bool
{
    return $this->roles()->where('role_name', $roleName)->exists();
}
```

## Cấu Trúc Bảng Roles

Theo `app/Models/Role.php`, bảng `roles` có cấu trúc:
- `role_id` (Primary Key)
- `role_name` (Tên role: admin, manager, customer, guest)
- `role_display_name` (Tên hiển thị: Administrator, Manager, Customer, Guest User)
- `role_created_at` (Timestamp)
- `role_updated_at` (Timestamp)

## Kiểm Tra Sau Khi Sửa

### 1. Kiểm tra roles đã được seed
```bash
sail artisan tinker --execute="App\Models\Role::all(['role_id', 'role_name'])"
```

**Kết quả:**
```
- ID: 1, Name: admin
- ID: 2, Name: manager
- ID: 3, Name: customer
- ID: 4, Name: guest
```

### 2. Kiểm tra user có role admin
```bash
sail artisan tinker --execute="\$user = App\Models\User::first(); echo \$user->isAdmin() ? 'YES' : 'NO';"
```

**Kết quả:** YES

### 3. Test middleware admin
- Gọi API admin endpoint với user có role admin → Success (200/201)
- Gọi API admin endpoint với user không có role admin → 403 Forbidden

## Files Liên Quan
- `app/Models/User.php` - Đã sửa method hasRole()
- `app/Models/Role.php` - Định nghĩa cấu trúc bảng roles
- `database/seeders/RoleSeeder.php` - Seed data cho roles
- `database/seeders/UserRoleSeeder.php` - Gán role cho users
- `app/Http/Middleware/EnsureUserIsAdmin.php` - Middleware sử dụng hasRole()

## Thời Gian Sửa
06/10/2025
