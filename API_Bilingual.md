# API Documentation - Cinema Booking System
# Tài liệu API - Hệ thống Đặt vé Xem phim

This document tracks the current status of API development.
*Tài liệu này theo dõi trạng thái phát triển API hiện tại.*

---

## 🟢 Existing APIs (Implemented)
## 🟢 API Đã Hiện Thực (Đã hoàn thành)

### 1. Authentication (Xác thực)
- `POST /api/register`
  - Create a new user account
  - *Tạo tài khoản người dùng mới*
- `POST /api/login`
  - Authenticate and get token
  - *Đăng nhập và nhận token xác thực*
- `POST /api/logout`
  - Revoke current session token
  - *Đăng xuất và hủy token phiên hiện tại*
- `POST /api/auth/forgot-password`
  - Send reset password link
  - *Gửi liên kết đặt lại mật khẩu*
- `POST /api/auth/reset-password`
  - Reset password using token
  - *Đặt lại mật khẩu bằng token*
- `GET /api/auth/google`
  - Redirect to Google Social Login
  - *Chuyển hướng đến Đăng nhập Google*
- `GET /api/auth/google/callback`
  - Handle Google login callback
  - *Xử lý phản hồi đăng nhập từ Google*

### 2. User Profile (Hồ sơ Người dùng)
- `GET /api/user/profile`
  - Get current user info
  - *Lấy thông tin người dùng hiện tại*
- `PUT /api/user/profile`
  - Update user info (name, phone, etc.)
  - *Cập nhật thông tin người dùng (tên, sđt, ...)*
- `GET /api/user/bookings`
  - List all bookings made by the user
  - *Liệt kê tất cả đơn đặt vé của người dùng*

### 3. Movies (Phim)
- `GET /api/movies`
  - List movies with pagination & filters
  - *Danh sách phim (có phân trang & bộ lọc)*
- `GET /api/movies/featured`
  - List top/featured movies
  - *Danh sách phim nổi bật/đánh giá cao*
- `GET /api/movies/search`
  - Search movies by title or cast
  - *Tìm kiếm phim theo tên hoặc diễn viên*
- `GET /api/movies/{id}`
  - Get detailed movie information
  - *Lấy thông tin chi tiết phim*
- `GET /api/movies/{id}/reviews`
  - List reviews for a specific movie
  - *Danh sách đánh giá cho một bộ phim cụ thể*

### 4. Theaters & Showtimes (Rạp & Lịch chiếu)
- `GET /api/theaters`
  - List theaters with city filters
  - *Danh sách rạp (có lọc theo thành phố)*
- `GET /api/theaters/{id}`
  - Get theater details
  - *Thông tin chi tiết rạp chiếu*
- `GET /api/showtimes`
  - List available showtimes
  - *Danh sách các suất chiếu hiện có*
- `GET /api/showtimes/{id}/seats`
  - Get real-time seat status (locked/booked) for a showtime
  - *Lấy trạng thái ghế thời gian thực (đã khóa/đặt) cho suất chiếu*

### 5. Booking Flow (Quy trình Đặt vé)
- `POST /api/bookings`
  - Create a new booking (includes seat locking)
  - *Tạo đơn đặt vé mới (bao gồm khóa ghế)*
- `GET /api/bookings/{id}`
  - Get booking details
  - *Lấy chi tiết đơn đặt vé*
- `POST /api/bookings/{id}/pay`
  - Process payment (placeholder for VNPay/ZaloPay)
  - *Xử lý thanh toán (giả lập cho VNPay/ZaloPay)*
- `GET /api/bookings/e-ticket/{id}`
  - Get data for electronic ticket rendering
  - *Lấy dữ liệu để hiển thị vé điện tử*

### 6. Others (Khác)
- `GET /api/promotions`
  - List active promotions
  - *Danh sách khuyến mãi đang hoạt động*
- `POST /api/promotions/validate`
  - Check code validity against a booking
  - *Kiểm tra mã giảm giá cho đơn hàng*
