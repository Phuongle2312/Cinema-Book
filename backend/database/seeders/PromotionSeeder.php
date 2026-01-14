<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PromotionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('promotions')->updateOrInsert(
            ['code' => 'SUMMER2024'],
            [
                'description' => 'Summer Sale Discount 20%',
                'discount_type' => 'percentage',
                'discount_value' => 20,
                'min_purchase_amount' => 100000,
                'max_discount_amount' => 50000,
                'valid_from' => Carbon::now(),
                'valid_to' => Carbon::now()->addMonths(3),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('promotions')->updateOrInsert(
            ['code' => 'WELCOME10'],
            [
                'description' => 'Welcome new users, discount 10k',
                'discount_type' => 'fixed',
                'discount_value' => 10000,
                'min_purchase_amount' => 0,
                'valid_from' => Carbon::now(),
                'valid_to' => Carbon::now()->addYear(),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('promotions')->updateOrInsert(
            ['code' => 'VIPMEMBER'],
            [
                'description' => 'VIP Member Exclusive 15%',
                'discount_type' => 'percentage',
                'discount_value' => 15,
                'min_purchase_amount' => 200000,
                'valid_from' => Carbon::now(),
                'valid_to' => Carbon::now()->addMonths(6),
                'is_active' => false, // Inactive example
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
