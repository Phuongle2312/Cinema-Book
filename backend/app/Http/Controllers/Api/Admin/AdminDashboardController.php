<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Movie;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    /**
     * Get admin dashboard statistics
     */
    public function stats()
    {
        // 1. Total Bookings
        $totalBookings = Booking::count();
        $bookingsChange = $this->calculatePercentageChange(Booking::class);

        // 2. Total Revenue (from successfully paid transactions/bookings)
        // Adjust status check based on your workflow (e.g. 'confirmed', 'completed', or verify_payment logic)
        $totalRevenue = Transaction::where('status', 'success')->sum('amount');
        $revenueChange = $this->calculateRevenueChange();

        // 3. Registered Users
        $totalUsers = User::count();
        $usersChange = $this->calculatePercentageChange(User::class);

        // 4. Active Movies
        $activeMovies = Movie::where('status', 'now_showing')->count();

        // 5. Recent Bookings
        $recentBookings = Booking::with(['user', 'showtime.movie'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($booking) {
                return [
                    'id' => '#BK' . $booking->booking_code,
                    'user' => $booking->user ? $booking->user->name : 'Unknown',
                    'movie' => $booking->showtime->movie->title,
                    'date' => $booking->created_at->format('Y-m-d'),
                    'amount' => number_format($booking->total_price) . ' VND',
                    'status' => ucfirst($booking->status),
                ];
            });

        // 6. Revenue Chart (Last 7 Days)
        $revenueChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $revenue = Transaction::where('status', 'success')
                ->whereDate('created_at', $date)
                ->sum('amount');
            $revenueChart[] = ['date' => $date, 'revenue' => (int) $revenue];
        }

        // 7. Top Movies by Revenue
        $topMovies = Booking::where('bookings.status', 'confirmed')
            ->join('showtimes', 'bookings.showtime_id', '=', 'showtimes.showtime_id')
            ->join('movies', 'showtimes.movie_id', '=', 'movies.movie_id')
            ->select('movies.title', DB::raw('SUM(bookings.total_price) as revenue'))
            ->groupBy('movies.movie_id', 'movies.title')
            ->orderByDesc('revenue')
            ->take(5)
            ->get()
            ->map(function ($item) {
                return ['name' => $item->title, 'value' => (int) $item->revenue];
            });

        // 8. Revenue by Theater
        $theaterRevenue = Booking::where('bookings.status', 'confirmed')
            ->join('showtimes', 'bookings.showtime_id', '=', 'showtimes.showtime_id')
            ->join('rooms', 'showtimes.room_id', '=', 'rooms.room_id')
            ->join('theaters', 'rooms.theater_id', '=', 'theaters.theater_id')
            ->select('theaters.name', DB::raw('SUM(bookings.total_price) as revenue'))
            ->groupBy('theaters.theater_id', 'theaters.name')
            ->orderByDesc('revenue')
            ->take(5)
            ->get()
            ->map(function ($item) {
                return ['name' => $item->name, 'value' => (int) $item->revenue];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => [
                    'bookings' => [
                        'value' => number_format($totalBookings),
                        'change' => $bookingsChange
                    ],
                    'revenue' => [
                        'value' => number_format($totalRevenue) . ' VND',
                        'change' => $revenueChange
                    ],
                    'users' => [
                        'value' => number_format($totalUsers),
                        'change' => $usersChange
                    ],
                    'active_movies' => [
                        'value' => $activeMovies,
                        'change' => 0 // Placeholder
                    ]
                ],
                'recent_bookings' => $recentBookings,
                'charts' => [
                    'revenue' => $revenueChart,
                    'top_movies' => $topMovies,
                    'theater_revenue' => $theaterRevenue
                ]
            ]
        ]);
    }

    // Helper to calculate percentage change vs last month
    private function calculatePercentageChange($model)
    {
        $currentMonth = $model::whereMonth('created_at', Carbon::now()->month)->count();
        $lastMonth = $model::whereMonth('created_at', Carbon::now()->subMonth()->month)->count();

        if ($lastMonth == 0)
            return $currentMonth > 0 ? 100 : 0;

        return round((($currentMonth - $lastMonth) / $lastMonth) * 100, 1);
    }

    private function calculateRevenueChange()
    {
        $currentMonth = Transaction::where('status', 'success')
            ->whereMonth('created_at', Carbon::now()->month)
            ->sum('amount');

        $lastMonth = Transaction::where('status', 'success')
            ->whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->sum('amount');

        if ($lastMonth == 0)
            return $currentMonth > 0 ? 100 : 0;

        return round((($currentMonth - $lastMonth) / $lastMonth) * 100, 1);
    }
}
