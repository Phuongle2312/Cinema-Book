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
        if (! $movie) {
            return response()->json([
                'success' => false,
                'message' => 'Phim không tồn tại',
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
                'message' => 'Bạn đã đánh giá phim này rồi',
            ], 400);
        }

        // Kiểm tra user đã xem phim này chưa (verified purchase)
        $hasBooking = Booking::where('user_id', $request->user()->id)
            ->whereHas('showtime', function ($query) use ($movieId) {
                $query->where('movie_id', $movieId);
            })
            ->where('payment_status', 'completed')
            ->exists();

        // Tạo review
        $review = Review::create([
            'user_id' => $request->user()->id,
            'movie_id' => $movieId,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'is_verified_purchase' => $hasBooking,
            'is_approved' => false, // Cần admin approve
        ]);

        $review->load('user:id,name,avatar');

        return response()->json([
            'success' => true,
            'message' => 'Đánh giá của bạn đã được gửi và đang chờ phê duyệt',
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
        if (! $movie) {
            return response()->json([
                'success' => false,
                'message' => 'Phim không tồn tại',
            ], 404);
        }

        $sortBy = $request->get('sort_by', 'created_at'); // created_at hoặc rating
        $order = $request->get('order', 'desc'); // asc hoặc desc

        $reviews = Review::where('movie_id', $movieId)
            ->approved() // Chỉ lấy reviews đã được approve
            ->with('user:id,name,avatar')
            ->orderBy($sortBy, $order)
            ->paginate(15);

        // Tính toán thống kê
        $stats = [
            'total_reviews' => $reviews->total(),
            'average_rating' => Review::where('movie_id', $movieId)
                ->approved()
                ->avg('rating'),
            'rating_distribution' => [
                '5_star' => Review::where('movie_id', $movieId)->approved()->where('rating', 5)->count(),
                '4_star' => Review::where('movie_id', $movieId)->approved()->where('rating', 4)->count(),
                '3_star' => Review::where('movie_id', $movieId)->approved()->where('rating', 3)->count(),
                '2_star' => Review::where('movie_id', $movieId)->approved()->where('rating', 2)->count(),
                '1_star' => Review::where('movie_id', $movieId)->approved()->where('rating', 1)->count(),
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $reviews,
            'stats' => $stats,
        ]);
    }
}
