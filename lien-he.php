<?php
require_once 'config/session.php';
require_once 'config/database.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'Vui lòng điền đầy đủ thông tin';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ';
    } else {
        // Lưu tin nhắn vào database
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("
                INSERT INTO messages (sender_id, receiver_id, subject, message, created_at) 
                VALUES (?, 1, ?, ?, NOW())
            ");
            $sender_id = isLoggedIn() ? $_SESSION['user_id'] : null;
            $stmt->execute([$sender_id, $subject, $message]);
            
            $success = 'Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi sớm nhất có thể.';
        } catch (Exception $e) {
            $error = 'Có lỗi xảy ra, vui lòng thử lại sau';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liên hệ - GameStore</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .contact-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--spacing-2xl);
            margin-bottom: var(--spacing-2xl);
        }
        .contact-info {
            background: var(--white);
            padding: var(--spacing-xl);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
        }
        .contact-form {
            background: var(--white);
            padding: var(--spacing-xl);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
        }
        .contact-item {
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
            margin-bottom: var(--spacing-lg);
            padding: var(--spacing-md);
            background: var(--bg-light);
            border-radius: var(--radius-md);
        }
        .contact-item i {
            width: 40px;
            height: 40px;
            background: var(--primary-color);
            color: var(--white);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .contact-item-content h4 {
            margin: 0 0 var(--spacing-xs) 0;
            color: var(--text-dark);
        }
        .contact-item-content p {
            margin: 0;
            color: var(--text-light);
        }
        .form-group {
            margin-bottom: var(--spacing-lg);
        }
        .form-group label {
            display: block;
            margin-bottom: var(--spacing-sm);
            font-weight: 500;
            color: var(--text-dark);
        }
        .form-control {
            width: 100%;
            padding: var(--spacing-sm) var(--spacing-md);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            font-size: 1rem;
            transition: all var(--transition-fast);
        }
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
        textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }
        @media (max-width: 768px) {
            .contact-section {
                grid-template-columns: 1fr;
            }
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
                    <a href="huong-dan.php" class="nav-link">Hướng dẫn</a>
                    <a href="lien-he.php" class="nav-link active">Liên hệ</a>
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
                <h1>Liên hệ với chúng tôi</h1>
                <p>Chúng tôi luôn sẵn sàng hỗ trợ bạn</p>
            </div>

            <div class="contact-section">
                <!-- Thông tin liên hệ -->
                <div class="contact-info">
                    <h2>Thông tin liên hệ</h2>
                    
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <div class="contact-item-content">
                            <h4>Email</h4>
                            <p>support@gamestore.vn</p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <div class="contact-item-content">
                            <h4>Hotline</h4>
                            <p>1900 1234 (Miễn phí)</p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <i class="fas fa-clock"></i>
                        <div class="contact-item-content">
                            <h4>Thời gian hỗ trợ</h4>
                            <p>24/7 - Luôn sẵn sàng</p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <div class="contact-item-content">
                            <h4>Địa chỉ</h4>
                            <p>Hà Nội, Việt Nam</p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <i class="fas fa-headset"></i>
                        <div class="contact-item-content">
                            <h4>Chat trực tuyến</h4>
                            <p>Có sẵn trên website</p>
                        </div>
                    </div>
                </div>

                <!-- Form liên hệ -->
                <div class="contact-form">
                    <h2>Gửi tin nhắn</h2>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <?php echo htmlspecialchars($success); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="form-group">
                            <label for="name">Họ và tên *</label>
                            <input type="text" id="name" name="name" class="form-control" 
                                   value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" class="form-control" 
                                   value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="subject">Chủ đề *</label>
                            <select id="subject" name="subject" class="form-control" required>
                                <option value="">Chọn chủ đề</option>
                                <option value="Hỗ trợ kỹ thuật">Hỗ trợ kỹ thuật</option>
                                <option value="Góp ý">Góp ý</option>
                                <option value="Báo lỗi">Báo lỗi</option>
                                <option value="Đăng ký người bán">Đăng ký người bán</option>
                                <option value="Khác">Khác</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Nội dung *</label>
                            <textarea id="message" name="message" class="form-control" 
                                      placeholder="Mô tả chi tiết vấn đề của bạn..." required><?php echo htmlspecialchars($message ?? ''); ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-paper-plane"></i> Gửi tin nhắn
                        </button>
                    </form>
                </div>
            </div>

            <!-- FAQ nhanh -->
            <div class="guide-section" style="background: var(--white); padding: var(--spacing-xl); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);">
                <h2><i class="fas fa-question-circle"></i> Câu hỏi thường gặp</h2>
                
                <div class="faq-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: var(--spacing-lg);">
                    <div class="faq-item">
                        <h4>Làm sao để đăng ký tài khoản?</h4>
                        <p>Click vào nút "Đăng ký" ở góc phải màn hình và điền thông tin theo hướng dẫn.</p>
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
                        <p>GameStore có hệ thống bảo mật đa lớp và hỗ trợ giao dịch an toàn.</p>
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
