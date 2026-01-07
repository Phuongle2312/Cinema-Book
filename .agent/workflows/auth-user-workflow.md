---
description: Quy trình Xác thực & Quản lý Người dùng
---
# Xác thực & Hồ sơ Người dùng (Authentication & User Profile)

Quy trình này bao gồm các bước để phát triển tính năng Đăng ký, Đăng nhập và Quản lý hồ sơ người dùng.

## Backend (Laravel)
9. 1.  **Cấu hình Môi trường & Bảo mật**
10:     - [x] Cài đặt và cấu hình Laravel Sanctum (hoặc Passport nếu cần OAuth2).
11:     - [x] Đảm bảo file `.env` đã có key `SANCTUM_STATEFUL_DOMAINS` nếu dùng SPA.
12: 
13: 2.  **API Xác thực (Authentication)**
14:     - [x] `POST /api/register`: Đăng ký người dùng mới. Validate dữ liệu (email, password, name).
15:     - [x] `POST /api/login`: Đăng nhập, trả về token (Sanctum).
16:     - [x] `POST /api/logout`: Đăng xuất, thu hồi token.
17: 
18: 3.  **API Hồ sơ (User Profile)**
19:     - [x] `GET /api/user`: Lấy thông tin người dùng hiện tại (qua token).
20:     - [x] `PUT /api/user/profile`: Cập nhật thông tin cá nhân.
21:     - [x] `PUT /api/user/password`: Đổi mật khẩu.
22: 
23: ## Frontend (React)
24: 1.  **Giao diện (UI)**
25:     - [x] Tạo form Login và Register với validation (Formik/React Hook Form).
26:     - [x] Tạo trang User Profile để xem và chỉnh sửa thông tin.
27: 
28: 2.  **Tích hợp (Integration)**
29:     - [x] Tích hợp API Login/Register. Lưu token vào localStorage/sessionStorage hoặc Cookie (HttpOnly).
30:     - [x] Xử lý luồng chuyển hướng (Redirect) sau khi login/logout.
31:     - [x] Hiển thị thông tin user lên Header/Navbar sau khi đăng nhập.
