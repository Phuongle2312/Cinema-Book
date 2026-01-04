<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

/**
 * Admin Controller: ReviewController
 * Mục đích: Kiểm duyệt và quản lý đánh giá (chỉ admin)
 */
class ReviewController extends Controller
{
    /**
     * Danh sách tất cả reviews (bao gồm chưa approve)
     * GET /api/admin/reviews
     */
    public function index(Request $request)
    {
        $query = Review::with(['user:id,name,email', 'movie:id,title']);

        // Filter by approval status
        if ($request->has('is_approved')) {
            $query->where('is_approved', $request->is_approved);
        }

        // Filter by movie
        if ($request->has('movie_id')) {
            $query->where('movie_id', $request->movie_id);
        }

        // Search by user name or email
        if ($request->has('search')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $reviews = $query->orderBy('created_at', 'desc')->paginate(20);

        // Statistics
        $stats = [
            'total' => Review::count(),
            'approved' => Review::where('is_approved', true)->count(),
            'pending' => Review::where('is_approved', false)->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $reviews,
            'stats' => $stats
        ]);
    }

    /**
     * Phê duyệt review
     * PUT /api/admin/reviews/{id}/approve
     */
    public function approve($id)
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Đánh giá không tồn tại'
            ], 404);
        }

        $review->update(['is_approved' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Đánh giá đã được phê duyệt',
            'data' => $review
        ]);
    }

    /**
     * Từ chối/Hủy phê duyệt review
     * PUT /api/admin/reviews/{id}/reject
     */
    public function reject($id)
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Đánh giá không tồn tại'
            ], 404);
        }

        $review->update(['is_approved' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Đánh giá đã bị từ chối',
            'data' => $review
        ]);
    }

    /**
     * Xóa review không phù hợp
     * DELETE /api/admin/reviews/{id}
     */
    public function destroy($id)
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Đánh giá không tồn tại'
            ], 404);
        }

        $review->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đánh giá đã được xóa'
        ]);
    }
}
