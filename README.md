# GameStore - Nền tảng giao dịch vật phẩm game

## 🎮 Giới thiệu

GameStore là một nền tảng giao dịch vật phẩm game hiện đại, cho phép người dùng mua bán skin, tài khoản và các vật phẩm game khác một cách an toàn và tiện lợi.

## ✨ Tính năng chính

### 🔐 Hệ thống xác thực
- Đăng ký/đăng nhập an toàn
- Xác thực email
- Bảo mật 2 lớp
- Quản lý phiên đăng nhập

### 🛒 Giao dịch
- Giao dịch người với người
- Giỏ hàng thông minh
- Thanh toán đa dạng (COD, chuyển khoản, ví điện tử)
- Hệ thống đánh giá và phản hồi

### 🎯 Quản lý sản phẩm
- Đăng bán vật phẩm dễ dàng
- Upload ảnh sản phẩm
- Phân loại theo game và loại vật phẩm
- Tìm kiếm và lọc thông minh

### 👥 Quản trị
- Dashboard admin đầy đủ
- Quản lý người dùng
- Quản lý sản phẩm
- Báo cáo thống kê
- Hệ thống báo cáo và xử lý

### 🛡️ Bảo mật
- Mã hóa dữ liệu
- Bảo vệ chống SQL injection
- Bảo vệ chống XSS
- Rate limiting
- Content Security Policy

## 🚀 Cài đặt

### Yêu cầu hệ thống
- PHP 7.4 trở lên
- MySQL 5.7 trở lên
- Web server (Apache/Nginx)
- Extension: PDO, PDO_MySQL

### Cài đặt nhanh

1. **Clone repository**
```bash
git clone https://github.com/yourusername/gamestore.git
cd gamestore
```

2. **Cấu hình database**
```php
// config/database.php
private $host = 'localhost';
private $db_name = 'gamestore_db';
private $username = 'your_username';
private $password = 'your_password';
```

3. **Chạy setup**
Truy cập `http://yourdomain.com/setup.php` và làm theo hướng dẫn

4. **Hoàn thành**
- Truy cập website: `http://yourdomain.com`
- Trang quản trị: `http://yourdomain.com/admin`
- Đăng nhập admin: `admin` / `admin123`

## 📁 Cấu trúc thư mục

```
gamestore/
├── 📁 admin/                 # Trang quản trị
│   ├── index.php            # Dashboard
│   ├── products.php         # Quản lý sản phẩm
│   ├── orders.php           # Quản lý đơn hàng
│   ├── users.php            # Quản lý người dùng
│   └── reports.php          # Báo cáo
├── 📁 api/                  # API endpoints
│   ├── cart.php            # Giỏ hàng
│   ├── orders.php          # Đơn hàng
│   ├── wishlist.php        # Yêu thích
│   └── products.php        # Sản phẩm
├── 📁 assets/              # Tài nguyên tĩnh
│   ├── css/                # Stylesheets
│   ├── js/                 # JavaScript
│   └── images/             # Hình ảnh
├── 📁 auth/                # Xác thực
│   ├── dang-nhap.php       # Đăng nhập
│   ├── dang-ky.php         # Đăng ký
│   └── dang-xuat.php       # Đăng xuất
├── 📁 config/              # Cấu hình
│   ├── database.php        # Kết nối DB
│   ├── session.php         # Quản lý session
│   ├── security.php        # Bảo mật
│   └── image-helper.php    # Helper ảnh
├── 📁 database/            # Database
│   └── schema.sql          # Cấu trúc DB
├── 📁 images/              # Ảnh sản phẩm
├── 📁 san-pham/            # Trang sản phẩm
├── 📁 user/                # Trang người dùng
├── index.php               # Trang chủ
├── setup.php              # Cài đặt ban đầu
└── README.md              # Tài liệu
```

## 🗄️ Database Schema

### Bảng chính
- **users**: Thông tin người dùng
- **products**: Sản phẩm game
- **orders**: Đơn hàng
- **cart**: Giỏ hàng
- **wishlist**: Danh sách yêu thích
- **reviews**: Đánh giá
- **messages**: Tin nhắn
- **reports**: Báo cáo

### Quan hệ
- User có nhiều Products (1:N)
- User có nhiều Orders (1:N)
- Order có nhiều Order Items (1:N)
- Product có nhiều Images (1:N)

## 🎨 Giao diện

### Responsive Design
- Mobile-first approach
- Breakpoints: 480px, 768px, 1024px
- Grid system linh hoạt
- Typography tối ưu

### UI Components
- Cards hiện đại
- Buttons với hover effects
- Forms validation
- Modals và dropdowns
- Loading states

### Color Scheme
- Primary: #6366f1 (Indigo)
- Secondary: #f1f5f9 (Light Gray)
- Success: #10b981 (Emerald)
- Warning: #f59e0b (Amber)
- Error: #ef4444 (Red)

## 🔧 API Endpoints

### Authentication
- `POST /auth/dang-nhap.php` - Đăng nhập
- `POST /auth/dang-ky.php` - Đăng ký
- `GET /auth/dang-xuat.php` - Đăng xuất

### Products
- `GET /api/products.php` - Lấy danh sách sản phẩm
- `POST /api/products.php` - Tạo sản phẩm mới
- `PUT /api/products.php` - Cập nhật sản phẩm
- `DELETE /api/products.php` - Xóa sản phẩm

### Cart
- `GET /api/cart.php` - Lấy giỏ hàng
- `POST /api/cart.php` - Thêm vào giỏ hàng
- `PUT /api/cart.php` - Cập nhật giỏ hàng
- `DELETE /api/cart.php` - Xóa khỏi giỏ hàng

### Orders
- `GET /api/orders.php` - Lấy đơn hàng
- `POST /api/orders.php` - Tạo đơn hàng
- `PUT /api/orders.php` - Cập nhật đơn hàng

## 🛡️ Bảo mật

### Input Validation
- Sanitize tất cả input
- Validate email, phone, password
- Escape output để chống XSS

### SQL Injection Prevention
- Sử dụng Prepared Statements
- Validate input parameters
- Escape special characters

### Rate Limiting
- Giới hạn số lần đăng nhập
- Giới hạn API requests
- Chống spam và abuse

### Session Security
- Secure session cookies
- Regenerate session ID
- Session timeout

## 📊 Performance

### Optimization
- Lazy loading images
- Minified CSS/JS
- Database indexing
- Caching strategies

### Monitoring
- Error logging
- Performance metrics
- User activity tracking

## 🚀 Deployment

### Production Setup
1. Cấu hình web server
2. Thiết lập SSL certificate
3. Cấu hình database production
4. Backup tự động
5. Monitoring và logging

### Security Checklist
- [ ] Đổi mật khẩu admin mặc định
- [ ] Cấu hình HTTPS
- [ ] Thiết lập firewall
- [ ] Backup database
- [ ] Xóa file setup.php

## 🤝 Đóng góp

1. Fork repository
2. Tạo feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Tạo Pull Request

## 📝 License

Distributed under the MIT License. See `LICENSE` for more information.

## 📞 Liên hệ

- Website: [https://gamestore.vn](https://gamestore.vn)
- Email: support@gamestore.vn
- GitHub: [@yourusername](https://github.com/yourusername)

## 🙏 Acknowledgments

- [Bootstrap](https://getbootstrap.com/) - CSS Framework
- [Font Awesome](https://fontawesome.com/) - Icons
- [PHP](https://php.net/) - Backend Language
- [MySQL](https://mysql.com/) - Database

---

**Made with ❤️ by GameStore Team**
