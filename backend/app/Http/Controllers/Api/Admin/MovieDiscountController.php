<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\MovieDiscount;
use App\Models\Movie;
use Illuminate\Http\Request;

/**
 * Admin Controller: Movie Discounts
 * Purpose: Admin manages discounts for specific movies
 */
class MovieDiscountController extends Controller
{
    /**
     * GET /api/admin/discounts
     * List all movie discounts
     */
    public function index(Request $request)
    {
        $query = MovieDiscount::with(['movie:movie_id,title,slug,poster_url', 'createdBy:id,name'])
            ->orderBy('created_at', 'desc');

        // Filter by active status
        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        // Filter by movie
        if ($request->has('movie_id')) {
            $query->where('movie_id', $request->movie_id);
        }

        $perPage = $request->get('per_page', 20);
        $discounts = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $discounts->items(),
            'meta' => [
                'current_page' => $discounts->currentPage(),
                'last_page' => $discounts->lastPage(),
                'per_page' => $discounts->perPage(),
                'total' => $discounts->total(),
            ]
        ]);
    }

    /**
     * POST /api/admin/discounts
     * Create a new discount for a movie
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'movie_id' => 'required|exists:movies,movie_id',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_active' => 'boolean',
        ]);

        // Validate percentage is <= 100
        if ($validated['discount_type'] === 'percentage' && $validated['discount_value'] > 100) {
            return response()->json([
                'success' => false,
                'message' => 'Percentage discount cannot exceed 100%'
            ], 422);
        }

        $validated['created_by'] = $request->user()->id;

        $discount = MovieDiscount::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Discount created successfully',
            'data' => $discount->load('movie')
        ], 201);
    }

    /**
     * GET /api/admin/discounts/{id}
     * View single discount detail
     */
    public function show($id)
    {
        $discount = MovieDiscount::with(['movie', 'createdBy'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $discount
        ]);
    }

    /**
     * PUT /api/admin/discounts/{id}
     * Update a discount
     */
    public function update(Request $request, $id)
    {
        $discount = MovieDiscount::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:100',
            'description' => 'nullable|string',
            'discount_type' => 'sometimes|in:percentage,fixed',
            'discount_value' => 'sometimes|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'is_active' => 'boolean',
        ]);

        // Validate percentage is <= 100
        $discountType = $validated['discount_type'] ?? $discount->discount_type;
        $discountValue = $validated['discount_value'] ?? $discount->discount_value;
        
        if ($discountType === 'percentage' && $discountValue > 100) {
            return response()->json([
                'success' => false,
                'message' => 'Percentage discount cannot exceed 100%'
            ], 422);
        }

        $discount->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Discount updated successfully',
            'data' => $discount->fresh('movie')
        ]);
    }

    /**
     * DELETE /api/admin/discounts/{id}
     * Delete a discount
     */
    public function destroy($id)
    {
        $discount = MovieDiscount::findOrFail($id);
        $discount->delete();

        return response()->json([
            'success' => true,
            'message' => 'Discount deleted successfully'
        ]);
    }

    /**
     * POST /api/admin/discounts/{id}/toggle
     * Toggle discount active status
     */
    public function toggle($id)
    {
        $discount = MovieDiscount::findOrFail($id);
        $discount->update(['is_active' => !$discount->is_active]);

        return response()->json([
            'success' => true,
            'message' => $discount->is_active ? 'Discount activated' : 'Discount deactivated',
            'data' => $discount
        ]);
    }

    /**
     * GET /api/admin/discounts/active
     * Get all currently active discounts
     */
    public function activeDiscounts()
    {
        $discounts = MovieDiscount::with('movie:movie_id,title,slug,poster_url')
            ->active()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $discounts
        ]);
    }
}
