<?php

namespace App\Services;

use App\Models\Showtime;
use App\Models\Seat;
use App\Models\Combo;

class PricingService
{
    /**
     * Calculate the price for a single seat in a showtime.
     *
     * @param Showtime $showtime
     * @param Seat $seat
     * @return float
     */
    public function calculateTicketPrice(Showtime $showtime, Seat $seat): float
    {
        // Base price from showtime (e.g., 80,000)
        $basePrice = $showtime->base_price;

        // Extra price from seat type (e.g., VIP +20,000)
        $extraPrice = $seat->extra_price;

        // Logic for potential dynamic pricing (e.g. weekend, holiday) could go here

        return $basePrice + $extraPrice;
    }

    /**
     * Calculate total price for a list of seats.
     *
     * @param Showtime $showtime
     * @param \Illuminate\Support\Collection|array $seats
     * @return float
     */
    public function calculateTotalSeatPrice(Showtime $showtime, $seats): float
    {
        $total = 0;
        foreach ($seats as $seat) {
            $total += $this->calculateTicketPrice($showtime, $seat);
        }
        return $total;
    }

    /**
     * Calculate total price for combos.
     * 
     * @param array $comboItems [['id' => 1, 'quantity' => 2], ...]
     * @return float
     */
    public function calculateComboPrice(array $comboItems): float
    {
        $total = 0;
        if (empty($comboItems))
            return 0;

        $comboIds = array_column($comboItems, 'id');
        $combos = Combo::whereIn('combo_id', $comboIds)->get()->keyBy('combo_id');

        foreach ($comboItems as $item) {
            $comboId = $item['id'];
            $quantity = $item['quantity'];

            if (isset($combos[$comboId])) {
                $total += $combos[$comboId]->price * $quantity;
            }
        }

        return $total;
    }
}
