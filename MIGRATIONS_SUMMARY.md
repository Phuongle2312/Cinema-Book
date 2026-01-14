# DANH SÁCH CÁC FILE MIGRATION - CINEMA BOOKING SYSTEM

Tài liệu này tổng hợp tất cả các file migration của dự án Cinema-Book.

---

## 📋 Tổng quan

- **Tổng số file migration**: 28 files
- **Thư mục**: `backend/database/migrations/`
- **Database**: `cinema_booking`
- **Generated**: <?= date('Y-m-d H:i:s') ?>

---

## 📂 Danh sách Migration Files (Theo thứ tự thời gian)

### 1. Core Laravel Migrations

#### `0001_01_01_000000_create_users_table.php`
- **Mục đích**: Tạo bảng users (người dùng)
- **Bảng**: `users`
- **Columns chính**:
  - id (bigint, PK, auto_increment)
  - name (varchar)
  - email (varchar, unique)
  - password (varchar)
  - role (enum: 'customer', 'admin')
  - phone (varchar, nullable)
  - avatar_url (varchar, nullable)
  - email_verified_at (timestamp, nullable)
  - remember_token (varchar, nullable)
  - created_at, updated_at (timestamps)

#### `0001_01_01_000001_create_cache_table.php`
- **Mục đích**: Tạo bảng cache
- **Bảng**: `cache`, `cache_locks`

#### `0001_01_01_000002_create_jobs_table.php`
- **Mục đích**: Tạo bảng jobs (queue)
- **Bảng**: `jobs`, `job_batches`, `failed_jobs`

---

### 2. Core Cinema Booking Tables

#### `2025_12_22_011831_create_core_tables.php` ⭐ (CORE)
- **Mục đích**: Tạo tất cả bảng chính của hệ thống
- **Bảng được tạo**:
  1. **genres** (Thể loại phim)
  2. **movies** (Phim)
  3. **movie_genres** (Pivot table: Movies - Genres)
  4. **theaters** (Rạp chiếu)
  5. **rooms** (Phòng chiếu)
  6. **seats** (Ghế ngồi)
  7. **seat_types** (Loại ghế: VIP, Standard, Couple)
  8. **showtimes** (Suất chiếu)
  9. **seat_locks** (Khóa ghế tạm thời)
  10. **bookings** (Đơn đặt vé)
  11. **booking_details** (Chi tiết đặt vé)
  12. **vouchers** (Mã giảm giá)
  13. **reviews** (Đánh giá phim)
  14. **pricing_rules** (Quy tắc định giá)

**Chi tiết bảng chính**:

```sql
-- MOVIES
CREATE TABLE movies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    duration INT NOT NULL COMMENT 'Thời lượng (phút)',
    release_date DATE NOT NULL,
    poster_url VARCHAR(255),
    trailer_url VARCHAR(255),
    language VARCHAR(50) DEFAULT 'Tiếng Việt',
    director VARCHAR(255),
    cast TEXT,
    age_rating VARCHAR(10) COMMENT 'P, T13, T16, T18',
    status ENUM('coming_soon', 'showing', 'ended') DEFAULT 'coming_soon',
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- THEATERS
CREATE TABLE theaters (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    location VARCHAR(255) NOT NULL,
    city VARCHAR(100) NOT NULL,
    address TEXT,
    phone VARCHAR(20),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- ROOMS
CREATE TABLE rooms (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    theater_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(50) NOT NULL,
    total_seats INT NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (theater_id) REFERENCES theaters(id) ON DELETE CASCADE
);

-- SEATS
CREATE TABLE seats (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    room_id BIGINT UNSIGNED NOT NULL,
    seat_number VARCHAR(10) NOT NULL COMMENT 'e.g A1, B5',
    row VARCHAR(5) NOT NULL COMMENT 'A, B, C...',
    seat_column INT NOT NULL COMMENT '1, 2, 3...',
    seat_type_id BIGINT UNSIGNED,
    is_available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (seat_type_id) REFERENCES seat_types(id)
);

-- SHOWTIMES
CREATE TABLE showtimes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    movie_id BIGINT UNSIGNED NOT NULL,
    room_id BIGINT UNSIGNED NOT NULL,
    start_time DATETIME NOT NULL,
    base_price DECIMAL(10,2) NOT NULL COMMENT 'Giá vé cơ bản',
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
);

-- SEAT_LOCKS (Khóa ghế tạm thời 5-10 phút)
CREATE TABLE seat_locks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    seat_id BIGINT UNSIGNED NOT NULL,
    showtime_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP,
    FOREIGN KEY (seat_id) REFERENCES seats(id) ON DELETE CASCADE,
    FOREIGN KEY (showtime_id) REFERENCES showtimes(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- BOOKINGS
CREATE TABLE bookings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    showtime_id BIGINT UNSIGNED NOT NULL,
    booking_code VARCHAR(20) UNIQUE NOT NULL,
    ticket_code VARCHAR(50) UNIQUE COMMENT 'QR Code',
    total_price DECIMAL(10,2) NOT NULL,
    combo_price DECIMAL(10,2) DEFAULT 0,
    final_price DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    payment_method VARCHAR(50),
    payment_status ENUM('unpaid', 'paid', 'refunded') DEFAULT 'unpaid',
    voucher_id BIGINT UNSIGNED,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (showtime_id) REFERENCES showtimes(id) ON DELETE CASCADE,
    FOREIGN KEY (voucher_id) REFERENCES vouchers(id) ON DELETE SET NULL
);

-- BOOKING_DETAILS
CREATE TABLE booking_details (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id BIGINT UNSIGNED NOT NULL,
    seat_id BIGINT UNSIGNED NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (seat_id) REFERENCES seats(id) ON DELETE CASCADE
);
```

