<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Booking;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

echo "--- MOCKING DASHBOARD QUERIES ---\n";

try {
    // 1. REVENUE CHART
    echo "1. Testing Revenue Chart logic...\n";
    $date = Carbon::now()->format('Y-m-d');
    $revenue = Transaction::where('status', 'success')
        ->whereDate('created_at', $date)
        ->sum('amount');
    echo "Revenue today: $revenue\n";

    // 2. TOP MOVIES
    echo "2. Testing Top Movies logic...\n";
    $topMovies = Booking::where('bookings.status', 'confirmed') // Qualify status just in case
        ->join('showtimes', 'bookings.showtime_id', '=', 'showtimes.showtime_id')
        ->join('movies', 'showtimes.movie_id', '=', 'movies.movie_id')
        ->select('movies.title', DB::raw('SUM(bookings.total_price) as revenue'))
        ->groupBy('movies.movie_id', 'movies.title')
        ->orderByDesc('revenue')
        ->take(5)
        ->get();
    echo "Top Movies count: " . $topMovies->count() . "\n";

    // 3. THEATER REVENUE
    echo "3. Testing Theater Revenue logic...\n";
    $theaterRevenue = Booking::where('bookings.status', 'confirmed')
        ->join('showtimes', 'bookings.showtime_id', '=', 'showtimes.showtime_id')
        ->join('rooms', 'showtimes.room_id', '=', 'rooms.room_id')
        ->join('theaters', 'rooms.theater_id', '=', 'theaters.theater_id')
        ->select('theaters.name', DB::raw('SUM(bookings.total_price) as revenue'))
        ->groupBy('theaters.theater_id', 'theaters.name')
        ->orderByDesc('revenue')
        ->take(5)
        ->get();
    echo "Theater Revenue count: " . $theaterRevenue->count() . "\n";

    // 4. Booking Change (Previous month)
    echo "4. Testing Booking Change logic...\n";
    $currentMonth = Booking::whereMonth('created_at', Carbon::now()->month)->count();
    echo "Current Month Bookings: $currentMonth\n";

} catch (\Exception $e) {
    echo "\n[ERROR] " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
