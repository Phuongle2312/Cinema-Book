<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use Illuminate\Http\Request;

/**
 * Controller: OfferController
 * Mục đích: Quản lý khuyến mãi và ưu đãi (thay cho Promotion)
 */
class OfferController extends Controller
{
    /**
     * Lấy danh sách các offers đang active
     * GET /api/offers
     */
    public function index()
    {
        $offers = Offer::active()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $offers,
        ]);
    }

    /**
     * Lấy danh sách ưu đãi hệ thống (không cần code)
     * GET /api/offers/system
     */
    public function system()
    {
        $offers = Offer::systemWide()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $offers,
        ]);
    }

    /**
     * Kiểm tra mã offer có hợp lệ không
     * POST /api/offers/validate
     */
    public function validate(Request $request)
    {
        $code = $request->input('code');
        $amount = $request->input('amount', 0);

        $offer = Offer::where('code', $code)->first();

        if (!$offer) {
            return response()->json([
                'success' => false,
                'message' => 'Offer code does not exist',
            ], 404);
        }

        if (!$offer->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'Offer code has expired or is invalid',
            ], 400);
        }

        if ($offer->min_purchase_amount && $amount < $offer->min_purchase_amount) {
            return response()->json([
                'success' => false,
                'message' => 'Order does not meet the minimum value for this offer',
            ], 400);
        }

        $discount = $offer->calculateDiscount($amount);

        return response()->json([
            'success' => true,
            'message' => 'Offer applied successfully',
            'data' => [
                'offer' => $offer,
                'discount_amount' => $discount,
                'final_amount' => max(0, $amount - $discount),
            ],
        ]);
    }
}
