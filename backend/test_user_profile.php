<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $user = App\Models\User::where('email', 'test@example.com')->first();
    if ($user) {
        echo "User found: " . $user->name . "\n";
        // Attempting to serialize to catch relationship issues in appends or casts
        $json = json_encode($user->toArray());
        echo "User serialized successfully\n";
    } else {
        echo "Test user not found\n";
    }
} catch (\Exception $e) {
    echo "Error during serialization: " . $e->getMessage() . "\n";
}
