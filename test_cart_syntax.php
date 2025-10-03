<?php

// Test file để kiểm tra syntax của Cart model
require_once __DIR__ . '/vendor/autoload.php';

echo "Checking Cart model syntax...\n";

// Check if Cart class exists and has the static method
if (class_exists('App\\Models\\Cart')) {
    echo "✅ Cart class loaded successfully\n";
    
    // Check if reOrderIds method exists
    if (method_exists('App\\Models\\Cart', 'reOrderIds')) {
        echo "✅ reOrderIds method exists\n";
        
        // Check if method is static using reflection
        $reflection = new ReflectionMethod('App\\Models\\Cart', 'reOrderIds');
        if ($reflection->isStatic()) {
            echo "✅ reOrderIds method is properly declared as static\n";
            echo "✅ SUCCESS: All syntax checks passed!\n";
        } else {
            echo "❌ ERROR: reOrderIds method is not static\n";
        }
    } else {
        echo "❌ ERROR: reOrderIds method does not exist\n";
    }
} else {
    echo "❌ ERROR: Cart class not found\n";
}