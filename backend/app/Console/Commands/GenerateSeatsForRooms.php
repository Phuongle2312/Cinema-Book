<?php

namespace App\Console\Commands;

use App\Models\Room;
use App\Models\Seat;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateSeatsForRooms extends Command
{
    protected $signature = 'seats:generate {--room= : Specific room ID to generate seats for}';
    protected $description = 'Generate seats for all rooms that do not have seats';

    public function handle()
    {
        $roomId = $this->option('room');
        
        if ($roomId) {
            $rooms = Room::where('room_id', $roomId)->get();
        } else {
            // Get rooms without seats
            $rooms = Room::whereDoesntHave('seats')->get();
        }

        if ($rooms->isEmpty()) {
            $this->info('All rooms already have seats or no rooms found.');
            return 0;
        }

        $this->info("Found {$rooms->count()} rooms without seats. Generating...");

        $bar = $this->output->createProgressBar($rooms->count());

        foreach ($rooms as $room) {
            $this->generateSeatsForRoom($room);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Seats generated successfully!');
        return 0;
    }

    private function generateSeatsForRoom(Room $room)
    {
        $rowCount = 8; // A-H
        $colCount = 12;
        $seats = [];
        $now = now();

        // Define seat types by row
        $rowConfig = [
            'A' => 'standard',
            'B' => 'standard',
            'C' => 'standard',
            'D' => 'vip',
            'E' => 'vip',
            'F' => 'vip',
            'G' => 'vip',
            'H' => 'couple', // Last row is couple
        ];

        $extraPrices = [
            'standard' => 0,
            'vip' => 20000,
            'couple' => 50000,
        ];

        foreach ($rowConfig as $row => $type) {
            for ($col = 1; $col <= $colCount; $col++) {
                $seats[] = [
                    'room_id' => $room->room_id,
                    'row' => $row,
                    'number' => $col,
                    'seat_code' => "{$row}{$col}",
                    'seat_type' => $type,
                    'extra_price' => $extraPrices[$type],
                    'is_available' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // Bulk insert
        DB::table('seats')->insert($seats);
    }
}
