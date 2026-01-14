<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentVerification;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Controller: PaymentController
 * Purpose: Handle user payment submission and history
 */
class PaymentController extends Controller
{
    /**
     * POST /api/payments/submit
     * User submits payment proof for a booking
     */
    public function submit(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,booking_id',
            'payment_method' => 'required|string|max:50',
            'payment_proof' => 'nullable|image|max:5120', // Max 5MB
            'customer_note' => 'nullable|string|max:500',
        ]);

        $user = $request->user();

        // Check booking belongs to user and is pending
        $booking = Booking::where('booking_id', $validated['booking_id'])
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found or not pending.'
            ], 404);
        }

        // Check if already submitted
        $existing = PaymentVerification::where('booking_id', $booking->booking_id)->first();
        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Payment proof already submitted.',
                'data' => $existing
            ], 400);
        }

        // Handle file upload
        $proofPath = null;
        if ($request->hasFile('payment_proof')) {
            $proofPath = $request->file('payment_proof')->store('payment_proofs', 'public');
        }

        // Create payment verification
        $verification = PaymentVerification::create([
            'booking_id' => $booking->booking_id,
            'user_id' => $user->id,
            'status' => 'pending',
            'payment_proof' => $proofPath,
            'payment_method' => $validated['payment_method'],
            'amount' => $booking->total_price,
            'customer_note' => $validated['customer_note'] ?? null,
            'submitted_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment proof submitted. Please wait for admin verification.',
            'data' => $verification
        ], 201);
    }

    /**
     * GET /api/payments/history
     * Get user's payment history
     */
    public function history(Request $request)
    {
        $user = $request->user();

        $payments = PaymentVerification::with(['booking.showtime.movie:movie_id,title,poster_url', 'verifiedByAdmin:id,name'])
            ->where('user_id', $user->id)
            ->orderBy('submitted_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $payments->items(),
            'meta' => [
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'total' => $payments->total(),
            ]
        ]);
    }

    /**
     * GET /api/payments/{id}
     * Get single payment verification detail
     */
    public function show(Request $request, $id)
    {
        $payment = PaymentVerification::with(['booking.showtime.movie', 'booking.seats'])
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $payment
        ]);
    }

    /**
     * GET /api/payments/check/{bookingId}
     * Check payment status for a booking
     */
    public function checkStatus(Request $request, $bookingId)
    {
        $payment = PaymentVerification::where('booking_id', $bookingId)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$payment) {
            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'No payment submission found'
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'status' => $payment->status,
                'submitted_at' => $payment->submitted_at,
                'verified_at' => $payment->verified_at,
                'admin_note' => $payment->admin_note,
            ]
        ]);
    }
}
