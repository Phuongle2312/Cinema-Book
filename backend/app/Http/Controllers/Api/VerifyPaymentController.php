<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\VerifyPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerifyPaymentController extends Controller
{
    /**
     * Admin: Lấy danh sách yêu cầu xác thực thanh toán
     */
    public function index()
    {
        if (! Auth::user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $requests = VerifyPayment::with(['user', 'booking.showtime.movie'])
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $requests->items(),
            'meta' => [
                'total' => $requests->total(),
                'last_page' => $requests->lastPage(),
            ],
        ]);
    }

    /**
     * User: Gửi yêu cầu xác thực thanh toán (chuyển khoản/tiền mặt)
     */
    public function store(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,booking_id',
            'transaction_code' => 'required|string|unique:verify_payments,transaction_code',
        ]);

        $vp = VerifyPayment::create([
            'booking_id' => $request->booking_id,
            'user_id' => Auth::id(),
            'transaction_code' => $request->transaction_code,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Yêu cầu xác thực đã được gửi. Vui lòng chờ Admin kiểm tra.',
            'data' => $vp,
        ]);
    }

    /**
     * Admin: Xác nhận thanh toán thành công
     */
    public function verify(Request $request, $id)
    {
        if (! Auth::user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $vp = VerifyPayment::findOrFail($id);
        $vp->update([
            'status' => 'verified',
            'verified_by' => Auth::id(),
            'verified_at' => now(),
            'admin_notes' => $request->notes,
        ]);

        // Cập nhật trạng thái booking
        $booking = Booking::find($vp->booking_id);
        if ($booking) {
            $booking->update(['status' => 'confirmed']);

            // Cập nhật level user nếu là lần đầu mua
            $user = $vp->user;
            if ($user->user_level < 2) {
                $user->update(['user_level' => 2]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã xác thực thanh toán thành công',
        ]);
    }
}
