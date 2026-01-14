# HƯỚNG DẪN XUẤT VÀ SAO LƯU DATABASE - CINEMA BOOKING

Tài liệu này cung cấp các phương pháp xuất và sao lưu database cho dự án Cinema-Book.

---

## 📦 Các file đã được tạo

### 1. **MIGRATIONS_SUMMARY.md**
- Tài liệu tổng hợp danh sách tất cả migrations
- Chi tiết về ERD, cấu trúc bảng
- Hướng dẫn sử dụng migrations

### 2. **cinema_booking_schema.sql**
- File SQL chứa cấu trúc toàn bộ database
- Không có dữ liệu (chỉ CREATE TABLE)
- Dùng để tạo database mới

### 3. **export_database.php**
- Script PHP để xuất cả cấu trúc + dữ liệu
- Yêu cầu MySQL đang chạy
- Tạo file SQL đầy đủ

---

## 🔧 PHƯƠNG PHÁP 1: Dùng PHP Script (Khuyến nghị)

### Điều kiện:
- MySQL server đang chạy
- XAMPP/WAMP/MAMP đã bật MySQL

### Các bước:

#### 1. Kiểm tra MySQL đang chạy
```bash
# Nếu dùng XAMPP Control Panel, check MySQL status = "Running"
# Hoặc mở http://localhost/phpmyadmin để kiểm tra
```

#### 2. Chạy script export
```bash
cd e:\SQL_EXAM\Cinema-Book\backend
php export_database.php
```

#### 3. File SQL sẽ được tạo tại:
```
backend/database/cinema_booking_export_YYYY-MM-DD_HHMMSS.sql
```

---

## 🔧 PHƯƠNG PHÁP 2: Dùng phpMyAdmin (Dễ nhất)

### Các bước:

#### 1. Mở phpMyAdmin
```
http://localhost/phpmyadmin
```

#### 2. Chọn database `cinema_booking`
- Click vào `cinema_booking` ở sidebar trái

#### 3. Click tab "Export"
- Chọn "Quick" hoặc "Custom"
- Format: SQL
- Click "Go"

#### 4. File SQL sẽ được tải về
```
cinema_booking.sql
```

---

## 🔧 PHƯƠNG PHÁP 3: Dùng mysqldump (Command Line)

### Điều kiện:
- Đã cài MySQL và có sẵn `mysqldump` trong PATH
- Hoặc biết đường dẫn đến mysqldump.exe

### Tìm đường dẫn mysqldump

#### Nếu dùng XAMPP:
```powershell
cd C:\xampp\mysql\bin
.\mysqldump.exe -u root cinema_booking > E:\SQL_EXAM\Cinema-Book\cinema_booking_full.sql
```

#### Nếu dùng MySQL standalone:
```powershell
cd "C:\Program Files\MySQL\MySQL Server 8.0\bin"
.\mysqldump.exe -u root -p cinema_booking > E:\SQL_EXAM\Cinema-Book\cinema_booking_full.sql
```

### Xuất với đầy đủ tùy chọn:
```powershell
mysqldump -u root --databases cinema_booking `
  --add-drop-database `
  --add-drop-table `
  --routines `
  --triggers `
  --events `
  --result-file="E:\SQL_EXAM\Cinema-Book\cinema_booking_complete.sql"
```

---

## 🔧 PHƯƠNG PHÁP 4: Dùng Laravel Artisan

### Xuất schema (không có dữ liệu)
```bash
cd backend
php artisan schema:dump
```

### Xuất schema và xóa migrations cũ
```bash
php artisan schema:dump --prune
```

**File sẽ được tạo tại:**
```
backend/database/schema/mysql-schema.sql
```

---

## 📋 SO SÁNH CÁC PHƯƠNG PHÁP

| Phương pháp | Ưu điểm | Nhược điểm | Độ khuyến nghị |
|-------------|---------|------------|----------------|
| **PHP Script** | • Tự động<br>• Không cần tool bên ngoài | • Yêu cầu MySQL chạy | ⭐⭐⭐⭐ |
| **phpMyAdmin** | • Dễ nhất<br>• GUI | • Phải vào trình duyệt | ⭐⭐⭐⭐⭐ |
| **mysqldump** | • Professional<br>• Nhiều options | • Phải biết PATH | ⭐⭐⭐⭐ |
| **Laravel Artisan** | • Tích hợp Laravel | • Chỉ schema, không có data | ⭐⭐⭐ |

