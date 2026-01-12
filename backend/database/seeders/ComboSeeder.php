<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ComboSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('combo_items')->truncate();
        DB::table('combos')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $combos = [
            [
                'name' => 'Combo 1 - Bắp Nước Nhỏ',
                'description' => 'Bắp rang bơ nhỏ + Nước ngọt nhỏ',
                'items' => [
                    ['item' => 'Bắp rang bơ', 'size' => 'S'],
                    ['item' => 'Coca Cola', 'size' => 'S'],
                ],
                'price' => 50000,
                'image_url' => 'https://api.chieu.id.vn/storage/combos/combo1.png',
                'is_available' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Combo 2 - Bắp Nước Lớn',
                'description' => 'Bắp rang bơ lớn + Nước ngọt lớn',
                'items' => [
                    ['item' => 'Bắp rang bơ', 'size' => 'L'],
                    ['item' => 'Coca Cola', 'size' => 'L'],
                ],
                'price' => 80000,
                'image_url' => 'https://api.chieu.id.vn/storage/combos/combo2.png',
                'is_available' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Combo 3 - Đôi Bạn Thân',
                'description' => '2 Bắp rang bơ lớn + 2 Nước ngọt lớn',
                'items' => [
                    ['item' => 'Bắp rang bơ', 'size' => 'L', 'quantity' => 2],
                    ['item' => 'Coca Cola', 'size' => 'L', 'quantity' => 2],
                ],
                'price' => 150000,
                'image_url' => 'https://api.chieu.id.vn/storage/combos/combo3.png',
                'is_available' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($combos as $comboData) {
            // Extract items
            $items = $comboData['items'];
            unset($comboData['items']);

            // Insert Combo
            $comboId = DB::table('combos')->insertGetId($comboData);

            // Insert Combo Items
            foreach ($items as $item) {
                DB::table('combo_items')->insert([
                    'combo_id' => $comboId,
                    'item_name' => $item['item'],
                    'item_size' => $item['size'],
                    'quantity' => $item['quantity'] ?? 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
