<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

echo "Checking tables...\n";

if (! Schema::hasTable('sessions')) {
    echo "Creating sessions table...\n";
    Schema::create('sessions', function (Blueprint $table) {
        $table->string('id')->primary();
        $table->foreignId('user_id')->nullable()->index();
        $table->string('ip_address', 45)->nullable();
        $table->text('user_agent')->nullable();
        $table->longText('payload');
        $table->integer('last_activity')->index();
    });
    echo "Sessions table created.\n";
} else {
    echo "Sessions table exists.\n";
}

if (! Schema::hasTable('cache')) {
    echo "Creating cache table...\n";
    Schema::create('cache', function (Blueprint $table) {
        $table->string('key')->primary();
        $table->mediumText('value');
        $table->integer('expiration');
    });
    echo "Cache table created.\n";
}

if (! Schema::hasTable('cache_locks')) {
    echo "Creating cache_locks table...\n";
    Schema::create('cache_locks', function (Blueprint $table) {
        $table->string('key')->primary();
        $table->string('owner');
        $table->integer('expiration');
    });
    echo "Cache_locks table created.\n";
}
