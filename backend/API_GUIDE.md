# 🎬 Hướng dẫn Test API Cinema Booking

## 📋 Tổng quan

Tất cả các API đã được triển khai thành công! Bạn có thể test các API bằng 2 cách:

### 1. Sử dụng Trang Web Test (Khuyến nghị) ⭐

Mở trình duyệt và truy cập:
```
http://localhost:8000/api-tester.html
```

**Tính năng:**
- ✅ Giao diện đẹp, dễ sử dụng
- ✅ Tự động lưu token sau khi đăng nhập
- ✅ Hiển thị response rõ ràng với syntax highlighting
- ✅ Hỗ trợ tất cả các API endpoints
- ✅ Có sẵn dữ liệu mẫu để test nhanh

### 2. Sử dụng cURL hoặc Postman

## 🚀 Bắt đầu

### Bước 1: Chạy Laravel Server

```bash
cd e:\Github\Cinema-Book\backend
php artisan serve
```

Server sẽ chạy tại: `http://localhost:8000`

### Bước 2: Chạy Migration (Nếu chưa chạy)

```bash
php artisan migrate
```

### Bước 3: Tạo Admin User (Quan trọng!)

Để test các API admin, bạn cần tạo một user với role admin:

```bash
php artisan tinker
```

Sau đó chạy lệnh:
```php
$user = new App\Models\User();
$user->name = 'Admin';
$user->email = 'admin@cinema.com';
$user->password = Hash::make('admin123');
$user->role = 'admin';
$user->save();
```

Hoặc tạo bằng SQL:
```sql
INSERT INTO users (name, email, password, role, created_at, updated_at) 
VALUES ('Admin', 'admin@cinema.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5UpCqHzMxZXlm', 'admin', NOW(), NOW());
```
*(Password: admin123)*

### Bước 4: Tạo Dữ liệu Mẫu cho Promotions

```bash
php artisan tinker
```

```php
App\Models\Promotion::create([
    'code' => 'SUMMER2026',
    'description' => 'Giảm giá mùa hè 20%',
    'discount_type' => 'percentage',
    'discount_value' => 20,
    'min_purchase_amount' => 100000,
    'max_discount_amount' => 50000,
    'valid_from' => now(),
    'valid_to' => now()->addMonths(3),
    'max_uses' => 100,
    'is_active' => true
]);

App\Models\Promotion::create([
    'code' => 'NEWYEAR50K',
    'description' => 'Giảm 50,000đ cho đơn hàng từ 200,000đ',
    'discount_type' => 'fixed',
    'discount_value' => 50000,
    'min_purchase_amount' => 200000,
    'valid_from' => now(),
    'valid_to' => now()->addMonths(1),
    'max_uses' => 50,
    'is_active' => true
]);
```

## 📚 Danh sách API Endpoints

### 🔐 Authentication (Public)

| Method | Endpoint | Mô tả |
|--------|----------|-------|
| POST | `/api/auth/register` | Đăng ký tài khoản mới |
| POST | `/api/auth/login` | Đăng nhập |
| POST | `/api/auth/forgot-password` | Quên mật khẩu |
| POST | `/api/auth/reset-password` | Đặt lại mật khẩu |
| POST | `/api/logout` | Đăng xuất (cần token) |

### 🎥 Movies (Public)

| Method | Endpoint | Mô tả |
|--------|----------|-------|
| GET | `/api/movies` | Danh sách phim |
| GET | `/api/movies/featured` | Phim nổi bật |
| GET | `/api/movies/search?q=keyword` | Tìm kiếm phim |
| GET | `/api/movies/filter` | Lọc phim theo tiêu chí |
| GET | `/api/movies/{id}` | Chi tiết phim |
| GET | `/api/movies/{id}/reviews` | Danh sách đánh giá |

### ⭐ Reviews

| Method | Endpoint | Mô tả | Auth |
|--------|----------|-------|------|
| GET | `/api/movies/{id}/reviews` | Lấy danh sách reviews | ❌ |
| POST | `/api/movies/{id}/reviews` | Tạo review mới | ✅ |

### 🔔 Notifications (Protected)

| Method | Endpoint | Mô tả |
|--------|----------|-------|
| GET | `/api/notifications` | Danh sách thông báo |
| POST | `/api/notifications/{id}/read` | Đánh dấu đã đọc |
| POST | `/api/notifications/read-all` | Đánh dấu tất cả đã đọc |

### 🎁 Promotions (Public)

| Method | Endpoint | Mô tả |
|--------|----------|-------|
| GET | `/api/promotions` | Danh sách khuyến mãi |
| POST | `/api/promotions/validate` | Kiểm tra mã khuyến mãi |

### 👤 User Profile (Protected)

| Method | Endpoint | Mô tả |
|--------|----------|-------|
| GET | `/api/user/profile` | Lấy thông tin cá nhân |
| PUT | `/api/user/profile` | Cập nhật thông tin |
| GET | `/api/user/bookings` | Lịch sử đặt vé |

### 👨‍💼 Admin - Theaters (Admin Only)

