<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PromotionController extends Controller
{
    /**
     * Danh sách khuyến mãi
     * GET /api/admin/discounts
     */
    public function index(Request $request)
    {
        $query = Promotion::query();

        if ($request->has('search')) {
            $query->where('code', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        $promotions = $query->orderBy('created_at', 'desc')->get();

        // Map data to match frontend expectation if needed, 
        // but frontend just expects array or paginated object. 
        // AdminOffers.js uses: discount.discount_percent, valid_until
        // But DB has: discount_value, valid_to.
        // We should format the response to match frontend or update frontend.
        // Let's transform here to match frontend existing code for "discount_percent" and "valid_until".
        
        $data = $promotions->map(function($p) {
            return [
                'id' => $p->id,
                'code' => $p->code,
                'description' => $p->description,
                'discount_percent' => $p->discount_type === 'percentage' ? $p->discount_value : 0, // Frontend expects 'discount_percent'
                'discount_value' => $p->discount_value, // Include raw value too
                'discount_type' => $p->discount_type,
                'valid_until' => $p->valid_to,
                'is_active' => $p->is_active,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Bật/Tắt khuyến mãi
     * POST /api/admin/discounts/{id}/toggle
     */
    public function toggle($id)
    {
        $promotion = Promotion::find($id);

        if (!$promotion) {
            return response()->json(['success' => false, 'message' => 'Promotion not found'], 404);
        }

        $promotion->is_active = !$promotion->is_active;
        $promotion->save();

        return response()->json([
            'success' => true,
            'message' => 'Updated status successfully',
            'data' => $promotion
        ]);
    }

    // Add Store/Update/Delete methods if requested later
}
