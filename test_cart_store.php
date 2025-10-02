<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::create(
        '/api/carts',
        'POST',
        [],
        [],
        [],
        ['HTTP_ACCEPT' => 'application/json'],
        json_encode([
            'items' => [
                ['product_id' => 1, 'quantity' => 2],
                ['product_id' => 2, 'quantity' => 1],
            ],
        ])
    )
);

echo 'Status: '.$response->getStatusCode()."\n";
echo 'Content: '.$response->getContent()."\n";

$kernel->terminate($request, $response);
