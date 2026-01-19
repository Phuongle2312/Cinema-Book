<?php

namespace Database\Seeders;

use App\Models\Genre;
use App\Models\Movie;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MovieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Process Original User Data from JSON
        $moviesJson = file_get_contents(base_path('../frontend/src/data/movies.json'));
        $moviesData = json_decode($moviesJson, true);

        $detailsJson = file_get_contents(base_path('../frontend/src/data/details.json'));
        $detailsData = json_decode($detailsJson, true);

        // Combine data using title as key
        $combinedData = [];
        foreach ($moviesData as $movie) {
            $combinedData[$movie['title']] = $movie;
        }

        foreach ($detailsData as $detail) {
            if (isset($combinedData[$detail['title']])) {
                $combinedData[$detail['title']] = array_merge($combinedData[$detail['title']], $detail);
            } else {
                $combinedData[$detail['title']] = $detail;
            }
        }

        // Read and merge Banner data for Age Rating
        $bannerPath = base_path('../frontend/src/data/banner.json');
        if (file_exists($bannerPath)) {
            $bannerJson = file_get_contents($bannerPath);
            $bannerData = json_decode($bannerJson, true);
            foreach ($bannerData as $bannerItem) {
                if (isset($combinedData[$bannerItem['title']])) {
                    $combinedData[$bannerItem['title']]['age'] = $bannerItem['age'] ?? 'P';
                }
            }
        }

        // 2. Define CGV Data (Hardcoded)
        $cgvData = [
            // Now Showing
            [
                'title' => 'CON KỂ BA NGHE',
                'poster_url' => 'https://iguov8nhvyobj.vcdn.cloud/media/catalog/product/cache/1/thumbnail/190x260/2e2b8cd282892c71872b9e67d2cb5039/c/o/con_k_ba_nghe_-_payoff_poster_-_kc_16012026.jpg',
                'genre' => 'Gia đình, Tâm Lý',
                'duration' => '111 phút',
                'release_date' => '16-01-2026',
                'age_rating' => 'T13',
                'status' => 'now_showing'
            ],
            [
                'title' => 'AVATAR 3: LỬA VÀ TRO TÀN',
                'poster_url' => 'https://iguov8nhvyobj.vcdn.cloud/media/catalog/product/cache/1/thumbnail/190x260/2e2b8cd282892c71872b9e67d2cb5039/c/g/cgv_350x495_1_2.jpg',
                'genre' => 'Hành Động, Khoa Học Viễn Tưởng, Phiêu Lưu, Thần thoại',
                'duration' => '197 phút',
                'release_date' => '19-12-2025',
                'age_rating' => 'T13',
                'status' => 'now_showing'
            ],
            [
                'title' => 'THIÊN ĐƯỜNG MÁU',
                'poster_url' => 'https://iguov8nhvyobj.vcdn.cloud/media/catalog/product/cache/1/thumbnail/190x260/2e2b8cd282892c71872b9e67d2cb5039/3/5/350x495-tdm_2.jpg',
                'genre' => 'Hành Động, Tâm Lý',
                'duration' => '113 phút',
                'release_date' => '31-12-2025',
                'age_rating' => 'T16',
                'status' => 'now_showing'
            ],
            [
                'title' => 'TÍ SẸO VÀ LÂU ĐÀI QUÁI VẸO',
                'poster_url' => 'https://iguov8nhvyobj.vcdn.cloud/media/catalog/product/cache/1/thumbnail/190x260/2e2b8cd282892c71872b9e67d2cb5039/t/i/ti_se_o_la_u_a_i_qua_i_ve_o_-_payoff_poster.jpg',
                'genre' => 'Gia đình, Hoạt Hình, Thần thoại',
                'duration' => '92 phút',
                'release_date' => '16-01-2026',
                'age_rating' => 'P',
                'status' => 'now_showing'
            ],
            [
                'title' => 'PHI VỤ ĐỘNG TRỜI 2',
                'poster_url' => 'https://iguov8nhvyobj.vcdn.cloud/media/catalog/product/cache/1/thumbnail/190x260/2e2b8cd282892c71872b9e67d2cb5039/c/g/cgv_350x495_1_1.jpg',
                'genre' => 'Gia đình, Hành Động, Phiêu Lưu, Thần thoại',
                'duration' => '107 phút',
                'release_date' => '28-11-2025',
                'age_rating' => 'P',
                'status' => 'now_showing'
            ],
            [
                'title' => '28 NĂM SAU: NGÔI ĐỀN TỬ THẦN',
                'poster_url' => 'https://iguov8nhvyobj.vcdn.cloud/media/catalog/product/cache/1/thumbnail/190x260/2e2b8cd282892c71872b9e67d2cb5039/3/5/350x495-28yrs.jpg',
                'genre' => 'Kinh Dị',
                "duration" => "107 phút",
                'release_date' => '16-01-2026',
                'age_rating' => 'T18',
                'status' => 'now_showing'
            ],
            [
                'title' => 'CHUYỆN TÌNH SIAM',
                'poster_url' => 'https://iguov8nhvyobj.vcdn.cloud/media/catalog/product/cache/1/thumbnail/190x260/2e2b8cd282892c71872b9e67d2cb5039/8/_/8._700x1000-siam.jpg',
                'genre' => 'Tâm Lý, Tình cảm',
                'duration' => '158 phút',
                'release_date' => '16-01-2026',
                'age_rating' => 'T16',
                'status' => 'now_showing'
            ],
            [
                'title' => 'NHÀ GA NUỐT NGƯỜI: ĐĂNG XUẤT',
                'poster_url' => 'https://iguov8nhvyobj.vcdn.cloud/media/catalog/product/cache/1/thumbnail/190x260/2e2b8cd282892c71872b9e67d2cb5039/4/7/470wx700h-kirasagi.jpg',
                'genre' => 'Kinh Dị',
                'duration' => '82 phút',
                'release_date' => '16-01-2026',
                'age_rating' => 'T16',
                'status' => 'now_showing'
            ],
            [
                'title' => '5 CENTIMET TRÊN GIÂY - PHIÊN BẢN LIVE-ACTION',
                'poster_url' => 'https://iguov8nhvyobj.vcdn.cloud/media/catalog/product/cache/1/thumbnail/190x260/2e2b8cd282892c71872b9e67d2cb5039/5/c/5cm_logo_la_special_mkt_material_digital_470x700.jpg',
                'genre' => 'Tâm Lý, Tình cảm',
                'duration' => '122 phút',
                'release_date' => '16-01-2026',
                'age_rating' => 'T13',
                'status' => 'now_showing'
            ],
            [
                'title' => 'PHIM ĐIỆN ẢNH CÔNG CHÚA MONONOKE',
                'poster_url' => 'https://iguov8nhvyobj.vcdn.cloud/media/catalog/product/cache/1/thumbnail/190x260/2e2b8cd282892c71872b9e67d2cb5039/o/l/ol_localize_princessmononokeposter_251118.jpg',
                'genre' => 'Hoạt Hình, Phiêu Lưu, Thần thoại',
                'duration' => '134 phút',
                'release_date' => '09-01-2026',
                'age_rating' => 'T13',
                'status' => 'now_showing'
            ],

            // Coming Soon
            [
                'title' => 'TIỂU YÊU QUÁI NÚI LÃNG LÃNG',
                'poster_url' => 'https://iguov8nhvyobj.vcdn.cloud/media/catalog/product/cache/1/thumbnail/190x260/2e2b8cd282892c71872b9e67d2cb5039/3/5/350x495-nobodu_1.jpg',
                'genre' => 'Hoạt Hình',
                'duration' => '118 phút',
                'release_date' => '23-01-2026',
                'age_rating' => 'P',
                'status' => 'coming_soon'
            ],
            [
                'title' => 'PHIM ĐIỆN ẢNH THÁM TỬ LỪNG DANH CONAN: QUẢ BOM CHỌC TRỜI',
                'poster_url' => 'https://iguov8nhvyobj.vcdn.cloud/media/catalog/product/cache/1/thumbnail/190x260/2e2b8cd282892c71872b9e67d2cb5039/p/o/poster_conan_qua_bom_choc_troi_6.jpg',
                'genre' => 'Bí ẩn, Hành Động, Hoạt Hình',
                'duration' => '95 phút',
                'release_date' => '23-01-2026',
                'age_rating' => 'P',
                'status' => 'coming_soon'
            ],
            [
                'title' => 'BẰNG CHỨNG SINH TỬ',
                'poster_url' => 'https://iguov8nhvyobj.vcdn.cloud/media/catalog/product/cache/1/thumbnail/190x260/2e2b8cd282892c71872b9e67d2cb5039/3/5/350x495-mercy.jpg',
                'genre' => 'Hồi hộp, Khoa Học Viễn Tưởng, Tội phạm',
                'duration' => '100 phút',
                'release_date' => '23-01-2026',
                'age_rating' => 'T16',
                'status' => 'coming_soon'
            ],
            [
                'title' => 'ĐỒI CÂM LẶNG: ÁC MỘNG TRONG SƯƠNG',
                'poster_url' => 'https://iguov8nhvyobj.vcdn.cloud/media/catalog/product/cache/1/thumbnail/190x260/2e2b8cd282892c71872b9e67d2cb5039/1/2/1200x1800-silent-hill.jpg',
                'genre' => 'Kinh Dị',
                'duration' => '106 phút',
                'release_date' => '23-01-2026',
                'age_rating' => 'T18',
                'status' => 'coming_soon'
            ],
            [
                'title' => 'HÀM CÁ MẬP (CHIẾU LẠI)',
                'poster_url' => 'https://iguov8nhvyobj.vcdn.cloud/media/catalog/product/cache/1/thumbnail/190x260/2e2b8cd282892c71872b9e67d2cb5039/j/a/jaw_imax_700x1000.jpg',
                'genre' => 'Hồi hộp, Kinh Dị',
                'duration' => '124 phút',
                'release_date' => '23-01-2026',
                'age_rating' => 'T13',
                'status' => 'coming_soon'
            ],
            [
                'title' => 'LINH TRƯỞNG',
                'poster_url' => 'https://iguov8nhvyobj.vcdn.cloud/media/catalog/product/cache/1/thumbnail/190x260/2e2b8cd282892c71872b9e67d2cb5039/p/r/prm_miniteaser_700x1000.jpg',
                'genre' => 'Hồi hộp, Kinh Dị',
                'duration' => '89 phút',
                'release_date' => '23-01-2026',
                'age_rating' => 'T18',
                'status' => 'coming_soon'
            ],
            [
                'title' => 'CHUỘT NHÍ SIÊU TỐC ĐỘ',
                'poster_url' => 'https://iguov8nhvyobj.vcdn.cloud/media/catalog/product/cache/1/thumbnail/190x260/2e2b8cd282892c71872b9e67d2cb5039/m/a/main_poster_grand_prix_of_europe_.jpg',
                'genre' => 'Hài, Hoạt Hình',
                'duration' => '98 phút',
                'release_date' => '23-01-2026',
                'age_rating' => 'P',
                'status' => 'coming_soon'
            ],
            [
                'title' => 'NHÀ TRẤN QUỶ',
                'poster_url' => 'https://iguov8nhvyobj.vcdn.cloud/media/catalog/product/cache/1/thumbnail/190x260/2e2b8cd282892c71872b9e67d2cb5039/_/1/_1_oh-social-main_poster.jpg',
                'genre' => 'Kinh Dị',
                'duration' => '117 phút',
                'release_date' => '23-01-2026',
                'age_rating' => 'T18',
                'status' => 'coming_soon'
            ],
            [
                'title' => 'RUNNING MAN VIỆT NAM MÙA 3 - CON RỐI TỰ DO',
                'poster_url' => 'https://iguov8nhvyobj.vcdn.cloud/media/catalog/product/cache/1/thumbnail/190x260/2e2b8cd282892c71872b9e67d2cb5039/r/m/rm3_posterep16_470x700_1_.jpg',
                'genre' => 'Truyền hình thực tế',
                'duration' => '135 phút',
                'release_date' => '24-01-2026',
                'age_rating' => 'T13',
                'status' => 'coming_soon'
            ],
            [
                'title' => 'CỨU',
                'poster_url' => 'https://iguov8nhvyobj.vcdn.cloud/media/catalog/product/cache/1/thumbnail/190x260/2e2b8cd282892c71872b9e67d2cb5039/s/n/sndhp_007c_g_vie-vn_68.58x101.6_.jpg',
                'genre' => 'Hồi hộp, Kinh Dị',
                'duration' => '113 phút',
                'release_date' => '30-01-2026',
                'age_rating' => 'T18',
                'status' => 'coming_soon'
            ]
        ];

        // 3. Process Original User Data
        foreach ($combinedData as $title => $item) {
            try {
                // Convert duration "1h 40m" to minutes
                $minutes = 0;
                if (isset($item['duration'])) {
                    if (preg_match('/(\d+)h/', $item['duration'], $matches)) {
                        $minutes += intval($matches[1]) * 60;
                    }
                    if (preg_match('/(\d+)m/', $item['duration'], $matches)) {
                        $minutes += intval($matches[1]);
                    }
                }

                // Handle Genres
                $genreIds = [];
                if (isset($item['genres'])) {
                    foreach ($item['genres'] as $genreName) {
                        if ($genreName === 'In Theaters')
                            continue;
                        $genre = Genre::firstOrCreate(
                            ['name' => $genreName],
                            ['slug' => Str::slug($genreName)]
                        );
                        $genreIds[] = $genre->genre_id;
                    }
                }

                $movie = Movie::updateOrCreate(
                    ['title' => $title],
                    [
                        'slug' => Str::slug($title),
                        'description' => $item['description'] ?? '',
                        'duration' => $minutes ?: 120,
                        'release_date' => Carbon::createFromDate($item['year'] ?? 2025, 1, 1),
                        'poster_url' => $item['image'] ?? null,
                        'banner_url' => $item['bannerImage'] ?? ($item['image'] ?? null),
                        'trailer_url' => $item['trailer'] ?? null,
                        'status' => 'now_showing', // Defaulting original movies to now_showing as before
                        'is_featured' => true,
                        'age_rating' => $item['age'] ?? 'P',
                    ]
                );

                if (!empty($genreIds)) {
                    $movie->genres()->sync($genreIds);
                }
                echo "Restored original movie: " . $title . "\n";
            } catch (\Exception $e) {
                echo "Error converting original movie [$title]: " . $e->getMessage() . "\n";
            }
        }

        // 4. Process CGV Data (Merge/Update)
        foreach ($cgvData as $item) {
            // Parse Duration
            $minutes = 120; // Default
            if (preg_match('/(\d+)/', $item['duration'], $matches)) {
                $minutes = intval($matches[1]);
            }

            // Parse Date
            try {
                $releaseDate = Carbon::createFromFormat('d-m-Y', $item['release_date']);
            } catch (\Exception $e) {
                $releaseDate = now();
            }

            try {
                $movie = Movie::updateOrCreate(
                    ['title' => $item['title']],
                    [
                        'slug' => Str::slug($item['title']),
                        'description' => $item['title'] . ' - Một bộ phim hấp dẫn bạn không thể bỏ qua.',
                        'duration' => $minutes,
                        'release_date' => $releaseDate,
                        'poster_url' => $item['poster_url'],
                        'status' => $item['status'],
                        'age_rating' => $item['age_rating'] === 'N/A' ? 'T13' : $item['age_rating'],
                        'is_featured' => $item['status'] === 'now_showing',
                    ]
                );

                // Genres
                if (!empty($item['genre'])) {
                    $genreNames = array_map('trim', explode(',', $item['genre']));
                    $genreIds = [];
                    foreach ($genreNames as $name) {
                        $genre = Genre::firstOrCreate(
                            ['name' => $name],
                            ['slug' => Str::slug($name)]
                        );
                        $genreIds[] = $genre->genre_id;
                    }
                    $movie->genres()->sync($genreIds);
                }

                echo "Updated/Created CGV: " . $item['title'] . "\n";
            } catch (\Exception $e) {
                echo "Error processing " . $item['title'] . ": " . $e->getMessage() . "\n";
            }
        }
    }
}
