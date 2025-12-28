# ============================================
# SCRIPT TỰ ĐỘNG CÀI ĐẶT XAMPP VÀ SETUP DATABASE
# ============================================

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  CINEMA BOOKING - DATABASE SETUP" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Kiểm tra XAMPP đã cài chưa
Write-Host "[1/5] Kiểm tra XAMPP..." -ForegroundColor Yellow
if (Test-Path "C:\xampp\mysql\bin\mysql.exe") {
    Write-Host "✓ XAMPP đã được cài đặt!" -ForegroundColor Green
    $xamppInstalled = $true
} else {
    Write-Host "✗ XAMPP chưa được cài đặt" -ForegroundColor Red
    Write-Host ""
    Write-Host "Vui lòng:" -ForegroundColor Yellow
    Write-Host "1. Tải XAMPP tại: https://www.apachefriends.org/download.html" -ForegroundColor White
    Write-Host "2. Cài đặt vào thư mục C:\xampp" -ForegroundColor White
    Write-Host "3. Chạy lại script này" -ForegroundColor White
    Write-Host ""
    
    # Hỏi có muốn dùng SQLite thay thế không
    $useSQLite = Read-Host "Bạn có muốn dùng SQLite thay thế (nhanh hơn cho development)? (y/n)"
    if ($useSQLite -eq 'y') {
        Write-Host ""
        Write-Host "[CHUYỂN SANG SQLITE]" -ForegroundColor Cyan
        
        # Cập nhật .env
        $envPath = "e:\Github\Cinema-Book\backend\.env"
        (Get-Content $envPath) | ForEach-Object {
            if ($_ -match '^DB_CONNECTION=') { "DB_CONNECTION=sqlite" }
            elseif ($_ -match '^DB_HOST=') { "# DB_HOST=127.0.0.1" }
            elseif ($_ -match '^DB_PORT=') { "# DB_PORT=3306" }
            elseif ($_ -match '^DB_DATABASE=') { "# DB_DATABASE=cinema_booking" }
            elseif ($_ -match '^DB_USERNAME=') { "# DB_USERNAME=root" }
            elseif ($_ -match '^DB_PASSWORD=') { "# DB_PASSWORD=" }
            else { $_ }
        } | Set-Content $envPath
        
        # Tạo file SQLite
        $sqlitePath = "e:\Github\Cinema-Book\backend\database\database.sqlite"
        New-Item -Path $sqlitePath -ItemType File -Force | Out-Null
        Write-Host "✓ Đã tạo file SQLite database" -ForegroundColor Green
        
        # Chạy migration
        Write-Host ""
        Write-Host "[5/5] Chạy migration..." -ForegroundColor Yellow
        Set-Location "e:\Github\Cinema-Book\backend"
        php artisan migrate:fresh
        
        Write-Host ""
        Write-Host "========================================" -ForegroundColor Green
        Write-Host "  HOÀN THÀNH! (SQLite Mode)" -ForegroundColor Green
        Write-Host "========================================" -ForegroundColor Green
        exit
    }
    exit
}

# Kiểm tra MySQL service
Write-Host ""
Write-Host "[2/5] Kiểm tra MySQL service..." -ForegroundColor Yellow
$mysqlProcess = Get-Process -Name "mysqld" -ErrorAction SilentlyContinue
if ($mysqlProcess) {
    Write-Host "✓ MySQL đang chạy!" -ForegroundColor Green
} else {
    Write-Host "✗ MySQL chưa chạy" -ForegroundColor Red
    Write-Host "Đang khởi động MySQL..." -ForegroundColor Yellow
    
    # Thử start MySQL qua XAMPP
    Start-Process "C:\xampp\mysql_start.bat" -WindowStyle Hidden -ErrorAction SilentlyContinue
    Start-Sleep -Seconds 3
    
    $mysqlProcess = Get-Process -Name "mysqld" -ErrorAction SilentlyContinue
    if ($mysqlProcess) {
        Write-Host "✓ MySQL đã được khởi động!" -ForegroundColor Green
    } else {
        Write-Host "✗ Không thể tự động khởi động MySQL" -ForegroundColor Red
        Write-Host "Vui lòng mở XAMPP Control Panel và nhấn Start ở MySQL" -ForegroundColor Yellow
        Read-Host "Nhấn Enter sau khi đã start MySQL"
    }
}

