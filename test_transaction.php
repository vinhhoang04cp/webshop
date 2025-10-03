<?php

// Test script để verify transaction handling
require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Load Laravel environment
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "Testing transaction level detection...\n";
    
    // Test bên ngoài transaction
    echo "Transaction level outside: " . DB::transactionLevel() . "\n";
    
    // Test bên trong transaction
    DB::beginTransaction();
    echo "Transaction level inside: " . DB::transactionLevel() . "\n";
    DB::rollback();
    
    echo "✅ SUCCESS: Transaction level detection works!\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}