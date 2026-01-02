<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MovieController;
use App\Http\Controllers\Api\TheaterController;
use App\Http\Controllers\Api\ShowtimeController;
use App\Http\Controllers\Api\BookingController;

/*
|--------------------------------------------------------------------------
| API Routes - Cinema Booking System
|--------------------------------------------------------------------------
*/

// ============================================
// PUBLIC ROUTES (Không cần authentication)
// ============================================

// Test route
Route::get('/test', function () {
    return response()->json([
        'success' => true,
        'message' => 'Cinema Booking API is working!',
        'timestamp' => now(),
        'version' => '1.0.0'
    ]);
});

// ============================================
// AUTHENTICATION ROUTES
// ============================================

// Shorthand routes (dễ nhớ hơn)
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Full routes với prefix 'auth'
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Social Login
    Route::get('/google', [AuthController::class, 'redirectToGoogle']);
    Route::get('/google/callback', [AuthController::class, 'handleGoogleCallback']);
    Route::get('/facebook', [AuthController::class, 'redirectToFacebook']);
    Route::get('/facebook/callback', [AuthController::class, 'handleFacebookCallback']);
});

// ============================================
// MOVIES & SEARCH ROUTES (Public)
// ============================================
Route::prefix('movies')->group(function () {
    Route::get('/', [MovieController::class, 'index']);              // GET /api/movies
    Route::get('/featured', [MovieController::class, 'featured']);   // GET /api/movies/featured
    Route::get('/search', [MovieController::class, 'search']);       // GET /api/movies/search?q=
    Route::get('/filter', [MovieController::class, 'filter']);       // GET /api/movies/filter
    Route::get('/{id}', [MovieController::class, 'show']);           // GET /api/movies/{id}
});

// ============================================
// THEATERS ROUTES (Public)
// ============================================
Route::prefix('theaters')->group(function () {
    Route::get('/', [TheaterController::class, 'index']);            // GET /api/theaters
    Route::get('/{id}', [TheaterController::class, 'show']);         // GET /api/theaters/{id}
});

// ============================================
// SHOWTIMES ROUTES (Public)
// ============================================
Route::prefix('showtimes')->group(function () {
    Route::get('/', [ShowtimeController::class, 'index']);               // GET /api/showtimes
    Route::get('/{id}/seats', [ShowtimeController::class, 'getSeats']); // GET /api/showtimes/{id}/seats
});

// ============================================
// PROTECTED ROUTES (Cần authentication)
// ============================================
Route::middleware('auth:sanctum')->group(function () {

    // ============================================
    // USER ROUTES
    // ============================================
    Route::prefix('user')->group(function () {
        Route::get('/profile', [AuthController::class, 'getUser']);      // GET /api/user/profile
        Route::put('/profile', [AuthController::class, 'updateUser']);   // PUT /api/user/profile
        Route::get('/bookings', [BookingController::class, 'userBookings']); // GET /api/user/bookings
    });

    // ============================================
    // BOOKING ROUTES
    // ============================================
    Route::prefix('bookings')->group(function () {
        Route::get('/{id}', [BookingController::class, 'show']);                  // GET /api/bookings/{id}
        Route::post('/', [BookingController::class, 'store']);                    // POST /api/bookings
        Route::post('/{id}/pay', [BookingController::class, 'pay']);              // POST /api/bookings/{id}/pay
        Route::get('/e-ticket/{id}', [BookingController::class, 'eTicket']);      // GET /api/bookings/e-ticket/{id}
    });

    // ============================================
    // AUTH ROUTES (Protected)
    // ============================================
    Route::post('/logout', [AuthController::class, 'logout']);         // POST /api/logout
});
