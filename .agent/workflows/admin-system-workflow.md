---
description: Quy trình Quản trị & Hệ thống
---
# Quản trị & Hệ thống (Admin Management & System)

Dành cho Admin quản lý nội dung và vận hành hệ thống.

## Backend (Laravel)
9. 1.  **API Quản trị (Admin CRUD)**
10:     - [x] `Movies`: Thêm, Sửa, Xóa phim. Upload Poster/Banner.
11:     - [x] `Theaters/Rooms`: Quản lý rạp và phòng chiếu (Sơ đồ ghế).
12:     - [x] `Showtimes`: Lên lịch chiếu phim.
13:     - [ ] `Users`: Quản lý danh sách người dùng.
14: 
15: 2.  **Báo cáo & Thống kê (Reports)**
16:     - [ ] `GET /api/admin/stats`: Thống kê doanh thu, số vé bán ra (theo ngày/tuần/tháng).
17:     - [ ] `GET /api/admin/top-movies`: Phim bán chạy nhất.
18: 
19: ## Frontend (React - Admin Dashboard)**
20: 1.  **Giao diện Quản lý**
21:     - [ ] Dashboard tổng quan (Biểu đồ doanh thu).
22:     - [ ] Các trang CRUD dạng bảng (Table) có phân trang, tìm kiếm.
23:     - [ ] Form nhập liệu (Create/Edit) với validation chặt chẽ.

2.  **Logic Admin**
    - [ ] Guard/Middleware: Chỉ cho phép user có role `admin` truy cập.
