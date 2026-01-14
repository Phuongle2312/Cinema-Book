<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use App\Models\Movie;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    /**
     * GET /api/wishlist
     * Lấy danh sách yêu thích của người dùng
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $wishlist = Wishlist::with('movie')
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $wishlist->pluck('movie')
        ]);
    }

    /**
     * POST /api/wishlist
     * Thêm hoặc xóa phim khỏi danh sách yêu thích (toggle)
     */
    public function toggle(Request $request)
    {
        $request->validate([
            'movie_id' => 'required|exists:movies,movie_id',
        ]);

        $user = $request->user();
        $movieId = $request->movie_id;

        $wishlist = Wishlist::where('user_id', $user->id)
            ->where('movie_id', $movieId)
            ->first();

        if ($wishlist) {
            $wishlist->delete();
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa khỏi danh sách yêu thích',
                'is_favorite' => false
            ]);
        }

        Wishlist::create([
            'user_id' => $user->id,
            'movie_id' => $movieId
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã thêm vào danh sách yêu thích',
            'is_favorite' => true
        ], 201);
    }

    /**
     * GET /api/wishlist/check/{movie_id}
     * Kiểm tra phim có trong danh sách yêu thích không
     */
    public function check($movieId, Request $request)
    {
        $isFavorite = Wishlist::where('user_id', $request->user()->id)
            ->where('movie_id', $movieId)
            ->exists();

        return response()->json([
            'success' => true,
            'is_favorite' => $isFavorite
        ]);
    }
}
