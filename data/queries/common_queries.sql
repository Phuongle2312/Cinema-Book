-- ================================================
-- CÁC CÂU LỆNH SQL THÔNG DỤNG - CINEMA BOOKING
-- ================================================

USE cinema_booking;

-- 1. QUẢN LÝ PHIM & SUẤT CHIẾU
-- ------------------------------------------------

-- Xem danh sách phim đang chiếu và số lượng suất chiếu
SELECT m.title, m.status, COUNT(st.id) as total_showtimes
FROM movies m
LEFT JOIN showtimes st ON m.id = st.movie_id
WHERE m.status = 'showing'
GROUP BY m.id, m.title, m.status;

-- Tìm các suất chiếu trong ngày hôm nay
SELECT m.title, t.name as theater, r.name as room, st.start_time
FROM showtimes st
JOIN movies m ON st.movie_id = m.id
JOIN rooms r ON st.room_id = r.id
JOIN theaters t ON r.theater_id = t.id
WHERE DATE(st.start_time) = CURDATE()
ORDER BY st.start_time;


-- 2. QUẢN LÝ ĐẶT VÉ & DOANH THU
-- ------------------------------------------------

-- Thống kê doanh thu theo từng phim
SELECT m.title, SUM(b.final_price) as total_revenue, COUNT(b.id) as tickets_sold
FROM movies m
JOIN showtimes st ON m.id = st.movie_id
JOIN bookings b ON st.id = b.showtime_id
WHERE b.status = 'confirmed'
GROUP BY m.id, m.title
ORDER BY total_revenue DESC;

-- Xem chi tiết các vé đã đặt của một khách hàng (ví dụ user_id = 1)
SELECT b.booking_code, b.total_price, b.status, b.created_at, m.title, st.start_time
FROM bookings b
JOIN showtimes st ON b.showtime_id = st.id
JOIN movies m ON st.movie_id = m.id
WHERE b.user_id = 1
ORDER BY b.created_at DESC;


-- 3. QUẢN LÝ RẠP & GHẾ
-- ------------------------------------------------

-- Kiểm tra tình trạng ghế của một suất chiếu (ví dụ showtime_id = 1)
SELECT s.seat_number, s.row, s.type, 
       IF(bs.id IS NULL, 'Available', 'Booked') as status
FROM seats s
LEFT JOIN booking_seats bs ON s.id = bs.seat_id AND bs.showtime_id = 1
WHERE s.room_id = (SELECT room_id FROM showtimes WHERE id = 1)
ORDER BY s.row, s.seat_column;

-- Đếm số lượng ghế theo loại trong mỗi phòng
SELECT r.name as room_name, s.type, COUNT(s.id) as seat_count
FROM rooms r
JOIN seats s ON r.id = s.room_id
GROUP BY r.id, s.type;


-- 4. BẢO TRÌ HỆ THỐNG
-- ------------------------------------------------

-- Xóa các lượt giữ ghế (seat locks) đã hết hạn
DELETE FROM seat_locks WHERE expires_at < NOW();

-- Tìm các đơn hàng chưa thanh toán quá 15 phút
SELECT booking_code, final_price, created_at
FROM bookings
WHERE status = 'pending' AND created_at < DATE_SUB(NOW(), INTERVAL 15 MINUTE);
