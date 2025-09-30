<?php

class APITester 
{
    private $baseUrl;
    
    public function __construct($baseUrl = 'http://127.0.0.1:8080') 
    {
        $this->baseUrl = $baseUrl;
    }
    
    public function testEndpoint($method, $endpoint, $data = null) 
    {
        $url = $this->baseUrl . $endpoint;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        
        switch (strtoupper($method)) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                if ($data) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
                break;
            case 'PUT':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                if ($data) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
                break;
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        return [
            'method' => $method,
            'endpoint' => $endpoint,
            'status_code' => $httpCode,
            'response' => $response,
            'error' => $error
        ];
    }
    
    public function testAllAPIs() 
    {
        echo "=== TESTING ALL APIs ===\n\n";
        
        $results = [];
        
        // Test Categories API
        echo "1. TESTING CATEGORIES API\n";
        echo "------------------------\n";
        
        // GET categories
        $result = $this->testEndpoint('GET', '/api/categories');
        $results['categories']['get_all'] = $result;
        echo "GET /api/categories: {$result['status_code']} - " . ($result['error'] ? $result['error'] : 'Success') . "\n";
        
        // POST category
        $categoryData = [
            'name' => 'Test Category ' . time(),
            'description' => 'Test category description'
        ];
        $result = $this->testEndpoint('POST', '/api/categories', $categoryData);
        $results['categories']['post'] = $result;
        echo "POST /api/categories: {$result['status_code']} - " . ($result['error'] ? $result['error'] : 'Success') . "\n";
        
        // Extract created category ID for further testing
        $createdCategoryId = null;
        if ($result['status_code'] == 201) {
            $responseData = json_decode($result['response'], true);
            if (isset($responseData['data']['id'])) {
                $createdCategoryId = $responseData['data']['id'];
            }
        }
        
        if ($createdCategoryId) {
            // GET specific category
            $result = $this->testEndpoint('GET', "/api/categories/{$createdCategoryId}");
            $results['categories']['get_one'] = $result;
            echo "GET /api/categories/{$createdCategoryId}: {$result['status_code']} - " . ($result['error'] ? $result['error'] : 'Success') . "\n";
            
            // PUT category
            $updateData = [
                'name' => 'Updated Test Category ' . time(),
                'description' => 'Updated test category description'
            ];
            $result = $this->testEndpoint('PUT', "/api/categories/{$createdCategoryId}", $updateData);
            $results['categories']['put'] = $result;
            echo "PUT /api/categories/{$createdCategoryId}: {$result['status_code']} - " . ($result['error'] ? $result['error'] : 'Success') . "\n";
            
            // DELETE category
            $result = $this->testEndpoint('DELETE', "/api/categories/{$createdCategoryId}");
            $results['categories']['delete'] = $result;
            echo "DELETE /api/categories/{$createdCategoryId}: {$result['status_code']} - " . ($result['error'] ? $result['error'] : 'Success') . "\n";
        }
        
        echo "\n";
        
        // Test Products API
        echo "2. TESTING PRODUCTS API\n";
        echo "----------------------\n";
        
        // GET products
        $result = $this->testEndpoint('GET', '/api/products');
        $results['products']['get_all'] = $result;
        echo "GET /api/products: {$result['status_code']} - " . ($result['error'] ? $result['error'] : 'Success') . "\n";
        
        // POST product
        $productData = [
            'name' => 'Test Product ' . time(),
            'description' => 'Test product description',
            'price' => 99.99,
            'category_id' => 1 // Assuming category with ID 1 exists
        ];
        $result = $this->testEndpoint('POST', '/api/products', $productData);
        $results['products']['post'] = $result;
        echo "POST /api/products: {$result['status_code']} - " . ($result['error'] ? $result['error'] : 'Success') . "\n";
        
        // Extract created product ID
        $createdProductId = null;
        if ($result['status_code'] == 201) {
            $responseData = json_decode($result['response'], true);
            if (isset($responseData['data']['id'])) {
                $createdProductId = $responseData['data']['id'];
            }
        }
        
        if ($createdProductId) {
            // GET specific product
            $result = $this->testEndpoint('GET', "/api/products/{$createdProductId}");
            $results['products']['get_one'] = $result;
            echo "GET /api/products/{$createdProductId}: {$result['status_code']} - " . ($result['error'] ? $result['error'] : 'Success') . "\n";
            
            // PUT product
            $updateData = [
                'name' => 'Updated Test Product ' . time(),
                'description' => 'Updated test product description',
                'price' => 149.99,
                'category_id' => 1
            ];
            $result = $this->testEndpoint('PUT', "/api/products/{$createdProductId}", $updateData);
            $results['products']['put'] = $result;
            echo "PUT /api/products/{$createdProductId}: {$result['status_code']} - " . ($result['error'] ? $result['error'] : 'Success') . "\n";
            
            // DELETE product
            $result = $this->testEndpoint('DELETE', "/api/products/{$createdProductId}");
            $results['products']['delete'] = $result;
            echo "DELETE /api/products/{$createdProductId}: {$result['status_code']} - " . ($result['error'] ? $result['error'] : 'Success') . "\n";
        }
        
        echo "\n";
        
        // Test Orders API
        echo "3. TESTING ORDERS API\n";
        echo "--------------------\n";
        
        $result = $this->testEndpoint('GET', '/api/orders');
        $results['orders']['get_all'] = $result;
        echo "GET /api/orders: {$result['status_code']} - " . ($result['error'] ? $result['error'] : 'Success') . "\n";
        
        $orderData = [
            'user_id' => 1,
            'total_amount' => 199.99,
            'status' => 'pending'
        ];
        $result = $this->testEndpoint('POST', '/api/orders', $orderData);
        $results['orders']['post'] = $result;
        echo "POST /api/orders: {$result['status_code']} - " . ($result['error'] ? $result['error'] : 'Success') . "\n";
        
        echo "\n";
        
        // Test OrderItems API
        echo "4. TESTING ORDER-ITEMS API\n";
        echo "-------------------------\n";
        
        $result = $this->testEndpoint('GET', '/api/order-items');
        $results['order_items']['get_all'] = $result;
        echo "GET /api/order-items: {$result['status_code']} - " . ($result['error'] ? $result['error'] : 'Success') . "\n";
        
        echo "\n";
        
        // Test ProductDetails API
        echo "5. TESTING PRODUCT-DETAILS API\n";
        echo "-----------------------------\n";
        
        $result = $this->testEndpoint('GET', '/api/product-details');
        $results['product_details']['get_all'] = $result;
        echo "GET /api/product-details: {$result['status_code']} - " . ($result['error'] ? $result['error'] : 'Success') . "\n";
        
        echo "\n";
        
        return $results;
    }
    
    public function generateSummary($results) 
    {
        echo "=== SUMMARY REPORT ===\n";
        echo "=====================\n\n";
        
        $totalTests = 0;
        $successfulTests = 0;
        $failedTests = 0;
        
        foreach ($results as $apiName => $endpoints) {
            echo strtoupper($apiName) . " API:\n";
            foreach ($endpoints as $action => $result) {
                $totalTests++;
                $status = $result['status_code'];
                $success = ($status >= 200 && $status < 300);
                
                if ($success) {
                    $successfulTests++;
                    echo "  ✅ {$action}: {$status}\n";
                } else {
                    $failedTests++;
                    echo "  ❌ {$action}: {$status}" . ($result['error'] ? " - {$result['error']}" : "") . "\n";
                }
            }
            echo "\n";
        }
        
        echo "OVERALL STATISTICS:\n";
        echo "Total Tests: {$totalTests}\n";
        echo "Successful: {$successfulTests}\n";
        echo "Failed: {$failedTests}\n";
        echo "Success Rate: " . round(($successfulTests / $totalTests) * 100, 2) . "%\n";
    }
}

// Run the tests
$tester = new APITester();
$results = $tester->testAllAPIs();
$tester->generateSummary($results);

?>