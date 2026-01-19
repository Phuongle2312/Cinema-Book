<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Movie;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Controller: ReviewController
 * Mục đích: Quản lý đánh giá và xếp hạng phim
 */
class ReviewController extends Controller
{
    /**
     * Tạo review mới cho phim
     * POST /api/movies/{id}/reviews
     */
    public function store(Request $request, $movieId)
    {
        // Kiểm tra phim có tồn tại không
        $movie = Movie::find($movieId);
        if (!$movie) {
            return response()->json([
                'success' => false,
                'message' => 'Movie not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Kiểm tra user đã review phim này chưa
        $existingReview = Review::where('user_id', $request->user()->id)
            ->where('movie_id', $movieId)
            ->first();

        if ($existingReview) {
            return response()->json([
                'success' => false,
                'message' => 'You have already reviewed this movie',
            ], 400);
        }

        // Kiểm tra user đã xem phim này chưa (verified purchase)
        // Lưu ý: Đã bỏ chặn bắt buộc mua vé (theo yêu cầu mới)
        $hasBooking = Booking::where('user_id', $request->user()->id)
            ->whereHas('showtime', function ($query) use ($movieId) {
                $query->where('movie_id', $movieId);
            })
            ->whereIn('status', ['confirmed', 'completed'])
            ->exists();

        if (!$hasBooking) {
            // return response()->json([
            //     'success' => false,
            //     'message' => 'Bạn cần mua vé và xem phim này để có thể gửi đánh giá.',
            // ], 403);
            // ALLOW ALL USERS
        }

        // Tạo review
        $review = Review::create([
            'user_id' => $request->user()->id,
            'movie_id' => $movieId,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'is_verified_purchase' => $hasBooking,
            'is_approved' => true, // Phê duyệt ngay lập tức
        ]);

        $review->load('user:id,name,avatar');

        return response()->json([
            'success' => true,
            'message' => 'Your review has been posted successfully!',
            'data' => $review,
        ], 201);
    }

    /**
     * Lấy danh sách reviews của phim
     * GET /api/movies/{id}/reviews
     */
    public function index(Request $request, $movieId)
    {
        // Kiểm tra phim có tồn tại không
        $movie = Movie::find($movieId);
        if (!$movie) {
            return response()->json([
                'success' => false,
                'message' => 'Movie not found',
            ], 404);
        }

        $sortBy = $request->get('sort_by', 'created_at'); // created_at hoặc rating
        $order = $request->get('order', 'desc'); // asc hoặc desc

        $reviews = Review::where('movie_id', $movieId)
            // ->approved() // Lấy tất cả reviews
            ->with('user:id,name,avatar')
            ->orderBy($sortBy, $order)
            ->paginate(15);

        // Tính toán thống kê
        $stats = [
            'total_reviews' => $reviews->total(),
            'average_rating' => Review::where('movie_id', $movieId)
                ->avg('rating'),
            'rating_distribution' => [
                '5_star' => Review::where('movie_id', $movieId)->where('rating', 5)->count(),
                '4_star' => Review::where('movie_id', $movieId)->where('rating', 4)->count(),
                '3_star' => Review::where('movie_id', $movieId)->where('rating', 3)->count(),
                '2_star' => Review::where('movie_id', $movieId)->where('rating', 2)->count(),
                '1_star' => Review::where('movie_id', $movieId)->where('rating', 1)->count(),
            ],
        ];

        // Check permission if user is logged in
        $userStatus = [
            'can_review' => false,
            'reason' => 'authentication_required'
        ];

        $user = $request->user('sanctum');

        if ($user) {
            $hasReviewed = Review::where('user_id', $user->id)->where('movie_id', $movieId)->exists();

            \Illuminate\Support\Facades\Log::info("INDEX_CHECK: UserID=" . $user->id . " MovieID=" . $movieId);

            // Check verified purchase
            $hasBooking = Booking::where('user_id', $user->id)
                ->whereHas('showtime', function ($query) use ($movieId) {
                    $query->where('movie_id', $movieId);
                })
                ->whereIn('status', ['confirmed', 'completed']) // Check both
                ->exists();

            \Illuminate\Support\Facades\Log::info("INDEX_CHECK_HAS_BOOKING: " . ($hasBooking ? 'YES' : 'NO'));

            if ($hasReviewed) {
                $userStatus['reason'] = 'already_reviewed';
            } else {
                // Allow review regardless of booking
                $userStatus['can_review'] = true;
                $userStatus['reason'] = $hasBooking ? 'ok' : 'no_booking_but_allowed'; // Optional info
            }
        }

        return response()->json([
            'success' => true,
            'data' => $reviews,
            'stats' => $stats,
            'user_status' => $userStatus
        ]);
    }
}
