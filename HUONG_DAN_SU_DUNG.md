# 🎮 GameStore - Hướng dẫn sử dụng hoàn chỉnh

## 📋 Tổng quan dự án

GameStore là một nền tảng giao dịch vật phẩm game trực tuyến, cho phép người dùng mua bán skin, tài khoản và các vật phẩm game khác một cách an toàn và tiện lợi.

## 🚀 Cài đặt và triển khai

### Yêu cầu hệ thống
- PHP 7.4+ 
- MySQL 5.7+
- Apache/Nginx
- XAMPP/WAMP (cho development)

### Bước 1: Tải và cài đặt
1. Tải toàn bộ source code vào thư mục `htdocs` của XAMPP
2. Khởi động Apache và MySQL trong XAMPP
3. Truy cập `http://localhost/game/setup.php` để khởi tạo database

### Bước 2: Cấu hình database
1. Mở file `config/database.php`
2. Cập nhật thông tin kết nối database:
```php
private $host = 'localhost';
private $db_name = 'gamestore_db';
private $username = 'root';
private $password = '';
```

### Bước 3: Khởi tạo dữ liệu
1. Truy cập `http://localhost/game/setup.php`
2. Chạy script để tạo database và dữ liệu mẫu
3. Tài khoản admin mặc định: `admin` / `admin123`

## 🏗️ Cấu trúc dự án

```
game/
├── assets/
│   ├── css/           # Stylesheet
│   ├── js/            # JavaScript
│   └── images/        # Hình ảnh
├── auth/              # Đăng nhập/đăng ký
├── user/              # Trang người dùng
├── admin/             # Trang quản trị
├── api/               # API endpoints
├── config/            # Cấu hình
├── database/          # Database schema
└── san-pham/          # Trang sản phẩm
```

## 👥 Tính năng chính

### 🔐 Hệ thống xác thực
- **Đăng ký tài khoản**: Tạo tài khoản mới với thông tin cá nhân
- **Đăng nhập**: Xác thực người dùng với username/email
- **Đăng xuất**: Kết thúc phiên đăng nhập
- **Phân quyền**: Admin và User với các quyền khác nhau

### 🛒 Hệ thống mua sắm
- **Duyệt sản phẩm**: Tìm kiếm và lọc sản phẩm theo game, danh mục
- **Chi tiết sản phẩm**: Xem thông tin chi tiết, hình ảnh, mô tả
- **Giỏ hàng**: Thêm, xóa, cập nhật số lượng sản phẩm
- **Danh sách yêu thích**: Lưu sản phẩm để mua sau
- **Đặt hàng**: Tạo đơn hàng và theo dõi trạng thái

### 🏪 Hệ thống bán hàng
- **Đăng sản phẩm**: Người bán có thể đăng bán vật phẩm
- **Quản lý sản phẩm**: Chỉnh sửa, xóa, cập nhật trạng thái
- **Theo dõi đơn hàng**: Xem và xử lý đơn hàng từ người mua
- **Thống kê**: Xem doanh thu và số liệu bán hàng

### 👨‍💼 Trang quản trị
- **Dashboard**: Tổng quan hệ thống với các thống kê
- **Quản lý người dùng**: Xem, chỉnh sửa, khóa tài khoản
- **Quản lý sản phẩm**: Duyệt, phê duyệt, xóa sản phẩm
- **Quản lý đơn hàng**: Theo dõi và xử lý đơn hàng
- **Báo cáo**: Thống kê doanh thu, người dùng, sản phẩm

## 🎯 Hướng dẫn sử dụng

### Cho người mua
1. **Đăng ký tài khoản** tại `/auth/dang-ky.php`
2. **Duyệt sản phẩm** tại `/san-pham/`
3. **Thêm vào giỏ hàng** các sản phẩm yêu thích
4. **Thanh toán** và chờ người bán liên hệ
5. **Theo dõi đơn hàng** tại `/user/don-hang.php`

### Cho người bán
1. **Liên hệ admin** để nâng cấp tài khoản thành người bán
2. **Đăng sản phẩm** tại `/user/them-san-pham.php`
3. **Quản lý sản phẩm** tại `/user/san-pham-cua-toi.php`
4. **Xử lý đơn hàng** từ người mua
5. **Giao hàng** theo thỏa thuận

