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
        $query = Movie::with(['hashtags', 'genres'])
                     ->withCount('wishlists');

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

        if ($sortBy === 'popularity') {
            $query->orderBy('wishlists_count', $sortOrder);
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
     * Lấy danh sách phim nổi bật (đang chiếu)
     */
    public function featured()
    {
        $movies = Movie::with(['hashtags', 'genres'])
            ->withCount('wishlists')
            ->whereIn('status', ['now_showing', 'coming_soon'])
            ->orderBy('wishlists_count', 'desc')
            ->orderBy('release_date', 'desc')
            ->limit(10)
            ->get();

        // Fallback: If no movies found, get latest movies
        if ($movies->isEmpty()) {
            $movies = Movie::with(['hashtags', 'genres'])
                ->withCount('wishlists')
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
     * Lấy chi tiết phim bao gồm genres, showtimes
     */
    public function show($id)
    {
        $query = Movie::with([
            'hashtags',
            'genres',
            'activeDiscount',
            'showtimes' => function ($query) {
                $query->where('start_time', '>=', now())
                    ->with('room.theater')
                    ->orderBy('start_time');
            }
        ])->withCount('wishlists');

        if (is_numeric($id)) {
            $movie = $query->where('movie_id', $id)->first();
        } else {
            $movie = $query->where('slug', $id)->first();
        }

        if (!$movie) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy phim'
            ], 404);
        }

        // Add computed fields
        $movie->popularity_score = $movie->wishlists_count;
        
        // Check if movie has active discount
        if ($movie->activeDiscount) {
            $movie->has_discount = true;
            $movie->discounted_price = $movie->activeDiscount->getFinalPrice($movie->base_price ?? 0);
        } else {
            $movie->has_discount = false;
        }

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

        $movies = Movie::with(['hashtags', 'genres'])
            ->withCount('wishlists')
            ->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                    ->orWhere('description', 'LIKE', "%{$query}%")
                    ->orWhere('actor', 'LIKE', "%{$query}%")
                    ->orWhere('director', 'LIKE', "%{$query}%");
            })
            ->orderBy('wishlists_count', 'desc')
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
            'date' => 'nullable|date',
            'status' => 'nullable|in:coming_soon,now_showing,ended'
        ]);

        $query = Movie::with(['hashtags'])->withCount('wishlists');

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

        // Sắp xếp theo popularity (wishlist count)
        $query->orderBy('wishlists_count', 'desc');

        $movies = $query->paginate(12);

        return response()->json([
            'success' => true,
            'data' => $movies->items(),
            'meta' => [
                'current_page' => $movies->currentPage(),
                'last_page' => $movies->lastPage(),
                'total' => $movies->total(),
                'filters' => $request->only(['city', 'hashtag_id', 'date', 'status'])
            ]
        ]);
    }
}

