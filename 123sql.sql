-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 08, 2026 at 04:08 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cinema_booking`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `showtime_id` bigint(20) UNSIGNED NOT NULL,
  `booking_code` varchar(20) NOT NULL,
  `total_seats` int(11) NOT NULL,
  `seats_total` decimal(10,0) NOT NULL DEFAULT 0,
  `combo_total` decimal(10,0) NOT NULL DEFAULT 0,
  `total_price` decimal(10,0) NOT NULL,
  `status` enum('pending','confirmed','cancelled','expired') NOT NULL DEFAULT 'pending',
  `expires_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `booking_combos`
--

CREATE TABLE `booking_combos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `booking_id` bigint(20) UNSIGNED NOT NULL,
  `combo_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(10,0) NOT NULL,
  `total_price` decimal(10,0) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `booking_details`
--

CREATE TABLE `booking_details` (
  `detail_id` bigint(20) UNSIGNED NOT NULL,
  `booking_id` bigint(20) UNSIGNED NOT NULL,
  `seat_id` bigint(20) UNSIGNED NOT NULL,
  `showtime_id` bigint(20) UNSIGNED NOT NULL,
  `ticket_code` varchar(30) NOT NULL,
  `base_price` decimal(10,0) NOT NULL,
  `seat_extra_price` decimal(10,0) NOT NULL DEFAULT 0,
  `dynamic_price_adjustment` decimal(10,0) NOT NULL DEFAULT 0,
  `final_price` decimal(10,0) NOT NULL,
  `applied_pricing_rules` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`applied_pricing_rules`)),
  `status` enum('valid','used','cancelled','expired') NOT NULL DEFAULT 'valid',
  `used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cast`
--

CREATE TABLE `cast` (
  `cast_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` enum('actor','director','both') NOT NULL DEFAULT 'actor',
  `avatar` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cities`
--

CREATE TABLE `cities` (
  `city_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `country` varchar(100) NOT NULL DEFAULT 'Vietnam',
  `timezone` varchar(50) NOT NULL DEFAULT 'Asia/Ho_Chi_Minh',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cities`
--

INSERT INTO `cities` (`city_id`, `name`, `slug`, `country`, `timezone`, `created_at`, `updated_at`) VALUES
(1, 'Hồ Chí Minh', 'ho-chi-minh', 'Vietnam', 'Asia/Ho_Chi_Minh', '2026-01-04 20:11:43', '2026-01-04 20:11:43');

-- --------------------------------------------------------

--
-- Table structure for table `combos`
--

CREATE TABLE `combos` (
  `combo_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,0) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `combo_items`
--

