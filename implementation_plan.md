# Project Audit: Database Cleanup and Flow Standardization

## Mục Tiêu

Hệ thống hóa lại database sau các bước rename và chuẩn hóa 3NF:
1. Loại bỏ các bảng dư thừa (legacy tables).
2. Nhất quán hóa luồng dữ liệu (Standard Flow).
3. Đảm bảo toàn vẹn dữ liệu (Foreign Keys & Constraints).

## Proposed Changes

### 1. Database Cleanup (Loại bỏ bảng thừa)

#### [DELETE] `booking_seats` Table
- **Lý do**: Bảng này dư thừa so với `booking_details`. `booking_details` có đầy đủ thông tin về giá, loại ghế hơn.
- **Hành động**: Xóa bảng `booking_seats` sau khi migrate relationship sang `booking_details`.

#### [DELETE] `screens` Table (Nếu còn tồn tại)
- **Lý do**: Đã được rename thành `rooms`.

#### [DELETE] Unused Columns
- Rà soát các cột `nullable()` nhưng không bao giờ có dữ liệu.

---

### 2. Flow Standardization (Chuẩn hóa luồng)

#### Booking Flow (Luồng Đặt Vé Chuẩn)
Luồng dữ liệu cần được khóa chặt chẽ:
1. **Seat Selection**: Tạo `seat_locks` (timeout 5-10p).
2. **Booking Creation**: Tạo `bookings` (status: `pending`).
3. **Detail Record**: Tạo `booking_details` (linked to `bookings`).
4. **Combo Record**: Tạo `booking_combos` (linked to `bookings`).
5. **Payment**: Tạo `transactions` (linked to `bookings`).
6. **Confirmation**: Khi `transactions.status = completed` -> `bookings.status = confirmed` -> Xóa `seat_locks`.

#### Database Level Constraints
- Thêm `ON DELETE CASCADE` cho các bảng chi tiết:
  - `booking_details` (cascade từ `bookings`)
  - `booking_combos` (cascade từ `bookings`)
  - `transactions` (cascade từ `bookings` hoặc keep for audit?) -> Thường nên giữ audit nhưng cascade delete nếu booking bị xóa cứng.
- Đảm bảo `unique` constraint cho `seat_locks` (tránh trùng ghế).

---

### 3. File Updates

#### [MODIFY] [Booking.php](file:///e:/Github/Cinema-Book/backend/app/Models/Booking.php)
- Cập nhật relationship `seats()` để dùng bảng `booking_details` thay vì `booking_seats`.
- Cập nhật `bookingSeats()` -> `bookingDetails()`.

#### [NEW] [BookingDetail.php](file:///e:/Github/Cinema-Book/backend/app/Models/BookingDetail.php)
- Tạo model mới tương ứng với bảng `booking_details`.
- Xóa model old `BookingSeat.php`.

#### [MODIFY] [Seat.php](file:///e:/Github/Cinema-Book/backend/app/Models/Seat.php)
- Cập nhật relationship `bookings()`.

---

## Migration Plan

### [NEW] [2026_01_05_cleanup_and_standardize_flow.php](file:///e:/Github/Cinema-Book/backend/database/migrations/2026_01_05_cleanup_and_standardize_flow.php)
1. **Drop** table `booking_seats`.
2. **Ensure** `booking_details` có đầy đủ foreign keys sang `bookings` và `seats`.
3. **Add** missing indexes for `transactions(booking_id)` và `seat_locks`.

## Verification Plan

### Automated Tests
- Chạy `php artisan test` (nếu có).
- Script kiểm tra tính toàn vẹn (Audit script).

### Manual Verification
1. Thực hiện quy trình đặt vé từ frontend.
2. Kiểm tra database sau mỗi bước (Lock -> Pending -> Confirmed).
3. Thử xóa 1 booking và kiểm tra `booking_details` có tự động xóa không.
