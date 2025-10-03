<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class ProductDetail extends Model
{
    use HasFactory;

    protected $table = 'product_details';

    protected $primaryKey = 'detail_id';

    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'size',
        'color',
        'material',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'detail_id', 'detail_id');
    }
    public function stocks()
    {
        return $this->hasMany(Stock::class, 'detail_id', 'detail_id');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'detail_id', 'detail_id');
    }

    /**
     * DEPRECATED & DISABLED: Reorder product detail IDs
     * WARNING: This method is DANGEROUS and can cause data loss!
     */
    public static function reorderIds() {
        \Log::warning('ProductDetail::reorderIds() called but disabled for safety. Consider removing this method entirely.');
        return;
        
        // DISABLED FOR SAFETY - manipulating primary keys is dangerous
        // Tắt tạm thời kiểm tra foreign key để có thể cập nhật ID
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        // Lấy tất cả product_details theo thứ tự ID hiện tại
        $productDetails = ProductDetail::orderBy('detail_id')->get();

        // Cập nhật lại ID để đảm bảo tính liên tục
        $newId = 1;
        foreach ($productDetails as $detail) {
            if ($detail->detail_id != $newId) {
                DB::table('product_details')->where('detail_id', $detail->detail_id)->update(['detail_id' => $newId]);
            }
            $newId++;
        }

        // Bật lại kiểm tra foreign key
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
