<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory; // su dung trait HasFactory de tao factory cho model

    // protected dung de dinh nghia cac thuoc tinh cua model

    protected $table = 'carts'; // ten bang trong CSDL

    protected $primaryKey = 'cart_id'; // khoa chinh cua bang

    protected $fillable = [ // cac truong co the gan gia tri hang loat
        'user_id',
    ];

    public function products() // quan he nhieu-nhieu voi Product qua bang cart_items
    {
        return $this->belongsToMany(Product::class, 'cart_items', 'cart_id', 'product_id') // $this la doi tuong Cart hien tai
        // belongsToMany: quan he nhieu-nhieu giua Cart va Product qua bang cart_items
        // (Product::class: model Product
        // 'cart_items': ten bang trung gian
        // 'cart_id': khoa ngoai trong bang cart_items tham chieu den khoa chinh cua bang carts
        // 'product_id': khoa ngoai trong bang cart_items tham chieu den khoa chinh cua bang products
            ->withPivot('quantity', 'price')
            ->withTimestamps();
    }

    public function user() // quan he 1-1 voi User
    {
        return $this->belongsTo(User::class, 'user_id', 'id'); // $this la doi tuong Cart hien tai
        // belongsTo: quan he 1-1 giua Cart va User
        // (User::class: model User
    }

    public function items() // quan he 1-n voi CartItem
    {
        return $this->hasMany(CartItem::class, 'cart_id', 'cart_id'); // 1 cart co nhieu cart item
    }

    public function totalPrice() // tinh tong gia tri don hang trong cart
    {
        return $this->items->sum(function ($item) { // $items la tap hop cac cart item trong cart , sum: tinh tong voi tham so la ham callback
            return $item->quantity * ($item->price ?? $item->product->price ?? 0); // goi den ham callback de tinh tong gia tri, sau do tra ve ket qua
        });
    }

    public function totalItems() // dem tong so luong san pham trong cart
    {
        return $this->items->sum('quantity'); // $items la tap hop cac cart item trong cart , sum: tinh tong voi tham so la truong 'quantity'
    }
}
