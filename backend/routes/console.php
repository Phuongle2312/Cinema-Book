<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\SeatLock;
use App\Models\Booking;
use Carbon\Carbon;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('seats:cleanup', function () {
    $this->info('Cleaning up expired seat locks and bookings...');
    
    // 1. Delete expired locks
    $deletedLocks = SeatLock::where('expires_at', '<', Carbon::now())->delete();
    
    // 2. Expire pending bookings
    // Note: Use update() directly for performance. If you need Model Events, loop and save.
    $expiredBookings = Booking::where('status', 'pending')
        ->where('expires_at', '<', Carbon::now())
        ->update(['status' => 'expired']);
        
    $this->info("Deleted {$deletedLocks} locks. Expired {$expiredBookings} bookings.");
    
})->purpose('Clean up expired seat locks and pending bookings');

// Schedule it
Schedule::command('seats:cleanup')->everyMinute();

