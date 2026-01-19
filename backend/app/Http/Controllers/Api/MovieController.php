<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use Illuminate\Http\Request;

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
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Lọc theo thành phố (qua theaters)
        if ($request->filled('city')) {
            $query->whereHas('showtimes.room.theater', function ($q) use ($request) {
                $q->whereHas('city', function ($cq) use ($request) {
                    $cq->where('name', $request->city);
                });
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
            ],
        ]);
    }

    /**
     * GET /api/movies/featured
     * Lấy danh sách phim nổi bật (đang chiếu)
     */
    public function featured()
    {
        $movies = Movie::with(['genres', 'languages'])
            ->where('is_featured', true)
            ->whereIn('status', ['now_showing', 'coming_soon'])
            ->latest()
            ->limit(10)
            ->get();

        // Fallback: If no featured movies, get latest now_showing movies
        if ($movies->count() < 4) {
            $latest = Movie::with(['genres', 'languages'])
                ->where('status', 'now_showing')
                ->latest()
                ->limit(10)
                ->get();
            $movies = $movies->merge($latest)->unique('movie_id')->take(10);
        }

        return response()->json([
            'success' => true,
            'data' => $movies,
        ]);
    }

    /**
     * GET /api/movies/{slug_or_id}
     * Lấy chi tiết phim bao gồm cast, genres, showtimes
     */
    public function show($slugOrId)
    {
        $query = Movie::with([
            'genres',
            'languages',
            'cast',
            'showtimes' => function ($query) {
                $query->where('start_time', '>=', now())
                    ->with('room.theater.city')
                    ->orderBy('start_time');
            },
        ]);

        if (is_numeric($slugOrId)) {
            $movie = $query->where('movie_id', $slugOrId)->first();
        } else {
            $movie = $query->where('slug', $slugOrId)->first();
        }

        if (!$movie) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy phim',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $movie,
        ]);
    }

    /**
     * GET /api/movies/search?q={query}
     * Tìm kiếm phim theo tên, diễn viên, đạo diễn
     */
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2',
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
            ->latest()
            ->paginate(12);

        return response()->json([
            'success' => true,
            'data' => $movies->items(),
            'meta' => [
                'current_page' => $movies->currentPage(),
                'last_page' => $movies->lastPage(),
                'total' => $movies->total(),
                'query' => $query,
            ],
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
            'genre' => 'nullable|string',
            'language' => 'nullable|string',
            'rating' => 'nullable|numeric|min:0|max:10',
            'date' => 'nullable|date',
            'status' => 'nullable|in:coming_soon,now_showing,ended',
        ]);

        $query = Movie::with(['genres', 'languages']);

        // Filter by City
        if ($request->filled('city')) {
            $query->whereHas('showtimes.room.theater.city', function ($q) use ($request) {
                $q->where('name', $request->city);
            });
        }

        // Filter by Genre (Name)
        if ($request->filled('genre')) {
            $query->whereHas('genres', function ($q) use ($request) {
                $q->where('name', $request->genre);
            });
        }

        // Filter by Language (Name)
        if ($request->filled('language')) {
            $query->whereHas('languages', function ($q) use ($request) {
                $q->where('name', $request->language); // Assuming language table has 'name' column
            });
        }

        // Filter by Rating (Minimum)
        if ($request->filled('rating')) {
            $query->withAvg('reviews', 'rating');
            $query->having('reviews_avg_rating', '>=', $request->rating);
        }

        // Filter by Date
        if ($request->filled('date')) {
            $query->whereHas('showtimes', function ($q) use ($request) {
                $q->whereDate('start_time', $request->date);
            });
        }

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by Query (Search Text) - Added to fix search issue
        if ($request->filled('query')) {
            $searchTerm = $request->query('query'); // Use 'query' method to avoid conflict with query variable
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'LIKE', "%{$searchTerm}%");
            });
        }

        $query->latest();

        $movies = $query->paginate(12);

        return response()->json([
            'success' => true,
            'data' => $movies->items(),
            'meta' => [
                'current_page' => $movies->currentPage(),
                'last_page' => $movies->lastPage(),
                'total' => $movies->total(),
                'filters' => $request->all(),
            ],
        ]);
    }
}
