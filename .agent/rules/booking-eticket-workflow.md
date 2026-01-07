---
trigger: always_on
---

# Workflow: Booking & E-Ticket Process

description: Standard procedure for handling ticket booking, seat selection, payment integration, and e-ticket generation.

## Step 1: Check Movie & Showtime Availability
- [ ] Validate implementation of `GET /api/showtimes/{id}`.
- [ ] Ensure frontend checks `seat_status` (available, booked, held) before allowing selection.
- [ ] **Rule:** Do not allow selecting seats that are 'booked' or locked by another user.

## Step 2: Handle Seat Selection & Holding
- [ ] User selects seats -> Frontend sends request to hold seats (temporary lock).
- [ ] Backend must implement a Redis/Database lock for 5-10 minutes.
- [ ] Response must return `hold_token` or `booking_session_id`.

## Step 3: Apply Vouchers/Discounts (Optional)
- [ ] Check if user entered a voucher code.
- [ ] Call `POST /api/vouchers/apply` to validate and recalculate total price.

## Step 4: Create Pending Booking
- [ ] Create a record in `bookings` table with status `PENDING`.
- [ ] Link selected seats to this booking ID.

## Step 5: Payment Processing
- [ ] Integrate with Payment Gateway (VNPay/Momo/Stripe).
- [ ] Upon success callback:
    - Update booking status to `CONFIRMED`.
    - Generate `ticket_code` (QR Code string).
    - Send email with E-Ticket to user.

## Step 6: E-Ticket Display
- [ ] Frontend displays E-Ticket with: QR Code, Movie Name, Theater, Seat Numbers, Showtime.