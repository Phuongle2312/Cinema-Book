<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tạo bảng cities và migrate dữ liệu từ theaters.city
     */
    public function up(): void
    {
        // 1. Tạo bảng cities
        Schema::create('cities', function (Blueprint $table) {
            $table->id('city_id');
            $table->string('name')->unique(); // Hà Nội, TP.HCM, Đà Nẵng
            $table->string('slug')->unique(); // ha-noi, tp-hcm, da-nang
            $table->string('country', 100)->default('Vietnam');
            $table->string('timezone', 50)->default('Asia/Ho_Chi_Minh');
            $table->timestamps();
            
            // Index cho performance
            $table->index('slug');
        });

        // 2. Extract unique cities từ bảng theaters và insert vào cities
        $uniqueCities = DB::table('theaters')
            ->select('city')
            ->distinct()
            ->whereNotNull('city')
            ->get();

        foreach ($uniqueCities as $cityData) {
            DB::table('cities')->insert([
                'name' => $cityData->city,
                'slug' => Str::slug($cityData->city),
                'country' => 'Vietnam',
                'timezone' => 'Asia/Ho_Chi_Minh',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 3. Thêm cột city_id vào bảng theaters
        Schema::table('theaters', function (Blueprint $table) {
            $table->foreignId('city_id')->nullable()->after('name')
                ->constrained('cities', 'city_id')
                ->onDelete('restrict'); // Không cho xóa city nếu còn theater
        });

        // 4. Migrate dữ liệu: map theaters.city -> cities.city_id
        $theaters = DB::table('theaters')->get();
        
        foreach ($theaters as $theater) {
            if ($theater->city) {
                $city = DB::table('cities')
                    ->where('name', $theater->city)
                    ->first();
                
                if ($city) {
                    DB::table('theaters')
                        ->where('theater_id', $theater->theater_id)
                        ->update(['city_id' => $city->city_id]);
                }
            }
        }

        // 5. Đặt city_id thành NOT NULL sau khi migrate xong
        Schema::table('theaters', function (Blueprint $table) {
            $table->foreignId('city_id')->nullable(false)->change();
        });

        // 6. Xóa cột city cũ
        Schema::table('theaters', function (Blueprint $table) {
            $table->dropColumn('city');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Thêm lại cột city vào theaters
        Schema::table('theaters', function (Blueprint $table) {
            $table->string('city')->nullable()->after('name');
        });

        // 2. Migrate dữ liệu ngược lại: cities.name -> theaters.city
        $theaters = DB::table('theaters')->get();
        
        foreach ($theaters as $theater) {
            if ($theater->city_id) {
                $city = DB::table('cities')
                    ->where('city_id', $theater->city_id)
                    ->first();
                
                if ($city) {
                    DB::table('theaters')
                        ->where('theater_id', $theater->theater_id)
                        ->update(['city' => $city->name]);
                }
            }
        }

        // 3. Xóa foreign key và cột city_id
        Schema::table('theaters', function (Blueprint $table) {
            $table->dropForeign(['city_id']);
            $table->dropColumn('city_id');
        });

        // 4. Xóa bảng cities
        Schema::dropIfExists('cities');
    }
};