- `POST /api/movies/{id}/reviews`
  - Submit a movie review (after booking)
  - *Gửi đánh giá phim (sau khi đã xem)*
- `GET /api/notifications`
  - List user notifications
  - *Danh sách thông báo của người dùng*
- `POST /api/notifications/{id}/read`
  - Mark specific notification as read
  - *Đánh dấu thông báo là đã đọc*

### 7. Admin (System Management) - (Quản trị Hệ thống)
- `CRUD /api/admin/theaters`
  - Manage theaters
  - *Quản lý rạp chiếu*
- `CRUD /api/admin/movies`
  - Manage movie database
  - *Quản lý kho phim*
- `CRUD /api/admin/showtimes`
  - Manage screen schedules
  - *Quản lý lịch chiếu*
- `ADMIN /api/admin/reviews`
  - Moderate user reviews (Approve/Reject)
  - *Kiểm duyệt đánh giá (Duyệt/Từ chối)*

---

## 🔴 Missing APIs (Planned/Needed)
## 🔴 API Còn Thiếu (Dự kiến/Cần thiết)

### 1. Missing Public Data (Dữ liệu Công khai còn thiếu)
- `GET /api/cities`
  - List all cities (for city selector)
  - *Danh sách thành phố (cho bộ chọn)*
- `GET /api/genres`
  - List movie genres (for filtering)
  - *Danh sách thể loại phim (để lọc)*
- `GET /api/combos`
  - List food/drink packages
  - *Danh sách gói đồ ăn/nước uống*
- `GET /api/cast`
  - Browse actors and directors
  - *Duyệt danh sách diễn viên và đạo diễn*

### 2. Missing User Features (Tính năng Người dùng còn thiếu)
- `POST /api/user/change-password`
  - Security update
  - *Đổi mật khẩu*
- `POST /api/user/avatar`
  - Upload profile picture
  - *Tải lên ảnh đại diện*
- `DELETE /api/user/account`
  - Option to delete account
  - *Tùy chọn xóa tài khoản*

### 3. Missing Admin Controls (Quản trị còn thiếu)
- `CRUD /api/admin/rooms`
  - Manage theater screens
  - *Quản lý phòng chiếu*
- `CRUD /api/admin/combos`
  - Manage food/drink offerings
  - *Quản lý combo đồ ăn/uống*
- `CRUD /api/admin/promotions`
  - Manage marketing campaigns
  - *Quản lý chiến dịch khuyến mãi*
- `CRUD /api/admin/cities`
  - Manage city data
  - *Quản lý dữ liệu thành phố*
- `CRUD /api/admin/cast`
  - Manage actor/director database
  - *Quản lý cơ sở dữ liệu diễn viên/đạo diễn*
- `GET /api/admin/dashboard`
  - Get system statistics (Revenue, Users, Active Bookings)
  - *Xem thống kê hệ thống (Doanh thu, User, Đơn đặt vé)*

### 4. Booking Flow Improvements (Cải thiện Quy trình Đặt vé)
- `POST /api/bookings/{id}/cancel`
  - Allow users to cancel pending bookings
  - *Cho phép hủy đơn đặt vé đang chờ*
- `GET /api/bookings/history`
  - Comprehensive booking history with rich data
  - *Lịch sử đặt vé chi tiết*
- `POST /api/seat-locks/cleanup`
  - Manual/Automated trigger to clear expired locks
  - *Kích hoạt dọn dẹp khóa ghế hết hạn (thủ công/tự động)*

---

## 🛠️ Notes (Ghi chú)
- All protected routes require an `Authorization: Bearer <token>` header.
  - *Tất cả API bảo mật đều yêu cầu header `Authorization: Bearer <token>`.*
- API response format follows the standard JSON structure:
  - *Định dạng phản hồi API tuân theo cấu trúc JSON chuẩn:*
  ```json
  {
    "success": boolean,
    "data": ... ,
    "message": string
  }
  ```
