<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Admin Controller: MovieController
 * Mục đích: Quản lý CRUD phim (chỉ admin)
 */
class MovieController extends Controller
{
    /**
     * Danh sách tất cả phim (bao gồm cả phim ẩn)
     * GET /api/admin/movies
     */
    public function index(Request $request)
    {
        $query = Movie::with(['genres', 'languages', 'cast'])
            ->withAvg('reviews', 'rating'); // Calculate average rating

        // Search
        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $movies = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $movies,
        ]);
    }

    /**
     * Thêm phim mới
     * POST /api/admin/movies
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration' => 'required|integer|min:1',
            'release_date' => 'required|date',
            'status' => 'required|in:now_showing,coming_soon,ended',
            'age_rating' => 'nullable|string|max:10', // Added
            'synopsis' => 'nullable|string', // Added
            'content' => 'nullable|string', // Added
            'poster_url' => 'nullable|string',
            'trailer_url' => 'nullable|string',
            'genre_ids' => 'nullable|array',
            'genre_ids.*' => 'exists:genres,genre_id',
            'language_ids' => 'nullable|array',
            'language_ids.*' => 'exists:languages,language_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // rating column has been removed and is now calculated view
        // Use only() to allow safe mass assignment
        $fillable = (new Movie())->getFillable();
        $movieData = $request->only($fillable);
        // Ensure boolean
        if ($request->has('is_featured')) {
            $movieData['is_featured'] = filter_var($request->is_featured, FILTER_VALIDATE_BOOLEAN);
        }

        $movie = Movie::create($movieData);

        // Attach relationships
        if ($request->has('genre_ids')) {
            $movie->genres()->attach($request->genre_ids);
        }

        if ($request->has('language_ids')) {
            $movie->languages()->attach($request->language_ids);
        }

        $movie->load(['genres', 'languages']);

        return response()->json([
            'success' => true,
            'message' => 'Phim đã được tạo thành công',
            'data' => $movie,
        ], 201);
    }

    /**
     * Cập nhật phim
     * PUT /api/admin/movies/{id}
     */
    public function update(Request $request, $id)
    {
        $movie = Movie::find($id);

        if (!$movie) {
            return response()->json([
                'success' => false,
                'message' => 'Phim không tồn tại',
            ], 404);
        }

        // Validations
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'duration' => 'sometimes|integer|min:1',
            'release_date' => 'sometimes|date',
            'status' => 'sometimes|in:now_showing,coming_soon,ended',
            'age_rating' => 'nullable|string|max:10',
            'synopsis' => 'nullable|string',
            'content' => 'nullable|string',
            'poster_url' => 'nullable|string',
            'trailer_url' => 'nullable|string',
            'genre_ids' => 'nullable|array',
            'genre_ids.*' => 'exists:genres,genre_id',
            'language_ids' => 'nullable|array',
            'language_ids.*' => 'exists:languages,language_id',
        ]);

        if ($validator->fails()) {
            \Illuminate\Support\Facades\Log::error('Movie Update Validation Failed', $validator->errors()->toArray());
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Use only() to avoid SQL errors with unknown columns
            $fillable = $movie->getFillable();
            // Important: Explicitly merge age_rating and new fields if they are not in fillable yet (cache issue?)
            // Just in case, let's force them if they are missing from fillable but present in request
            $data = $request->only($fillable);

            // Ensure boolean for is_featured if passed
            if ($request->has('is_featured')) {
                $data['is_featured'] = filter_var($request->is_featured, FILTER_VALIDATE_BOOLEAN);
            }

            $movie->update($data);

            // Sync relationships
            if ($request->has('genre_ids')) {
                $movie->genres()->sync($request->genre_ids);
            }

            if ($request->has('language_ids')) {
                $movie->languages()->sync($request->language_ids);
            }

            $movie->load(['genres', 'languages']);

            return response()->json([
                'success' => true,
                'message' => 'Phim đã được cập nhật',
                'data' => $movie,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Movie Update Exception: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi server: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Xóa phim
     * DELETE /api/admin/movies/{id}
     */
    public function destroy($id)
    {
        $movie = Movie::find($id);

        if (!$movie) {
            return response()->json([
                'success' => false,
                'message' => 'Phim không tồn tại',
            ], 404);
        }

        // Kiểm tra xem phim có booking nào không (an toàn dữ liệu)
        // Nếu có showtime đã có người đặt vé => không cho xóa
        if ($movie->showtimes()->whereHas('bookings')->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa phim đã có vé đặt. Hãy đổi status thành "ended" để ẩn phim.',
            ], 400);
        }

        // Nếu chỉ có showtime rỗng (chưa ai đặt), cho phép xóa (cascade sẽ xóa showtime)


        $movie->delete();

        return response()->json([
            'success' => true,
            'message' => 'Phim đã được xóa',
        ]);
    }
}
