<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Hashtag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

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
        $query = Movie::with(['hashtags']);

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
            'data' => $movies
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
            'description' => 'required|string',
            'duration' => 'required|integer|min:1',
            'release_date' => 'required|date',
            'status' => 'required|in:now_showing,coming_soon,ended',
            'poster_url' => 'nullable|url',
            'trailer_url' => 'nullable|url',
            'rating' => 'nullable|numeric|min:0|max:10',
            'hashtag_ids' => 'nullable|array',
            'hashtag_ids.*' => 'exists:hashtags,hashtag_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $movie = Movie::create($request->except(['hashtag_ids']));

        // Attach relationships
        if ($request->has('hashtag_ids')) {
            $movie->hashtags()->attach($request->hashtag_ids);
        }

        $movie->load(['hashtags']);

        return response()->json([
            'success' => true,
            'message' => 'Phim đã được tạo thành công',
            'data' => $movie
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
                'message' => 'Phim không tồn tại'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'duration' => 'sometimes|integer|min:1',
            'release_date' => 'sometimes|date',
            'status' => 'sometimes|in:now_showing,coming_soon,ended',
            'poster_url' => 'nullable|url',
            'trailer_url' => 'nullable|url',
            'rating' => 'nullable|numeric|min:0|max:10',
            'hashtag_ids' => 'nullable|array',
            'hashtag_ids.*' => 'exists:hashtags,hashtag_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $movie->update($request->except(['hashtag_ids']));

        // Sync relationships
        if ($request->has('hashtag_ids')) {
            $movie->hashtags()->sync($request->hashtag_ids);
        }

        $movie->load(['hashtags']);

        return response()->json([
            'success' => true,
            'message' => 'Phim đã được cập nhật',
            'data' => $movie
        ]);
    }

    /**
     * Xóa phim
     * DELETE /api/admin/movies/{id}
     */
    public function destroy($id)
    {
        return response()->json([
            'success' => false,
            'message' => 'Feature is being updated'
        ], 403);
    }
}
