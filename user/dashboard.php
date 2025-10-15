<?php
require_once '../config/session.php';
require_once '../config/database.php';

requireLogin();

$user = getCurrentUser();
$stats = [];
$recent_products = [];
$recent_orders = [];

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Thống kê của user
    $stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE seller_id = ?");
    $stmt->execute([$user['id']]);
    $stats['total_products'] = $stmt->fetchColumn();
    
    $stmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE buyer_id = ?");
    $stmt->execute([$user['id']]);
    $stats['total_orders'] = $stmt->fetchColumn();
    
    $stmt = $conn->prepare("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE buyer_id = ? AND status = 'completed'");
    $stmt->execute([$user['id']]);
    $stats['total_spent'] = $stmt->fetchColumn();
    
    $stmt = $conn->prepare("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE seller_id = ? AND status = 'completed'");
    $stmt->execute([$user['id']]);
    $stats['total_earned'] = $stmt->fetchColumn();
    
    // Sản phẩm gần đây của user
    $stmt = $conn->prepare("
        SELECT p.*, g.name as game_name
        FROM products p
        LEFT JOIN games g ON p.game_id = g.id
        WHERE p.seller_id = ?
        ORDER BY p.created_at DESC
        LIMIT 5
    ");
    $stmt->execute([$user['id']]);
    $recent_products = $stmt->fetchAll();
    
    // Đơn hàng gần đây của user
    $stmt = $conn->prepare("
        SELECT o.*, u.username as seller_name
        FROM orders o
        LEFT JOIN users u ON o.seller_id = u.id
        WHERE o.buyer_id = ?
        ORDER BY o.created_at DESC
        LIMIT 5
    ");
    $stmt->execute([$user['id']]);
    $recent_orders = $stmt->fetchAll();
    
} catch (Exception $e) {
    error_log("User dashboard error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - GameStore</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .user-dashboard {
            max-width: 1200px;
            margin: 0 auto;
            padding: var(--spacing-xl);
        }
        .dashboard-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: var(--white);
            padding: var(--spacing-xl);
            border-radius: var(--radius-lg);
            margin-bottom: var(--spacing-xl);
            text-align: center;
        }
        .dashboard-header h1 {
            margin: 0 0 var(--spacing-sm) 0;
            font-size: 2rem;
        }
        .dashboard-header p {
            margin: 0;
            opacity: 0.9;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: var(--spacing-lg);
            margin-bottom: var(--spacing-xl);
        }
        .stat-card {
            background: var(--white);
            padding: var(--spacing-lg);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            text-align: center;
            border-left: 4px solid var(--primary-color);
        }
        .stat-card.products { border-left-color: var(--success-color); }
        .stat-card.orders { border-left-color: var(--secondary-color); }
        .stat-card.spent { border-left-color: var(--cta-color); }
        .stat-card.earned { border-left-color: var(--warning-color); }
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: var(--spacing-xs);
        }
        .stat-label {
            color: var(--text-light);
            font-size: 0.875rem;
        }
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: var(--spacing-lg);
            margin-bottom: var(--spacing-xl);
        }
        .quick-action {
            background: var(--white);
            padding: var(--spacing-lg);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            text-align: center;
            text-decoration: none;
            color: var(--text-dark);
            transition: all var(--transition-fast);
        }
        .quick-action:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        .quick-action i {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: var(--spacing-md);
        }
        .quick-action h3 {
            margin: 0 0 var(--spacing-sm) 0;
            color: var(--text-dark);
        }
        .quick-action p {
            margin: 0;
            color: var(--text-light);
            font-size: 0.875rem;
        }
        .recent-section {
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            margin-bottom: var(--spacing-xl);
        }
        .recent-header {
            padding: var(--spacing-lg);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .recent-header h3 {
            margin: 0;
            color: var(--text-dark);
        }
        .recent-content {
            padding: var(--spacing-lg);
        }
        .recent-item {
            display: flex;
            align-items: center;
            padding: var(--spacing-md) 0;
            border-bottom: 1px solid var(--border-color);
        }
        .recent-item:last-child {
            border-bottom: none;
        }
        .recent-item-info {
            flex-grow: 1;
        }
        .recent-item-title {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: var(--spacing-xs);
        }
        .recent-item-meta {
            font-size: 0.875rem;
            color: var(--text-light);
        }
        .recent-item-status {
            padding: 4px 8px;
            border-radius: var(--radius-sm);
            font-size: 0.75rem;
            font-weight: 500;
        }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-completed { background: #d1fae5; color: #065f46; }
        .status-active { background: #dbeafe; color: #1e40af; }
        .status-inactive { background: #fee2e2; color: #991b1b; }
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
                    <a href="../san-pham/loai-san-pham.php" class="nav-link">Loại sản phẩm</a>
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
                                <a href="dashboard.php">Dashboard</a>
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
        <div class="user-dashboard">
            <!-- Dashboard Header -->
            <div class="dashboard-header">
                <h1>Chào mừng trở lại, <?php echo htmlspecialchars($user['full_name']); ?>!</h1>
                <p>Quản lý tài khoản và sản phẩm của bạn</p>
            </div>

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card products">
                    <div class="stat-number"><?php echo number_format($stats['total_products']); ?></div>
                    <div class="stat-label">Sản phẩm đã đăng</div>
                </div>
                <div class="stat-card orders">
                    <div class="stat-number"><?php echo number_format($stats['total_orders']); ?></div>
                    <div class="stat-label">Đơn hàng đã mua</div>
                </div>
                <div class="stat-card spent">
                    <div class="stat-number"><?php echo number_format($stats['total_spent']); ?>đ</div>
                    <div class="stat-label">Đã chi tiêu</div>
                </div>
                <div class="stat-card earned">
                    <div class="stat-number"><?php echo number_format($stats['total_earned']); ?>đ</div>
                    <div class="stat-label">Đã kiếm được</div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <a href="dang-san-pham.php" class="quick-action">
                    <i class="fas fa-plus-circle"></i>
                    <h3>Đăng sản phẩm</h3>
                    <p>Đăng bán vật phẩm game của bạn</p>
                </a>
                <a href="san-pham-cua-toi.php" class="quick-action">
                    <i class="fas fa-box"></i>
                    <h3>Sản phẩm của tôi</h3>
                    <p>Quản lý sản phẩm đã đăng</p>
                </a>
                <a href="don-hang.php" class="quick-action">
                    <i class="fas fa-shopping-cart"></i>
                    <h3>Đơn hàng</h3>
                    <p>Xem lịch sử mua hàng</p>
                </a>
                <a href="yeu-thich.php" class="quick-action">
                    <i class="fas fa-heart"></i>
                    <h3>Yêu thích</h3>
                    <p>Sản phẩm đã lưu</p>
                </a>
                <a href="tai-khoan.php" class="quick-action">
                    <i class="fas fa-user-cog"></i>
                    <h3>Cài đặt</h3>
                    <p>Thông tin tài khoản</p>
                </a>
                <a href="../san-pham/loai-san-pham.php" class="quick-action">
                    <i class="fas fa-balance-scale"></i>
                    <h3>So sánh giá</h3>
                    <p>Tìm sản phẩm tốt nhất</p>
                </a>
            </div>

            <!-- Recent Products -->
            <?php if (!empty($recent_products)): ?>
            <div class="recent-section">
                <div class="recent-header">
                    <h3>Sản phẩm gần đây</h3>
                    <a href="san-pham-cua-toi.php" class="btn btn-outline btn-sm">Xem tất cả</a>
                </div>
                <div class="recent-content">
                    <?php foreach ($recent_products as $product): ?>
                        <div class="recent-item">
                            <div class="recent-item-info">
                                <div class="recent-item-title"><?php echo htmlspecialchars($product['name']); ?></div>
                                <div class="recent-item-meta">
                                    <?php echo htmlspecialchars($product['game_name']); ?> • 
                                    <?php echo number_format($product['price']); ?>đ • 
                                    <?php echo date('d/m/Y', strtotime($product['created_at'])); ?>
                                </div>
                            </div>
                            <span class="recent-item-status status-<?php echo $product['status']; ?>">
                                <?php echo ucfirst($product['status']); ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Recent Orders -->
            <?php if (!empty($recent_orders)): ?>
            <div class="recent-section">
                <div class="recent-header">
                    <h3>Đơn hàng gần đây</h3>
                    <a href="don-hang.php" class="btn btn-outline btn-sm">Xem tất cả</a>
                </div>
                <div class="recent-content">
                    <?php foreach ($recent_orders as $order): ?>
                        <div class="recent-item">
                            <div class="recent-item-info">
                                <div class="recent-item-title">#<?php echo htmlspecialchars($order['order_number']); ?></div>
                                <div class="recent-item-meta">
                                    <?php echo number_format($order['total_amount']); ?>đ • 
                                    <?php echo date('d/m/Y', strtotime($order['created_at'])); ?>
                                </div>
                            </div>
                            <span class="recent-item-status status-<?php echo $order['status']; ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
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
