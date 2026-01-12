<?php

require __DIR__ . '/backend/vendor/autoload.php';
$app = require_once __DIR__ . '/backend/bootstrap/app.php';

use App\Http\Controllers\Api\MovieController;
use Illuminate\Support\Facades\Request;

try {
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle(
        $request = Illuminate\Http\Request::create('/api/movies/featured', 'GET')
    );

    $data = [
        'status' => $response->getStatusCode(),
        'content' => $response->getContent()
    ];
    file_put_contents('debug_output.json', json_encode($data, JSON_PRETTY_PRINT));
} catch (Throwable $e) {
    $error = [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ];
    file_put_contents('debug_output.json', json_encode($error, JSON_PRETTY_PRINT));
}
