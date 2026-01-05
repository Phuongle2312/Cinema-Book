<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


use App\Models\Promotion;

/**
 * Controller: PromotionController
 * Mục đích: Quản lý khuyến mãi và ưu đãi
 */
class PromotionController extends Controller
{
    /**
     * Lấy danh sách promotions đang active
     * GET /api/promotions
     */
    public function index()
    {
        $promotions = Promotion::active()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $promotions
        ]);
    }

    /**
     * Kiểm tra mã promotion có hợp lệ không
     * POST /api/promotions/validate
     */
    public function validate(Request $request)
    {
        $code = $request->input('code');
        $amount = $request->input('amount', 0);

        $promotion = Promotion::where('code', $code)->first();

        if (!$promotion) {
            return response()->json([
                'success' => false,
                'message' => 'Mã khuyến mãi không tồn tại'
            ], 404);
        }

        if (!$promotion->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'Mã khuyến mãi đã hết hạn hoặc không còn hiệu lực'
            ], 400);
        }

        if ($promotion->min_purchase_amount && $amount < $promotion->min_purchase_amount) {
            return response()->json([
                'success' => false,
                'message' => 'Đơn hàng chưa đủ giá trị tối thiểu để áp dụng mã này'
            ], 400);
        }

        $discount = $promotion->calculateDiscount($amount);

        return response()->json([
            'success' => true,
            'message' => 'Mã khuyến mãi hợp lệ',
            'data' => [
                'promotion' => $promotion,
                'discount_amount' => $discount,
                'final_amount' => max(0, $amount - $discount)
            ]
        ]);
    }
}