---

### 3. Additional Features

#### `2025_12_24_043103_create_combos_table.php`
- **Bảng**: `combos`
- **Mục đích**: Combo bắp nước
- **Columns**:
  - id, name, description, price, image_url, is_available
  - created_at, updated_at

#### `2025_12_24_043115_create_booking_combos_table.php`
- **Bảng**: `booking_combos`
- **Mục đích**: Liên kết booking với combo
- **Columns**:
  - id, booking_id, combo_id, quantity, price

#### `2025_12_24_043119_modify_reviews_add_booking_id.php`
- **Thay đổi**: Thêm `booking_id` vào bảng `reviews`
- **Mục đích**: Chỉ cho phép review nếu đã đặt vé

#### `2025_12_24_043122_modify_showtimes_remove_end_time.php`
- **Thay đổi**: Xóa cột `end_time` khỏi bảng `showtimes`
- **Lý do**: Tính toán tự động từ duration + cleaning time

#### `2025_12_24_043125_modify_bookings_add_combo_fields.php`
- **Thay đổi**: Thêm `combo_price` vào bảng `bookings`

#### `2025_12_26_011617_create_notifications_table.php`
- **Bảng**: `notifications`
- **Mục đích**: Thông báo cho user

#### `2025_12_26_011617_create_promotions_table.php`
- **Bảng**: `promotions`
- **Mục đích**: Chương trình khuyến mãi

#### `2025_12_26_040735_rename_screens_to_rooms_table.php`
- **Thay đổi**: Đổi tên `screens` → `rooms`

#### `2025_12_26_040738_rename_tickets_to_booking_details_table.php`
- **Thay đổi**: Đổi tên `tickets` → `booking_details`

#### `2025_12_29_024150_create_personal_access_tokens_table.php`
- **Bảng**: `personal_access_tokens`
- **Mục đích**: Laravel Sanctum authentication

---

### 4. Enhancements & Optimizations (2026)

#### `2026_01_05_000001_create_cities_table.php`
- **Bảng**: `cities`
- **Mục đích**: Quản lý thành phố (Hà Nội, TP.HCM, Đà Nẵng...)
- **Thay đổi**: Thêm FK `city_id` vào bảng `theaters`

#### `2026_01_05_000002_create_combo_items_table.php`
- **Bảng**: `combo_items`
- **Mục đích**: Chi tiết combo (1 Bắp + 2 Nước...)

#### `2026_01_05_000003_remove_movie_rating_column.php`
- **Thay đổi**: Xóa cột `rating` khỏi `movies`
- **Lý do**: Tính toán từ bảng `reviews`

#### `2026_01_05_000004_add_performance_indexes.php`
- **Mục đích**: Thêm indexes để tối ưu performance
- **Indexes**:
  - `showtimes.start_time`
  - `bookings.status`
  - `seat_locks.expires_at`
  - `movies.status`

#### `2026_01_05_000005_cleanup_and_standardize_flow.php`
- **Mục đích**: Chuẩn hóa flow và cleanup

#### `2026_01_05_031221_add_columns_to_theaters_table.php`
- **Thay đổi**: Thêm columns cho `theaters`

#### `2026_01_05_031431_add_columns_to_seats_table.php`
- **Thay đổi**: Thêm columns cho `seats`

#### `2026_01_05_031625_add_columns_to_showtimes_table.php`
- **Thay đổi**: Thêm columns cho `showtimes`

