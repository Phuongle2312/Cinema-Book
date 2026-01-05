# Integrated APIs - Cinema Booking System

This document lists the backend APIs that have been successfully integrated into the React frontend.

## 🟢 Authentication & User Profile
- `POST /api/auth/register` - Integrated in `authService.register`
- `POST /api/auth/login` - Integrated in `authService.login`
- `POST /api/logout` - Integrated in `authService.logout`
- `GET /api/user/profile` - Integrated in `authService.getProfile`
- `PUT /api/user/profile` - Integrated in `authService.updateProfile`
- `GET /api/user/bookings` - Integrated in `bookingService.getUserBookings`

## 🟢 Movies
- `GET /api/movies` - Integrated in `movieService.getMovies`
- `GET /api/movies/featured` - Integrated in `movieService.getFeaturedMovies`
- `GET /api/movies/search` - Integrated in `movieService.searchMovies`
- `GET /api/movies/filter` - Integrated in `movieService.filterMovies`
- `GET /api/movies/{id}` - Integrated in `movieService.getMovieById`

## 🟢 Theaters & Showtimes
- `GET /api/theaters` - Integrated in `theaterService.getTheaters`
- `GET /api/theaters/{id}` - Integrated in `theaterService.getTheaterById`
- `GET /api/showtimes` - Integrated in `showtimeService.getShowtimes`
- `GET /api/showtimes/{id}/seats` - Integrated in `showtimeService.getSeats`

## 🟢 Booking Flow
- `POST /api/bookings` - Integrated in `bookingService.createBooking`
- `GET /api/bookings/{id}` - Integrated in `bookingService.getBookingById`
- `POST /api/bookings/{id}/pay` - Integrated in `bookingService.processPayment`
- `GET /api/bookings/e-ticket/{id}` - Integrated in `bookingService.getETicket`

---

## 🟡 Partially Integrated / Pending Review
- `GET /api/movies/{id}/reviews` - Backend exists, but frontend service needs verification of usage in components.

## 🔴 Not Yet Integrated in Services
- `POST /api/auth/forgot-password`
- `POST /api/auth/reset-password`
- `GET /api/promotions`
- `POST /api/promotions/validate`
- `POST /api/movies/{id}/reviews` (Submission)
- `GET /api/notifications`
- `POST /api/notifications/{id}/read`
- `ADMIN CRUD APIs` (Theaters, Movies, Showtimes, Reviews)

---
*Note: All service calls are routed through `frontend/src/services/api.js` pointing to `http://127.0.0.1:8000/api`.*
