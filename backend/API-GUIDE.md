# 📚 HƯỚNG DẪN GỌI API - CINEMA BOOKING SYSTEM

## 🌐 Base URL
```
http://127.0.0.1:8000/api
```

---

## 📋 DANH SÁCH API ENDPOINTS

### ✅ 1. TEST API (Public)
**Kiểm tra xem API có hoạt động không**

```http
GET /api/test
```

**Response:**
```json
{
  "success": true,
  "message": "Cinema Booking API is working!",
  "timestamp": "2025-12-24T02:32:01.562631Z"
}
```

**Cách test trong browser:**
```
http://127.0.0.1:8000/api/test
```

---

### 🔐 2. AUTHENTICATION APIs

#### 2.1. Đăng ký tài khoản mới
```http
POST /api/auth/register
Content-Type: application/json

{
  "name": "Nguyen Van A",
  "email": "user@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "0123456789",
  "date_of_birth": "1990-01-01"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Đăng ký thành công",
  "data": {
    "user": {
      "id": 1,
      "name": "Nguyen Van A",
      "email": "user@example.com",
      "role": "user"
    },
    "token": "1|xxxxxxxxxxxxxxxxxxxxxx",
    "token_type": "Bearer"
  }
}
```

#### 2.2. Đăng nhập
```http
POST /api/auth/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Đăng nhập thành công",
  "data": {
    "user": { ... },
    "token": "2|xxxxxxxxxxxxxxxxxxxxxx",
    "token_type": "Bearer"
  }
}
```

#### 2.3. Đăng xuất (Cần token)
```http
POST /api/logout
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "message": "Đăng xuất thành công"
}
```

---

### 👤 3. USER APIs (Cần Authentication)

#### 3.1. Lấy thông tin user hiện tại
```http
GET /api/user
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Nguyen Van A",
    "email": "user@example.com",
    "phone": "0123456789",
    "role": "user"
  }
}
```

#### 3.2. Cập nhật thông tin user
```http
PUT /api/user
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Nguyen Van B",
  "phone": "0987654321",
  "date_of_birth": "1995-05-15"
}
```

---

### 🔗 4. SOCIAL LOGIN APIs

#### 4.1. Google Login
```http
GET /api/auth/google
```
Redirect đến trang đăng nhập Google

#### 4.2. Google Callback
```http
GET /api/auth/google/callback
```

#### 4.3. Facebook Login
```http
GET /api/auth/facebook
```

#### 4.4. Facebook Callback
```http
GET /api/auth/facebook/callback
```

---

## 🧪 CÁCH TEST API

### 1. Sử dụng Browser (GET requests)
Mở browser và truy cập:
```
http://127.0.0.1:8000/api/test
```

### 2. Sử dụng PowerShell
```powershell
# Test GET request
Invoke-WebRequest -Uri "http://127.0.0.1:8000/api/test" -UseBasicParsing | Select-Object -ExpandProperty Content

# Test POST request (Register)
$body = @{
    name = "Test User"
    email = "test@example.com"
    password = "password123"
    password_confirmation = "password123"
} | ConvertTo-Json

Invoke-WebRequest -Uri "http://127.0.0.1:8000/api/auth/register" `
    -Method POST `
    -Body $body `
    -ContentType "application/json" `
    -UseBasicParsing | Select-Object -ExpandProperty Content
```

### 3. Sử dụng Postman
1. Mở Postman
2. Tạo request mới
3. Chọn method (GET/POST/PUT)
4. Nhập URL: `http://127.0.0.1:8000/api/test`
5. Click Send

### 4. Sử dụng cURL (nếu có)
```bash
# Test API
curl http://127.0.0.1:8000/api/test

# Register
curl -X POST http://127.0.0.1:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

---

## 🔑 AUTHENTICATION

Các API cần authentication phải gửi kèm header:
```
Authorization: Bearer {token}
```

Token nhận được từ response khi đăng ký hoặc đăng nhập.

**Ví dụ:**
```
Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxxx
```

---

## 📝 LƯU Ý

1. **Server phải đang chạy:**
   ```bash
   cd E:\Github\Cinema-Book\backend
   php artisan serve
   ```

2. **MySQL phải đang chạy:**
   - Mở XAMPP Control Panel
   - Start MySQL

3. **CORS:** Nếu gọi từ frontend khác domain, cần cấu hình CORS

4. **Content-Type:** Luôn set `Content-Type: application/json` cho POST/PUT requests

---

## 🎯 NEXT STEPS

Các API sẽ được thêm sau:
- Movies API (danh sách phim, chi tiết phim)
- Showtimes API (lịch chiếu)
- Booking API (đặt vé)
- Theaters API (rạp chiếu)
- Seats API (ghế ngồi)

---

**Tạo bởi:** Antigravity AI  
**Ngày:** 2025-12-24
