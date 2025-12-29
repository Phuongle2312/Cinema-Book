USE master;
GO

-- 1. Xóa DB cũ nếu đang tồn tại để làm mới hoàn toàn
IF EXISTS (SELECT name FROM sys.databases WHERE name = N'cinbook')
BEGIN
    ALTER DATABASE cinbook SET SINGLE_USER WITH ROLLBACK IMMEDIATE;
    DROP DATABASE cinbook;
END
GO

CREATE DATABASE cinbook;
GO

USE cinbook;
GO

-- =============================================
-- 2. TẠO CẤU TRÚC BẢNG (SCHEMA)
-- =============================================

CREATE TABLE [seat_types] (
    [seat_type_id] BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY,
    [name] NVARCHAR(255) NOT NULL,
    [surcharge] DECIMAL(10, 0) DEFAULT 0
);

CREATE TABLE [users] (
   [id] bigint IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [name] nvarchar(255) NOT NULL,
  [email] nvarchar(255) NOT NULL UNIQUE,
  [email_verified_at] datetime2 NULL,
  [password] nvarchar(255) NULL,
  [phone] varchar(15) NULL,
  [date_of_birth] date NULL,
  [role] varchar(20) NOT NULL DEFAULT 'user' CHECK ([role] IN ('user', 'admin')),
  [provider] nvarchar(255) NULL,
  [provider_id] nvarchar(255) NULL,
  [avatar] nvarchar(max) NULL,
  [remember_token] varchar(100) NULL,
  [created_at] datetime2 NULL DEFAULT GETDATE(),
  [updated_at] datetime2 NULL
);

CREATE TABLE [combos] (
 [combo_id] bigint IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [name] nvarchar(255) NOT NULL,
  [description] nvarchar(max) NULL,
  [items] nvarchar(max) NOT NULL CHECK (ISJSON([items]) = 1),
  [price] decimal(10,0) NOT NULL,
  [image_url] nvarchar(255) NULL,
  [is_available] bit NOT NULL DEFAULT 1,
  [created_at] datetime2 NULL DEFAULT GETDATE(),
  [updated_at] datetime2 NULL
);

CREATE TABLE [movies] (
   [movie_id] bigint IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [title] nvarchar(255) NOT NULL,
  [slug] nvarchar(255) NOT NULL UNIQUE,
  [description] nvarchar(max) NULL,
  [duration] int NOT NULL,
  [release_date] date NOT NULL,
  [poster_url] nvarchar(255) NULL,
  [trailer_url] nvarchar(255) NULL,
  [banner_url] nvarchar(255) NULL,
  [rating] decimal(3,1) NOT NULL DEFAULT 0.0,
  [status] varchar(20) NOT NULL DEFAULT 'coming_soon' CHECK ([status] IN ('coming_soon', 'now_showing', 'ended')),
  [created_at] datetime2 NULL DEFAULT GETDATE(),
  [updated_at] datetime2 NULL
);
-- Bảng lưu ảnh phim
CREATE TABLE [movie_images] (
    [image_id] BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY,
    [movie_id] BIGINT NOT NULL,
    [image_url] NVARCHAR(500) NOT NULL,
    [image_type] VARCHAR(50) NOT NULL DEFAULT 'gallery' 
        CHECK ([image_type] IN ('poster', 'banner', 'gallery', 'thumbnail')),
    [is_primary] BIT NOT NULL DEFAULT 0,
    [sort_order] INT NOT NULL DEFAULT 0,
    [created_at] DATETIME2 NULL DEFAULT GETDATE(),
    [updated_at] DATETIME2 NULL
);

-- Khóa ngoại liên kết với bảng movies
ALTER TABLE [movie_images]
ADD CONSTRAINT [FK_movie_images_movies]
FOREIGN KEY ([movie_id]) REFERENCES [movies]([movie_id])
ON DELETE CASCADE;
GO


