<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DebugController extends Controller
{
    public function checkBookings(Request $request, $movieId)
    {
        $userId = $request->user()->id;

        $bookings = Booking::where('user_id', $userId)
            ->with(['showtime.movie'])
            ->get();

        $matched = $bookings->filter(function ($b) use ($movieId) {
            return $b->showtime && $b->showtime->movie_id == $movieId;
        });

        return response()->json([
            'user_id' => $userId,
            'movie_id_requested' => $movieId,
            'total_bookings' => $bookings->count(),
            'matched_bookings_count' => $matched->count(),
            'matched_bookings_details' => $matched->map(function ($b) {
                return [
                    'booking_id' => $b->booking_id,
                    'status' => $b->status,
                    'showtime_id' => $b->showtime_id,
                    'movie_id_in_showtime' => $b->showtime->movie_id ?? 'N/A',
                    'confirmed_at' => $b->confirmed_at,
                    'payment_status' => $b->payment_status // Check if column exists or is null
                ];
            })
        ]);
    }
}
