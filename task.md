# Database Audit and Flow Standardization

## Planning Phase
- [x] Audit all tables in `cinema_booking`
- [x] Identify redundant/useless tables
- [x] Map out the "Standard Flow" (User -> Booking -> Seat Lock -> Transaction)
- [x] Identify missing foreign key constraints or logic breaks
- [x] Create implementation plan for cleanup and flow strengthening

## Implementation Phase - Cleanup
- [x] Delete identified redundant tables
- [x] Remove unused columns
- [x] Clean up orphan records (if any)
## Step 6: Complete Admin Panel Functionality (Real Data)
- [x] **Showtimes**: Implement Backend Controller & Frontend Integration (`ManageShowtimes.js`).
- [x] **Offers**: Create `OfferController`, Routes, and Frontend Integration (`ManageOffers.js`).
- [x] **Reviews**: Create `ReviewController`, Routes, and Frontend Integration (`ManageReviews.js`).
- [x] **Cinemas**: Update `ManageCinemas.js` to fetch cities dynamically (fix hardcoded values).
- [x] **Payments**: Verify `ManagePayments.js` is using real backend API.

## Implementation Phase - Flow Standardization
- [x] Add missing foreign keys and indexes
- [x] Implement cascaded deletes where appropriate
- [x] Strengthen seat locking/release logic if needed
- [x] Validate transaction -> booking integrity

## Audit & Logic Refinement (Latest Request)
- [x] **Concurrency Logic**: Fix `ShowtimeController` & `BookingService` to handle `pending_verification` seats (Prevent Double Booking).
- [x] **Review Policy**: Relax restrictions in `ReviewController` to allow reviews without purchase (verified purchase tag logic preserved).
- [x] **Auth Security**: Enforce mixed-case password validation in `AuthController`.
- [x] **System Reality Check**: Validate Offer logic and Movie status filtering.

## Verification Phase
- [x] Verify 3NF compliance
- [x] Test core flows (Booking, Payment, Seat selection)
- [x] Audit APIs and create API.md
- [x] Final project status report

## Frontend Refactoring (Real API Integration)
- [x] **bookingService.js**: Replace mock data with real API calls
    - [x] `createBooking` (`POST /api/bookings`)
    - [x] `getBookingById` (`GET /api/bookings/{id}`)
    - [x] `processPayment` (`POST /api/bookings/{id}/pay`)
    - [x] `getETicket` (`GET /api/bookings/e-ticket/{id}`)
    - [x] `getUserBookings` (`GET /api/user/bookings`)