CREATE TABLE [theaters] (
  [theater_id] bigint IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [name] nvarchar(255) NOT NULL,
  [city] nvarchar(255) NOT NULL,
  [address] nvarchar(255) NOT NULL,
  [phone] nvarchar(255) NULL,
  [facilities] nvarchar(max) NULL,
  [created_at] datetime2 NULL DEFAULT GETDATE(),
  [updated_at] datetime2 NULL
);

CREATE TABLE [rooms] (
  [room_id] bigint IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [theater_id] bigint NOT NULL,
  [name] nvarchar(255) NOT NULL,
  [total_seats] int NOT NULL DEFAULT 0,
  [screen_type] varchar(20) NOT NULL DEFAULT 'standard' CHECK ([screen_type] IN ('standard', 'vip', 'imax', '4dx')),
  [created_at] datetime2 NULL DEFAULT GETDATE(),
  [updated_at] datetime2 NULL,
  CONSTRAINT [FK_rooms_theaters] FOREIGN KEY ([theater_id]) REFERENCES [theaters] ([theater_id]) ON DELETE CASCADE
);

CREATE TABLE [seats] (
   [seat_id] bigint IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [room_id] bigint NOT NULL,
  [row] nvarchar(255) NOT NULL,
  [number] int NOT NULL,
  [seat_type_id] bigint NULL,
  [extra_price] decimal(10,0) NOT NULL DEFAULT 0,
  [created_at] datetime2 NULL DEFAULT GETDATE(),
  [updated_at] datetime2 NULL,
  CONSTRAINT [UQ_seats_unique] UNIQUE ([room_id], [row], [number]),
  CONSTRAINT [FK_seats_rooms] FOREIGN KEY ([room_id]) REFERENCES [rooms] ([room_id]) ON DELETE CASCADE,
  CONSTRAINT [FK_seats_types] FOREIGN KEY ([seat_type_id]) REFERENCES [seat_types] ([seat_type_id]) ON DELETE SET NULL
);

CREATE TABLE [showtimes] (
  [showtime_id] bigint IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [movie_id] bigint NOT NULL,
  [room_id] bigint NOT NULL,
  [start_time] datetime2 NOT NULL,
  [base_price] decimal(10,0) NOT NULL,
  [status] varchar(20) NOT NULL DEFAULT 'scheduled' CHECK ([status] IN ('scheduled', 'ongoing', 'completed', 'cancelled')),
  [created_at] datetime2 NULL DEFAULT GETDATE(),
  [updated_at] datetime2 NULL,
  CONSTRAINT [UQ_showtimes_unique] UNIQUE ([room_id], [start_time]),
  CONSTRAINT [FK_showtimes_movies] FOREIGN KEY ([movie_id]) REFERENCES [movies] ([movie_id]) ON DELETE CASCADE,
  CONSTRAINT [FK_showtimes_rooms] FOREIGN KEY ([room_id]) REFERENCES [rooms] ([room_id]) ON DELETE CASCADE
);

