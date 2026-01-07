---
description: Quy trình Đánh giá & Thông báo
---
# Đánh giá & Thông báo (Reviews & Notifications)

Tính năng tương tác người dùng và thông báo hệ thống.

## Backend (Laravel)
9. 1.  **API Đánh giá (Reviews)**
10:     - [x] `POST /api/movies/{id}/reviews`: Gửi đánh giá (Rating star + Comment).
11:     - [x] `GET /api/movies/{id}/reviews`: Lấy danh sách review của phim (Phân trang).
12:     - [x] `DELETE /api/reviews/{id}`: Xóa review (Admin hoặc Owner).
13: 
14: 2.  **API Thông báo (Notifications)**
15:     - [x] `GET /api/notifications`: Lấy danh sách thông báo của user.
16:     - [x] `POST /api/notifications/mark-read`: Đánh dấu đã đọc.
17:     - [x] Background Job: Gửi mail xác nhận vé, nhắc nhở giờ chiếu.
18: 
19: ## Frontend (React)
20: 1.  **Giao diện Review**
21:     - [x] Component đánh giá sao (Star Rating) và nhập bình luận trên trang chi tiết phim.
22:     - [x] Danh sách bình luận của người khác.
23: 
24: 2.  **Giao diện Thông báo**
25:     - [x] Chuông thông báo (Notification Bell) trên Header.
26:     - [x] Danh sách thông báo thả xuống (Dropdown) hoặc trang riêng.
