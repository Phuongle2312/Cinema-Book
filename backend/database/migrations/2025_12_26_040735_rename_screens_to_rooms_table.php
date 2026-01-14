<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Bước 1: Đổi tên bảng screens thành rooms
        Schema::rename('screens', 'rooms');

        // Bước 2: Đổi tên primary key trong bảng rooms
        DB::statement('ALTER TABLE rooms CHANGE screen_id room_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');

        // Bước 3: Đổi tên foreign key trong showtimes
        DB::statement('ALTER TABLE showtimes DROP FOREIGN KEY showtimes_screen_id_foreign');
        DB::statement('ALTER TABLE showtimes CHANGE screen_id room_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE showtimes ADD CONSTRAINT showtimes_room_id_foreign FOREIGN KEY (room_id) REFERENCES rooms(room_id) ON DELETE CASCADE');

        // Bước 4: Đổi tên foreign key trong seats
        DB::statement('ALTER TABLE seats DROP FOREIGN KEY seats_screen_id_foreign');
        DB::statement('ALTER TABLE seats CHANGE screen_id room_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE seats ADD CONSTRAINT seats_room_id_foreign FOREIGN KEY (room_id) REFERENCES rooms(room_id) ON DELETE CASCADE');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback: Đổi lại foreign key trong seats
        DB::statement('ALTER TABLE seats DROP FOREIGN KEY seats_room_id_foreign');
        DB::statement('ALTER TABLE seats CHANGE room_id screen_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE seats ADD CONSTRAINT seats_screen_id_foreign FOREIGN KEY (screen_id) REFERENCES rooms(screen_id) ON DELETE CASCADE');

        // Rollback: Đổi lại foreign key trong showtimes
        DB::statement('ALTER TABLE showtimes DROP FOREIGN KEY showtimes_room_id_foreign');
        DB::statement('ALTER TABLE showtimes CHANGE room_id screen_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE showtimes ADD CONSTRAINT showtimes_screen_id_foreign FOREIGN KEY (screen_id) REFERENCES rooms(screen_id) ON DELETE CASCADE');

        // Rollback: Đổi lại primary key
        DB::statement('ALTER TABLE rooms CHANGE room_id screen_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');

        // Rollback: Đổi lại tên bảng
        Schema::rename('rooms', 'screens');
    }
};
