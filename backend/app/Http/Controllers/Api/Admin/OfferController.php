<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Offer; // Assuming Offer model exists or maps to 'promotions' table
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OfferController extends Controller
{
    public function index(Request $request)
    {
        $query = Offer::query();

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%') // Added title search
                    ->orWhere('description', 'like', '%' . $request->search . '%')
                    ->orWhere('code', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('type')) {
            if ($request->type === 'Event') {
                $query->where('is_system_wide', true);
            } elseif ($request->type === 'Voucher') {
                $query->where('is_system_wide', false);
            }
        }

        $offers = $query->orderBy('created_at', 'desc')->paginate(20);

        // Transform data for frontend
        $offers->getCollection()->transform(function ($offer) {
            $formatted = [
                'id' => $offer->id ?? $offer->promotion_id, // Safety check for ID
                'title' => $offer->title,
                'type' => $offer->is_system_wide ? 'Event' : 'Voucher',
                'code' => $offer->code,
                'discount' => '',
                'expiry' => $offer->valid_to ? \Carbon\Carbon::parse($offer->valid_to)->format('Y-m-d') : null,
                'status' => 'Active',
                // Raw data for edit form
                'raw_discount_value' => $offer->discount_value,
                'raw_discount_type' => $offer->discount_type,
            ];

            // Format Discount
            if ($offer->is_system_wide) {
                // Event
                $formatted['discount'] = 'Event Info';
            } else {
                // Voucher
                if ($offer->discount_type === 'percentage') {
                    $formatted['discount'] = round($offer->discount_value) . '% Off';
                } else {
                    $formatted['discount'] = number_format($offer->discount_value) . ' VND';
                }
            }

            // Calculate Status
            $now = now();
            if ($offer->valid_to && $now->gt(\Carbon\Carbon::parse($offer->valid_to)->endOfDay())) {
                $formatted['status'] = 'Expired';
            } elseif ($offer->valid_from && $now->lt($offer->valid_from)) {
                $formatted['status'] = 'Upcoming';
            } else {
                $formatted['status'] = $offer->is_active ? 'Active' : 'Inactive';
            }

            return (object) $formatted;
        });

        return response()->json([
            'success' => true,
            'data' => $offers,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'type' => 'required|in:Voucher,Event',
            'code' => 'nullable|string|max:50',
            'discount' => 'nullable|string', // Deprecated in favor of type/value
            'discount_type' => 'nullable|in:percentage,fixed',
            'discount_value' => 'nullable|numeric|min:0',
            'expiry' => 'nullable|string',
            'status' => 'required|in:Active,Upcoming,Expired',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $this->mapFrontendToBackend($request);
        $offer = Offer::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Offer created successfully',
            'data' => $offer,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $offer = Offer::find($id);

        if (!$offer) {
            return response()->json(['success' => false, 'message' => 'Offer not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:Voucher,Event',
            'code' => 'nullable|string|max:50',
            'discount' => 'sometimes|string',
            'status' => 'sometimes|in:Active,Upcoming,Expired',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $this->mapFrontendToBackend($request);
        $offer->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Offer updated successfully',
            'data' => $offer,
        ]);
    }

    public function destroy($id)
    {
        $offer = Offer::find($id);

        if (!$offer) {
            return response()->json(['success' => false, 'message' => 'Offer not found'], 404);
        }

        $offer->delete();

        return response()->json([
            'success' => true,
            'message' => 'Offer deleted successfully',
        ]);
    }

    /**
     * Helper to map frontend inputs to DB columns
     */
    private function mapFrontendToBackend(Request $request)
    {
        $data = [];

        if ($request->has('title')) {
            $data['title'] = $request->title; // Fixed: Map to title, not description
            $data['description'] = $request->description ?? $request->title; // Populate description too if needed
        }

        if ($request->has('type')) {
            $data['type'] = $request->type; // Ensure type column is filled if using 'offers' table with 'type' column
            $data['is_system_wide'] = ($request->type === 'Event');
        }

        if ($request->has('code')) {
            $data['code'] = $request->code;
        }

        if ($request->has('discount_type') && $request->has('discount_value')) {
            $data['discount_type'] = $request->discount_type;
            $data['discount_value'] = floatval($request->discount_value);
        } elseif ($request->has('discount')) {
            // Fallback for string input
            $discountStr = $request->discount;
            if (strpos($discountStr, '%') !== false) {
                $data['discount_type'] = 'percentage';
                $data['discount_value'] = floatval($discountStr);
            } else {
                $data['discount_type'] = 'fixed';
                $data['discount_value'] = floatval($discountStr);
            }
        }

        if ($request->has('expiry')) {
            $data['valid_to'] = $request->expiry;
            // Default valid_from to now if creating and not provided
            if (!$request->has('valid_from') && !$request->has('id')) { // minimal check
                $data['valid_from'] = now();
            }
        }

        if ($request->has('status')) {
            $data['is_active'] = ($request->status === 'Active' || $request->status === 'Upcoming');
        }

        return $data;
    }
}
