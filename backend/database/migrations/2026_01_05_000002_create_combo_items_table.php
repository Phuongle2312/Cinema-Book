<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tạo bảng combo_items và migrate dữ liệu từ combos.items JSON
     */
    public function up(): void
    {
        // 1. Tạo bảng combo_items
        Schema::create('combo_items', function (Blueprint $table) {
            $table->id('combo_item_id');
            $table->foreignId('combo_id')->constrained('combos', 'combo_id')->onDelete('cascade');
            $table->string('item_name'); // Tên món: Bắp rang bơ, Coca Cola
            $table->string('item_size', 50)->nullable(); // Size: S, M, L, XL
            $table->integer('quantity')->default(1); // Số lượng
            $table->timestamps();
            
            // Index cho performance
            $table->index('combo_id');
        });

        // 2. Migrate dữ liệu từ combos.items JSON sang combo_items
        $combos = DB::table('combos')->get();
        
        foreach ($combos as $combo) {
            if ($combo->items) {
                // Parse JSON items
                $items = json_decode($combo->items, true);
                
                if (is_array($items)) {
                    foreach ($items as $item) {
                        DB::table('combo_items')->insert([
                            'combo_id' => $combo->combo_id,
                            'item_name' => $item['item'] ?? $item['name'] ?? 'Unknown Item',
                            'item_size' => $item['size'] ?? null,
                            'quantity' => $item['quantity'] ?? 1,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }

        // 3. Xóa cột items JSON từ bảng combos
        Schema::table('combos', function (Blueprint $table) {
            $table->dropColumn('items');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Thêm lại cột items vào combos
        Schema::table('combos', function (Blueprint $table) {
            $table->json('items')->nullable()->after('description');
        });

        // 2. Migrate dữ liệu ngược lại: combo_items -> combos.items JSON
        $combos = DB::table('combos')->get();
        
        foreach ($combos as $combo) {
            $items = DB::table('combo_items')
                ->where('combo_id', $combo->combo_id)
                ->get()
                ->map(function ($item) {
                    return [
                        'item' => $item->item_name,
                        'size' => $item->item_size,
                        'quantity' => $item->quantity,
                    ];
                })
                ->toArray();
            
            DB::table('combos')
                ->where('combo_id', $combo->combo_id)
                ->update(['items' => json_encode($items)]);
        }

        // 3. Xóa bảng combo_items
        Schema::dropIfExists('combo_items');
    }
};
