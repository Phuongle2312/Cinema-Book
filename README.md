# 🎬 Cinema-Book - Hệ Thống Đặt Vé Xem Phim

Hệ thống đặt vé xem phim trực tuyến được xây dựng với Laravel (Backend) và React (Frontend).

## 📋 Mô Tả Dự Án

Cinema-Book là một ứng dụng web hoàn chỉnh cho phép người dùng:
- 🎥 Xem danh sách phim đang chiếu và sắp chiếu
- 🎫 Đặt vé xem phim trực tuyến
- 💺 Chọn ghế ngồi theo sơ đồ rạp
- 💳 Thanh toán trực tuyến (VNPay/MoMo)
- 👤 Quản lý tài khoản và lịch sử đặt vé
- ⭐ Đánh giá và review phim
- 🔔 Nhận thông báo về vé đã đặt

## 🛠️ Công Nghệ Sử Dụng

### Backend
- **Laravel 11.x** - PHP Framework
- **MySQL/MariaDB** - Database
- **Laravel Sanctum** - API Authentication
- **Social Login** - Google & Facebook OAuth

### Frontend
- **React 19.x** - UI Library
- **React Router** - Routing
- **Axios** - HTTP Client
- **Lucide React** - Icons

## 📦 Cài Đặt

### Yêu Cầu Hệ Thống
- PHP >= 8.2
- Composer
- Node.js >= 18.x
- MySQL/MariaDB
- XAMPP/WAMP (khuyến nghị)

### Bước 1: Clone Repository
```bash
git clone https://github.com/Phuongle2312/Cinema-Book.git
cd Cinema-Book
```

### Bước 2: Cài Đặt Backend
```bash
cd backend

# Cài đặt dependencies
composer install

# Copy file .env
copy .env.example .env

# Generate application key
php artisan key:generate

# Tạo database 'cinema_booking' trong MySQL
# Sau đó chạy migrations
php artisan migrate

# (Optional) Seed dữ liệu mẫu
php artisan db:seed

# Chạy server
php artisan serve
```

Backend sẽ chạy tại: `http://localhost:8000`

### Bước 3: Cài Đặt Frontend
```bash
cd frontend

# Cài đặt dependencies
npm install

# Chạy development server
npm start
```

Frontend sẽ chạy tại: `http://localhost:3000`

## 🔧 Cấu Hình

### Database (.env)
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cinema_booking
DB_USERNAME=root
DB_PASSWORD=
```

### Social Login (Optional)
Để sử dụng đăng nhập qua Google/Facebook, cập nhật trong `.env`:
```env
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
FACEBOOK_CLIENT_ID=your_facebook_app_id
FACEBOOK_CLIENT_SECRET=your_facebook_app_secret
```

## 🎯 Tính Năng Chính

### Người Dùng
- ✅ Đăng ký/Đăng nhập (Email hoặc Social Login)
- ✅ Quên mật khẩu & Reset password
- ✅ Xem danh sách phim (Featured, Search, Filter)
- ✅ Xem chi tiết phim và trailer
- ✅ Chọn suất chiếu và ghế ngồi
- ✅ Khóa ghế tạm thời (5-6 phút)
- ✅ Thanh toán và nhận vé điện tử
- ✅ Xem lịch sử đặt vé
- ✅ Đánh giá và review phim
- ✅ Nhận thông báo

### Admin
- ✅ Quản lý rạp chiếu (CRUD)
- ✅ Quản lý phim (CRUD)
- ✅ Quản lý suất chiếu (CRUD)
- ✅ Kiểm duyệt reviews
- ✅ Quản lý khuyến mãi

## 📁 Cấu Trúc Dự Án

```
Cinema-Book/
├── backend/              # Laravel Backend
│   ├── app/
│   │   ├── Http/Controllers/Api/
│   │   ├── Models/
│   │   └── ...
│   ├── database/migrations/
│   ├── routes/api.php
│   └── ...
├── frontend/            # React Frontend
│   ├── src/
│   │   ├── components/
│   │   ├── pages/
│   │   ├── services/
│   │   └── App.js
│   └── ...
└── README.md
```

## 🔗 API Endpoints

### Public Routes
- `GET /api/test` - Test API
- `POST /api/login` - Đăng nhập
- `POST /api/register` - Đăng ký
- `GET /api/movies` - Danh sách phim
- `GET /api/movies/{id}` - Chi tiết phim
- `GET /api/theaters` - Danh sách rạp
- `GET /api/showtimes` - Suất chiếu

### Protected Routes (Cần Authentication)
- `GET /api/user/profile` - Thông tin user
- `POST /api/bookings` - Tạo booking
- `GET /api/user/bookings` - Lịch sử đặt vé
- `POST /api/movies/{id}/reviews` - Đánh giá phim
- `GET /api/notifications` - Thông báo

## 👥 Đóng Góp

Dự án được phát triển bởi:
- **Phương Lê** - [Phuongle2312](https://github.com/Phuongle2312)

## 📝 Lịch Sử Cập Nhật

### v1.0.0 (2026-01-05)
- ✅ Merge nhánh `ngotrangvinh` vào `main`
- ✅ Kết hợp tính năng Password Reset và Social Login
- ✅ Hoàn thiện Backend API
- ✅ Hoàn thiện Frontend UI
- ✅ Tích hợp đầy đủ Backend-Frontend

## 📄 License

Dự án này được phát triển cho mục đích học tập.

## 📞 Liên Hệ

- GitHub: [@Phuongle2312](https://github.com/Phuongle2312)
- Repository: [Cinema-Book](https://github.com/Phuongle2312/Cinema-Book)

---

**Happy Coding! 🎬🍿**