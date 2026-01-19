<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\Request;

class CityController extends Controller
{
    /**
     * GET /api/cities
     * Lấy danh sách thành phố có rạp
     */
    public function index(Request $request)
    {
        // Admin: Fetch all cities
        if ($request->has('all')) {
            return response()->json([
                'success' => true,
                'data' => City::all(),
            ]);
        }

        // Public: Only cities with active theaters
        $cities = City::whereHas('theaters', function ($q) {
            $q->where('is_active', true);
        })->get();

        return response()->json([
            'success' => true,
            'data' => $cities,
        ]);
    }
}
