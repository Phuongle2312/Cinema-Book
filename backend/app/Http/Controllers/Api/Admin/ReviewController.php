<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = Review::with(['user', 'movie']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->orWhereHas('movie', function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            });
        }

        $reviews = $query->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $reviews,
        ]);
    }

    public function update(Request $request, $id)
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json(['success' => false, 'message' => 'Review not found'], 404);
        }

        // Validate status
        $request->validate([
            'status' => 'required|in:Pending,Approved,Rejected'
        ]);

        $review->status = $request->status;
        $review->save();

        return response()->json([
            'success' => true,
            'message' => 'Review status updated',
            'data' => $review,
        ]);
    }

    public function destroy($id)
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json(['success' => false, 'message' => 'Review not found'], 404);
        }

        $review->delete();

        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully',
        ]);
    }
}
