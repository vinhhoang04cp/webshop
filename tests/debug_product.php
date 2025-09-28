<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;
use App\Models\Category;

echo "=== Debug Product Reorder ===\n";

try {
    // Kiểm tra category có sẵn
    $category = Category::first();
    if (!$category) {
        echo "No category found, creating one...\n";
        $category = Category::create([
            'name' => 'Debug Category',
            'description' => 'Debug category'
        ]);
    }

    // Tạo product mới
    echo "Creating new product...\n";
    $product = Product::create([
        'name' => 'Debug Test Product',
        'description' => 'Debug test description',
        'price' => 100.00,
        'category_id' => $category->category_id,
        'stock_quantity' => 10,
        'image_url' => 'debug.jpg'
    ]);

    echo "Product created with ID: {$product->product_id}\n";

    // Hiển thị tất cả products trước reorder
    echo "\nBefore reorder:\n";
    $products = Product::orderBy('product_id')->get();
    foreach ($products as $prod) {
        echo "ID: {$prod->product_id}, Name: {$prod->name}\n";
    }

    // Gọi reorder
    echo "\nCalling reorderIds()...\n";
    Product::reorderIds();

    // Hiển thị sau reorder
    echo "After reorder:\n";
    $products = Product::orderBy('product_id')->get();
    foreach ($products as $prod) {
        echo "ID: {$prod->product_id}, Name: {$prod->name}\n";
    }

    // Xóa product test
    Product::where('name', 'Debug Test Product')->delete();
    echo "\nDebug test product deleted.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "File: " . $e->getFile() . "\n";
}