<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // thu vien HasFactory dung de tao factory cho model
use Illuminate\Database\Eloquent\Model; // su dung Model cua Eloquent

class Order extends Model
{
    use HasFactory; // su dung trait HasFactory de tao factory cho model

    protected $table = 'orders'; // ten bang trong CSDL

    protected $primaryKey = 'order_id'; // khoa chinh cua bang

    protected $fillable = [ // cac truong co the gan gia tri hang loat
        'user_id',
        'order_date',
        'total_amount',
        'status',
        'shipping_name',
        'shipping_phone',
        'shipping_address',
        'note',
    ];

    protected $casts = [
        'order_date' => 'datetime', // dinh dang kieu datetime
    ];

    // Định nghĩa các trạng thái hợp lệ
    const STATUS_PENDING = 'pending'; // STATUS_PENDING trang thai cho don hang moi tao, 'pending' la trang thai goc trong CSDL

    const STATUS_PROCESSING = 'processing'; // STATUS_PROCESSING trang thai don hang dang duoc xu ly, 'processing' la trang thai goc trong CSDL

    const STATUS_SHIPPED = 'shipped'; // STATUS_SHIPPED trang thai don hang da duoc gui, 'shipped' la trang thai goc trong CSDL

    const STATUS_DELIVERED = 'delivered'; // STATUS_DELIVERED trang thai don hang da duoc giao, 'delivered' la trang thai goc trong CSDL

    const STATUS_CANCELLED = 'cancelled'; // STATUS_CANCELLED trang thai don hang bi huy, 'cancelled' la trang thai goc trong CSDL

    // Hang so dinh nghia cac chuyen doi trang thai hop le,tuan tu trang thai hien tai sang cac trang thai moi
    const STATUS_TRANSITIONS = [
        self::STATUS_PENDING => [self::STATUS_PROCESSING, self::STATUS_CANCELLED], // self::STATUS_PENDING la trang thai hien tai , co the chuyen sang trang thai self::STATUS_PROCESSING hoac self::STATUS_CANCELLED
        self::STATUS_PROCESSING => [self::STATUS_SHIPPED, self::STATUS_CANCELLED], // self::STATUS_PROCESSING la trang thai hien tai , co the chuyen sang trang thai self::STATUS_SHIPPED hoac self::STATUS_CANCELLED
        self::STATUS_SHIPPED => [self::STATUS_DELIVERED], // self::STATUS_SHIPPED la trang thai hien tai , co the chuyen sang trang thai self::STATUS_DELIVERED
        self::STATUS_DELIVERED => [], // self::STATUS_DELIVERED la trang thai hien tai , khong the chuyen sang trang thai nao khac
        self::STATUS_CANCELLED => [], // self::STATUS_CANCELLED la trang thai hien tai , khong the chuyen sang trang thai nao khac
    ];

    /**
     * Kiểm tra xem có thể chuyển sang trạng thái mới không
     */
    public function canTransitionTo(string $newStatus): bool // Ham kiem tra xem co the chuyen sang trang thai moi khong, voi tham so la trang thai moi
    {
        $currentStatus = $this->status ?? self::STATUS_PENDING;
        // $currentStatus la trang thai hien tai , neu khong co thi mac dinh la self::STATUS_PENDING, lay tu thuoc tinh status cua doi tuong hien tai

        if (! isset(self::STATUS_TRANSITIONS[$currentStatus])) { // ham isset kiem tra xem trang thai hien tai co ton tai trong mang STATUS_TRANSITIONS khong
            return false; // neu khong ton tai thi tra ve false
        }

        return in_array($newStatus, self::STATUS_TRANSITIONS[$currentStatus]); // in_array kiem tra xem trang thai moi co trong mang cac trang thai hop le cua trang thai hien tai khong
    }

    /**
     * Chuyển sang trạng thái mới nếu hợp lệ
     */
    public function transitionTo(string $newStatus): bool // Ham chuyen sang trang thai moi neu hop le, voi tham so la trang thai moi, bool la kieu tra ve
    {
        if (! $this->canTransitionTo($newStatus)) { // $this->canTransitionTo($newStatus) la doi tuong hien tai goi den ham canTransitionTo de kiem tra xem co the chuyen sang trang thai moi khong
            return false; // neu khong hop le thi tra ve false
        }

        $this->status = $newStatus; // gan trang thai moi cho thuoc tinh status cua doi tuong hien tai

        return $this->save(); // luu thay doi vao CSDL va tra ve ket qua cua ham save (true neu thanh cong, false neu that bai)
    }

    public function user() // quan he 1-1 voi User
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function items() // quan he 1-n voi OrderItem
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'order_id');
    }

    public function products() // quan he nhieu-nhieu voi Product qua bang order_items
    {
        return $this->belongsToMany(Product::class, 'order_items', 'order_id', 'product_id')
        // $this la doi tuong Order hien tai
        // belongsToMany: quan he nhieu-nhieu giua Order va Product qua bang order_items
        // (Product::class: model Product
        // 'order_items': ten bang trung gian
        // 'order_id': khoa ngoai trong bang order_items tham chieu den khoa chinh cua bang orders
        // 'product_id': khoa ngoai trong bang order_items tham chieu den khoa chinh cua bang products
            ->withPivot('quantity', 'price')
            ->withTimestamps();
    }

    public function getProductCountAttribute() // Ham lay so luong san pham
    {
        return $this->items()->count(); // dem so luong order item trong don hang, items() la quan he 1-n voi OrderItem
    }

    public function getTotalQuantityAttribute()
    {
        return $this->items()->sum('quantity'); // tinh tong so luong san pham trong don hang
    }

    // Removed getTotalAmountAttribute to avoid conflict with fillable total_amount field

    public static function reorderIds()
    {
        // Tắt tạm thời kiểm tra foreign key để có thể cập nhật ID
        \DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        // Lấy tất cả orders theo thứ tự ID hiện tại
        $orders = self::orderBy('order_id')->get();

        // Cập nhật lại ID tuần tự bắt đầu từ 1
        $newId = 1;
        foreach ($orders as $order) {
            $order->order_id = $newId++;
            $order->save();
        }

        // Bật lại kiểm tra foreign key
        \DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
