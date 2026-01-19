<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

echo "--- CHECKING PUBLIC APIS ---\n";

function testRoute($uri, $method = 'GET', $params = [])
{
    echo "Testing $method $uri... ";
    try {
        $request = Request::create($uri, $method, $params);
        $response = Route::dispatch($request);

        $content = $response->getContent();
        $data = json_decode($content, true);

        if ($response->status() == 200 && ($data['success'] ?? false) == true) {
            echo "OK. Count: " . (isset($data['data']) ? count($data['data']) : 'N/A') . "\n";
            if (!empty($data['data']) && is_array($data['data']) && isset($data['data'][0])) {
                print_r($data['data'][0]);
            } elseif (!empty($data['data']) && is_object($data['data'])) {
                print_r($data['data']);
            }
        } else {
            echo "FAIL. Status: " . $response->status() . "\n";
            echo "Error: " . substr($content, 0, 200) . "...\n";
        }
    } catch (\Exception $e) {
        echo "EXCEPTION: " . $e->getMessage() . "\n";
    }
}

testRoute('/api/movies');