---

## 🔄 IMPORT DATABASE (Khôi phục)

### Phương pháp 1: phpMyAdmin
1. Mở phpMyAdmin
2. Tạo database mới: `cinema_booking_backup`
3. Click "Import"
4. Chọn file `.sql`
5. Click "Go"

### Phương pháp 2: Command Line
```powershell
# Nếu dùng XAMPP
cd C:\xampp\mysql\bin
.\mysql.exe -u root cinema_booking < E:\SQL_EXAM\Cinema-Book\cinema_booking_full.sql

# Hoặc
.\mysql.exe -u root -e "CREATE DATABASE cinema_booking_test"
.\mysql.exe -u root cinema_booking_test < E:\SQL_EXAM\Cinema-Book\cinema_booking_full.sql
```

### Phương pháp 3: Laravel Migration
```bash
cd backend

# Reset database
php artisan migrate:fresh

# Chạy seeders
php artisan db:seed
```

---

## 📊 LỊCH SAO LƯU KHUYẾN NGHỊ

### Hàng ngày (Development)
```bash
php artisan migrate:fresh --seed
```

### Trước mỗi lần deploy
```bash
# Sao lưu production
mysqldump -u root -p cinema_booking > backup_$(date +%Y%m%d).sql

# Hoặc dùng phpMyAdmin export
```

### Tự động (Scheduled Backup)
Tạo Windows Task Scheduler chạy script backup hàng ngày vào 2:00 AM

---

## 🔍 XÁC MINH SAO LƯU

### Kiểm tra file SQL đã tạo:
```powershell
# Xem kích thước file
Get-ChildItem E:\SQL_EXAM\Cinema-Book\*.sql | Select Name, Length

# Xem nội dung (10 dòng đầu)
Get-Content E:\SQL_EXAM\Cinema-Book\cinema_booking_full.sql -TotalCount 10
```

### Kiểm tra database hiện tại:
```sql
-- Chạy trong phpMyAdmin hoặc MySQL CLI
USE cinema_booking;

-- Đếm số bảng
SELECT COUNT(*) as total_tables 
FROM information_schema.tables 
WHERE table_schema = 'cinema_booking';

-- Đếm số dòng trong các bảng chính
SELECT 'movies' as table_name, COUNT(*) as row_count FROM movies
UNION ALL
SELECT 'theaters', COUNT(*) FROM theaters
UNION ALL
SELECT 'bookings', COUNT(*) FROM bookings
UNION ALL
SELECT 'seats', COUNT(*) FROM seats;
```

---

## 🆘 TROUBLESHOOTING

### Lỗi: "MySQL connection refused"
**Giải pháp:**
1. Mở XAMPP Control Panel
2. Start MySQL service
3. Kiểm tra port 3306 không bị chiếm

### Lỗi: "Access denied for user 'root'@'localhost'"
**Giải pháp:**
1. Kiểm tra `.env`:
```env
DB_USERNAME=root
DB_PASSWORD=
```
2. Nếu có password, thêm tham số `-p` khi dùng mysqldump

### Lỗi: "mysqldump: command not found"
**Giải pháp:**
Thay vì `mysqldump`, dùng đường dẫn đầy đủ:
```powershell
C:\xampp\mysql\bin\mysqldump.exe -u root cinema_booking > backup.sql
```

---

## 📁 CẤU TRÚC THỦ CÔNG MIGRATIONS

Nếu bạn muốn copy tất cả migrations vào 1 file:

```bash
# PowerShell
cd e:\SQL_EXAM\Cinema-Book\backend\database\migrations
Get-Content *.php > E:\SQL_EXAM\Cinema-Book\all_migrations.txt
```

---

## ✅ CHECKLIST TRƯỚC KHI BACKUP

- [ ] MySQL đang chạy
- [ ] Đã commit code mới nhất vào Git
- [ ] Kiểm tra tất cả migrations đã chạy: `php artisan migrate:status`
- [ ] Tạo thư mục backup nếu chưa có
- [ ] Ghi chú version/ngày backup

---

## 📝 GHI CHÚ

- **File schema** (`cinema_booking_schema.sql`): Chỉ có cấu trúc, không có data
- **File export từ PHP script**: Có cả cấu trúc + data
- **File export từ mysqldump**: Có cả cấu trúc + data + triggers + procedures

---

*Tài liệu được tạo: <?= date('Y-m-d H:i:s') ?>*
*Dự án: Cinema Booking System*