CREATE TABLE `combo_items` (
  `combo_item_id` bigint(20) UNSIGNED NOT NULL,
  `combo_id` bigint(20) UNSIGNED NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `item_size` varchar(50) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `genres`
--

CREATE TABLE `genres` (
  `genre_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `genres`
--

INSERT INTO `genres` (`genre_id`, `name`, `slug`, `created_at`, `updated_at`) VALUES
(1, 'Action', 'action', '2026-01-04 19:17:58', '2026-01-04 19:17:58'),
(2, 'In Theaters', 'in-theaters', '2026-01-04 19:17:58', '2026-01-04 19:17:58'),
(3, 'Anime', 'anime', '2026-01-04 19:17:58', '2026-01-04 19:17:58'),
(4, 'Animation', 'animation', '2026-01-04 19:17:58', '2026-01-04 19:17:58'),
(5, 'Fantasy', 'fantasy', '2026-01-04 19:17:58', '2026-01-04 19:17:58'),
(6, 'Romance', 'romance', '2026-01-04 19:17:58', '2026-01-04 19:17:58'),
(7, 'Thriller', 'thriller', '2026-01-04 19:17:58', '2026-01-04 19:17:58'),
(8, 'War', 'war', '2026-01-04 19:17:58', '2026-01-04 19:17:58'),
(9, 'Drama', 'drama', '2026-01-04 19:17:58', '2026-01-04 19:17:58'),
(10, 'Crime', 'crime', '2026-01-04 19:17:58', '2026-01-04 19:17:58'),
(11, 'Mystery', 'mystery', '2026-01-04 19:17:58', '2026-01-04 19:17:58'),
(12, 'Adventure', 'adventure', '2026-01-04 19:17:58', '2026-01-04 19:17:58'),
(13, 'Family', 'family', '2026-01-04 19:17:58', '2026-01-04 19:17:58'),
(14, 'Kids', 'kids', '2026-01-04 19:17:58', '2026-01-04 19:17:58'),
(15, 'Comedy', 'comedy', '2026-01-04 19:17:58', '2026-01-04 19:17:58'),
(16, 'Musical', 'musical', '2026-01-04 19:17:58', '2026-01-04 19:17:58'),
(17, 'Magic', 'magic', '2026-01-04 19:17:58', '2026-01-04 19:17:58');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `languages`
--

CREATE TABLE `languages` (
  `language_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(10) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_12_22_011831_create_core_tables', 1),
(5, '2025_12_22_023118_add_missing_tables', 1),
(6, '2025_12_24_043103_create_combos_table', 1),
(7, '2025_12_24_043115_create_booking_combos_table', 1),
(8, '2025_12_24_043119_modify_reviews_add_booking_id', 1),
(9, '2025_12_24_043122_modify_showtimes_remove_end_time', 1),
(10, '2025_12_24_043125_modify_bookings_add_combo_fields', 1),
(11, '2025_12_26_011617_create_notifications_table', 1),
(12, '2025_12_26_011617_create_promotions_table', 1),
(13, '2025_12_26_040735_rename_screens_to_rooms_table', 1),
(14, '2025_12_26_040738_rename_tickets_to_booking_details_table', 1),
(15, '2025_12_29_024150_create_personal_access_tokens_table', 1),
(16, '2026_01_05_000001_create_cities_table', 1),
(17, '2026_01_05_000002_create_combo_items_table', 1),
(18, '2026_01_05_000003_remove_movie_rating_column', 1),
(19, '2026_01_05_000004_add_performance_indexes', 1),
(20, '2026_01_05_000005_cleanup_and_standardize_flow', 1),
(21, '2026_01_05_031221_add_columns_to_theaters_table', 2),
(22, '2026_01_05_031431_add_columns_to_seats_table', 3),
(23, '2026_01_05_031625_add_columns_to_showtimes_table', 4),
(24, '2026_01_05_032038_add_columns_to_seats_table', 5),
(25, '2026_01_05_032515_add_columns_to_showtimes_table', 5),
(26, '2026_01_05_040000_add_is_featured_to_movies_table', 6);

-- --------------------------------------------------------

--
-- Table structure for table `movies`
--

CREATE TABLE `movies` (
  `movie_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `duration` int(11) NOT NULL,
  `release_date` date NOT NULL,
  `poster_url` varchar(255) DEFAULT NULL,
  `trailer_url` varchar(255) DEFAULT NULL,
  `banner_url` varchar(255) DEFAULT NULL,
  `status` enum('coming_soon','now_showing','ended') NOT NULL DEFAULT 'coming_soon',
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `movies`
--

INSERT INTO `movies` (`movie_id`, `title`, `slug`, `description`, `duration`, `release_date`, `poster_url`, `trailer_url`, `banner_url`, `status`, `is_featured`, `created_at`, `updated_at`) VALUES
(1, 'Chainsaw Man - The Movie: Reze Arc', 'chainsaw-man-the-movie-reze-arc', 'Following the hit anime adaptation series, Chainsaw Man makes its theatrical debut in an epic adventure filled with explosive action sequences. In the previous installment, we learned that Denji worked as a Devil Hunter for the yakuza to pay off his parents\' debt but was betrayed by them...', 100, '2025-01-01', 'https://static.nutscdn.com/vimg/1920-0/eb306939fa7568010872341d862118f1.jpg', NULL, 'https://static.nutscdn.com/vimg/1920-0/eb306939fa7568010872341d862118f1.jpg', 'now_showing', 0, '2026-01-04 19:17:58', '2026-01-04 19:17:58'),
(2, 'Sisu: Road to Revenge', 'sisu-road-to-revenge', 'Thinking he had finally retired, enemies once again force the \'old warrior\' to return. When a new enemy appears, Atami once again \'powers up\' to confront them.', 89, '2023-01-01', 'https://static.nutscdn.com/vimg/1920-0/06decb477f7585cd2519b6666f3110b8.webp', NULL, 'https://static.nutscdn.com/vimg/1920-0/06decb477f7585cd2519b6666f3110b8.webp', 'now_showing', 0, '2026-01-04 19:17:58', '2026-01-04 19:17:58'),
(3, 'Now You See Me 3: Now You Don\'t', 'now-you-see-me-3-now-you-dont', 'After many years of absence, the legendary Four Horsemen of Now You See Me officially return with a completely new mission: the most daring diamond heist of their career. But this time, they\'re not alone, joined by a new generation of magicians including Greenblatt, Smith and...', 112, '2025-01-01', 'https://static.nutscdn.com/vimg/1920-0/951859ef2ec65a3be41fa36f156365b4.webp', NULL, 'https://static.nutscdn.com/vimg/1920-0/951859ef2ec65a3be41fa36f156365b4.webp', 'now_showing', 0, '2026-01-04 19:17:58', '2026-01-04 19:17:58'),
(4, 'Zootopia 2', 'zootopia-2', 'After many years of absence, the legendary Four Horsemen of Now You See Me officially return with a completely new mission: the most daring diamond heist of their career. But this time, they\'re not alone, joined by a new generation of magicians including Greenblatt, Smith and...', 110, '2025-01-01', 'https://static.nutscdn.com/vimg/1920-0/17338b547a5ebff5a0e35b902dacfa9d.webp', NULL, 'https://static.nutscdn.com/vimg/1920-0/17338b547a5ebff5a0e35b902dacfa9d.webp', 'now_showing', 0, '2026-01-04 19:17:58', '2026-01-04 19:17:58'),
(5, 'Wake Up Dead Man: A Knives Out Mystery', 'wake-up-dead-man-a-knives-out-mystery', 'Detective Benoit Blanc teams up with an enthusiastic young priest to investigate a seemingly impossible crime at a church in a small town with a dark past.', 145, '2025-01-01', 'https://static.nutscdn.com/vimg/1920-0/cd4ef646a4fa3f0954f04d5e315a0ef2.webp', NULL, 'https://static.nutscdn.com/vimg/1920-0/cd4ef646a4fa3f0954f04d5e315a0ef2.webp', 'now_showing', 0, '2026-01-04 19:17:58', '2026-01-04 19:17:58'),
(6, 'Wike: For Good', 'wike-for-good', 'Detective Benoit Blanc teams up with an enthusiastic young priest to investigate a seemingly impossible crime at a church in a small town with a dark past.', 145, '2025-01-01', 'https://static.nutscdn.com/vimg/1920-0/213eb724175dfd71ccfed2ab74dc291c.webp', NULL, 'https://static.nutscdn.com/vimg/1920-0/213eb724175dfd71ccfed2ab74dc291c.webp', 'now_showing', 0, '2026-01-04 19:17:58', '2026-01-04 19:17:58');

-- --------------------------------------------------------

--
-- Table structure for table `movie_cast`
--

CREATE TABLE `movie_cast` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `movie_id` bigint(20) UNSIGNED NOT NULL,
  `cast_id` bigint(20) UNSIGNED NOT NULL,
  `role` enum('actor','director') NOT NULL DEFAULT 'actor',
  `character_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `movie_genre`
--

CREATE TABLE `movie_genre` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `movie_id` bigint(20) UNSIGNED NOT NULL,
  `genre_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `movie_genre`
--

INSERT INTO `movie_genre` (`id`, `movie_id`, `genre_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, NULL),
(2, 1, 2, NULL, NULL),
(3, 1, 3, NULL, NULL),
(4, 1, 4, NULL, NULL),
(5, 1, 5, NULL, NULL),
(6, 1, 6, NULL, NULL),
(7, 2, 1, NULL, NULL),
(8, 2, 2, NULL, NULL),
(9, 2, 7, NULL, NULL),
(10, 2, 8, NULL, NULL),
(11, 3, 9, NULL, NULL),
(12, 3, 2, NULL, NULL),
(13, 3, 7, NULL, NULL),
(14, 3, 10, NULL, NULL),
(15, 3, 11, NULL, NULL),
(16, 3, 12, NULL, NULL),
(17, 4, 13, NULL, NULL),
(18, 4, 2, NULL, NULL),
(19, 4, 14, NULL, NULL),
(20, 4, 15, NULL, NULL),
(21, 4, 4, NULL, NULL),
(22, 4, 5, NULL, NULL),
(23, 5, 2, NULL, NULL),
(24, 5, 10, NULL, NULL),
(25, 5, 11, NULL, NULL),
(26, 5, 15, NULL, NULL),
(27, 5, 12, NULL, NULL),
(28, 6, 2, NULL, NULL),
(29, 6, 16, NULL, NULL),
(30, 6, 14, NULL, NULL),
(31, 6, 5, NULL, NULL),
(32, 6, 17, NULL, NULL),
(33, 6, 6, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `movie_language`
--

CREATE TABLE `movie_language` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `movie_id` bigint(20) UNSIGNED NOT NULL,
  `language_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('original','subtitle','dubbed') NOT NULL DEFAULT 'subtitle',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `movie_ratings`
-- (See below for the actual view)
--
CREATE TABLE `movie_ratings` (
`movie_id` bigint(20) unsigned
,`average_rating` decimal(14,4)
,`review_count` bigint(21)
,`excellent_count` decimal(22,0)
,`good_count` decimal(22,0)
,`poor_count` decimal(22,0)
);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\User', 1, 'auth_token', '06dce54ba3caed3c1fc9e2ab6f0cf2a54958968ff33ac06d66e7d6914760fc15', '[\"*\"]', NULL, NULL, '2026-01-04 19:19:04', '2026-01-04 19:19:04'),
(2, 'App\\Models\\User', 2, 'auth_token', '08a00cafcf48cae86f4a18c9a4920be6074fb2ed704ff8590678cbbe21536075', '[\"*\"]', NULL, NULL, '2026-01-04 19:22:47', '2026-01-04 19:22:47'),
(3, 'App\\Models\\User', 1, 'test', '12f8fd239dff1e6c8e9d1b819b98361a019568ccbd456538e722acd70687a5c6', '[\"*\"]', NULL, NULL, '2026-01-04 19:40:08', '2026-01-04 19:40:08'),
(4, 'App\\Models\\User', 1, 'test_token', 'f2527b2e75679e516ad3c48995d36f7fd918a3eff2a3521fce28bafa33115c86', '[\"*\"]', '2026-01-04 19:57:45', NULL, '2026-01-04 19:56:51', '2026-01-04 19:57:45'),
(5, 'App\\Models\\User', 1, 'fresh_test_token', '8464c869a0e9160ca00d2cbf3477e9a8f847f3f7b1ead93090e3d1b114de8845', '[\"*\"]', '2026-01-04 20:25:15', NULL, '2026-01-04 20:24:51', '2026-01-04 20:25:15'),
(9, 'App\\Models\\User', 1, 'auth_token', '2d5224dac44a56936bcdcc2142bdddddb32839c2eb1e0d4fd079e78d20d2b1e5', '[\"*\"]', '2026-01-04 21:14:48', NULL, '2026-01-04 21:07:34', '2026-01-04 21:14:48'),
(10, 'App\\Models\\User', 3, 'auth_token', '5f4fc13be572891c9c1e3456376da72594b0ef3029ad506df90f251a98ba7a38', '[\"*\"]', '2026-01-06 19:40:00', NULL, '2026-01-04 21:38:26', '2026-01-06 19:40:00'),
(11, 'App\\Models\\User', 4, 'auth_token', '840670046aebdc4a014ddf0bcfa57f0092a5e66d9226c73254ba9dfb115a16f1', '[\"*\"]', '2026-01-06 19:30:42', NULL, '2026-01-06 19:30:41', '2026-01-06 19:30:42'),
(12, 'App\\Models\\User', 3, 'auth_token', '4b5ff1d5d7ded805e2f450d3115496c1f14f47950a3517fcd8a6fc27483f4526', '[\"*\"]', '2026-01-06 20:40:46', NULL, '2026-01-06 19:40:23', '2026-01-06 20:40:46'),
(13, 'App\\Models\\User', 3, 'auth_token', '149aeae388534c6eb25c58a63503d46397c18ca79cafe899ad91943d9880c804', '[\"*\"]', '2026-01-06 20:47:05', NULL, '2026-01-06 20:44:25', '2026-01-06 20:47:05'),
(15, 'App\\Models\\User', 3, 'auth_token', '66833354b9de37e582ba5d3147001a9e6fa28527f017cc979b1ed1c9ba0c975e', '[\"*\"]', NULL, NULL, '2026-01-06 20:50:23', '2026-01-06 20:50:23');

-- --------------------------------------------------------

--
-- Table structure for table `pricing_rules`
--

CREATE TABLE `pricing_rules` (
  `pricing_rule_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `rule_type` enum('time_based','day_based','seat_based','movie_based') NOT NULL DEFAULT 'time_based',
  `conditions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`conditions`)),
  `adjustment_type` enum('fixed','percentage') NOT NULL DEFAULT 'fixed',
  `adjustment_value` decimal(10,2) NOT NULL,
  `priority` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `valid_from` date DEFAULT NULL,
  `valid_to` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `promotions`
--

CREATE TABLE `promotions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(255) NOT NULL COMMENT 'Mã khuyến mãi',
  `description` text DEFAULT NULL COMMENT 'Mô tả chi tiết',
  `discount_type` enum('percentage','fixed') NOT NULL DEFAULT 'percentage' COMMENT 'Loại giảm giá: % hoặc số tiền cố định',
  `discount_value` decimal(10,2) NOT NULL COMMENT 'Giá trị giảm giá',
  `min_purchase_amount` decimal(10,2) DEFAULT NULL COMMENT 'Số tiền tối thiểu để áp dụng',
  `max_discount_amount` decimal(10,2) DEFAULT NULL COMMENT 'Số tiền giảm tối đa (cho percentage)',
  `valid_from` datetime DEFAULT NULL COMMENT 'Ngày bắt đầu hiệu lực',
  `valid_to` datetime DEFAULT NULL COMMENT 'Ngày kết thúc hiệu lực',
  `max_uses` int(11) DEFAULT NULL COMMENT 'Số lần sử dụng tối đa',
  `current_uses` int(11) NOT NULL DEFAULT 0 COMMENT 'Số lần đã sử dụng',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Trạng thái kích hoạt',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `movie_id` bigint(20) UNSIGNED NOT NULL,
  `booking_id` bigint(20) UNSIGNED DEFAULT NULL,
  `is_verified_purchase` tinyint(1) NOT NULL DEFAULT 0,
  `rating` int(11) NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `room_id` bigint(20) UNSIGNED NOT NULL,
  `theater_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_seats` int(11) NOT NULL DEFAULT 0,
  `screen_type` enum('standard','vip','imax','4dx') NOT NULL DEFAULT 'standard',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`room_id`, `theater_id`, `name`, `total_seats`, `screen_type`, `created_at`, `updated_at`) VALUES
(1, 1, 'Room 1', 50, 'imax', '2026-01-04 20:13:10', '2026-01-04 20:13:10');

-- --------------------------------------------------------

--
-- Table structure for table `seats`
--

CREATE TABLE `seats` (
  `seat_id` bigint(20) UNSIGNED NOT NULL,
  `room_id` bigint(20) UNSIGNED NOT NULL,
  `row` varchar(255) NOT NULL,
  `number` int(11) NOT NULL,
  `seat_code` varchar(255) NOT NULL,
  `seat_type` varchar(255) NOT NULL DEFAULT 'standard',
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `seats`
--

INSERT INTO `seats` (`seat_id`, `room_id`, `row`, `number`, `seat_code`, `seat_type`, `is_available`, `created_at`, `updated_at`) VALUES
(1, 1, 'A', 1, 'A1', 'standard', 1, '2026-01-04 20:15:52', '2026-01-04 20:15:52'),
(2, 1, 'A', 2, 'A2', 'standard', 1, '2026-01-04 20:15:52', '2026-01-04 20:15:52'),
(3, 1, 'A', 3, 'A3', 'standard', 1, '2026-01-04 20:15:52', '2026-01-04 20:15:52'),
(4, 1, 'A', 4, 'A4', 'standard', 1, '2026-01-04 20:15:52', '2026-01-04 20:15:52'),
(5, 1, 'A', 5, 'A5', 'standard', 1, '2026-01-04 20:15:52', '2026-01-04 20:15:52'),
(6, 1, 'A', 6, 'A6', 'standard', 1, '2026-01-04 20:15:52', '2026-01-04 20:15:52'),
(7, 1, 'A', 7, 'A7', 'standard', 1, '2026-01-04 20:15:52', '2026-01-04 20:15:52'),
(8, 1, 'A', 8, 'A8', 'standard', 1, '2026-01-04 20:15:52', '2026-01-04 20:15:52'),
(9, 1, 'A', 9, 'A9', 'standard', 1, '2026-01-04 20:15:52', '2026-01-04 20:15:52'),
(10, 1, 'A', 10, 'A10', 'standard', 1, '2026-01-04 20:15:52', '2026-01-04 20:15:52'),
(11, 1, 'B', 1, 'B1', 'standard', 1, '2026-01-04 20:15:52', '2026-01-04 20:15:52'),
(12, 1, 'B', 2, 'B2', 'standard', 1, '2026-01-04 20:15:52', '2026-01-04 20:15:52'),
(13, 1, 'B', 3, 'B3', 'standard', 1, '2026-01-04 20:15:52', '2026-01-04 20:15:52'),
(14, 1, 'B', 4, 'B4', 'standard', 1, '2026-01-04 20:15:52', '2026-01-04 20:15:52'),
(15, 1, 'B', 5, 'B5', 'standard', 1, '2026-01-04 20:15:52', '2026-01-04 20:15:52'),
(16, 1, 'B', 6, 'B6', 'standard', 1, '2026-01-04 20:15:52', '2026-01-04 20:15:52'),
(17, 1, 'B', 7, 'B7', 'standard', 1, '2026-01-04 20:15:52', '2026-01-04 20:15:52'),
(18, 1, 'B', 8, 'B8', 'standard', 1, '2026-01-04 20:15:52', '2026-01-04 20:15:52'),
(19, 1, 'B', 9, 'B9', 'standard', 1, '2026-01-04 20:15:52', '2026-01-04 20:15:52'),
(20, 1, 'B', 10, 'B10', 'standard', 1, '2026-01-04 20:15:52', '2026-01-04 20:15:52'),
(21, 1, 'C', 1, 'C1', 'standard', 1, '2026-01-04 20:15:52', '2026-01-04 20:15:52'),
(22, 1, 'C', 2, 'C2', 'standard', 1, '2026-01-04 20:15:52', '2026-01-04 20:15:52'),
(23, 1, 'C', 3, 'C3', 'standard', 1, '2026-01-04 20:15:52', '2026-01-04 20:15:52'),
(24, 1, 'C', 4, 'C4', 'standard', 1, '2026-01-04 20:15:52', '2026-01-04 20:15:52'),
(25, 1, 'C', 5, 'C5', 'standard', 1, '2026-01-04 20:15:52', '2026-01-04 20:15:52'),
(26, 1, 'C', 6, 'C6', 'standard', 1, '2026-01-04 20:15:52', '2026-01-04 20:15:52'),
(27, 1, 'C', 7, 'C7', 'standard', 1, '2026-01-04 20:15:52', '2026-01-04 20:15:52'),
(28, 1, 'C', 8, 'C8', 'standard', 1, '2026-01-04 20:15:52', '2026-01-04 20:15:52'),
(29, 1, 'C', 9, 'C9', 'standard', 1, '2026-01-04 20:15:52', '2026-01-04 20:15:52'),
(30, 1, 'C', 10, 'C10', 'standard', 1, '2026-01-04 20:15:52', '2026-01-04 20:15:52'),
(31, 1, 'D', 1, 'D1', 'vip', 1, '2026-01-04 20:15:52', '2026-01-04 20:15:52'),
(32, 1, 'D', 2, 'D2', 'vip', 1, '2026-01-04 20:15:52', '2026-01-04 20:15:52'),
(33, 1, 'D', 3, 'D3', 'vip', 1, '2026-01-04 20:15:52', '2026-01-04 20:15:52'),
(34, 1, 'D', 4, 'D4', 'vip', 1, '2026-01-04 20:15:52', '2026-01-04 20:15:52'),
(35, 1, 'D', 5, 'D5', 'vip', 1, '2026-01-04 20:15:52', '2026-01-04 20:15:52'),
(36, 1, 'D', 6, 'D6', 'vip', 1, '2026-01-04 20:15:52', '2026-01-04 20:15:52'),
(37, 1, 'D', 7, 'D7', 'vip', 1, '2026-01-04 20:15:52', '2026-01-04 20:15:52'),
(38, 1, 'D', 8, 'D8', 'vip', 1, '2026-01-04 20:15:52', '2026-01-04 20:15:52'),
(39, 1, 'D', 9, 'D9', 'vip', 1, '2026-01-04 20:15:52', '2026-01-04 20:15:52'),
(40, 1, 'D', 10, 'D10', 'vip', 1, '2026-01-04 20:15:52', '2026-01-04 20:15:52'),
(41, 1, 'E', 1, 'E1', 'couple', 1, '2026-01-04 20:15:52', '2026-01-04 20:15:52'),
(42, 1, 'E', 2, 'E2', 'couple', 1, '2026-01-04 20:15:52', '2026-01-04 20:15:52'),
(43, 1, 'E', 3, 'E3', 'couple', 1, '2026-01-04 20:15:52', '2026-01-04 20:15:52'),
(44, 1, 'E', 4, 'E4', 'couple', 1, '2026-01-04 20:15:52', '2026-01-04 20:15:52'),
(45, 1, 'E', 5, 'E5', 'couple', 1, '2026-01-04 20:15:52', '2026-01-04 20:15:52'),
(46, 1, 'E', 6, 'E6', 'couple', 1, '2026-01-04 20:15:52', '2026-01-04 20:15:52'),
(47, 1, 'E', 7, 'E7', 'couple', 1, '2026-01-04 20:15:52', '2026-01-04 20:15:52'),
(48, 1, 'E', 8, 'E8', 'couple', 1, '2026-01-04 20:15:52', '2026-01-04 20:15:52'),
(49, 1, 'E', 9, 'E9', 'couple', 1, '2026-01-04 20:15:52', '2026-01-04 20:15:52'),
(50, 1, 'E', 10, 'E10', 'couple', 1, '2026-01-04 20:15:52', '2026-01-04 20:15:52');

-- --------------------------------------------------------

--
-- Table structure for table `seat_locks`
--

CREATE TABLE `seat_locks` (
  `lock_id` bigint(20) UNSIGNED NOT NULL,
  `seat_id` bigint(20) UNSIGNED NOT NULL,
  `showtime_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `locked_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `seat_types`
--

CREATE TABLE `seat_types` (
  `seat_type_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `base_extra_price` decimal(10,0) NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `color_code` varchar(7) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('kC32A1ptdqAXmVH0z68RCjaFxIDrQDeCHX3zyHgS', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiZlhFZXBsYXBUS0VhS1VST1BSb1ljOW5hb3ExUllnR1hWcG9YUkY4RiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1767748395),
('khFL06ntiUcs8vgMK8KEsXtVzEtFq1AxXs9LGuTC', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiZzZaMENVYzZKUGFBS05KcHZtUnVtRm1lak1ONUg3ZEtHa0tKaUY1diI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1767669361),
('MMqOXJqf6qPPB33uWOLCN1Dl1DqYntsbUoZxbxpr', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoid29HMjlkaDBBZG5oOGw4SlBjUTJoaFp2ZHZ3NENXcHRtRXNNZ3JPSiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1767587865),
('OiO0plU5oSJwEmelEp6dzYhOrzuBD4CtJwPqmWlq', NULL, '127.0.0.1', 'PostmanRuntime/7.51.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiMGh2Y0pxdHduZjI2N21qZ1J3ZXdYUlRSeFBTYWRDRWNGTFZncVY5dyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1767586354),
('pRmmmsl8SEIwW1pl9ZOS2w7wHF9Kbbr2dGVIemme', NULL, '127.0.0.1', 'PostmanRuntime/7.51.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiOHkyVmt3Mkh0dW94eWtQWjcxODU0UTE2TTNFSkdzMlZ4U3l2cnU1QyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1767586144),
('Yf5JMBCSWikt2fQrzuGFFGO4WgGLiwxWLuRbbWby', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoic3Npbmc1N1ljYjgyQWltVWF5UTVlOXRVSlg5RVNIRXBlbTNZZEJsTSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1767748395);

-- --------------------------------------------------------

--
-- Table structure for table `showtimes`
--

CREATE TABLE `showtimes` (
  `showtime_id` bigint(20) UNSIGNED NOT NULL,
  `movie_id` bigint(20) UNSIGNED NOT NULL,
  `room_id` bigint(20) UNSIGNED NOT NULL,
  `show_date` date DEFAULT NULL,
  `show_time` varchar(255) DEFAULT NULL,
  `start_time` datetime DEFAULT NULL,
  `base_price` decimal(10,0) NOT NULL,
  `vip_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `available_seats` int(11) NOT NULL DEFAULT 0,
  `status` enum('scheduled','ongoing','completed','cancelled') NOT NULL DEFAULT 'scheduled',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `showtimes`
--

INSERT INTO `showtimes` (`showtime_id`, `movie_id`, `room_id`, `show_date`, `show_time`, `start_time`, `base_price`, `vip_price`, `is_active`, `available_seats`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2026-01-05', '10:00', '2026-01-05 10:00:00', 100000, 120000.00, 1, 50, 'scheduled', '2026-01-04 20:19:13', '2026-01-04 20:19:13'),
(2, 1, 1, '2026-01-05', '14:00', '2026-01-05 14:00:00', 100000, 120000.00, 1, 50, 'scheduled', '2026-01-04 20:19:13', '2026-01-04 20:19:13'),
(3, 1, 1, '2026-01-05', '18:00', '2026-01-05 18:00:00', 100000, 120000.00, 1, 50, 'scheduled', '2026-01-04 20:19:13', '2026-01-04 20:19:13'),
(4, 1, 1, '2026-01-05', '21:00', '2026-01-05 21:00:00', 100000, 120000.00, 1, 50, 'scheduled', '2026-01-04 20:19:13', '2026-01-04 20:19:13'),
(5, 1, 1, '2026-01-06', '10:00', '2026-01-06 10:00:00', 100000, 120000.00, 1, 50, 'scheduled', '2026-01-04 20:19:13', '2026-01-04 20:19:13'),
(6, 1, 1, '2026-01-06', '14:00', '2026-01-06 14:00:00', 100000, 120000.00, 1, 50, 'scheduled', '2026-01-04 20:19:13', '2026-01-04 20:19:13'),
(7, 1, 1, '2026-01-06', '18:00', '2026-01-06 18:00:00', 100000, 120000.00, 1, 50, 'scheduled', '2026-01-04 20:19:13', '2026-01-04 20:19:13'),
(8, 1, 1, '2026-01-06', '21:00', '2026-01-06 21:00:00', 100000, 120000.00, 1, 50, 'scheduled', '2026-01-04 20:19:13', '2026-01-04 20:19:13');

-- --------------------------------------------------------

--
-- Table structure for table `theaters`
--

CREATE TABLE `theaters` (
  `theater_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `city_id` bigint(20) UNSIGNED NOT NULL,
  `address` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `facilities` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `theaters`
--

INSERT INTO `theaters` (`theater_id`, `name`, `slug`, `city_id`, `address`, `phone`, `description`, `image_url`, `latitude`, `longitude`, `is_active`, `facilities`, `created_at`, `updated_at`) VALUES
(1, 'CGV Vincom Đồng Khởi', 'cgv-vincom-dong-khoi', 1, 'Tầng 3, TTTM Vincom Center B, 72 Lê Thánh Tôn, Bến Nghé, Quận 1', '1900 6017', 'Rạp chiếu phim hiện đại nhất tại trung tâm thành phố.', NULL, NULL, NULL, 1, NULL, '2026-01-04 20:13:10', '2026-01-04 20:13:10');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` bigint(20) UNSIGNED NOT NULL,
  `booking_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `transaction_code` varchar(30) NOT NULL,
  `amount` decimal(10,0) NOT NULL,
  `payment_method` enum('cash','credit_card','momo','zalopay','vnpay') NOT NULL DEFAULT 'cash',
  `status` enum('pending','success','failed','refunded') NOT NULL DEFAULT 'pending',
  `payment_details` text DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `provider` varchar(255) DEFAULT NULL,
  `provider_id` varchar(255) DEFAULT NULL,
  `avatar` text DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `phone`, `date_of_birth`, `role`, `provider`, `provider_id`, `avatar`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Test User', 'test@example.com', '2026-01-04 19:17:57', '$2y$12$s24cPSiLNXyah.Ga0NEbDezdGspflI3GJM0jCAmfC18LlaLWXAGzu', NULL, NULL, 'user', NULL, NULL, NULL, 'hNxytFXdvW', '2026-01-04 19:17:58', '2026-01-04 19:17:58'),
(2, 'Nguyễn Văn A', 'vana@example.com', NULL, '$2y$12$HDO/VZqCsZ.KcsaUjUVFj..oh9nfOAuqLMf53jjo/5/2qyahCBevS', '0123456789', NULL, 'user', NULL, NULL, NULL, NULL, '2026-01-04 19:22:47', '2026-01-04 19:22:47'),
(3, 'Le Tri Phuong', 'letriphuong23.12@gmail.com', NULL, '$2y$12$V.8yw.ncO26Au/1292mV4ODQDU.0WzJzft.BvI2KZlVgFRkie0mOm', '0986651866', NULL, 'user', NULL, NULL, NULL, NULL, '2026-01-04 21:38:26', '2026-01-04 21:38:26'),
(4, 'Test User 36506882', 'testuser36506882@example.com', NULL, '$2y$12$XDp4RWRiuTFcj1b69FqfKeBB6qV1rTMsx9lm8aAVUAr3opYLQuKxS', '0123456789', NULL, 'user', NULL, NULL, NULL, NULL, '2026-01-06 19:30:41', '2026-01-06 19:30:41'),
(5, 'Ngo Trang Vinh', 'vinh@gmail.com', NULL, '$2y$12$Or8IVOwehD7IEh4alpEgiuxQeAmYvFJ9/xyb3Wsp80yZnKnN2eS9K', '0987654321', NULL, 'user', NULL, NULL, NULL, NULL, '2026-01-06 20:47:44', '2026-01-06 20:47:44');

-- --------------------------------------------------------

--
-- Structure for view `movie_ratings`
--
DROP TABLE IF EXISTS `movie_ratings`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `movie_ratings`  AS SELECT `m`.`movie_id` AS `movie_id`, coalesce(avg(`r`.`rating`),0) AS `average_rating`, coalesce(count(`r`.`review_id`),0) AS `review_count`, coalesce(sum(case when `r`.`rating` >= 8 then 1 else 0 end),0) AS `excellent_count`, coalesce(sum(case when `r`.`rating` >= 6 and `r`.`rating` < 8 then 1 else 0 end),0) AS `good_count`, coalesce(sum(case when `r`.`rating` < 6 then 1 else 0 end),0) AS `poor_count` FROM (`movies` `m` left join `reviews` `r` on(`m`.`movie_id` = `r`.`movie_id`)) GROUP BY `m`.`movie_id` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD UNIQUE KEY `bookings_booking_code_unique` (`booking_code`),
  ADD KEY `bookings_showtime_id_foreign` (`showtime_id`),
  ADD KEY `bookings_booking_code_index` (`booking_code`),
  ADD KEY `bookings_status_index` (`status`),
  ADD KEY `bookings_user_id_status_index` (`user_id`,`status`),
  ADD KEY `bookings_created_at_index` (`created_at`);

--
-- Indexes for table `booking_combos`
--
ALTER TABLE `booking_combos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_combos_combo_id_foreign` (`combo_id`),
  ADD KEY `booking_combos_booking_id_combo_id_index` (`booking_id`,`combo_id`);

--
-- Indexes for table `booking_details`
--
ALTER TABLE `booking_details`
  ADD PRIMARY KEY (`detail_id`),
  ADD UNIQUE KEY `tickets_showtime_id_seat_id_unique` (`showtime_id`,`seat_id`),
  ADD UNIQUE KEY `tickets_ticket_code_unique` (`ticket_code`),
  ADD KEY `booking_details_booking_id_foreign` (`booking_id`),
  ADD KEY `booking_details_seat_id_foreign` (`seat_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cast`
--
ALTER TABLE `cast`
  ADD PRIMARY KEY (`cast_id`);

--
-- Indexes for table `cities`
--
ALTER TABLE `cities`
  ADD PRIMARY KEY (`city_id`),
  ADD UNIQUE KEY `cities_name_unique` (`name`),
  ADD UNIQUE KEY `cities_slug_unique` (`slug`),
  ADD KEY `cities_slug_index` (`slug`);

--
-- Indexes for table `combos`
--
ALTER TABLE `combos`
  ADD PRIMARY KEY (`combo_id`);

--
-- Indexes for table `combo_items`
--
ALTER TABLE `combo_items`
  ADD PRIMARY KEY (`combo_item_id`),
  ADD KEY `combo_items_combo_id_index` (`combo_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `genres`
--
ALTER TABLE `genres`
  ADD PRIMARY KEY (`genre_id`),
  ADD UNIQUE KEY `genres_name_unique` (`name`),
  ADD UNIQUE KEY `genres_slug_unique` (`slug`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `languages`
--
ALTER TABLE `languages`
  ADD PRIMARY KEY (`language_id`),
  ADD UNIQUE KEY `languages_name_unique` (`name`),
  ADD UNIQUE KEY `languages_code_unique` (`code`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `movies`
--
ALTER TABLE `movies`
  ADD PRIMARY KEY (`movie_id`),
  ADD UNIQUE KEY `movies_slug_unique` (`slug`),
  ADD KEY `movies_status_index` (`status`),
  ADD KEY `movies_release_date_index` (`release_date`),
  ADD KEY `movies_status_release_date_index` (`status`,`release_date`);

--
-- Indexes for table `movie_cast`
--
ALTER TABLE `movie_cast`
  ADD PRIMARY KEY (`id`),
  ADD KEY `movie_cast_movie_id_foreign` (`movie_id`),
  ADD KEY `movie_cast_cast_id_foreign` (`cast_id`);

--
-- Indexes for table `movie_genre`
--
ALTER TABLE `movie_genre`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `movie_genre_movie_id_genre_id_unique` (`movie_id`,`genre_id`),
  ADD KEY `movie_genre_genre_id_foreign` (`genre_id`);

--
-- Indexes for table `movie_language`
--
ALTER TABLE `movie_language`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `movie_language_movie_id_language_id_type_unique` (`movie_id`,`language_id`,`type`),
  ADD KEY `movie_language_language_id_foreign` (`language_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indexes for table `pricing_rules`
--
ALTER TABLE `pricing_rules`
  ADD PRIMARY KEY (`pricing_rule_id`);

--
-- Indexes for table `promotions`
--
ALTER TABLE `promotions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `promotions_code_unique` (`code`),
  ADD KEY `promotions_code_index` (`code`),
  ADD KEY `promotions_valid_from_valid_to_index` (`valid_from`,`valid_to`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD UNIQUE KEY `reviews_user_id_movie_id_unique` (`user_id`,`movie_id`),
  ADD UNIQUE KEY `reviews_booking_id_unique` (`booking_id`),
  ADD KEY `reviews_movie_id_index` (`movie_id`),
  ADD KEY `reviews_rating_index` (`rating`),
  ADD KEY `reviews_movie_id_created_at_index` (`movie_id`,`created_at`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`room_id`),
  ADD KEY `screens_theater_id_foreign` (`theater_id`);

--
-- Indexes for table `seats`
--
ALTER TABLE `seats`
  ADD PRIMARY KEY (`seat_id`),
  ADD UNIQUE KEY `seats_screen_id_row_number_unique` (`room_id`,`row`,`number`);

--
-- Indexes for table `seat_locks`
--
ALTER TABLE `seat_locks`
  ADD PRIMARY KEY (`lock_id`),
  ADD UNIQUE KEY `seat_locks_showtime_id_seat_id_unique` (`showtime_id`,`seat_id`),
  ADD KEY `seat_locks_seat_id_foreign` (`seat_id`),
  ADD KEY `seat_locks_user_id_foreign` (`user_id`),
  ADD KEY `seat_locks_expires_at_index` (`expires_at`),
  ADD KEY `seat_locks_showtime_id_expires_at_index` (`showtime_id`,`expires_at`);

--
-- Indexes for table `seat_types`
--
ALTER TABLE `seat_types`
  ADD PRIMARY KEY (`seat_type_id`),
  ADD UNIQUE KEY `seat_types_code_unique` (`code`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `showtimes`
--
ALTER TABLE `showtimes`
  ADD PRIMARY KEY (`showtime_id`),
  ADD UNIQUE KEY `showtimes_screen_id_start_time_unique` (`room_id`,`start_time`),
  ADD KEY `showtimes_start_time_index` (`start_time`),
  ADD KEY `showtimes_status_index` (`status`),
  ADD KEY `showtimes_movie_id_start_time_index` (`movie_id`,`start_time`);

--
-- Indexes for table `theaters`
--
ALTER TABLE `theaters`
  ADD PRIMARY KEY (`theater_id`),
  ADD KEY `theaters_city_id_foreign` (`city_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD UNIQUE KEY `transactions_transaction_code_unique` (`transaction_code`),
  ADD KEY `transactions_transaction_code_index` (`transaction_code`),
  ADD KEY `transactions_status_index` (`status`),
  ADD KEY `transactions_payment_method_index` (`payment_method`),
  ADD KEY `transactions_user_id_status_index` (`user_id`,`status`),
  ADD KEY `transactions_paid_at_index` (`paid_at`),
  ADD KEY `transactions_booking_id_foreign` (`booking_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_provider_provider_id_index` (`provider`,`provider_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `booking_combos`
--
ALTER TABLE `booking_combos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `booking_details`
--
ALTER TABLE `booking_details`
  MODIFY `detail_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cast`
--
ALTER TABLE `cast`
  MODIFY `cast_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cities`
--
ALTER TABLE `cities`
  MODIFY `city_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `combos`
--
ALTER TABLE `combos`
  MODIFY `combo_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `combo_items`
--
ALTER TABLE `combo_items`
  MODIFY `combo_item_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `genres`
--
ALTER TABLE `genres`
  MODIFY `genre_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `languages`
--
ALTER TABLE `languages`
  MODIFY `language_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `movies`
--
ALTER TABLE `movies`
  MODIFY `movie_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `movie_cast`
--
ALTER TABLE `movie_cast`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `movie_genre`
--
ALTER TABLE `movie_genre`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `movie_language`
--
ALTER TABLE `movie_language`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `pricing_rules`
--
ALTER TABLE `pricing_rules`
  MODIFY `pricing_rule_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `promotions`
--
ALTER TABLE `promotions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `room_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `seats`
--
ALTER TABLE `seats`
  MODIFY `seat_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `seat_locks`
--
ALTER TABLE `seat_locks`
  MODIFY `lock_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `seat_types`
--
ALTER TABLE `seat_types`
  MODIFY `seat_type_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `showtimes`
--
ALTER TABLE `showtimes`
  MODIFY `showtime_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `theaters`
--
ALTER TABLE `theaters`
  MODIFY `theater_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_showtime_id_foreign` FOREIGN KEY (`showtime_id`) REFERENCES `showtimes` (`showtime_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `booking_combos`
--
ALTER TABLE `booking_combos`
  ADD CONSTRAINT `booking_combos_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `booking_combos_combo_id_foreign` FOREIGN KEY (`combo_id`) REFERENCES `combos` (`combo_id`) ON DELETE CASCADE;

--
-- Constraints for table `booking_details`
--
ALTER TABLE `booking_details`
  ADD CONSTRAINT `booking_details_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `booking_details_seat_id_foreign` FOREIGN KEY (`seat_id`) REFERENCES `seats` (`seat_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tickets_showtime_id_foreign` FOREIGN KEY (`showtime_id`) REFERENCES `showtimes` (`showtime_id`) ON DELETE CASCADE;

--
-- Constraints for table `combo_items`
--
ALTER TABLE `combo_items`
  ADD CONSTRAINT `combo_items_combo_id_foreign` FOREIGN KEY (`combo_id`) REFERENCES `combos` (`combo_id`) ON DELETE CASCADE;

--
-- Constraints for table `movie_cast`
--
ALTER TABLE `movie_cast`
  ADD CONSTRAINT `movie_cast_cast_id_foreign` FOREIGN KEY (`cast_id`) REFERENCES `cast` (`cast_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `movie_cast_movie_id_foreign` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`movie_id`) ON DELETE CASCADE;

--
-- Constraints for table `movie_genre`
--
ALTER TABLE `movie_genre`
  ADD CONSTRAINT `movie_genre_genre_id_foreign` FOREIGN KEY (`genre_id`) REFERENCES `genres` (`genre_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `movie_genre_movie_id_foreign` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`movie_id`) ON DELETE CASCADE;

--
-- Constraints for table `movie_language`
--
ALTER TABLE `movie_language`
  ADD CONSTRAINT `movie_language_language_id_foreign` FOREIGN KEY (`language_id`) REFERENCES `languages` (`language_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `movie_language_movie_id_foreign` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`movie_id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_movie_id_foreign` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`movie_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rooms`
--
ALTER TABLE `rooms`
  ADD CONSTRAINT `screens_theater_id_foreign` FOREIGN KEY (`theater_id`) REFERENCES `theaters` (`theater_id`) ON DELETE CASCADE;

--
-- Constraints for table `seats`
--
ALTER TABLE `seats`
  ADD CONSTRAINT `seats_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`room_id`) ON DELETE CASCADE;

--
-- Constraints for table `seat_locks`
--
ALTER TABLE `seat_locks`
  ADD CONSTRAINT `seat_locks_seat_id_foreign` FOREIGN KEY (`seat_id`) REFERENCES `seats` (`seat_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `seat_locks_showtime_id_foreign` FOREIGN KEY (`showtime_id`) REFERENCES `showtimes` (`showtime_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `seat_locks_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `showtimes`
--
ALTER TABLE `showtimes`
  ADD CONSTRAINT `showtimes_movie_id_foreign` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`movie_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `showtimes_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`room_id`) ON DELETE CASCADE;

--
-- Constraints for table `theaters`
--
ALTER TABLE `theaters`
  ADD CONSTRAINT `theaters_city_id_foreign` FOREIGN KEY (`city_id`) REFERENCES `cities` (`city_id`);

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
