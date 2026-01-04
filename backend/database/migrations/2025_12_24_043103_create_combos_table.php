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
        Schema::create('combos', function (Blueprint $table) {
            $table->id('combo_id');
            $table->string('name'); // Tên combo: "Combo 1", "Bắp nước lớn"
            $table->text('description')->nullable(); // Mô tả chi tiết
            
            // Danh sách items trong combo (JSON)
            // Ví dụ: [{"item": "Bắp rang bơ", "size": "L"}, {"item": "Coca Cola", "size": "L"}]
            $table->json('items');
            
            $table->decimal('price', 10, 0); // Giá combo (VNĐ)
            $table->string('image_url')->nullable(); // Hình ảnh combo
            $table->boolean('is_available')->default(true); // Còn bán không
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('combos');
    }
};
