<?php
require_once '../config/session.php';
require_once '../config/database.php';

requireLogin();

$user = getCurrentUser();
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    
    if (empty($full_name) || empty($email)) {
        $error = 'Họ tên và email là bắt buộc';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ';
    } else {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("
                UPDATE users 
                SET full_name = ?, email = ?, phone = ?, address = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$full_name, $email, $phone, $address, $user['id']]);
            
            $success = 'Cập nhật thông tin thành công';
            $user = getCurrentUser(); // Refresh user data
        } catch (Exception $e) {
            $error = 'Có lỗi xảy ra, vui lòng thử lại';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tài khoản - GameStore</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .account-layout {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: var(--spacing-xl);
            margin-top: var(--spacing-xl);
        }
        .account-sidebar {
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            padding: var(--spacing-lg);
            height: fit-content;
        }
        .account-content {
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            padding: var(--spacing-xl);
        }
        .account-nav {
            list-style: none;
        }
        .account-nav li {
            margin-bottom: var(--spacing-sm);
        }
        .account-nav a {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            padding: var(--spacing-sm) var(--spacing-md);
            color: var(--text-dark);
            text-decoration: none;
            border-radius: var(--radius-md);
            transition: all var(--transition-fast);
        }
        .account-nav a:hover,
        .account-nav a.active {
            background: var(--primary-color);
            color: var(--white);
        }
        .form-section {
            margin-bottom: var(--spacing-xl);
        }
        .form-section h3 {
            margin-bottom: var(--spacing-lg);
            color: var(--text-dark);
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--spacing-lg);
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
        .user-info {
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
            margin-bottom: var(--spacing-lg);
            padding: var(--spacing-md);
            background: var(--bg-light);
            border-radius: var(--radius-md);
        }
        .user-avatar {
            width: 60px;
            height: 60px;
            background: var(--primary-color);
            color: var(--white);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        .user-details h4 {
            margin: 0 0 var(--spacing-xs) 0;
            color: var(--text-dark);
        }
        .user-details p {
            margin: 0;
            color: var(--text-light);
        }
        @media (max-width: 768px) {
            .account-layout {
                grid-template-columns: 1fr;
            }
            .form-row {
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
                    <a href="../index.php">
                        <i class="fas fa-gamepad"></i>
                        <span>GameStore</span>
                    </a>
                </div>
                
                <nav class="nav">
                    <a href="../index.php" class="nav-link">Trang chủ</a>
                    <a href="../san-pham/" class="nav-link">Sản phẩm</a>
                    <a href="../danh-muc/" class="nav-link">Danh mục</a>
                    <a href="../huong-dan.php" class="nav-link">Hướng dẫn</a>
                    <a href="../lien-he.php" class="nav-link">Liên hệ</a>
                </nav>
                
                <div class="header-actions">
                    <div class="search-box">
                        <form action="../san-pham/" method="GET">
                            <input type="text" name="search" placeholder="Tìm kiếm vật phẩm..." class="search-input">
                            <button type="submit" class="search-btn">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>
                    
                    <div class="user-actions">
                        <a href="gio-hang.php" class="cart-btn">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="cart-count" id="cart-count">0</span>
                        </a>
                        <div class="user-menu">
                            <button class="user-btn">
                                <i class="fas fa-user"></i>
                                <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>
                            </button>
                            <div class="user-dropdown">
                                <a href="tai-khoan.php">Tài khoản</a>
                                <a href="don-hang.php">Đơn hàng</a>
                                <a href="yeu-thich.php">Yêu thích</a>
                                <a href="../auth/dang-xuat.php">Đăng xuất</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main">
        <div class="container">
            <div class="account-layout">
                <!-- Sidebar -->
                <aside class="account-sidebar">
                    <div class="user-info">
                        <div class="user-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="user-details">
                            <h4><?php echo htmlspecialchars($user['full_name']); ?></h4>
                            <p>@<?php echo htmlspecialchars($user['username']); ?></p>
                        </div>
                    </div>
                    
                    <nav class="account-nav">
                        <li><a href="tai-khoan.php" class="active"><i class="fas fa-user"></i> Thông tin cá nhân</a></li>
                        <li><a href="don-hang.php"><i class="fas fa-shopping-cart"></i> Đơn hàng</a></li>
                        <li><a href="yeu-thich.php"><i class="fas fa-heart"></i> Yêu thích</a></li>
                        <li><a href="doi-mat-khau.php"><i class="fas fa-lock"></i> Đổi mật khẩu</a></li>
                        <li><a href="san-pham-cua-toi.php"><i class="fas fa-box"></i> Sản phẩm của tôi</a></li>
                        <li><a href="../auth/dang-xuat.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a></li>
                    </nav>
                </aside>

                <!-- Content -->
                <div class="account-content">
                    <h1>Thông tin tài khoản</h1>
                    
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
                        <div class="form-section">
                            <h3>Thông tin cá nhân</h3>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="username">Tên đăng nhập</label>
                                    <input type="text" id="username" class="form-control" 
                                           value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                                    <small class="text-muted">Không thể thay đổi tên đăng nhập</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="role">Vai trò</label>
                                    <input type="text" id="role" class="form-control" 
                                           value="<?php echo ucfirst($user['role']); ?>" disabled>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="full_name">Họ và tên *</label>
                                <input type="text" id="full_name" name="full_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email *</label>
                                <input type="email" id="email" name="email" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Số điện thoại</label>
                                <input type="tel" id="phone" name="phone" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['phone']); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="address">Địa chỉ</label>
                                <textarea id="address" name="address" class="form-control" rows="3"><?php echo htmlspecialchars($user['address']); ?></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Cập nhật thông tin
                            </button>
                        </div>
                    </form>
                    
                    <div class="form-section">
                        <h3>Thống kê tài khoản</h3>
                        <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--spacing-lg);">
                            <div class="stat-card" style="background: var(--bg-light); padding: var(--spacing-lg); border-radius: var(--radius-md); text-align: center;">
                                <div class="stat-number" style="font-size: 2rem; font-weight: 700; color: var(--primary-color);">0</div>
                                <div class="stat-label" style="color: var(--text-light);">Đơn hàng</div>
                            </div>
                            <div class="stat-card" style="background: var(--bg-light); padding: var(--spacing-lg); border-radius: var(--radius-md); text-align: center;">
                                <div class="stat-number" style="font-size: 2rem; font-weight: 700; color: var(--primary-color);">0</div>
                                <div class="stat-label" style="color: var(--text-light);">Sản phẩm yêu thích</div>
                            </div>
                            <div class="stat-card" style="background: var(--bg-light); padding: var(--spacing-lg); border-radius: var(--radius-md); text-align: center;">
                                <div class="stat-number" style="font-size: 2rem; font-weight: 700; color: var(--primary-color);">0</div>
                                <div class="stat-label" style="color: var(--text-light);">Sản phẩm đã bán</div>
                            </div>
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
                        <li><a href="../san-pham/">Sản phẩm</a></li>
                        <li><a href="../danh-muc/">Danh mục</a></li>
                        <li><a href="../huong-dan.php">Hướng dẫn</a></li>
                        <li><a href="../lien-he.php">Liên hệ</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Hỗ trợ</h4>
                    <ul>
                        <li><a href="../tro-giup.php">Trợ giúp</a></li>
                        <li><a href="../dieu-khoan.php">Điều khoản</a></li>
                        <li><a href="../bao-mat.php">Bảo mật</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 GameStore. Tất cả quyền được bảo lưu.</p>
            </div>
        </div>
    </footer>

    <script src="../assets/js/main.js"></script>
</body>
</html>
