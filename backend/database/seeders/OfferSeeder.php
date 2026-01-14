<?php

namespace Database\Seeders;

use App\Models\Offer;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class OfferSeeder extends Seeder
{
    public function run(): void
    {
        Offer::updateOrCreate(
            ['description' => 'Ưu đãi Khai trương - Giảm 20% cho tất cả đơn hàng'],
            [
                'code' => null, // System-wide doesn't need a code
                'discount_type' => 'percentage',
                'discount_value' => 20,
                'min_purchase_amount' => 0,
                'max_discount_amount' => 50000,
                'valid_from' => Carbon::now()->subDays(1),
                'valid_to' => Carbon::now()->addDays(30),
                'is_active' => true,
                'is_system_wide' => true,
            ]
        );

        Offer::updateOrCreate(
            ['description' => 'Ưu đãi Đặc biệt - Giảm 30.000đ cho đơn hàng trên 200.000đ'],
            [
                'code' => null,
                'discount_type' => 'fixed',
                'discount_value' => 30000,
                'min_purchase_amount' => 200000,
                'max_discount_amount' => null,
                'valid_from' => Carbon::now()->subDays(1),
                'valid_to' => Carbon::now()->addDays(15),
                'is_active' => true,
                'is_system_wide' => true,
            ]
        );
    }
}
