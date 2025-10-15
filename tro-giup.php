<?php
require_once 'config/session.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trợ giúp - GameStore</title>
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
                <h1>Trung tâm trợ giúp</h1>
                <p>Tìm câu trả lời cho các câu hỏi thường gặp</p>
            </div>

            <div class="help-content">
                <div class="help-search">
                    <h2>Tìm kiếm trợ giúp</h2>
                    <div class="search-box-large">
                        <input type="text" placeholder="Nhập câu hỏi của bạn..." class="search-input-large">
                        <button class="btn btn-primary">
                            <i class="fas fa-search"></i> Tìm kiếm
                        </button>
                    </div>
                </div>

                <div class="help-categories">
                    <h2>Danh mục trợ giúp</h2>
                    <div class="categories-grid">
                        <div class="help-category">
                            <i class="fas fa-user-plus"></i>
                            <h3>Tài khoản</h3>
                            <p>Đăng ký, đăng nhập, quản lý tài khoản</p>
                        </div>
                        <div class="help-category">
                            <i class="fas fa-shopping-cart"></i>
                            <h3>Mua sắm</h3>
                            <p>Hướng dẫn mua hàng, thanh toán</p>
                        </div>
                        <div class="help-category">
                            <i class="fas fa-store"></i>
                            <h3>Bán hàng</h3>
                            <p>Đăng sản phẩm, quản lý cửa hàng</p>
                        </div>
                        <div class="help-category">
                            <i class="fas fa-shield-alt"></i>
                            <h3>Bảo mật</h3>
                            <p>Bảo vệ tài khoản, giao dịch an toàn</p>
                        </div>
                    </div>
                </div>

                <div class="faq-section">
                    <h2>Câu hỏi thường gặp</h2>
                    <div class="faq-list">
                        <div class="faq-item">
                            <h4>Làm sao để đăng ký tài khoản?</h4>
                            <p>Click vào nút "Đăng ký" ở góc phải màn hình, điền thông tin theo hướng dẫn và xác nhận email.</p>
                        </div>
                        <div class="faq-item">
                            <h4>Làm sao để mua hàng?</h4>
                            <p>Tìm sản phẩm, thêm vào giỏ hàng, thanh toán và chờ người bán liên hệ giao hàng.</p>
                        </div>
                        <div class="faq-item">
                            <h4>Làm sao để bán sản phẩm?</h4>
                            <p>Liên hệ admin để nâng cấp tài khoản thành người bán, sau đó có thể đăng sản phẩm.</p>
                        </div>
                        <div class="faq-item">
                            <h4>Phí giao dịch là bao nhiêu?</h4>
                            <p>Người mua không mất phí. Người bán chỉ trả phí hoa hồng nhỏ khi giao dịch thành công.</p>
                        </div>
                        <div class="faq-item">
                            <h4>Làm sao để đảm bảo an toàn?</h4>
                            <p>GameStore có hệ thống bảo mật đa lớp, xác thực người dùng và hỗ trợ giao dịch an toàn.</p>
                        </div>
                    </div>
                </div>

                <div class="contact-support">
                    <h2>Liên hệ hỗ trợ</h2>
                    <p>Không tìm thấy câu trả lời? Liên hệ với chúng tôi:</p>
                    <div class="contact-methods">
                        <div class="contact-method">
                            <i class="fas fa-envelope"></i>
                            <h4>Email</h4>
                            <p>support@gamestore.vn</p>
                        </div>
                        <div class="contact-method">
                            <i class="fas fa-phone"></i>
                            <h4>Hotline</h4>
                            <p>1900 1234</p>
                        </div>
                        <div class="contact-method">
                            <i class="fas fa-comments"></i>
                            <h4>Chat trực tuyến</h4>
                            <p>Có sẵn 24/7</p>
                        </div>
                    </div>
                </div>
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
