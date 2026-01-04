# API Documentation - Cinema Booking System

This document tracks the current status of API development, categorized by "Existing" (Implemented) and "Missing" (Planned/Needed).

---

## 🟢 Existing APIs (Implemented)

### 1. Authentication
- `POST /api/register` - Create a new user account
- `POST /api/login` - Authenticate and get token
- `POST /api/logout` - Revoke current session token
- `POST /api/auth/forgot-password` - Send reset password link
- `POST /api/auth/reset-password` - Reset password using token
- `GET /api/auth/google` - Redirect to Google Social Login
- `GET /api/auth/google/callback` - Handle Google login callback

### 2. User Profile
- `GET /api/user/profile` - Get current user info
- `PUT /api/user/profile` - Update user info (name, phone, etc.)
- `GET /api/user/bookings` - List all bookings made by the user

### 3. Movies
- `GET /api/movies` - List movies with pagination & filters
- `GET /api/movies/featured` - List top/featured movies
- `GET /api/movies/search` - Search movies by title or cast
- `GET /api/movies/{id}` - Get detailed movie information
- `GET /api/movies/{id}/reviews` - List reviews for a specific movie

### 4. Theaters & Showtimes
- `GET /api/theaters` - List theaters with city filters
- `GET /api/theaters/{id}` - Get theater details
- `GET /api/showtimes` - List available showtimes
- `GET /api/showtimes/{id}/seats` - Get real-time seat status (locked/booked) for a showtime

### 5. Booking Flow
- `POST /api/bookings` - Create a new booking (includes seat locking)
- `GET /api/bookings/{id}` - Get booking details
- `POST /api/bookings/{id}/pay` - Process payment (placeholder for VNPay/ZaloPay)
- `GET /api/bookings/e-ticket/{id}` - Get data for electronic ticket rendering

### 6. Others
- `GET /api/promotions` - List active promotions
- `POST /api/promotions/validate` - Check code validity against a booking
- `POST /api/movies/{id}/reviews` - Submit a movie review (after booking)
- `GET /api/notifications` - List user notifications
- `POST /api/notifications/{id}/read` - Mark specific notification as read

### 7. Admin (System Management)
- `CRUD /api/admin/theaters` - Manage theaters
- `CRUD /api/admin/movies` - Manage movie database
- `CRUD /api/admin/showtimes` - Manage screen schedules
- `ADMIN /api/admin/reviews` - Moderate user reviews (Approve/Reject)

---

## 🔴 Missing APIs (Planned/Needed)

### 1. Missing Public Data
- `GET /api/cities` - List all cities (for city selector)
- `GET /api/genres` - List movie genres (for filtering)
- `GET /api/combos` - List food/drink packages (to display in catalog)
- `GET /api/cast` - Browse actors and directors

### 2. Missing User Features
- `POST /api/user/change-password` - Security update
- `POST /api/user/avatar` - Upload profile picture
- `DELETE /api/user/account` - Option to delete account

### 3. Missing Admin Controls
- `CRUD /api/admin/rooms` - Manage theater screens
- `CRUD /api/admin/combos` - Manage food/drink offerings
- `CRUD /api/admin/promotions` - Manage marketing campaigns
- `CRUD /api/admin/cities` - Manage city data
- `CRUD /api/admin/cast` - Manage actor/director database
- `GET /api/admin/dashboard` - Get system statistics (Revenue, Users, Active Bookings)

### 4. Booking Flow Improvements
- `POST /api/bookings/{id}/cancel` - Allow users to cancel pending bookings
- `GET /api/bookings/history` - Comprehensive booking history with rich data
- `POST /api/seat-locks/cleanup` - Manual/Automated trigger to clear expired locks (currently handled by scope logic)

---

## 🛠️ Notes
- All protected routes require an `Authorization: Bearer <token>` header.
- API response format follows the standard JSON structure: `{ "success": boolean, "data": ... , "message": string }`.
