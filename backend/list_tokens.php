<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tokens = DB::table('personal_access_tokens')->get();
foreach ($tokens as $token) {
    print_r($token);
}
