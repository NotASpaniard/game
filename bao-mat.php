<?php
require_once 'config/session.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chính sách bảo mật - GameStore</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="index.php">
                        <i class="fas fa-gamepad"></i>
                        <span>GameStore</span>
                    </a>
                </div>
                
                <nav class="nav">
                    <a href="index.php" class="nav-link">Trang chủ</a>
                    <a href="san-pham/" class="nav-link">Sản phẩm</a>
                    <a href="danh-muc/" class="nav-link">Danh mục</a>
                    <a href="huong-dan.php" class="nav-link">Hướng dẫn</a>
                    <a href="lien-he.php" class="nav-link">Liên hệ</a>
                </nav>
                
                <div class="header-actions">
                    <div class="search-box">
                        <form action="san-pham/" method="GET">
                            <input type="text" name="search" placeholder="Tìm kiếm vật phẩm..." class="search-input">
                            <button type="submit" class="search-btn">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>
                    
                    <div class="user-actions">
                        <?php if (isLoggedIn()): ?>
                            <a href="user/gio-hang.php" class="cart-btn">
                                <i class="fas fa-shopping-cart"></i>
                                <span class="cart-count" id="cart-count">0</span>
                            </a>
                            <div class="user-menu">
                                <button class="user-btn">
                                    <i class="fas fa-user"></i>
                                    <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>
                                </button>
                                <div class="user-dropdown">
                                    <a href="user/tai-khoan.php">Tài khoản</a>
                                    <a href="user/don-hang.php">Đơn hàng</a>
                                    <a href="user/yeu-thich.php">Yêu thích</a>
                                    <a href="auth/dang-xuat.php">Đăng xuất</a>
                                </div>
                            </div>
                        <?php else: ?>
                            <a href="auth/dang-nhap.php" class="btn btn-outline">Đăng nhập</a>
                            <a href="auth/dang-ky.php" class="btn btn-primary">Đăng ký</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main">
        <div class="container">
            <div class="page-header">
                <h1>Chính sách bảo mật</h1>
                <p>Cập nhật lần cuối: 01/01/2025</p>
            </div>

            <div class="privacy-content">
                <section class="privacy-section">
                    <h2>1. Thu thập thông tin</h2>
                    <p>Chúng tôi thu thập thông tin cá nhân khi bạn:</p>
                    <ul>
                        <li>Đăng ký tài khoản</li>
                        <li>Thực hiện giao dịch</li>
                        <li>Liên hệ hỗ trợ</li>
                        <li>Sử dụng các tính năng của website</li>
                    </ul>
                    <p><strong>Thông tin thu thập bao gồm:</strong></p>
                    <ul>
                        <li>Họ tên, email, số điện thoại</li>
                        <li>Địa chỉ giao hàng</li>
                        <li>Thông tin giao dịch</li>
                        <li>Dữ liệu sử dụng website</li>
                    </ul>
                </section>

                <section class="privacy-section">
                    <h2>2. Sử dụng thông tin</h2>
                    <p>Thông tin cá nhân được sử dụng để:</p>
                    <ul>
                        <li>Cung cấp và cải thiện dịch vụ</li>
                        <li>Xử lý giao dịch</li>
                        <li>Gửi thông báo quan trọng</li>
                        <li>Hỗ trợ khách hàng</li>
                        <li>Tuân thủ pháp luật</li>
                    </ul>
                </section>

                <section class="privacy-section">
                    <h2>3. Bảo vệ thông tin</h2>
                    <p>Chúng tôi áp dụng các biện pháp bảo mật:</p>
                    <ul>
                        <li>Mã hóa SSL/TLS</li>
                        <li>Bảo vệ cơ sở dữ liệu</li>
                        <li>Kiểm soát truy cập</li>
                        <li>Giám sát bảo mật 24/7</li>
                        <li>Đào tạo nhân viên về bảo mật</li>
                    </ul>
                </section>

                <section class="privacy-section">
                    <h2>4. Chia sẻ thông tin</h2>
                    <p>Chúng tôi KHÔNG bán, cho thuê hoặc chia sẻ thông tin cá nhân với bên thứ ba, trừ khi:</p>
                    <ul>
                        <li>Có sự đồng ý của bạn</li>
                        <li>Tuân thủ pháp luật</li>
                        <li>Bảo vệ quyền lợi hợp pháp</li>
                        <li>Với đối tác tin cậy (có thỏa thuận bảo mật)</li>
                    </ul>
                </section>

                <section class="privacy-section">
                    <h2>5. Cookie và công nghệ theo dõi</h2>
                    <p>Chúng tôi sử dụng cookie để:</p>
                    <ul>
                        <li>Cải thiện trải nghiệm người dùng</li>
                        <li>Ghi nhớ tùy chọn</li>
                        <li>Phân tích lưu lượng truy cập</li>
                        <li>Bảo mật tài khoản</li>
                    </ul>
                    <p>Bạn có thể tắt cookie trong trình duyệt, nhưng có thể ảnh hưởng đến chức năng website.</p>
                </section>

                <section class="privacy-section">
                    <h2>6. Quyền của bạn</h2>
                    <p>Bạn có quyền:</p>
                    <ul>
                        <li>Truy cập thông tin cá nhân</li>
                        <li>Chỉnh sửa thông tin</li>
                        <li>Xóa tài khoản</li>
                        <li>Rút lại đồng ý</li>
                        <li>Khiếu nại về bảo mật</li>
                    </ul>
                </section>

                <section class="privacy-section">
                    <h2>7. Bảo mật giao dịch</h2>
                    <p>Mọi giao dịch được bảo vệ bằng:</p>
                    <ul>
                        <li>Mã hóa end-to-end</li>
                        <li>Xác thực 2 lớp</li>
                        <li>Giám sát gian lận</li>
                        <li>Bảo hiểm giao dịch</li>
                    </ul>
                </section>

                <section class="privacy-section">
                    <h2>8. Liên hệ về bảo mật</h2>
                    <p>Nếu bạn có câu hỏi về chính sách bảo mật:</p>
                    <ul>
                        <li>Email: privacy@gamestore.vn</li>
                        <li>Hotline: 1900 1234</li>
                        <li>Địa chỉ: Hà Nội, Việt Nam</li>
                    </ul>
                </section>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>GameStore</h3>
                    <p>Nền tảng giao dịch vật phẩm game uy tín và an toàn</p>
                </div>
                <div class="footer-section">
                    <h4>Liên kết nhanh</h4>
                    <ul>
                        <li><a href="san-pham/">Sản phẩm</a></li>
                        <li><a href="danh-muc/">Danh mục</a></li>
                        <li><a href="huong-dan.php">Hướng dẫn</a></li>
                        <li><a href="lien-he.php">Liên hệ</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Hỗ trợ</h4>
                    <ul>
                        <li><a href="tro-giup.php">Trợ giúp</a></li>
                        <li><a href="dieu-khoan.php">Điều khoản</a></li>
                        <li><a href="bao-mat.php">Bảo mật</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 GameStore. Tất cả quyền được bảo lưu.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>
