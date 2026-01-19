<?php

namespace Database\Seeders;

use App\Models\Offer;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class OfferSeeder extends Seeder
{
    public function run(): void
    {
        $offers = [
            [
                'title' => 'Member Day - Buy 1 Get 1',
                'description' => 'Exclusive offer for CineBook members every Monday. Buy one ticket and get the second one for free!',
                'image_url' => 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?q=80&w=2070&auto=format&fit=crop',
                'tag' => 'Member Only',
                'date_display' => 'Every Monday',
                'type' => 'offer',
                'discount_type' => 'percentage',
                'discount_value' => 50, // Simulated BOGO
                'is_active' => true,
                'is_system_wide' => false,
            ],
            [
                'title' => 'Midnight Premiere: Space Odyssey',
                'description' => 'Be the first to witness the grand premiere of the year. Special snacks and souvenirs for midnight guests.',
                'image_url' => 'https://images.unsplash.com/photo-1478720568477-152d9b164e26?q=80&w=2070&auto=format&fit=crop',
                'tag' => 'Exclusive',
                'date_display' => 'Jan 15, 2026',
                'type' => 'event',
                'discount_type' => 'fixed',
                'discount_value' => 0,
                'is_active' => true,
                'is_system_wide' => false,
            ],
            [
                'title' => 'Family Weekend Combo',
                'description' => 'Enjoy a perfect family outing with our Mega Combo: 4 Tickets + Large Popcorn + 4 Drinks at 30% off.',
                'image_url' => 'https://images.unsplash.com/photo-1517604931442-7e0c8ed2963c?q=80&w=2070&auto=format&fit=crop',
                'tag' => 'Discount',
                'date_display' => 'Sat & Sun',
                'type' => 'offer',
                'discount_type' => 'percentage',
                'discount_value' => 30,
                'is_active' => true,
                'is_system_wide' => false,
            ],
            [
                'title' => 'Director\'s Cut Screening',
                'description' => 'Join us for a special screening of "The Last Horizon" followed by a Q&A session with the director.',
                'image_url' => 'https://images.unsplash.com/photo-1536440136628-849c177e76a1?q=80&w=2025&auto=format&fit=crop',
                'tag' => 'Special Event',
                'date_display' => 'Feb 02, 2026',
                'type' => 'event',
                'discount_type' => 'fixed',
                'discount_value' => 0,
                'is_active' => true,
                'is_system_wide' => false,
            ],
            [
                'title' => 'Student Special',
                'description' => 'Show your student ID and get flat 50% off on weekday matinee shows. Valid for 2D screenings only.',
                'image_url' => 'https://images.unsplash.com/photo-1524985069026-dd778a71c7b4?q=80&w=2071&auto=format&fit=crop',
                'tag' => 'Student Offer',
                'date_display' => 'Mon - Thu',
                'type' => 'offer',
                'discount_type' => 'percentage',
                'discount_value' => 50,
                'is_active' => true,
                'is_system_wide' => false,
            ],
            [
                'title' => 'Cosplay Night',
                'description' => 'Dress up as your favorite superhero and win exclusive merchandise! Best costume gets a year of free movies.',
                'image_url' => 'https://images.unsplash.com/photo-1620510625142-b45cbb784397?q=80&w=2070&auto=format&fit=crop',
                'tag' => 'Fan Event',
                'date_display' => 'Mar 10, 2026',
                'type' => 'event',
                'discount_type' => 'fixed',
                'discount_value' => 0,
                'is_active' => true,
                'is_system_wide' => false,
            ],
        ];

        // New Events added
        $newEvents = [
            [
                'title' => 'Cinema Under the Stars',
                'description' => 'Experience the magic of movies outdoors! Bring your blankets and enjoy a classic film under the night sky.',
                'image_url' => 'https://images.unsplash.com/photo-1513106580091-1d82408b8cd8?q=80&w=2076&auto=format&fit=crop',
                'tag' => 'Outdoor Experience',
                'date_display' => 'Apr 20, 2026',
                'type' => 'event',
                'discount_type' => 'fixed',
                'discount_value' => 0,
                'is_active' => true,
                'is_system_wide' => false,
            ],
            [
                'title' => 'Horror Movie Marathon',
                'description' => 'A spine-chilling night featuring 5 back-to-back horror classics. Surviving until dawn gets you a free breakfast!',
                'image_url' => 'https://images.unsplash.com/photo-1542204165-65bf26472b9b?q=80&w=1974&auto=format&fit=crop',
                'tag' => 'Challenge',
                'date_display' => 'Oct 31, 2026',
                'type' => 'event',
                'discount_type' => 'fixed',
                'discount_value' => 0,
                'is_active' => true,
                'is_system_wide' => false,
            ],
            [
                'title' => 'Anime Film Festival',
                'description' => 'Celebrating Japanese animation with screenings of Studio Ghibli masterpieces and modern hits. Cosplayers welcome!',
                'image_url' => 'https://images.unsplash.com/photo-1613376023733-0a73315d9b06?q=80&w=2070&auto=format&fit=crop',
                'tag' => 'Festival',
                'date_display' => 'May 15-17, 2026',
                'type' => 'event',
                'discount_type' => 'fixed',
                'discount_value' => 0,
                'is_active' => true,
                'is_system_wide' => false,
            ],
            [
                'title' => 'Red Carpet Gala',
                'description' => 'Walk the red carpet tailored for our VIP members. Champagne reception and an exclusive preview of the summer blockbuster.',
                'image_url' => 'https://images.unsplash.com/photo-1519750157975-f66f50009aad?q=80&w=1974&auto=format&fit=crop',
                'tag' => 'VIP Event',
                'date_display' => 'Jul 01, 2026',
                'type' => 'event',
                'discount_type' => 'fixed',
                'discount_value' => 0,
                'is_active' => true,
                'is_system_wide' => false,
            ],
            [
                'title' => 'Vintage Classics Night',
                'description' => 'Travel back in time with black-and-white gems from the golden age of cinema. Popcorn served in retro boxes.',
                'image_url' => 'https://images.unsplash.com/photo-1440404653325-ab127d49abc1?q=80&w=2070&auto=format&fit=crop',
                'tag' => 'Retro Night',
                'date_display' => 'First Friday',
                'type' => 'event',
                'discount_type' => 'fixed',
                'discount_value' => 0,
                'is_active' => true,
                'is_system_wide' => false,
            ],
        ];

        $offers = array_merge($offers, $newEvents);

        foreach ($offers as $offerData) {
            Offer::updateOrCreate(
                ['title' => $offerData['title']],
                array_merge($offerData, [
                    'valid_from' => Carbon::now()->subDays(10),
                    'valid_to' => Carbon::now()->addDays(60),
                ])
            );
        }
    }
}
