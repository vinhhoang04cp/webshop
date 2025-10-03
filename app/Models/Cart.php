<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Cart extends Model
{
    use HasFactory;

    protected $table = 'carts';

    protected $primaryKey = 'cart_id';

    protected $fillable = [
        'user_id',
        'total_amount',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'cart_items', 'cart_id', 'product_id')
            ->withPivot('quantity', 'price')
            ->withTimestamps();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function items()
    {
        return $this->hasMany(CartItem::class, 'cart_id', 'cart_id');
    }

    public function totalPrice()
    {
        return $this->items->sum(function ($item) {
            return $item->quantity * $item->price;
        });
    }

    public function totalItems()
    {
        return $this->items->sum('quantity');
    }

    public static function reOrderIds()
    {
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');

            $carts = self::orderBy('cart_id')->get();
            
            if ($carts->isEmpty()) {
                DB::statement('SET FOREIGN_KEY_CHECKS = 1');
                return;
            }

            $offset = 1000000;

            // First pass: assign temporary IDs
            foreach ($carts as $index => $cart) {
                $tempId = $offset + $index + 1;
                DB::table('carts')->where('cart_id', $cart->cart_id)->update(['cart_id' => $tempId]);
                
                // Update foreign keys in cart_items
                DB::table('cart_items')->where('cart_id', $cart->cart_id)->update(['cart_id' => $tempId]);
            }

            // Second pass: assign final sequential IDs
            foreach ($carts as $index => $cart) {
                $newId = $index + 1;
                $tempId = $offset + $index + 1;
                
                DB::table('carts')->where('cart_id', $tempId)->update(['cart_id' => $newId]);
                
                // Update foreign keys in cart_items
                DB::table('cart_items')->where('cart_id', $tempId)->update(['cart_id' => $newId]);
            }

            $maxId = $carts->count();
            DB::statement('ALTER TABLE carts AUTO_INCREMENT = ' . ($maxId + 1));

            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
            
        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
            throw $e;
        }
    }
}
