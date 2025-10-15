<?php
require_once 'config/session.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Điều khoản sử dụng - GameStore</title>
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
                <h1>Điều khoản sử dụng</h1>
                <p>Cập nhật lần cuối: 01/01/2025</p>
            </div>

            <div class="terms-content">
                <section class="terms-section">
                    <h2>1. Chấp nhận điều khoản</h2>
                    <p>Bằng việc sử dụng dịch vụ GameStore, bạn đồng ý tuân thủ các điều khoản và điều kiện được nêu trong tài liệu này. Nếu bạn không đồng ý với bất kỳ điều khoản nào, vui lòng không sử dụng dịch vụ.</p>
                </section>

                <section class="terms-section">
                    <h2>2. Mô tả dịch vụ</h2>
                    <p>GameStore là nền tảng giao dịch vật phẩm game trực tuyến, cho phép người dùng mua bán skin, tài khoản và các vật phẩm game khác một cách an toàn và tiện lợi.</p>
                </section>

                <section class="terms-section">
                    <h2>3. Đăng ký tài khoản</h2>
                    <p>Để sử dụng dịch vụ, bạn cần tạo tài khoản với thông tin chính xác và đầy đủ. Bạn có trách nhiệm bảo mật thông tin đăng nhập và tất cả hoạt động diễn ra dưới tài khoản của bạn.</p>
                </section>

                <section class="terms-section">
                    <h2>4. Quy tắc sử dụng</h2>
                    <p>Bạn cam kết không:</p>
                    <ul>
                        <li>Sử dụng dịch vụ cho mục đích bất hợp pháp</li>
                        <li>Đăng tải nội dung vi phạm bản quyền</li>
                        <li>Gian lận hoặc lừa đảo trong giao dịch</li>
                        <li>Spam hoặc gửi thông tin không mong muốn</li>
                        <li>Xâm phạm quyền riêng tư của người khác</li>
                    </ul>
                </section>

                <section class="terms-section">
                    <h2>5. Giao dịch và thanh toán</h2>
                    <p>Tất cả giao dịch được thực hiện dưới trách nhiệm của các bên tham gia. GameStore chỉ đóng vai trò là nền tảng kết nối và không chịu trách nhiệm về chất lượng sản phẩm.</p>
                </section>

                <section class="terms-section">
                    <h2>6. Bảo mật thông tin</h2>
                    <p>Chúng tôi cam kết bảo vệ thông tin cá nhân của bạn theo chính sách bảo mật. Tuy nhiên, không có hệ thống nào là hoàn toàn an toàn 100%.</p>
                </section>

                <section class="terms-section">
                    <h2>7. Chấm dứt dịch vụ</h2>
                    <p>GameStore có quyền tạm dừng hoặc chấm dứt tài khoản của bạn nếu vi phạm các điều khoản sử dụng mà không cần thông báo trước.</p>
                </section>

                <section class="terms-section">
                    <h2>8. Thay đổi điều khoản</h2>
                    <p>Chúng tôi có quyền thay đổi các điều khoản này bất cứ lúc nào. Việc tiếp tục sử dụng dịch vụ sau khi có thay đổi được coi là bạn đồng ý với các điều khoản mới.</p>
                </section>

                <section class="terms-section">
                    <h2>9. Liên hệ</h2>
                    <p>Nếu bạn có câu hỏi về các điều khoản này, vui lòng liên hệ:</p>
                    <ul>
                        <li>Email: legal@gamestore.vn</li>
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
