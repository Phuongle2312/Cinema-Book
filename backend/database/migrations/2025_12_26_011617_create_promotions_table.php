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
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->comment('Mã khuyến mãi');
            $table->text('description')->nullable()->comment('Mô tả chi tiết');
            $table->enum('discount_type', ['percentage', 'fixed'])->default('percentage')->comment('Loại giảm giá: % hoặc số tiền cố định');
            $table->decimal('discount_value', 10, 2)->comment('Giá trị giảm giá');
            $table->decimal('min_purchase_amount', 10, 2)->nullable()->comment('Số tiền tối thiểu để áp dụng');
            $table->decimal('max_discount_amount', 10, 2)->nullable()->comment('Số tiền giảm tối đa (cho percentage)');
            $table->timestamp('valid_from')->comment('Ngày bắt đầu hiệu lực');
            $table->timestamp('valid_to')->comment('Ngày kết thúc hiệu lực');
            $table->integer('max_uses')->nullable()->comment('Số lần sử dụng tối đa');
            $table->integer('current_uses')->default(0)->comment('Số lần đã sử dụng');
            $table->boolean('is_active')->default(true)->comment('Trạng thái kích hoạt');
            $table->timestamps();
            
            $table->index('code');
            $table->index(['valid_from', 'valid_to']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