# Tạo database
Write-Host ""
Write-Host "[3/5] Tạo database 'cinema_booking'..." -ForegroundColor Yellow
$mysqlPath = "C:\xampp\mysql\bin\mysql.exe"
$createDbQuery = "CREATE DATABASE IF NOT EXISTS cinema_booking CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

& $mysqlPath -u root -e $createDbQuery 2>$null
if ($LASTEXITCODE -eq 0) {
    Write-Host "✓ Database đã được tạo!" -ForegroundColor Green
} else {
    Write-Host "✗ Lỗi khi tạo database" -ForegroundColor Red
    Write-Host "Thử tạo thủ công qua phpMyAdmin: http://localhost/phpmyadmin" -ForegroundColor Yellow
    exit
}

# Kiểm tra file .env
Write-Host ""
Write-Host "[4/5] Kiểm tra cấu hình .env..." -ForegroundColor Yellow
$envPath = "e:\Github\Cinema-Book\backend\.env"
if (Test-Path $envPath) {
    $envContent = Get-Content $envPath -Raw
    if ($envContent -match 'DB_CONNECTION=mysql') {
        Write-Host "✓ File .env đã cấu hình MySQL!" -ForegroundColor Green
    } else {
        Write-Host "✗ File .env chưa đúng cấu hình" -ForegroundColor Red
        Write-Host "Đang cập nhật..." -ForegroundColor Yellow
        
        (Get-Content $envPath) | ForEach-Object {
            if ($_ -match '^DB_CONNECTION=') { "DB_CONNECTION=mysql" }
            elseif ($_ -match '^DB_HOST=') { "DB_HOST=127.0.0.1" }
            elseif ($_ -match '^DB_PORT=') { "DB_PORT=3306" }
            elseif ($_ -match '^DB_DATABASE=') { "DB_DATABASE=cinema_booking" }
            elseif ($_ -match '^DB_USERNAME=') { "DB_USERNAME=root" }
            elseif ($_ -match '^DB_PASSWORD=') { "DB_PASSWORD=" }
            else { $_ }
        } | Set-Content $envPath
        
        Write-Host "✓ Đã cập nhật file .env!" -ForegroundColor Green
    }
} else {
    Write-Host "✗ Không tìm thấy file .env" -ForegroundColor Red
    exit
}

# Chạy migration
Write-Host ""
Write-Host "[5/5] Chạy migration..." -ForegroundColor Yellow
Set-Location "e:\Github\Cinema-Book\backend"

Write-Host "Đang xóa database cũ và tạo lại..." -ForegroundColor Cyan
php artisan migrate:fresh

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "========================================" -ForegroundColor Green
    Write-Host "  HOÀN THÀNH!" -ForegroundColor Green
    Write-Host "========================================" -ForegroundColor Green
    Write-Host ""
    Write-Host "Database đã sẵn sàng với 16 bảng:" -ForegroundColor White
    Write-Host "  • users (mở rộng)" -ForegroundColor Gray
    Write-Host "  • genres, languages, cast" -ForegroundColor Gray
    Write-Host "  • theaters, screens, movies" -ForegroundColor Gray
    Write-Host "  • movie_genre, movie_language, movie_cast" -ForegroundColor Gray
    Write-Host "  • showtimes, seats" -ForegroundColor Gray
    Write-Host "  • bookings, booking_seats, seat_locks" -ForegroundColor Gray
    Write-Host "  • transactions, reviews" -ForegroundColor Gray
    Write-Host ""
    Write-Host "Kiểm tra tại: http://localhost/phpmyadmin" -ForegroundColor Cyan
    Write-Host ""
} else {
    Write-Host ""
    Write-Host "✗ Lỗi khi chạy migration!" -ForegroundColor Red
    Write-Host "Vui lòng kiểm tra lỗi ở trên" -ForegroundColor Yellow
}
