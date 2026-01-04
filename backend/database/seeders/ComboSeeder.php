<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Combo;

class ComboSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Tạo dữ liệu mẫu cho các combo đồ ăn, nước uống
     */
    public function run(): void
    {
        $combos = [
            [
                'name' => 'Combo 1 - Bắp Nước Nhỏ',
                'description' => 'Bắp rang bơ nhỏ + Nước ngọt nhỏ',
                'items' => [
                    ['item' => 'Bắp rang bơ', 'size' => 'S'],
                    ['item' => 'Coca Cola', 'size' => 'S'],
                ],
                'price' => 50000,
                'image_url' => '/images/combos/combo1.jpg',
                'is_available' => true,
            ],
            [
                'name' => 'Combo 2 - Bắp Nước Lớn',
                'description' => 'Bắp rang bơ lớn + Nước ngọt lớn',
                'items' => [
                    ['item' => 'Bắp rang bơ', 'size' => 'L'],
                    ['item' => 'Coca Cola', 'size' => 'L'],
                ],
                'price' => 80000,
                'image_url' => '/images/combos/combo2.jpg',
                'is_available' => true,
            ],
            [
                'name' => 'Combo 3 - Bắp Đôi',
                'description' => '2 Bắp rang bơ lớn + 2 Nước ngọt lớn',
                'items' => [
                    ['item' => 'Bắp rang bơ', 'size' => 'L', 'quantity' => 2],
                    ['item' => 'Coca Cola', 'size' => 'L', 'quantity' => 2],
                ],
                'price' => 150000,
                'image_url' => '/images/combos/combo3.jpg',
                'is_available' => true,
            ],
            [
                'name' => 'Combo 4 - Snack Mix',
                'description' => 'Bắp rang + Nachos + 2 Nước ngọt',
                'items' => [
                    ['item' => 'Bắp rang bơ', 'size' => 'M'],
                    ['item' => 'Nachos phô mai', 'size' => 'M'],
                    ['item' => 'Pepsi', 'size' => 'M', 'quantity' => 2],
                ],
                'price' => 120000,
                'image_url' => '/images/combos/combo4.jpg',
                'is_available' => true,
            ],
            [
                'name' => 'Combo 5 - Family',
                'description' => 'Combo gia đình: 3 Bắp lớn + 3 Nước ngọt lớn + Khoai tây chiên',
                'items' => [
                    ['item' => 'Bắp rang bơ', 'size' => 'L', 'quantity' => 3],
                    ['item' => 'Nước ngọt', 'size' => 'L', 'quantity' => 3],
                    ['item' => 'Khoai tây chiên', 'size' => 'L'],
                ],
                'price' => 250000,
                'image_url' => '/images/combos/combo5.jpg',
                'is_available' => true,
            ],
            [
                'name' => 'Combo 6 - Premium',
                'description' => 'Combo cao cấp: Bắp caramel + Nước ép trái cây + Hotdog',
                'items' => [
                    ['item' => 'Bắp caramel', 'size' => 'L'],
                    ['item' => 'Nước ép cam', 'size' => 'M'],
                    ['item' => 'Hotdog phô mai', 'size' => 'Regular'],
                ],
                'price' => 110000,
                'image_url' => '/images/combos/combo6.jpg',
                'is_available' => true,
            ],
        ];

        foreach ($combos as $combo) {
            Combo::create($combo);
        }
    }
}
