<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentVerification;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Admin Controller: Payment Verification
 * Purpose: Admin approves/rejects manual payment submissions
 */
class PaymentVerificationController extends Controller
{
    /**
     * GET /api/admin/payments
     * List all payment verifications (with filters)
     */
    public function index(Request $request)
    {
        $query = PaymentVerification::with(['booking.showtime.movie', 'user', 'verifiedByAdmin'])
            ->orderBy('submitted_at', 'desc');

        // Filter by status
        if ($request->has('status') && in_array($request->status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $request->status);
        }

        // Default to pending if not specified
        if (!$request->has('status')) {
            $query->where('status', 'pending');
        }

        $perPage = $request->get('per_page', 20);
        $payments = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $payments->items(),
            'meta' => [
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'per_page' => $payments->perPage(),
                'total' => $payments->total(),
            ]
        ]);
    }

    /**
     * GET /api/admin/payments/{id}
     * View single payment verification detail
     */
    public function show($id)
    {
        $payment = PaymentVerification::with(['booking.showtime.movie', 'booking.seats', 'user', 'verifiedByAdmin'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $payment
        ]);
    }

    /**
     * POST /api/admin/payments/{id}/approve
     * Approve a payment - confirms booking
     */
    public function approve(Request $request, $id)
    {
        $request->validate([
            'admin_note' => 'nullable|string|max:500'
        ]);

        $payment = PaymentVerification::findOrFail($id);

        if ($payment->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This payment has already been processed.'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Approve payment and confirm booking
            $payment->approve($request->user()->id, $request->admin_note);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment approved. Booking confirmed.',
                'data' => $payment->fresh(['booking', 'verifiedByAdmin'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/admin/payments/{id}/reject
     * Reject a payment
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'admin_note' => 'required|string|max:500'
        ]);

        $payment = PaymentVerification::findOrFail($id);

        if ($payment->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This payment has already been processed.'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $payment->reject($request->user()->id, $request->admin_note);

            // Optionally cancel the booking
            $payment->booking->update(['status' => 'cancelled']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment rejected.',
                'data' => $payment->fresh(['booking', 'verifiedByAdmin'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/admin/payments/stats
     * Get payment statistics
     */
    public function stats()
    {
        $stats = [
            'pending_count' => PaymentVerification::pending()->count(),
            'approved_today' => PaymentVerification::approved()
                ->whereDate('verified_at', today())
                ->count(),
            'rejected_today' => PaymentVerification::rejected()
                ->whereDate('verified_at', today())
                ->count(),
            'total_approved_amount' => PaymentVerification::approved()
                ->whereDate('verified_at', today())
                ->sum('amount'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
