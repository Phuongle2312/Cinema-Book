<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$token = '3|Mri6lYLSiVy7Kx7rXdxiWTQK6P8BNBREdN0IhFHodcc523493';

$request = Illuminate\Http\Request::create('/api/user/profile', 'GET');
$request->headers->set('Authorization', 'Bearer ' . $token);
$request->headers->set('Accept', 'application/json');

$response = $kernel->handle($request);
echo "Status: " . $response->getStatusCode() . "\n";
echo "Content: " . $response->getContent() . "\n";

if ($response->getStatusCode() == 500) {
    // Try to find the error in log if it was recorded
    echo "Check laravel.log for details.\n";
}
