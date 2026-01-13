<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Thêm indexes để tối ưu performance cho các query thường dùng
     */
    public function up(): void
    {
        // Skipped
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Skipped
    }

    /**
     * Helper method to check if index exists
     */
    private function hasIndex(string $table, string $index): bool
    {
        $indexes = Schema::getConnection()
            ->getDoctrineSchemaManager()
            ->listTableIndexes($table);
        
        return isset($indexes[$index]);
    }
};
