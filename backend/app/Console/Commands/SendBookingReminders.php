<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use Illuminate\Support\Facades\Mail;
use App\Mail\BookingReminderMail;

class SendBookingReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-booking-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email reminders for upcoming bookings (2 hours before)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now();
        $limit = now()->addHours(2);

        $bookings = Booking::whereHas('showtime', function ($q) use ($now, $limit) {
            $q->whereBetween('start_time', [$now, $limit]);
        })
            ->where('status', 'confirmed')
            ->whereNull('reminder_sent_at')
            ->with(['user', 'showtime.movie', 'showtime.room.theater', 'seats'])
            ->get();

        $this->info("Found " . $bookings->count() . " bookings to remind.");

        foreach ($bookings as $booking) {
            try {
                if ($booking->user && $booking->user->email) {
                    Mail::to($booking->user->email)->send(new BookingReminderMail($booking));

                    $booking->update(['reminder_sent_at' => now()]);
                    $this->info("Sent reminder to {$booking->user->email} for Booking {$booking->booking_code}");
                }
            } catch (\Exception $e) {
                $this->error("Failed to send to {$booking->booking_code}: " . $e->getMessage());
            }
        }
    }
}
