<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // thu vien HasFactory dung de tao factory cho model
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $table = 'cart_items'; // ten bang trong CSDL

    protected $primaryKey = 'cart_item_id'; // khoa chinh cua bang

    protected $fillable = [ // cac truong co the gan gia tri hang loat
        'cart_id',
        'product_id',
        'quantity',
        'price',
    ];

    public function cart() // quan he 1-1 voi Cart
    {
        return $this->belongsTo(Cart::class, 'cart_id', 'cart_id'); // $this la doi tuong CartItem hien tai
        // belongsTo: quan he 1-1 giua CartItem va Cart
        // (Cart::class: model Cart, 'cart_id': khoa ngoai trong bang cart_items tham chieu den khoa chinh cua bang carts)
        // 'cart_id': khoa chinh cua bang carts
    }

    public function product() // quan he 1-1 voi Product
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id'); // $this la doi tuong CartItem hien tai
        // belongsTo: quan he 1-1 giua CartItem va Product
        // (Product::class: model Product, 'product_id': khoa ngoai trong bang cart_items tham chieu den khoa chinh cua bang products)
        // 'product_id': khoa chinh cua bang products
    }

    /**
     * Phương thức trợ giúp để lấy giá hiệu quả (từ column price nếu có, hoặc từ product)
     */
    public function getEffectivePrice()
    {
        // Nếu có price được lưu trong cart_items, dùng nó; nếu không thì lấy từ product
        return $this->attributes['price'] ?? ($this->product ? $this->product->price : 0);
    }

    /**
     * Phương thức trợ giúp để tính tổng giá
     */
    public function getItemTotal() // tinh tong gia cua cart item
    {
        return $this->quantity * $this->getEffectivePrice(); // $this->quantity: so luong san pham trong cart item , goi den ham getEffectivePrice de lay gia hieu qua
    }

    protected $casts = [ // dinh dang kieu du lieu cho cac truong
        'price' => 'decimal:2', // dinh dang kieu decimal voi 2 chu so thap phan
        'quantity' => 'integer', // dinh dang kieu integer
    ];

    /**
     * Accessor để tính tổng giá của item này
     */
    public function getTotalPriceAttribute() // lay tong gia cua cart item
    {
        return $this->getItemTotal(); // goi den ham getItemTotal de lay tong gia cua cart item
    }
}
