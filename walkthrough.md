# Walkthrough: Database Cleanup and Flow Standardization

## 📋 Tổng Quan

Đã hoàn thành rà soát dự án, xóa bỏ các bảng dư thừa và **"khóa luồng chuẩn"** cho hệ thống đặt vé. Việc này giúp database gọn gàng hơn và đảm bảo tính toàn vẹn dữ liệu khi một bản ghi bị xóa.

---

## ✅ Công Việc Đã Hoàn Thành

### 1. Dọn Dẹp Database (Cleanup)

#### Xóa Bảng Dư Thừa:
- ✅ **Bảng `booking_seats`**: Đã xóa bỏ hoàn toàn vì trùng lặp chức năng với `booking_details`.
- ✅ **Bảng `screens`**: Đã xóa bỏ (vì đã được thay thế bằng `rooms`).

#### Thắt Chặt Khóa Ngoại (Constraints):
Đã nâng cấp các khóa ngoại lên `ON DELETE CASCADE` để đảm bảo:
- Khi xóa 1 **Booking** -> Các bản ghi liên quan trong `booking_details`, `booking_combos`, và `transactions` sẽ tự động được xóa sạch, không để lại rác trong database.

---

### 2. Chuẩn Hóa Luồng (Logical Flow)

#### Luồng Dữ Liệu Sau Khi Chuẩn Hóa:
1. **Seat Selection**: Tạo `seat_locks`.
2. **Booking**: Tạo `bookings` (Status: `pending`).
3. **Details**: Thông tin ghế được lưu duy nhất vào `booking_details`.
4. **Payment**: Tạo `transactions`.
5. **Finalize**: Khi thanh toán xong -> Cập nhật `bookings.status = confirmed` và đồng bộ ghế.

---

### 3. Cập Nhật Code (Models)

#### [NEW] BookingDetail Model
- Tạo model [BookingDetail.php](file:///e:/Github/Cinema-Book/backend/app/Models/BookingDetail.php) để quản lý chi tiết ghế và trạng thái sử dụng vé.

#### [MODIFY] Booking Model
- Cập nhật relationship `seats()` để query trực tiếp từ bảng `booking_details`.
- Xóa bỏ các tham chiếu đến `booking_seats`.

#### [MODIFY] Seat Model
- Cập nhật quy trình kiểm tra ghế đã được đặt (`isBookedForShowtime`) thông qua bảng `booking_details`.

---

## 🎯 Kết Quả Cuối Cùng

### Kiểm Tra Thực Tế:
- ✅ **Toàn vẹn dữ liệu**: Thử nghiệm xóa Booking -> Details và Transactions tự động xóa theo.
- ✅ **Gọn gàng**: List table chỉ còn các bảng thực sự cần thiết, không còn bảng cũ `tickets` hay `booking_seats`.

### Danh Sách Migration Đã Chạy:
- ✅ `2026_01_05_cleanup_and_standardize_flow.php` (Manual Sync)

---

**Dự án hiện đã đạt chuẩn về cả cấu trúc 3NF và luồng logic database! 🎉**
