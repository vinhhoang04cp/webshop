<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * UserRole Model
 * 
 * Đây là model cho bảng pivot 'user_roles' 
 * Quản lý mối quan hệ Many-to-Many giữa User và Role
 * Một user có thể có nhiều role, một role có thể được gán cho nhiều user
 */
class UserRole extends Model
{
    use HasFactory;

    // Chỉ định tên bảng trong database
    // Laravel mặc định sẽ dùng tên model số nhiều (user_roles)
    // Nhưng khai báo rõ ràng để tránh nhầm lẫn
    protected $table = 'user_roles';

    // Định nghĩa composite primary key (khóa chính kết hợp)
    // Bảng pivot thường dùng kết hợp 2 foreign key làm primary key
    // ['user_id', 'role_id'] đảm bảo 1 user không có duplicate role
    protected $primaryKey = ['user_id', 'role_id'];

    // Tắt auto-increment vì đây là composite key
    // Laravel mặc định primary key là auto-increment integer
    // Nhưng composite key không thể auto-increment được
    public $incrementing = false;

    // Tắt timestamps (created_at, updated_at)
    // Bảng pivot thường không cần track thời gian tạo/cập nhật
    // Thay vào đó có thể dùng field custom như 'assigned_at'
    public $timestamps = false;

    // Các field có thể mass assignment
    // Định nghĩa các cột có thể được gán giá trị hàng loạt qua create() hoặc fill()
    protected $fillable = [
        'user_id',      // ID của user
        'role_id',      // ID của role  
        'assigned_at',  // Thời gian gán role cho user
    ];

    // Cast kiểu dữ liệu cho các attributes
    // 'assigned_at' sẽ được tự động convert sang Carbon datetime object
    // Giúp dễ dàng thao tác với date/time
    protected $casts = [
        'assigned_at' => 'datetime',
    ];
}
