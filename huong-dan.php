<?php
require_once 'config/session.php';
require_once 'config/database.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hướng dẫn sử dụng - GameStore</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .guide-section {
            margin-bottom: var(--spacing-2xl);
            padding: var(--spacing-xl);
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
        }
        .guide-section h2 {
            color: var(--primary-color);
            margin-bottom: var(--spacing-lg);
        }
        .step-list {
            list-style: none;
            counter-reset: step-counter;
        }
        .step-list li {
            counter-increment: step-counter;
            margin-bottom: var(--spacing-lg);
            padding: var(--spacing-lg);
            background: var(--bg-light);
            border-radius: var(--radius-md);
            border-left: 4px solid var(--primary-color);
            position: relative;
        }
        .step-list li::before {
            content: counter(step-counter);
            position: absolute;
            left: -2px;
            top: var(--spacing-md);
            background: var(--primary-color);
            color: var(--white);
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.875rem;
        }
        .faq-item {
            margin-bottom: var(--spacing-lg);
            padding: var(--spacing-lg);
            background: var(--bg-light);
            border-radius: var(--radius-md);
        }
        .faq-question {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: var(--spacing-sm);
        }
        .faq-answer {
            color: var(--text-light);
        }
    </style>
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
                    <a href="huong-dan.php" class="nav-link active">Hướng dẫn</a>
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
                <h1>Hướng dẫn sử dụng</h1>
                <p>Tìm hiểu cách sử dụng GameStore một cách hiệu quả</p>
            </div>

            <!-- Hướng dẫn mua hàng -->
            <div class="guide-section">
                <h2><i class="fas fa-shopping-cart"></i> Hướng dẫn mua hàng</h2>
                <ol class="step-list">
                    <li>
                        <h3>Tạo tài khoản</h3>
                        <p>Đăng ký tài khoản miễn phí để có thể mua sắm và giao dịch trên GameStore.</p>
                    </li>
                    <li>
                        <h3>Tìm kiếm sản phẩm</h3>
                        <p>Sử dụng thanh tìm kiếm hoặc duyệt theo danh mục để tìm vật phẩm game bạn muốn mua.</p>
                    </li>
                    <li>
                        <h3>Xem chi tiết sản phẩm</h3>
                        <p>Click vào sản phẩm để xem thông tin chi tiết, hình ảnh và mô tả.</p>
                    </li>
                    <li>
                        <h3>Thêm vào giỏ hàng</h3>
                        <p>Click "Thêm vào giỏ" để thêm sản phẩm vào giỏ hàng của bạn.</p>
                    </li>
                    <li>
                        <h3>Thanh toán</h3>
                        <p>Vào giỏ hàng, kiểm tra sản phẩm và tiến hành thanh toán.</p>
                    </li>
                    <li>
                        <h3>Nhận hàng</h3>
                        <p>Người bán sẽ liên hệ và giao hàng cho bạn sau khi xác nhận đơn hàng.</p>
                    </li>
                </ol>
            </div>

            <!-- Hướng dẫn bán hàng -->
            <div class="guide-section">
                <h2><i class="fas fa-store"></i> Hướng dẫn bán hàng</h2>
                <ol class="step-list">
                    <li>
                        <h3>Đăng ký tài khoản người bán</h3>
                        <p>Liên hệ admin để nâng cấp tài khoản thành người bán.</p>
                    </li>
                    <li>
                        <h3>Đăng sản phẩm</h3>
                        <p>Vào trang quản lý sản phẩm và đăng bán vật phẩm game của bạn.</p>
                    </li>
                    <li>
                        <h3>Upload ảnh</h3>
                        <p>Thêm ảnh chất lượng cao để thu hút người mua.</p>
                    </li>
                    <li>
                        <h3>Đặt giá hợp lý</h3>
                        <p>Nghiên cứu thị trường để đặt giá cạnh tranh.</p>
                    </li>
                    <li>
                        <h3>Quản lý đơn hàng</h3>
                        <p>Theo dõi và xử lý đơn hàng từ người mua.</p>
                    </li>
                    <li>
                        <h3>Giao hàng</h3>
                        <p>Liên hệ với người mua và giao hàng theo thỏa thuận.</p>
                    </li>
                </ol>
            </div>

            <!-- FAQ -->
            <div class="guide-section">
                <h2><i class="fas fa-question-circle"></i> Câu hỏi thường gặp</h2>
                
                <div class="faq-item">
                    <div class="faq-question">Làm thế nào để đảm bảo giao dịch an toàn?</div>
                    <div class="faq-answer">
                        GameStore có hệ thống bảo mật đa lớp, xác thực người dùng và hỗ trợ giao dịch. 
                        Luôn kiểm tra thông tin người bán và sử dụng các phương thức thanh toán an toàn.
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">Tôi có thể hoàn tiền nếu không hài lòng?</div>
                    <div class="faq-answer">
                        Có, chúng tôi có chính sách hoàn tiền 100% trong vòng 7 ngày nếu sản phẩm không đúng mô tả.
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">Phí giao dịch là bao nhiêu?</div>
                    <div class="faq-answer">
                        GameStore không thu phí từ người mua. Người bán chỉ trả phí hoa hồng nhỏ khi giao dịch thành công.
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">Làm sao để liên hệ hỗ trợ?</div>
                    <div class="faq-answer">
                        Bạn có thể liên hệ qua email support@gamestore.vn hoặc hotline 1900 1234. 
                        Đội ngũ hỗ trợ hoạt động 24/7.
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">Tôi có thể bán vật phẩm của game nào?</div>
                    <div class="faq-answer">
                        GameStore hỗ trợ nhiều game phổ biến như League of Legends, Counter-Strike, PUBG, 
                        Free Fire, Valorant và nhiều game khác.
                    </div>
                </div>
            </div>

            <!-- Liên hệ hỗ trợ -->
            <div class="guide-section">
                <h2><i class="fas fa-headset"></i> Cần hỗ trợ thêm?</h2>
                <p>Nếu bạn cần hỗ trợ thêm, vui lòng liên hệ với chúng tôi:</p>
                <div class="contact-info">
                    <p><i class="fas fa-envelope"></i> Email: support@gamestore.vn</p>
                    <p><i class="fas fa-phone"></i> Hotline: 1900 1234</p>
                    <p><i class="fas fa-clock"></i> Thời gian: 24/7</p>
                </div>
                <a href="lien-he.php" class="btn btn-primary">Liên hệ ngay</a>
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
