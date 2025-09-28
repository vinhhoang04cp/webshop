<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories'; // Ten bang trong CSDL

    protected $primaryKey = 'category_id';   // Khoa chinh cua bang

    public $timestamps = true; // Tu dong quan ly created_at va updated_at

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Reorder category IDs to ensure sequential numbering (1, 2, 3, ...)
     * This method will be called after create, update, or delete operations
     */
    public static function reorderIds()
    {
        // Tắt tạm thời kiểm tra foreign key để có thể cập nhật ID
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        
        // Lấy tất cả categories theo thứ tự ID hiện tại
        $categories = self::orderBy('category_id', 'asc')->get();
        
        if ($categories->isEmpty()) {
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
            return;
        }
        
        // Sử dụng một giá trị offset lớn để tránh trùng lặp
        $offset = 1000000;
        
        // Đầu tiên, cập nhật tất cả IDs thành giá trị lớn để tránh trùng lặp
        foreach ($categories as $index => $category) {
            $tempId = $offset + $index + 1;
            DB::table('categories')
                ->where('category_id', $category->category_id)
                ->update(['category_id' => $tempId]);
                
            // Cập nhật foreign key references nếu có
            DB::table('products')
                ->where('category_id', $category->category_id)
                ->update(['category_id' => $tempId]);
        }
        
        // Sau đó cập nhật thành giá trị cuối cùng (1, 2, 3, ...)
        foreach ($categories as $index => $category) {
            $newId = $index + 1;
            $tempId = $offset + $index + 1;
            
            DB::table('categories')
                ->where('category_id', $tempId)
                ->update(['category_id' => $newId]);
                
            // Cập nhật foreign key references
            DB::table('products')
                ->where('category_id', $tempId)
                ->update(['category_id' => $newId]);
        }
        
        // Reset AUTO_INCREMENT của bảng để ID tiếp theo sẽ đúng
        $maxId = $categories->count();
        DB::statement("ALTER TABLE categories AUTO_INCREMENT = " . ($maxId + 1));
        
        // Bật lại kiểm tra foreign key
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id', 'category_id'); // Moi quan he 1-n voi Product
    }
}
