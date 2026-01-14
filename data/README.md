# CƠ SỞ DỮ LIỆU & DỮ LIỆU DỰ ÁN - CINEMA BOOKING

Thư mục này chứa toàn bộ các tài liệu, cấu trúc và câu lệnh liên quan đến Database của hệ thống Đặt vé rạp chiếu phim.

---

## 📁 Cấu trúc thư mục `data/`

- **`sql/`**: Chứa các bản sao lưu database.
    - `schema_*.sql`: Chỉ bao gồm cấu trúc các bảng và View (CREATE TABLE/VIEW).
    - `full_backup_*.sql`: Bao gồm cả cấu trúc và toàn bộ dữ liệu hiện có (INSERT INTO).
- **`queries/`**: Các câu lệnh SQL hữu ích.
    - `common_queries.sql`: Chứa các truy vấn về doanh thu, suất chiếu, tình trạng ghế và bảo trì hệ thống.
- **`migrations/`**: Bản sao của tất cả các file Laravel Migrations theo thứ tự thời gian để bạn dễ dàng theo dõi logic thay đổi DB.
- **`export.php`**: Script PHP để bạn có thể tự xuất lại database bất cứ lúc nào (Yêu cầu MySQL đang chạy).

---

## 🚀 Hướng dẫn nhanh

### 1. Khôi phục toàn bộ Database (Cấu trúc + Dữ liệu)
Nếu bạn muốn tạo lại database giống hệt hiện tại:
```bash
mysql -u root cinema_booking < data/sql/full_backup_2026-01-12_150507.sql
```

### 2. Xem các chỉ số kinh doanh
Mở file `data/queries/common_queries.sql` và copy-paste các câu lệnh vào MySQL Workbench hoặc phpMyAdmin để xem doanh thu, top phim, v.v.

### 3. Đồng bộ hóa Migrations
Nếu bạn làm việc trên một máy khác, bạn có thể copy các file trong `data/migrations/` vào thư mục `backend/database/migrations/` của máy đó và chạy `php artisan migrate`.

---

## 📊 Thống kê Database Hiện Tại

- **Số bảng (Tables)**: 32
- **Số View**: 1 (`showtimes`)
- **Dữ liệu quan trọng**: Movies (Phim), Theaters (Rạp), Seats (Ghế), Bookings (Đơn hàng).

---
*Cập nhật ngày: 12/01/2026*
MD;
