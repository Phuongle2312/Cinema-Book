<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\MovieController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OfferController;
use App\Http\Controllers\Api\ShowtimeController;
use App\Http\Controllers\Api\TheaterController;
use App\Http\Controllers\Api\VerifyPaymentController;
use App\Http\Controllers\Api\WishlistController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Cinema Booking System
|--------------------------------------------------------------------------
*/

// ============================================
// PUBLIC ROUTES
// ============================================

Route::get('/test', function () {
    return response()->json([
        'success' => true,
        'message' => 'Cinema Booking API is working!',
        'timestamp' => now(),
    ]);
});

// AUTH
Route::post('/login', [AuthController::class, 'login']);
Route::get('/login', function () {
    return response()->json(['message' => 'Unauthenticated.'], 401);
})->name('login');
Route::post('/register', [AuthController::class, 'register']);

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

// MOVIES
Route::prefix('movies')->group(function () {
    Route::get('/', [MovieController::class, 'index']);
    Route::get('/featured', [MovieController::class, 'featured']);
    Route::get('/search', [MovieController::class, 'search']);
    Route::get('/filter', [MovieController::class, 'filter']);
    Route::get('/{slug_or_id}', [MovieController::class, 'show']);
});

// THEATERS
Route::prefix('theaters')->group(function () {
    Route::get('/', [TheaterController::class, 'index']);
    Route::get('/{id}', [TheaterController::class, 'show']);
});

// SHOWTIMES
Route::prefix('showtimes')->group(function () {
    Route::get('/', [ShowtimeController::class, 'index']);
    Route::get('/{id}/seats', [ShowtimeController::class, 'getSeats']);
});

// OFFERS (Formerly Promotions)
Route::get('/offers', [OfferController::class, 'index']);
Route::get('/offers/system', [OfferController::class, 'system']);
Route::post('/offers/validate', [OfferController::class, 'validate']);

// LOGOUT
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout']);

// ============================================
// PROTECTED ROUTES
// ============================================
Route::middleware('auth:sanctum')->group(function () {

    // USER
    Route::prefix('user')->group(function () {
        Route::get('/profile', [AuthController::class, 'getUser']);
        Route::put('/profile', [AuthController::class, 'updateUser']);
        Route::get('/bookings', [BookingController::class, 'userBookings']);
    });

    // WISHLIST
    Route::prefix('wishlist')->group(function () {
        Route::get('/', [WishlistController::class, 'index']);
        Route::post('/', [WishlistController::class, 'store']);
        Route::get('/check/{movieId}', [WishlistController::class, 'check']);
    });

    // BOOKINGS & PAYMENTS
    Route::prefix('bookings')->group(function () {
        Route::get('/', [BookingController::class, 'userBookings']);
        Route::get('/{id}', [BookingController::class, 'show']);
        Route::post('/', [BookingController::class, 'store']);
        Route::post('/{id}/pay', [BookingController::class, 'pay']);
        Route::get('/e-ticket/{id}', [BookingController::class, 'eTicket']);
    });

    // VERIFY PAYMENTS
    Route::prefix('verify-payments')->group(function () {
        Route::post('/', [VerifyPaymentController::class, 'store']);
        Route::get('/', [VerifyPaymentController::class, 'index']); // Admin check
        Route::post('/{id}/verify', [VerifyPaymentController::class, 'verify']); // Admin verify
    });

    // NOTIFICATIONS
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
    });
});

// ============================================
// ADMIN ROUTES
// ============================================
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::apiResource('theaters', \App\Http\Controllers\Api\Admin\TheaterController::class);
    Route::apiResource('movies', \App\Http\Controllers\Api\Admin\MovieController::class);
    Route::apiResource('showtimes', \App\Http\Controllers\Api\Admin\ShowtimeController::class);
});
