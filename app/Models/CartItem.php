<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CartItem extends Model
{
    use HasFactory;

    protected $table = 'cart_items';

    protected $primaryKey = 'cart_item_id';

    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
        'price',
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class, 'cart_id', 'cart_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    public function price()
    {
        // Nếu có price được lưu trong cart_items, dùng nó; nếu không thì lấy từ product
        return $this->price ?? ($this->product ? $this->product->price : 0);
    }

    public function totalPrice()
    {
        return $this->quantity * ($this->price ?? ($this->product ? $this->product->price : 0));
    }

    protected $appends = ['price', 'total_price'];

    protected $casts = [
        'price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function getPriceAttribute()
    {
        return $this->price();
    }

    public function getTotalPriceAttribute()
    {
        return $this->totalPrice();
    }

    /**
     * Reorder cart item IDs to ensure sequential numbering (1, 2, 3, ...).
     * This method will be called after create, update, or delete operations.
     */
    /**
     * DEPRECATED & DISABLED: Reorder cart item IDs
     * WARNING: This method is DANGEROUS and can cause data loss!
     */
    public static function reorderIds()
    {
        \Log::warning('CartItem::reorderIds() called but disabled for safety. Consider removing this method entirely.');
        return;
        
        // DISABLED FOR SAFETY - manipulating primary keys is dangerous
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        $cartItems = self::orderBy('cart_item_id', 'asc')->get();

        if ($cartItems->isEmpty()) {
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
            return;
        }

        $offset = 1000000;

        // First pass: assign temporary IDs
        foreach ($cartItems as $index => $cartItem) {
            $tempId = $offset + $index + 1;
            DB::table('cart_items')
                ->where('cart_item_id', $cartItem->cart_item_id)
                ->update(['cart_item_id' => $tempId]);
        }

        // Second pass: assign final sequential IDs
        foreach ($cartItems as $index => $cartItem) {
            $newId = $index + 1;
            $tempId = $offset + $index + 1;

            DB::table('cart_items')
                ->where('cart_item_id', $tempId)
                ->update(['cart_item_id' => $newId]);
        }

        $maxId = $cartItems->count();
        DB::statement('ALTER TABLE cart_items AUTO_INCREMENT = ' . ($maxId + 1));

        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
