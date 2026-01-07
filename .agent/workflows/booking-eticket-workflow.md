---
description: Quy trình Đặt vé & Vé điện tử
---
# Đặt vé & Vé điện tử (Booking & E-Ticketing)

Quy trình cốt lõi của hệ thống: Chọn ghế, thanh toán và nhận vé.

## Backend (Laravel)
9. 1.  **API Suất chiếu & Ghế (Showtimes & Seats)**
10:     - [x] `GET /api/movies/{id}/showtimes`: Lấy suất chiếu theo phim và rạp.
11:     - [x] `GET /api/showtimes/{id}/seats`: Lấy trạng thái ghế (Trống, Đã đặt, Đang giữ).
12: 
13: 2.  **Quy trình Đặt (Booking Flow)**
14:     - [x] `POST /api/bookings/lock`: Giữ ghế (Lock seat) tạm thời (Redis hoặc DB status 'held').
15:     - [x] `POST /api/bookings/create`: Tạo đơn hàng chờ thanh toán.
16:     - [x] `POST /api/bookings/confirm`: Xác nhận thanh toán thành công -> Đổi trạng thái 'booked'.
17: 
18: 3.  **Vé & Lịch sử (E-Ticket)**
19:     - [x] `GET /api/bookings/{id}/ticket`: Tạo dữ liệu vé điện tử (QR Code).
20:     - [x] `GET /api/user/bookings`: Lịch sử đặt vé của user.
21: 
22: ## Frontend (React)
23: 1.  **Giao diện Đặt vé**
24:     - [x] Màn hình chọn Suất chiếu (Ngày/Giờ/Rạp).
25:     - [x] Sơ đồ ghế (Seat Map): Hiển thị ghế VIP, Thường, Đôi. Xử lý logic chọn ghế.
26: 
27: 2.  **Thanh toán & Vé**
28:     - [x] Màn hình tóm tắt đặt vé (Ghế, Combo, Tổng tiền).
29:     - [x] Tích hợp cổng thanh toán (Giả lập hoặc API thật).
30:     - [x] Màn hình "Thành công" hiển thị E-Ticket (Mã vé/QR).
