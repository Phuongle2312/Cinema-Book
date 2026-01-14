<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function index()
    {
        $wishlists = Wishlist::with('movie.genres')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $wishlists,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'movie_id' => 'required|exists:movies,movie_id',
        ]);

        $exists = Wishlist::where('user_id', Auth::id())
            ->where('movie_id', $request->movie_id)
            ->first();

        if ($exists) {
            $exists->delete();

            return response()->json([
                'success' => true,
                'message' => 'Đã xóa khỏi danh sách yêu thích',
                'is_favorite' => false,
            ]);
        }

        Wishlist::create([
            'user_id' => Auth::id(),
            'movie_id' => $request->movie_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã thêm vào danh sách yêu thích',
            'is_favorite' => true,
        ]);
    }

    public function check($movieId)
    {
        $exists = Wishlist::where('user_id', Auth::id())
            ->where('movie_id', $movieId)
            ->exists();

        return response()->json([
            'success' => true,
            'is_favorite' => $exists,
        ]);
    }
}
