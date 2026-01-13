<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('movie_cast');
        Schema::dropIfExists('casts');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Không thể rollback vì data bị mất
    }
};
