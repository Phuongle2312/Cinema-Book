-- =============================================
-- 3. CHÈN DỮ LIỆU MẪU (HÀ NỘI & TRENDING)
-- =============================================

-- Danh mục
SET IDENTITY_INSERT [seat_types] ON;
INSERT INTO [seat_types] ([seat_type_id], [name], [surcharge]) VALUES (1, N'Standard', 0), (2, N'VIP', 20000);
SET IDENTITY_INSERT [seat_types] OFF;

SET IDENTITY_INSERT [users] ON;
INSERT INTO [users] ([id], [name], [email], [role]) VALUES (1, N'Admin', 'admin@cinema.com', 'admin'), (2, N'Văn A', 'vana@gmail.com', 'user');
SET IDENTITY_INSERT [users] OFF;

SET IDENTITY_INSERT [combos] ON;
INSERT INTO [combos] ([combo_id], [name], [items], [price]) VALUES (1, N'Combo 1', N'[{"item":"Corn","size":"S"}]', 50000);
SET IDENTITY_INSERT [combos] OFF;

-- Rạp & Phim
SET IDENTITY_INSERT [theaters] ON;
INSERT INTO [theaters] ([theater_id], [name], [city], [address]) VALUES (1, N'CGV Vincom Bà Triệu', N'Hà Nội', N'Hai Bà Trưng, HN');
SET IDENTITY_INSERT [theaters] OFF;

SET IDENTITY_INSERT [rooms] ON;
INSERT INTO [rooms] ([room_id], [theater_id], [name], [screen_type]) VALUES (1, 1, N'P.01 - IMAX', 'imax');
SET IDENTITY_INSERT [rooms] OFF;

SET IDENTITY_INSERT [seats] ON;
INSERT INTO [seats] ([seat_id], [room_id], [row], [number], [seat_type_id]) VALUES (1, 1, 'A', 1, 1), (2, 1, 'A', 2, 1);
SET IDENTITY_INSERT [seats] OFF;

SET IDENTITY_INSERT [movies] ON;
INSERT INTO [movies] ([movie_id], [title], [slug], [duration], [release_date], [status]) VALUES (1, N'Captain America 4', 'captain-america-4', 135, '2025-02-14', 'now_showing');
SET IDENTITY_INSERT [movies] OFF;

SET IDENTITY_INSERT [showtimes] ON;
INSERT INTO [showtimes] ([showtime_id], [movie_id], [room_id], [start_time], [base_price]) VALUES (1, 1, 1, '2025-12-30 19:00:00', 150000);
SET IDENTITY_INSERT [showtimes] OFF;

-- Giao dịch đặt vé
SET IDENTITY_INSERT [bookings] ON;
INSERT INTO [bookings] ([booking_id], [user_id], [showtime_id], [booking_code], [total_price]) VALUES (1, 2, 1, 'HN888', 300000);
SET IDENTITY_INSERT [bookings] OFF;

INSERT INTO [booking_seats] ([booking_id], [seat_id], [showtime_id], [price]) VALUES (1, 1, 1, 150000), (1, 2, 1, 150000);
GO

-- Thêm ảnh poster chính
INSERT INTO movie_images (movie_id, image_url, image_type, is_primary, sort_order)
VALUES (1, 'https://cdn.../poster1.jpg', 'poster', 1, 1);

-- Thêm ảnh gallery
INSERT INTO movie_images (movie_id, image_url, image_type, sort_order)
VALUES (1, 'https://cdn.../gallery1.jpg', 'gallery', 2);


-- Kiểm tra kết quả
SELECT * FROM theaters;
SELECT * FROM movies;