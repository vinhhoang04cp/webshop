<?php

// Test file để kiểm tra Cart::reOrderIds() method
require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Cart;

// Load Laravel environment
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "Testing Cart::reOrderIds() method...\n";
    
    // Test gọi static method
    Cart::reOrderIds();
    
    echo "✅ SUCCESS: Cart::reOrderIds() method executed without errors!\n";
    echo "✅ The method is now properly declared as static.\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "❌ File: " . $e->getFile() . "\n";
    echo "❌ Line: " . $e->getLine() . "\n";
}