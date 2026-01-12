CREATE TABLE `rooms` (
  `room_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `theater_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_seats` int(11) NOT NULL DEFAULT 0,
  `screen_type` enum('standard','vip','imax','4dx') NOT NULL DEFAULT 'standard',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`room_id`),
  KEY `screens_theater_id_foreign` (`theater_id`),
  CONSTRAINT `screens_theater_id_foreign` FOREIGN KEY (`theater_id`) REFERENCES `theaters` (`theater_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci