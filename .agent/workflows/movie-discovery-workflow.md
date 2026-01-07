---
description: Quy trình Khám phá Phim & Tìm kiếm
---
# Khám phá Phim & Tìm kiếm (Movie Discovery & Search)

Quy trình này hướng dẫn phát triển các tính năng hiển thị phim, tìm kiếm và chi tiết phim.

## Backend (Laravel)
9. 1.  **API Phim (Movies)**
10:     - [x] `GET /api/movies/featured`: Lấy danh sách phim nổi bật (Banner/Slider).
11:     - [x] `GET /api/movies/now-showing`: Phim đang chiếu.
12:     - [x] `GET /api/movies/coming-soon`: Phim sắp chiếu.
13:     - [x] `GET /api/movies/{id}`: Chi tiết phim (bao gồm thể loại, diễn viên, trailer).
14: 
15: 2.  **Tìm kiếm & Lọc (Search & Filter)**
16:     - [x] `GET /api/movies/search?query=...`: Tìm kiếm theo tên.
17:     - [x] Bổ sung bộ lọc: Theo thể loại (`genre`), rạp (`theater`), ngày chiếu (`date`).
18: 
19: ## Frontend (React)
20: 1.  **Trang Chủ (Home Page)**
21:     - [x] Slider/Carousel cho phim nổi bật.
22:     - [x] Danh sách thẻ (Card) phim Đang chiếu / Sắp chiếu.
23: 
24: 2.  **Trang Danh sách & Tìm kiếm**
25:     - [x] Trang 'Movies' với bộ lọc (Sidebar hoặc Topbar).
26:     - [x] Thanh tìm kiếm (Search Bar) với gợi ý (debounce).
27: 
28: 3.  **Trang Chi tiết (Movie Details)**
29:     - [x] Hiển thị thông tin đầy đủ: Poster, Trailer (Youtube embed), Synopsis, Cast.
30:     - [x] Nút "Đặt vé" (Booking) điều hướng sang quy trình đặt vé.