### Cho admin
1. **Đăng nhập** với tài khoản admin
2. **Truy cập admin panel** tại `/admin/`
3. **Quản lý hệ thống** qua các menu
4. **Theo dõi báo cáo** và thống kê

## 🔧 API Endpoints

### Cart API (`/api/cart.php`)
- `POST` - Thêm sản phẩm vào giỏ hàng
- `PUT` - Cập nhật số lượng
- `DELETE` - Xóa sản phẩm khỏi giỏ hàng
- `GET` - Lấy thông tin giỏ hàng

### Wishlist API (`/api/wishlist.php`)
- `POST` - Thêm vào danh sách yêu thích
- `DELETE` - Xóa khỏi danh sách yêu thích
- `GET` - Kiểm tra trạng thái yêu thích

### Orders API (`/api/orders.php`)
- `POST` - Tạo đơn hàng mới
- `GET` - Lấy danh sách đơn hàng
- `PUT` - Cập nhật trạng thái đơn hàng

### Products API (`/api/products.php`)
- `GET` - Lấy thông tin sản phẩm
- `POST` - Tạo sản phẩm mới
- `PUT` - Cập nhật sản phẩm
- `DELETE` - Xóa sản phẩm

## 🛡️ Bảo mật

### Các tính năng bảo mật đã triển khai
- **Mã hóa mật khẩu**: Sử dụng `password_hash()`
- **XSS Protection**: `htmlspecialchars()` cho tất cả output
- **SQL Injection**: Prepared statements cho tất cả queries
- **CSRF Protection**: Token validation cho forms
- **Rate Limiting**: Giới hạn số request
- **Input Validation**: Kiểm tra và làm sạch dữ liệu đầu vào
- **Session Security**: Secure session configuration

### Khuyến nghị bảo mật
1. **Cập nhật thường xuyên** các dependencies
2. **Sử dụng HTTPS** trong production
3. **Backup database** định kỳ
4. **Monitor logs** để phát hiện tấn công
5. **Cập nhật PHP** lên phiên bản mới nhất

## 📱 Responsive Design

Website được thiết kế responsive với:
- **Mobile-first approach**
- **Flexible grid system**
- **Touch-friendly interface**
- **Optimized images**
- **Fast loading**

## 🚀 Triển khai Production

### Checklist trước khi deploy
- [ ] Cập nhật database credentials
- [ ] Thiết lập HTTPS
- [ ] Cấu hình error logging
- [ ] Backup database
- [ ] Test tất cả tính năng
- [ ] Cập nhật file permissions
- [ ] Thiết lập monitoring

### Cấu hình server
```apache
# .htaccess
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
```

## 📊 Database Schema

### Bảng chính
- **users**: Thông tin người dùng
- **products**: Sản phẩm game
- **orders**: Đơn hàng
- **cart**: Giỏ hàng
- **wishlist**: Danh sách yêu thích
- **games**: Danh sách game
- **game_categories**: Danh mục game

### Quan hệ
- User → Products (1:N)
- User → Orders (1:N)
- Product → Game (N:1)
- Order → Order Items (1:N)

## 🔍 Troubleshooting

### Lỗi thường gặp
1. **Database connection failed**
   - Kiểm tra thông tin kết nối trong `config/database.php`
   - Đảm bảo MySQL đang chạy

2. **Session not working**
   - Kiểm tra `session_start()` trong các file
   - Đảm bảo session path có quyền ghi

3. **Images not loading**
   - Kiểm tra quyền thư mục `images/`
   - Đảm bảo file tồn tại

4. **CSS/JS not loading**
   - Kiểm tra đường dẫn trong HTML
   - Clear browser cache

### Debug mode
Để bật debug mode, thêm vào đầu file:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## 📞 Hỗ trợ

### Liên hệ
- **Email**: support@gamestore.vn
- **Hotline**: 1900 1234
- **Website**: http://localhost/game

### Tài liệu tham khảo
- [PHP Documentation](https://www.php.net/docs.php)
- [MySQL Documentation](https://dev.mysql.com/doc/)
- [Bootstrap Documentation](https://getbootstrap.com/docs/)

## 🎉 Kết luận

GameStore đã được xây dựng hoàn chỉnh với đầy đủ tính năng cho một nền tảng giao dịch game items. Hệ thống có thể mở rộng và tùy chỉnh theo nhu cầu cụ thể.

**Chúc bạn sử dụng thành công! 🚀**
