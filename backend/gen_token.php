<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Models\User::where('email', 'test@example.com')->first();
if ($user) {
    echo $user->createToken('test')->plainTextToken;
} else {
    echo "User not found";
}
