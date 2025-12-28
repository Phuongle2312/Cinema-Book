<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Genre;
use App\Models\Language;
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
        $query = Movie::with(['genres', 'languages']);

        // Lọc theo trạng thái (now_showing, coming_soon, ended)
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Lọc theo thành phố (qua theaters)
        if ($request->has('city')) {
            $query->whereHas('showtimes.room.theater', function ($q) use ($request) {
                $q->where('city', $request->city);
            });
        }

        // Sắp xếp
        $sortBy = $request->get('sort_by', 'release_date');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

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
        $movies = Movie::with(['genres', 'languages'])
            ->where('status', 'now_showing')
            ->where('rating', '>=', 7.0)
            ->orderBy('rating', 'desc')
            ->limit(10)
            ->get();

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
            'genres',
            'languages',
            'cast' => function ($query) {
                $query->withPivot('role', 'character_name');
            },
            'showtimes' => function ($query) {
                $query->where('start_time', '>=', now())
                      ->with('room.theater')
                      ->orderBy('start_time');
            },
            'reviews' => function ($query) {
                $query->where('is_verified_purchase', true)
                      ->with('user:id,name')
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

        $movies = Movie::with(['genres', 'languages'])
            ->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                  ->orWhere('description', 'LIKE', "%{$query}%")
                  ->orWhereHas('cast', function ($castQuery) use ($query) {
                      $castQuery->where('name', 'LIKE', "%{$query}%");
                  });
            })
            ->orderBy('rating', 'desc')
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
            'genre_id' => 'nullable|exists:genres,genre_id',
            'language_id' => 'nullable|exists:languages,language_id',
            'rating' => 'nullable|numeric|min:0|max:10',
            'date' => 'nullable|date',
            'status' => 'nullable|in:coming_soon,now_showing,ended'
        ]);

        $query = Movie::with(['genres', 'languages']);

        // Lọc theo thành phố
        if ($request->has('city')) {
            $query->whereHas('showtimes.room.theater', function ($q) use ($request) {
                $q->where('city', $request->city);
            });
        }

        // Lọc theo thể loại
        if ($request->has('genre_id')) {
            $query->whereHas('genres', function ($q) use ($request) {
                $q->where('genre_id', $request->genre_id);
            });
        }

        // Lọc theo ngôn ngữ
        if ($request->has('language_id')) {
            $query->whereHas('languages', function ($q) use ($request) {
                $q->where('language_id', $request->language_id);
            });
        }

        // Lọc theo rating tối thiểu
        if ($request->has('rating')) {
            $query->where('rating', '>=', $request->rating);
        }

        // Lọc theo ngày chiếu
        if ($request->has('date')) {
            $query->whereHas('showtimes', function ($q) use ($request) {
                $q->whereDate('start_time', $request->date);
            });
        }

        // Lọc theo trạng thái
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Sắp xếp
        $query->orderBy('rating', 'desc');

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