#### `2026_01_05_032038_add_columns_to_seats_table.php`
- **Thay đổi**: Thêm `type` và `extra_price` cho `seats`

#### `2026_01_05_032515_add_columns_to_showtimes_table.php`
- **Thay đổi**: Thêm columns bổ sung cho `showtimes`

#### `2026_01_05_040000_add_is_featured_to_movies_table.php`
- **Thay đổi**: Thêm `is_featured` cho `movies`
- **Mục đích**: Đánh dấu phim nổi bật

#### `2026_01_09_000000_create_sessions_table_fix.php`
- **Bảng**: `sessions`
- **Mục đích**: Fix session table

#### `2026_01_12_000000_restore_booking_seats_table.php`
- **Bảng**: `booking_seats`
- **Mục đích**: Restore bảng booking_seats (thay thế booking_details trong một số trường hợp)

---

## 🗂️ ERD (Entity Relationship Diagram)

```
┌─────────┐
│  users  │
└────┬────┘
     │
     ├─────► bookings ◄──── showtimes ◄──┬── movies
     │           │                        │
     │           │                        └── rooms ◄── theaters ◄── cities
     │           │                                  │
     │           └──────► booking_details ◄─────────┼── seats ◄── seat_types
     │                           │                  │
     │                           └── booking_combos │
     │                                   │          │
     └────────────────────────────► seat_locks ─────┘

┌──────────┐      ┌─────────────┐      ┌─────────┐
│  movies  ├──────┤movie_genres ├──────┤ genres  │
└──────────┘      └─────────────┘      └─────────┘

┌──────────┐      ┌─────────┐
│ bookings ├──────┤ reviews │
└──────────┘      └─────────┘

┌──────────┐      ┌──────────┐
│ combos   ├──────┤combo_items│
└──────────┘      └──────────┘
```

---

## 📊 Tổng kết các bảng chính

| # | Tên Bảng | Mục đích | Row ước tính |
|---|----------|----------|--------------|
| 1 | users | Người dùng | ~1000 |
| 2 | movies | Phim | ~100 |
| 3 | genres | Thể loại phim | ~20 |
| 4 | movie_genres | Movies ↔ Genres | ~300 |
| 5 | cities | Thành phố | ~10 |
| 6 | theaters | Rạp chiếu | ~50 |
| 7 | rooms | Phòng chiếu | ~200 |
| 8 | seats | Ghế ngồi | ~20,000 |
| 9 | seat_types | Loại ghế | ~4 |
| 10 | showtimes | Suất chiếu | ~5,000 |
| 11 | seat_locks | Khóa ghế | ~500 (tạm thời) |
| 12 | bookings | Đơn đặt vé | ~10,000 |
| 13 | booking_details | Chi tiết vé | ~30,000 |
| 14 | booking_seats | Chi tiết ghế đặt | ~30,000 |
| 15 | combos | Combo bắp nước | ~20 |
| 16 | combo_items | Chi tiết combo | ~60 |
| 17 | booking_combos | Combo trong đơn | ~8,000 |
| 18 | vouchers | Mã giảm giá | ~100 |
| 19 | reviews | Đánh giá phim | ~3,000 |
| 20 | pricing_rules | Quy tắc giá | ~50 |
| 21 | promotions | Khuyến mãi | ~30 |
| 22 | notifications | Thông báo | ~5,000 |

---

## 🚀 Hướng dẫn sử dụng

### 1. Reset Database (Fresh Migration)
```bash
cd backend
php artisan migrate:fresh --seed
```

### 2. Rollback Migration
```bash
php artisan migrate:rollback
```

### 3. Chạy lại Migration
```bash
php artisan migrate
```

### 4. Kiểm tra status
```bash
php artisan migrate:status
```

---

## 📝 Ghi chú quan trọng

### ⚠️ Seat Locking Logic
- **Timeout**: 5-10 phút (config: `SEAT_LOCK_TIMEOUT=6`)
- **Bảng**: `seat_locks`
- **Cleanup**: Scheduled job xóa lock hết hạn

### 💳 Payment Flow
1. User chọn ghế → Tạo `seat_locks`
2. Create `booking` với status = `pending`
3. Payment confirmation → Update status = `confirmed`
4. Delete `seat_locks` → Insert `booking_details`

### 🎟️ E-Ticket Generation
- **Trigger**: Khi `booking.status` = `confirmed`
- **Fields**: `booking_code`, `ticket_code` (QR)
- **Email**: Gửi eTicket qua email

---

*Document generated: <?= date('Y-m-d H:i:s') ?>*
