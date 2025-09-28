<?php

// Test script để kiểm tra việc reorder IDs của Category
// Chạy script này bằng: php test_category_reorder.php

require_once __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Category;
use Illuminate\Support\Facades\DB;

echo "=== Test Category ID Reordering ===\n";

try {
    // Kiểm tra categories hiện tại
    echo "1. Current categories before test:\n";
    $existingCategories = Category::orderBy('category_id')->get();
    foreach ($existingCategories as $cat) {
        echo "   ID: {$cat->category_id}, Name: {$cat->name}\n";
    }
    
    // Tạo một số categories test mới
    echo "\n2. Creating test categories...\n";
    
    $category1 = Category::create([
        'name' => 'Test Electronics',
        'description' => 'Test Electronic products'
    ]);
    echo "   Created category: {$category1->name} (ID: {$category1->category_id})\n";
    
    $category2 = Category::create([
        'name' => 'Test Books',
        'description' => 'Test Books and literature'
    ]);
    echo "   Created category: {$category2->name} (ID: {$category2->category_id})\n";
    
    $category3 = Category::create([
        'name' => 'Test Clothing',
        'description' => 'Test Fashion and clothing'
    ]);
    echo "   Created category: {$category3->name} (ID: {$category3->category_id})\n";
    
    // Hiển thị IDs hiện tại
    echo "\n3. All categories after creating test data:\n";
    $categories = Category::orderBy('category_id')->get();
    foreach ($categories as $cat) {
        echo "   ID: {$cat->category_id}, Name: {$cat->name}\n";
    }
    
    // Test xóa category test ở giữa (Test Books)
    echo "\n4. Deleting category 'Test Books'...\n";
    $category2->delete();
    
    echo "\n5. Categories after deletion and reorder:\n";
    $categories = Category::orderBy('category_id')->get();
    foreach ($categories as $cat) {
        echo "   ID: {$cat->category_id}, Name: {$cat->name}\n";
    }
    
    // Test tạo category mới
    echo "\n6. Creating new test category 'Test Sports'...\n";
    $category4 = Category::create([
        'name' => 'Test Sports',
        'description' => 'Test Sports equipment'
    ]);
    
    echo "\n7. Final categories:\n";
    $categories = Category::orderBy('category_id')->get();
    foreach ($categories as $cat) {
        echo "   ID: {$cat->category_id}, Name: {$cat->name}\n";
    }
    
    // Cleanup test data
    echo "\n8. Cleaning up test data...\n";
    Category::where('name', 'like', 'Test %')->delete();
    
    echo "\n9. Categories after cleanup:\n";
    $categories = Category::orderBy('category_id')->get();
    foreach ($categories as $cat) {
        echo "   ID: {$cat->category_id}, Name: {$cat->name}\n";
    }
    
    echo "\n=== Test completed successfully! ===\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}