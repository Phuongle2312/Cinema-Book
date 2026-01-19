<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('offers')) {
            Schema::create('offers', function (Blueprint $table) {
                $table->id();
                $table->string('title')->nullable();
                $table->string('code')->unique()->nullable();
                $table->text('description')->nullable();
                $table->string('image_url')->nullable();
                $table->string('tag')->nullable();
                $table->string('date_display')->nullable();
                $table->string('type')->default('offer'); // offer/event
                $table->string('discount_type')->default('fixed'); // fixed/percentage
                $table->decimal('discount_value', 10, 2)->default(0);
                $table->decimal('min_purchase_amount', 10, 2)->nullable();
                $table->decimal('max_discount_amount', 10, 2)->nullable();
                $table->dateTime('valid_from')->nullable();
                $table->dateTime('valid_to')->nullable();
                $table->integer('max_uses')->nullable();
                $table->integer('current_uses')->default(0);
                $table->boolean('is_active')->default(true);
                $table->boolean('is_system_wide')->default(false);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offers_table_repair');
    }
};
