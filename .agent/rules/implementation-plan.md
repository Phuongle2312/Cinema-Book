---
trigger: always_on
---

# Implementation Plan: Booking System Core

Based on the provided MySQL schema (`123.sql`), implement the Booking Logic following these strict steps.

## Phase 1: Models & Relationships
**Goal:** Ensure Laravel Models map correctly to the existing DB tables.
- [ ] Update `Seat` model: Relation `room()`, `locks()`.
- [ ] Update `Showtime` model: Relation `movie()`, `room()`, `locks()`, `bookings()`.
- [ ] Update `SeatLock` model: Fillable [`seat_id`, `showtime_id`, `user_id`, `expires_at`].
- [ ] Update `Booking` & `BookingDetail` models.

## Phase 2: Seat Map & Locking API (Critical)
**Goal:** Prevent double booking using the `seat_locks` table.
1.  **Endpoint:** `GET /api/showtimes/{id}/seats`
    * Fetch all seats for the room.
    * **Join 1:** `booking_details` to flag seats as `booked`.
    * **Join 2:** `seat_locks` to flag seats as `held`.
    * **Response:** JSON structure organized by `row` (A, B, C...) for easy Frontend rendering.

2.  **Endpoint:** `POST /api/bookings/hold`
    * **Validation:** Check if seats exist and are available (not in `booking_details` OR valid `seat_locks`).
    * **Action:** Insert into `seat_locks` (set expiry +5 mins).
    * **Cleanup:** Implement a Scheduled Job (`php artisan schedule:run`) to delete expired locks from DB.

## Phase 3: Booking Creation & Pricing
**Goal:** Convert held seats into a pending booking.
1.  **Service:** `PricingService`
    * Calculate `final_price` based on `showtimes.base_price` + `seat_types.base_extra_price`.
    * Future proofing: Prepared to integrate `pricing_rules` table.

2.  **Endpoint:** `POST /api/bookings/create`
    * **Input:** `showtime_id`, `seat_ids`, `combo_ids` (optional).
    * **DB Transaction:**
        * Validate User owns the locks in `seat_locks`.
        * Create `Booking` (Pending).
        * Create `BookingDetail` items.
        * Create `BookingCombo` items.
        * **Do NOT delete locks yet** (Wait for payment) OR convert locks to "booking_pending" state logic.

## Phase 4: Payment Confirmation (Simulation)
**Goal:** Finalize the booking.
1.  **Endpoint:** `POST /api/payment/callback` (Mockup for now)
    * Update `bookings.status` -> `confirmed`.
    * Generate `booking_code` & `ticket_code`.
    * Delete associated `seat_locks`.