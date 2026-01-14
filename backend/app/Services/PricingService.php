<?php

namespace App\Services;

use App\Models\Showtime;
use App\Models\Seat;
use App\Models\Combo;

class PricingService
{
    /**
     * Tính giá vé cho 1 ghế
     * Công thức: Giá gốc suất chiếu + Phụ thu ghế
     */
    public function calculateTicketPrice(Showtime $showtime, Seat $seat): float
    {
        $basePrice = $showtime->base_price ?? 0;
        
        // Fallback: Nếu showtime không có giá riêng, lấy từ movie (nếu có logic này)
        // Hiện tại Model Showtime đã có base_price, ta ưu tiên dùng nó.
        
        $extraPrice = $seat->extra_price ?? 0;

        return $basePrice + $extraPrice;
    }

    /**
     * Tính tổng tiền danh sách ghế
     */
    public function calculateSeatsTotal(Showtime $showtime, $seats): float
    {
        $total = 0;
        foreach ($seats as $seat) {
            $total += $this->calculateTicketPrice($showtime, $seat);
        }
        return $total;
    }

    /**
     * Tính tổng tiền combo
     * @param array $combosData Format: [['combo_id' => 1, 'quantity' => 2], ...]
     */
    public function calculateCombosTotal(array $combosData): float
    {
        $total = 0;
        foreach ($combosData as $item) {
            $combo = Combo::find($item['combo_id']);
            if ($combo) {
                $total += $combo->price * $item['quantity'];
            }
        }
        return $total;
    }
}
