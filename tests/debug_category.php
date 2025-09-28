<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Category;

echo "=== Debug Category Reorder ===\n";

// Tạo category mới
echo "Creating new category...\n";
$category = Category::create([
    'name' => 'Debug Test Category',
    'description' => 'Debug test description'
]);

echo "Category created with ID: {$category->category_id}\n";

// Hiển thị tất cả categories trước reorder
echo "\nBefore reorder:\n";
$categories = Category::orderBy('category_id')->get();
foreach ($categories as $cat) {
    echo "ID: {$cat->category_id}, Name: {$cat->name}\n";
}

// Gọi reorder
echo "\nCalling reorderIds()...\n";
Category::reorderIds();

// Hiển thị sau reorder
echo "After reorder:\n";
$categories = Category::orderBy('category_id')->get();
foreach ($categories as $cat) {
    echo "ID: {$cat->category_id}, Name: {$cat->name}\n";
}

// Xóa category test
Category::where('name', 'Debug Test Category')->delete();
echo "\nDebug test category deleted.\n";