<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Theater;
use Illuminate\Http\Request;

/**
 * TheaterController
 * Xử lý API liên quan đến rạp chiếu
 */
class TheaterController extends Controller
{
    /**
     * GET /api/theaters
     * Lấy danh sách rạp theo thành phố
     */
    public function index(Request $request)
    {
        $query = Theater::query();

        // Lọc theo thành phố (theo tên từ relationship City)
        if ($request->has('city')) {
            $query->whereHas('city', function ($q) use ($request) {
                $q->where('name', $request->city);
            });
        }

        // Lấy danh sách rạp với số lượng rooms và thông tin thành phố
        $query->withCount('rooms')->with('city');

        $theaters = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $theaters,
        ]);
    }

    /**
     * GET /api/theaters/{id}
     * Lấy chi tiết rạp bao gồm rooms
     */
    public function show($id)
    {
        $theater = Theater::with('rooms')->find($id);

        if (!$theater) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy rạp',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $theater,
        ]);
    }
}
