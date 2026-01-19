<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    /**
     * Get all bookings with filtering
     */
    public function index(Request $request)
    {
        $query = Booking::with(['user', 'showtime.movie', 'showtime.room.theater']);

        // Search by Booking Code or User Name
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('booking_code', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by Status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by Date
        if ($request->has('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $bookings = $query->latest()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $bookings
        ]);
    }

    /**
     * Update booking status (e.g. Cancel)
     */
    public function update(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);

        $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled,pending_verification'
        ]);

        $status = $request->status;

        if ($status === 'confirmed' && $booking->status !== 'confirmed') {
            $booking->confirm();
            // Send Email
            try {
                $booking->load('user', 'showtime.movie', 'showtime.room.theater', 'seats');
                \Illuminate\Support\Facades\Mail::to($booking->user->email)->send(new \App\Mail\BookingConfirmationMail($booking));
            } catch (\Exception $e) {
                \Log::error('Admin Mail sending failed: ' . $e->getMessage());
            }
        } elseif ($status === 'cancelled' && $booking->status !== 'cancelled') {
            $booking->cancel();
        } else {
            $booking->update(['status' => $status]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Booking status updated',
            'data' => $booking
        ]);
    }

    /**
     * Delete booking (Soft delete or force delete)
     */
    public function destroy($id)
    {
        $booking = Booking::findOrFail($id);
        $booking->delete();

        return response()->json([
            'success' => true,
            'message' => 'Booking deleted successfully'
        ]);
    }
}
