<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MovieController;
use App\Http\Controllers\Api\TheaterController;
use App\Http\Controllers\Api\ShowtimeController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\WishlistController;

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
// Named route for Sanctum auth redirection
Route::get('/login', function () {
    return response()->json(['message' => 'Unauthenticated.'], 401);
})->name('login');
Route::post('/register', [AuthController::class, 'register']);

// Full routes với prefix 'auth'
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    
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
// PROMOTIONS ROUTES (Public)
// ============================================
// ============================================
// LOGOUT ROUTES (Handled manually for idempotency)
// ============================================
// Using match(['get', 'post']) to allow browser testing without MethodNotAllowed error
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout']);
Route::prefix('auth')->group(function () {
    Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout']);
});

// Promotions removed as per plan
// Route::get('/promotions', [PromotionController::class, 'index']);
// Route::post('/promotions/validate', [PromotionController::class, 'validate']);

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
        Route::get('/', [BookingController::class, 'userBookings']);              // GET /api/bookings (List my bookings)
        Route::get('/{id}', [BookingController::class, 'show']);                  // GET /api/bookings/{id}
        Route::post('/hold', [BookingController::class, 'hold']);                 // POST /api/bookings/hold
        Route::post('/', [BookingController::class, 'store']);                    // POST /api/bookings
        Route::post('/{id}/pay', [BookingController::class, 'pay']);              // POST /api/bookings/{id}/pay
        Route::get('/e-ticket/{id}', [BookingController::class, 'eTicket']);      // GET /api/bookings/e-ticket/{id}
    });

    // ============================================
    // NOTIFICATIONS ROUTES
    // ============================================
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);              // GET /api/notifications
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead']); // POST /api/notifications/{id}/read
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead']); // POST /api/notifications/read-all
    });

    // ============================================
    // PAYMENT ROUTES (User submits payment proof)
    // ============================================
    Route::prefix('payments')->group(function () {
        Route::post('/submit', [PaymentController::class, 'submit']);           // POST /api/payments/submit
        Route::get('/history', [PaymentController::class, 'history']);          // GET /api/payments/history
        Route::get('/check/{bookingId}', [PaymentController::class, 'checkStatus']); // GET /api/payments/check/{bookingId}
        Route::get('/{id}', [PaymentController::class, 'show']);                // GET /api/payments/{id}
    });

    // ============================================
    // WISHLIST ROUTES
    // ============================================
    Route::prefix('wishlist')->group(function () {
        Route::get('/', [WishlistController::class, 'index']);             // GET /api/wishlist
        Route::post('/toggle', [WishlistController::class, 'toggle']);     // POST /api/wishlist/toggle
        Route::get('/check/{movie_id}', [WishlistController::class, 'check']); // GET /api/wishlist/check/{movie_id}
    });
    
    // Logout removed from here to be handled manually
});

// ============================================
// ADMIN ROUTES (Cần authentication + admin role)
// ============================================
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    
    // Dashboard Stats
    Route::get('/dashboard/stats', [\App\Http\Controllers\Api\Admin\DashboardController::class, 'stats']);

    // CRUD Theaters
    Route::apiResource('theaters', \App\Http\Controllers\Api\Admin\TheaterController::class)->except(['destroy']);
    
    // CRUD Movies
    Route::apiResource('movies', \App\Http\Controllers\Api\Admin\MovieController::class)->except(['destroy']);
    
    // CRUD Showtimes
    Route::apiResource('showtimes', \App\Http\Controllers\Api\Admin\ShowtimeController::class)->except(['destroy']);
    
    // Users List
    Route::get('/users', [\App\Http\Controllers\Api\Admin\UserController::class, 'index']);
    
    // ============================================
    // PAYMENT VERIFICATION (Admin approves/rejects)
    // ============================================
    Route::prefix('payments')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\Admin\PaymentVerificationController::class, 'index']);
        Route::get('/stats', [\App\Http\Controllers\Api\Admin\PaymentVerificationController::class, 'stats']);
        Route::get('/{id}', [\App\Http\Controllers\Api\Admin\PaymentVerificationController::class, 'show']);
        Route::post('/{id}/approve', [\App\Http\Controllers\Api\Admin\PaymentVerificationController::class, 'approve']);
        Route::post('/{id}/reject', [\App\Http\Controllers\Api\Admin\PaymentVerificationController::class, 'reject']);
    });

    // DISCOUNTS / OFFERS (Replaced by MovieDiscountController below)
    // Route::get('/discounts', [\App\Http\Controllers\Api\Admin\PromotionController::class, 'index']);
    // Route::post('/discounts/{id}/toggle', [\App\Http\Controllers\Api\Admin\PromotionController::class, 'toggle']);

    // ============================================
    // MOVIE DISCOUNTS (Admin manages discounts)
    // ============================================
    Route::prefix('discounts')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\Admin\MovieDiscountController::class, 'index']);
        Route::get('/active', [\App\Http\Controllers\Api\Admin\MovieDiscountController::class, 'activeDiscounts']);
        Route::post('/', [\App\Http\Controllers\Api\Admin\MovieDiscountController::class, 'store']);
        Route::get('/{id}', [\App\Http\Controllers\Api\Admin\MovieDiscountController::class, 'show']);
        Route::put('/{id}', [\App\Http\Controllers\Api\Admin\MovieDiscountController::class, 'update']);
        Route::delete('/{id}', [\App\Http\Controllers\Api\Admin\MovieDiscountController::class, 'destroy']);
        Route::post('/{id}/toggle', [\App\Http\Controllers\Api\Admin\MovieDiscountController::class, 'toggle']);
    });
});