| Method | Endpoint | Mô tả |
|--------|----------|-------|
| GET | `/api/admin/theaters` | Danh sách rạp |
| POST | `/api/admin/theaters` | Tạo rạp mới |
| PUT | `/api/admin/theaters/{id}` | Cập nhật rạp |
| DELETE | `/api/admin/theaters/{id}` | Xóa rạp |

### 👨‍💼 Admin - Movies (Admin Only)

| Method | Endpoint | Mô tả |
|--------|----------|-------|
| GET | `/api/admin/movies` | Danh sách phim (admin) |
| POST | `/api/admin/movies` | Tạo phim mới |
| PUT | `/api/admin/movies/{id}` | Cập nhật phim |
| DELETE | `/api/admin/movies/{id}` | Xóa phim |

### 👨‍💼 Admin - Showtimes (Admin Only)

| Method | Endpoint | Mô tả |
|--------|----------|-------|
| GET | `/api/admin/showtimes` | Danh sách suất chiếu |
| POST | `/api/admin/showtimes` | Tạo suất chiếu mới |
| PUT | `/api/admin/showtimes/{id}` | Cập nhật suất chiếu |
| DELETE | `/api/admin/showtimes/{id}` | Xóa suất chiếu |

### 👨‍💼 Admin - Review Moderation (Admin Only)

| Method | Endpoint | Mô tả |
|--------|----------|-------|
| GET | `/api/admin/reviews` | Danh sách tất cả reviews |
| PUT | `/api/admin/reviews/{id}/approve` | Phê duyệt review |
| PUT | `/api/admin/reviews/{id}/reject` | Từ chối review |
| DELETE | `/api/admin/reviews/{id}` | Xóa review |

## 🧪 Ví dụ Test với cURL

### 1. Đăng ký tài khoản

```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Nguyễn Văn A",
    "email": "user@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "phone": "0123456789"
  }'
```

### 2. Đăng nhập

```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "password123"
  }'
```

**Response sẽ trả về token:**
```json
{
  "success": true,
  "data": {
    "token": "1|abc123xyz..."
  }
}
```

### 3. Lấy danh sách phim

```bash
curl http://localhost:8000/api/movies
```

### 4. Tạo review (cần token)

```bash
curl -X POST http://localhost:8000/api/movies/1/reviews \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "rating": 5,
    "comment": "Phim rất hay!"
  }'
```

### 5. Lấy danh sách promotions

```bash
curl http://localhost:8000/api/promotions
```

### 6. Validate mã khuyến mãi

```bash
curl -X POST http://localhost:8000/api/promotions/validate \
  -H "Content-Type: application/json" \
  -d '{
    "code": "SUMMER2026",
    "amount": 200000
  }'
```

### 7. Admin - Tạo phim mới (cần admin token)

```bash
curl -X POST http://localhost:8000/api/admin/movies \
  -H "Authorization: Bearer ADMIN_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Avengers: Endgame",
    "description": "Cuộc chiến cuối cùng...",
    "duration": 181,
    "release_date": "2026-04-26",
    "status": "now_showing"
  }'
```

### 8. Admin - Phê duyệt review

```bash
curl -X PUT http://localhost:8000/api/admin/reviews/1/approve \
  -H "Authorization: Bearer ADMIN_TOKEN_HERE"
```

## 🔑 Lưu ý về Authentication

### Đối với API cần authentication:
- Thêm header: `Authorization: Bearer YOUR_TOKEN`
- Token được lấy từ response của `/api/auth/login` hoặc `/api/auth/register`

### Đối với API admin:
- User phải có `role = 'admin'` trong database
- Đăng nhập bằng tài khoản admin để lấy admin token

## 🎯 Các tính năng đặc biệt

### Password Reset Flow:
1. Gọi `/api/auth/forgot-password` với email
2. Kiểm tra Laravel log để lấy token (hoặc `debug_token` trong response nếu `APP_DEBUG=true`)
3. Gọi `/api/auth/reset-password` với email, token và password mới

### Review System:
- User chỉ có thể review 1 lần cho mỗi phim
- Review mới cần admin approve trước khi hiển thị
- Review có `is_verified_purchase = true` nếu user đã đặt vé xem phim đó

### Promotion Validation:
- Kiểm tra mã còn hiệu lực
- Kiểm tra số lần sử dụng
- Kiểm tra giá trị đơn hàng tối thiểu
- Tính toán số tiền giảm giá

## 📱 Response Format

Tất cả API đều trả về format chuẩn:

```json
{
  "success": true/false,
  "message": "...",
  "data": {...}
}
```

## ❓ Troubleshooting

### Lỗi 401 Unauthorized
- Kiểm tra token có đúng không
- Token có hết hạn không
- Header Authorization có đúng format không

### Lỗi 403 Forbidden
- User không có quyền admin
- Kiểm tra field `role` trong bảng `users`

### Lỗi 422 Validation Error
- Kiểm tra dữ liệu đầu vào
- Đọc message trong response để biết field nào bị lỗi

## 🎉 Hoàn thành!

Bạn đã có đầy đủ các API cần thiết cho hệ thống Cinema Booking. Chúc bạn test thành công! 🚀
