<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;
use App\Models\Category;

echo "=== Test Product ID Reordering ===\n";

try {
    // Kiểm tra categories có sẵn để test
    $categories = Category::all();
    if ($categories->isEmpty()) {
        echo "Creating test categories first...\n";
        $category = Category::create([
            'name' => 'Test Category for Products',
            'description' => 'Test category description'
        ]);
        $categoryId = $category->category_id;
    } else {
        $categoryId = $categories->first()->category_id;
        echo "Using existing category ID: {$categoryId}\n";
    }

    // Kiểm tra products hiện tại
    echo "\n1. Current products before test:\n";
    $existingProducts = Product::orderBy('product_id')->get();
    foreach ($existingProducts as $prod) {
        echo "   ID: {$prod->product_id}, Name: {$prod->name}\n";
    }
    
    // Tạo một số products test mới
    echo "\n2. Creating test products...\n";
    
    $product1 = Product::create([
        'name' => 'Test Product 1',
        'description' => 'Test Product 1 description',
        'price' => 100.00,
        'category_id' => $categoryId,
        'stock_quantity' => 10,
        'image_url' => 'test1.jpg'
    ]);
    echo "   Created product: {$product1->name} (ID: {$product1->product_id})\n";
    
    $product2 = Product::create([
        'name' => 'Test Product 2',
        'description' => 'Test Product 2 description',
        'price' => 200.00,
        'category_id' => $categoryId,
        'stock_quantity' => 20,
        'image_url' => 'test2.jpg'
    ]);
    echo "   Created product: {$product2->name} (ID: {$product2->product_id})\n";
    
    $product3 = Product::create([
        'name' => 'Test Product 3',
        'description' => 'Test Product 3 description',
        'price' => 300.00,
        'category_id' => $categoryId,
        'stock_quantity' => 30,
        'image_url' => 'test3.jpg'
    ]);
    echo "   Created product: {$product3->name} (ID: {$product3->product_id})\n";
    
    // Hiển thị IDs hiện tại
    echo "\n3. All products after creating test data:\n";
    $products = Product::orderBy('product_id')->get();
    foreach ($products as $prod) {
        echo "   ID: {$prod->product_id}, Name: {$prod->name}\n";
    }
    
    // Test xóa product test ở giữa (Test Product 2)
    echo "\n4. Deleting product 'Test Product 2'...\n";
    $product2->delete();
    
    echo "\n5. Products after deletion and reorder:\n";
    $products = Product::orderBy('product_id')->get();
    foreach ($products as $prod) {
        echo "   ID: {$prod->product_id}, Name: {$prod->name}\n";
    }
    
    // Test tạo product mới
    echo "\n6. Creating new test product 'Test Product 4'...\n";
    $product4 = Product::create([
        'name' => 'Test Product 4',
        'description' => 'Test Product 4 description',
        'price' => 400.00,
        'category_id' => $categoryId,
        'stock_quantity' => 40,
        'image_url' => 'test4.jpg'
    ]);
    
    echo "\n7. Final products:\n";
    $products = Product::orderBy('product_id')->get();
    foreach ($products as $prod) {
        echo "   ID: {$prod->product_id}, Name: {$prod->name}\n";
    }
    
    // Cleanup test data
    echo "\n8. Cleaning up test data...\n";
    Product::where('name', 'like', 'Test Product %')->delete();
    
    echo "\n9. Products after cleanup:\n";
    $products = Product::orderBy('product_id')->get();
    foreach ($products as $prod) {
        echo "   ID: {$prod->product_id}, Name: {$prod->name}\n";
    }
    
    echo "\n=== Test completed successfully! ===\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}