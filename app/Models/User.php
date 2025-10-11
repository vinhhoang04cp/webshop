<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // Thêm dòng này để sử dụng Laravel Sanctum

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable; // Thêm HasApiTokens để hỗ trợ Laravel Sanctum

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [ // Thuộc tính có thể gán hàng loạt
        'name',
        'email',
        'password',
        'phone',
        'address',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [ // Thuộc tính ẩn khi chuyển đổi model thành mảng hoặc JSON
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array // Chuyển đổi kiểu dữ liệu cho các thuộc tính
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function roles() // ... Quan hệ nhiều-nhiều với Role thông qua bảng trung gian user_roles
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id');
    }

    public function cart()
    {
        return $this->hasOne(Cart::class, 'user_id', 'id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id', 'id');
    }

    /**
     * Kiểm tra user có role cụ thể không
     */
    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('role_name', $roleName)->exists();
    }

    /**
     * Kiểm tra user có phải admin không
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Kiểm tra user có phải manager không
     */
    public function isManager(): bool
    {
        return $this->hasRole('manager');
    }

    /**
     * Kiểm tra user có quyền truy cập dashboard (admin hoặc manager)
     */
    public function canAccessDashboard(): bool
    {
        return $this->isAdmin() || $this->isManager();
    }

    /**
     * Kiểm tra user có quyền cụ thể
     */
    public function hasPermission(string $permission): bool
    {
        // Admin có tất cả quyền
        if ($this->isAdmin()) {
            return true;
        }

        // Định nghĩa các quyền cho từng role
        $permissions = [
            'manager' => [
                'view_products',
                'create_product',
                'edit_product',
                'view_orders',
                'edit_order',
                'view_categories',
                'create_category',
                'edit_category',
                'view_reports',
            ],
            'user' => [
                'view_products',
                'create_order',
                'view_own_orders',
            ]
        ];

        // Kiểm tra quyền dựa trên role của user
        foreach ($this->roles as $role) {
            if (isset($permissions[$role->role_name]) && 
                in_array($permission, $permissions[$role->role_name])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Kiểm tra user có thể thực hiện hành động trên resource
     */
    public function canManage(string $resource): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $manageableResources = [
            'manager' => ['products', 'categories', 'orders', 'users'],
            'user' => ['own_orders', 'cart']
        ];

        foreach ($this->roles as $role) {
            if (isset($manageableResources[$role->role_name]) && 
                in_array($resource, $manageableResources[$role->role_name])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Lấy tất cả quyền của user
     */
    public function getAllPermissions(): array
    {
        if ($this->isAdmin()) {
            return ['*']; // Admin có tất cả quyền
        }

        $allPermissions = [];
        $permissions = [
            'manager' => [
                'view_products', 'create_product', 'edit_product', 'delete_product',
                'view_orders', 'edit_order', 'delete_order',
                'view_categories', 'create_category', 'edit_category', 'delete_category',
                'view_reports', 'view_users'
            ],
            'user' => [
                'view_products', 'create_order', 'view_own_orders', 'edit_own_profile'
            ]
        ];

        foreach ($this->roles as $role) {
            if (isset($permissions[$role->role_name])) {
                $allPermissions = array_merge($allPermissions, $permissions[$role->role_name]);
            }
        }

        return array_unique($allPermissions);
    }
}
