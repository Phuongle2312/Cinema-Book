<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Movie;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats()
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();

        // 1. Total Bookings
        $totalBookings = Booking::count();
        $bookingsLastMonth = Booking::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->count();
        $bookingsThisMonth = Booking::where('created_at', '>=', $startOfMonth)->count();
        
        $bookingsGrowth = 0;
        if ($bookingsLastMonth > 0) {
            $bookingsGrowth = (($bookingsThisMonth - $bookingsLastMonth) / $bookingsLastMonth) * 100;
        }

        // 2. Total Revenue (Confirmed Bookings)
        $totalRevenue = Booking::where('status', 'confirmed')->sum('total_price');
        $revenueLastMonth = Booking::where('status', 'confirmed')
            ->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
            ->sum('total_price');
        $revenueThisMonth = Booking::where('status', 'confirmed')
            ->where('created_at', '>=', $startOfMonth)
            ->sum('total_price');

        $revenueGrowth = 0;
        if ($revenueLastMonth > 0) {
            $revenueGrowth = (($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100;
        }

        // 3. Registered Users
        $totalUsers = User::count();
        $newUsersThisMonth = User::where('created_at', '>=', $startOfMonth)->count();

        // 4. Active Movies
        $activeMovies = Movie::where('status', 'now_showing')->count();
        $totalGenres = DB::table('movie_hashtag')
            ->join('hashtags', 'movie_hashtag.hashtag_id', '=', 'hashtags.id')
            ->where('hashtags.type', 'genre')
            ->distinct('hashtags.id')
            ->count('hashtags.id');

        // 5. Recent Bookings (Limit 10)
        $recentBookings = Booking::with(['user', 'showtime.movie'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($booking) {
                return [
                    'id' => $booking->booking_id,
                    'user' => $booking->user ? $booking->user->name : 'Guest',
                    'movie' => $booking->showtime && $booking->showtime->movie ? $booking->showtime->movie->title : 'Deleted Movie',
                    'date' => Carbon::parse($booking->created_at)->format('Y-m-d'),
                    'amount' => $booking->total_price, // Assuming total_price exists, or calculate
                    'status' => ucfirst($booking->status),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'bookings' => [
                    'total' => $totalBookings,
                    'growth' => round($bookingsGrowth, 1)
                ],
                'revenue' => [
                    'total' => $totalRevenue,
                    'growth' => round($revenueGrowth, 1)
                ],
                'users' => [
                    'total' => $totalUsers,
                    'new_this_month' => $newUsersThisMonth
                ],
                'movies' => [
                    'active' => $activeMovies,
                    'genres_count' => $totalGenres
                ],
                'recent_bookings' => $recentBookings
            ]
        ]);
    }
}
