<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Hashtag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * MovieController
 * Xử lý tất cả API liên quan đến phim
 */
class MovieController extends Controller
{
    /**
     * GET /api/movies
     * Lấy danh sách phim với phân trang và lọc cơ bản
     */
    public function index(Request $request)
    {
        $query = Movie::with(['hashtags']);

        // Lọc theo trạng thái (now_showing, coming_soon, ended)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Lọc theo thành phố (qua theaters)
        if ($request->filled('city')) {
            $query->whereHas('showtimes.room.theater', function ($q) use ($request) {
                $q->where('city', $request->city);
            });
        }

        // Sắp xếp
        $sortBy = $request->get('sort_by', 'release_date');
        $sortOrder = $request->get('sort_order', 'desc');

        if ($sortBy === 'rating') {
            $query->withAvg('reviews', 'rating')
                ->orderBy('reviews_avg_rating', $sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Phân trang
        $perPage = $request->get('per_page', 12);
        $movies = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $movies->items(),
            'meta' => [
                'current_page' => $movies->currentPage(),
                'last_page' => $movies->lastPage(),
                'per_page' => $movies->perPage(),
                'total' => $movies->total(),
            ]
        ]);
    }

    /**
     * GET /api/movies/featured
     * Lấy danh sách phim nổi bật (rating cao, đang chiếu)
     */
    public function featured()
    {
        $movies = Movie::with(['hashtags'])
            ->withAvg('reviews', 'rating')
            // ->where('is_featured', true) // Column missing
            ->whereIn('status', ['now_showing', 'coming_soon'])
            ->orderBy('reviews_avg_rating', 'desc')
            ->limit(10)
            ->get();

        // Fallback: If no featured movies, get latest now_showing movies
        if ($movies->isEmpty()) {
            $movies = Movie::with(['hashtags'])
                ->withAvg('reviews', 'rating')
                ->where('status', 'now_showing')
                ->latest()
                ->limit(10)
                ->get();
        }

        return response()->json([
            'success' => true,
            'data' => $movies
        ]);
    }

    /**
     * GET /api/movies/{id}
     * Lấy chi tiết phim bao gồm cast, genres, showtimes
     */
    public function show($id)
    {
        $movie = Movie::with([
            'hashtags',
            'showtimes' => function ($query) {
                $query->where('start_time', '>=', now())
                    ->with('room.theater')
                    ->orderBy('start_time');
            },
            'reviews' => function ($query) {
                $query->with('user:id,name')
                    ->latest()
                    ->limit(10);
            }
        ])->find($id);

        if (!$movie) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy phim'
            ], 404);
        }

        // Tính average rating từ reviews
        $avgRating = $movie->reviews()->avg('rating');
        $movie->average_rating = round($avgRating, 1);
        $movie->review_count = $movie->reviews()->count();

        return response()->json([
            'success' => true,
            'data' => $movie
        ]);
    }

    /**
     * GET /api/movies/search?q={query}
     * Tìm kiếm phim theo tên, diễn viên, đạo diễn
     */
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2'
        ]);

        $query = $request->get('q');

        $movies = Movie::with(['hashtags'])
            ->withAvg('reviews', 'rating')
            ->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                    ->orWhere('description', 'LIKE', "%{$query}%");
            })
            ->orderBy('reviews_avg_rating', 'desc')
            ->paginate(12);

        return response()->json([
            'success' => true,
            'data' => $movies->items(),
            'meta' => [
                'current_page' => $movies->currentPage(),
                'last_page' => $movies->lastPage(),
                'total' => $movies->total(),
                'query' => $query
            ]
        ]);
    }

    /**
     * GET /api/movies/filter
     * Lọc phim nâng cao theo nhiều tiêu chí
     */
    public function filter(Request $request)
    {
        $request->validate([
            'city' => 'nullable|string',
            'hashtag_id' => 'nullable|exists:hashtags,hashtag_id',
            'rating' => 'nullable|numeric|min:0|max:10',
            'date' => 'nullable|date',
            'status' => 'nullable|in:coming_soon,now_showing,ended'
        ]);

        $query = Movie::with(['hashtags']);

        // Lọc theo thành phố
        if ($request->filled('city')) {
            $query->whereHas('showtimes.room.theater', function ($q) use ($request) {
                $q->where('city', $request->city);
            });
        }

        // Lọc theo hashtag (Thể loại/Ngôn ngữ)
        if ($request->filled('hashtag_id')) {
            $query->whereHas('hashtags', function ($q) use ($request) {
                $q->where('hashtags.hashtag_id', $request->hashtag_id);
            });
        } elseif ($request->filled('hashtag')) {
            $query->whereHas('hashtags', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->hashtag}%");
            });
        }

        // Lọc theo rating tối thiểu
        if ($request->filled('rating')) {
            $query->withAvg('reviews', 'rating')
                ->having('reviews_avg_rating', '>=', $request->rating);
        } else {
            // Always load avg rating for sorting if not filtered
            $query->withAvg('reviews', 'rating');
        }

        // Lọc theo ngày chiếu
        if ($request->filled('date')) {
            $query->whereHas('showtimes', function ($q) use ($request) {
                $q->whereDate('start_time', $request->date);
            });
        }

        // Lọc theo trạng thái
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Sắp xếp
        $query->orderBy('reviews_avg_rating', 'desc');

        $movies = $query->paginate(12);

        return response()->json([
            'success' => true,
            'data' => $movies->items(),
            'meta' => [
                'current_page' => $movies->currentPage(),
                'last_page' => $movies->lastPage(),
                'total' => $movies->total(),
                'filters' => $request->only(['city', 'genre_id', 'language_id', 'rating', 'date', 'status'])
            ]
        ]);
    }
}