CREATE TABLE [bookings] (
  [booking_id] bigint IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [user_id] bigint NOT NULL,
  [showtime_id] bigint NOT NULL,
  [booking_code] varchar(20) NOT NULL UNIQUE,
  [total_price] decimal(10,0) NOT NULL,
  [status] varchar(20) DEFAULT 'confirmed',
  CONSTRAINT [FK_bookings_users] FOREIGN KEY ([user_id]) REFERENCES [users] ([id]),
  CONSTRAINT [FK_bookings_showtimes] FOREIGN KEY ([showtime_id]) REFERENCES [showtimes] ([showtime_id])
);
-- 10. Bảng booking_combos
CREATE TABLE [booking_combos] (
  [id] bigint IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [booking_id] bigint NOT NULL,
  [combo_id] bigint NOT NULL,
  [quantity] int NOT NULL DEFAULT 1,
  [unit_price] decimal(10,0) NOT NULL,
  [total_price] decimal(10,0) NOT NULL,
  [created_at] datetime2 NULL DEFAULT GETDATE(),
  [updated_at] datetime2 NULL,
  CONSTRAINT [FK_booking_combos_booking] FOREIGN KEY ([booking_id]) REFERENCES [bookings] ([booking_id]) ON DELETE CASCADE,
  CONSTRAINT [FK_booking_combos_combo] FOREIGN KEY ([combo_id]) REFERENCES [combos] ([combo_id]) ON DELETE CASCADE
);
GO
-- 11. Bảng booking_details (ĐÃ SỬA LỖI CASCADE)
CREATE TABLE [booking_details] (
  [detail_id] bigint IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [booking_id] bigint NOT NULL,
  [seat_id] bigint NOT NULL,
  [showtime_id] bigint NOT NULL,
  [ticket_code] varchar(30) NOT NULL UNIQUE,
  [base_price] decimal(10,0) NOT NULL,
  [seat_extra_price] decimal(10,0) DEFAULT 0,
  [dynamic_price_adjustment] decimal(10,0) DEFAULT 0,
  [final_price] decimal(10,0) NOT NULL,
  [applied_pricing_rules] nvarchar(max) NULL CHECK (ISJSON([applied_pricing_rules]) = 1 OR [applied_pricing_rules] IS NULL),
  [status] varchar(20) DEFAULT 'valid' CHECK ([status] IN ('valid', 'used', 'cancelled', 'expired')),
  [used_at] datetime2 NULL,
  [created_at] datetime2 NULL DEFAULT GETDATE(),
  [updated_at] datetime2 NULL,
  CONSTRAINT [UQ_booking_details_unique] UNIQUE ([showtime_id], [seat_id]),
  
  -- Giữ Cascade cho Booking (quan trọng nhất)
  CONSTRAINT [FK_booking_details_booking] FOREIGN KEY ([booking_id]) REFERENCES [bookings] ([booking_id]) ON DELETE CASCADE,
  
  -- Đổi sang NO ACTION cho Seat và Showtime để tránh lỗi Msg 1785
  CONSTRAINT [FK_booking_details_seat] FOREIGN KEY ([seat_id]) REFERENCES [seats] ([seat_id]) ON DELETE NO ACTION,
  CONSTRAINT [FK_booking_details_showtime] FOREIGN KEY ([showtime_id]) REFERENCES [showtimes] ([showtime_id]) ON DELETE NO ACTION
);
GO


CREATE TABLE [booking_seats] (
[id] bigint IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [booking_id] bigint NOT NULL,
  [seat_id] bigint NOT NULL,
  [showtime_id] bigint NOT NULL,
  [price] decimal(10,0) NOT NULL,
  [created_at] datetime2 NULL DEFAULT GETDATE(),
  [updated_at] datetime2 NULL,
  CONSTRAINT [UQ_booking_seats_unique] UNIQUE ([showtime_id], [seat_id]),
  CONSTRAINT [FK_booking_seats_booking] FOREIGN KEY ([booking_id]) REFERENCES [bookings] ([booking_id]) ON DELETE CASCADE,
  -- Đổi sang NO ACTION
  CONSTRAINT [FK_booking_seats_seat] FOREIGN KEY ([seat_id]) REFERENCES [seats] ([seat_id]) ON DELETE NO ACTION,
  CONSTRAINT [FK_booking_seats_showtime] FOREIGN KEY ([showtime_id]) REFERENCES [showtimes] ([showtime_id]) ON DELETE NO ACTION
);
GO
DROP TABLE IF EXISTS [reviews];
DROP TABLE IF EXISTS [booking_seats];
DROP TABLE IF EXISTS [booking_details];
DROP TABLE IF EXISTS [booking_combos];
DROP TABLE IF EXISTS [bookings];
DROP TABLE IF EXISTS [showtimes];
DROP TABLE IF EXISTS [seats];
DROP TABLE IF EXISTS [rooms];
DROP TABLE IF EXISTS [theaters];
DROP TABLE IF EXISTS [movies];
DROP TABLE IF EXISTS [combos];
DROP TABLE IF EXISTS [users];
DROP TABLE IF EXISTS [seat_types];
GO

