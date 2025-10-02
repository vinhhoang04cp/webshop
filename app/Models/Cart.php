<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $table = 'carts';

    protected $primaryKey = 'cart_id';

    protected $fillable = [
        'user_id',
    ];

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

    public function reOrderIds()
    {

        \DB::statement('SET FOREIGN_KEY_CHECKS = 0'); // tat kiem tra foreign key

        $carts = self::orderBy('cart_id')->get(); // lay tat ca cart hien tai theo thu tu ID
        $newId = 1; // ID moi bat dau tu 1

        foreach ($carts as $cart) {
            \DB::table($this->table)->where('cart_id', $cart->cart_id)->update(['cart_id' => $newId]);
            $newId++;
        }

        \DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
