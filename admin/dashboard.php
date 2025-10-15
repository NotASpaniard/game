<?php
require_once '../config/session.php';
require_once '../config/database.php';

requireAdmin();

$stats = [];
$recent_orders = [];
$recent_products = [];
$recent_users = [];

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Thống kê tổng quan
    $stats['total_users'] = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $stats['total_products'] = $conn->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $stats['total_orders'] = $conn->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $stats['total_revenue'] = $conn->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status = 'completed'")->fetchColumn();
    
    // Đơn hàng gần đây
    $stmt = $conn->query("
        SELECT o.*, u.username as buyer_name, u.full_name as buyer_full_name
        FROM orders o
        LEFT JOIN users u ON o.buyer_id = u.id
        ORDER BY o.created_at DESC
        LIMIT 5
    ");
    $recent_orders = $stmt->fetchAll();
    
    // Sản phẩm gần đây
    $stmt = $conn->query("
        SELECT p.*, u.username as seller_name, g.name as game_name
        FROM products p
        LEFT JOIN users u ON p.seller_id = u.id
        LEFT JOIN games g ON p.game_id = g.id
        ORDER BY p.created_at DESC
        LIMIT 5
    ");
    $recent_products = $stmt->fetchAll();
    
    // Người dùng gần đây
    $stmt = $conn->query("
        SELECT * FROM users
        ORDER BY created_at DESC
        LIMIT 5
    ");
    $recent_users = $stmt->fetchAll();
    
} catch (Exception $e) {
    error_log("Admin dashboard error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - GameStore</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: var(--spacing-lg);
            margin-bottom: var(--spacing-xl);
        }
        .stat-card {
            background: var(--white);
            padding: var(--spacing-lg);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            border-left: 4px solid var(--primary-color);
        }
        .stat-card.revenue {
            border-left-color: var(--cta-color);
        }
        .stat-card.orders {
            border-left-color: var(--secondary-color);
        }
        .stat-card.products {
            border-left-color: var(--success-color);
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: var(--spacing-xs);
        }
        .stat-label {
            color: var(--text-light);
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .stat-change {
            font-size: 0.875rem;
            margin-top: var(--spacing-xs);
        }
        .stat-change.positive {
            color: var(--success-color);
        }
        .stat-change.negative {
            color: var(--error-color);
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
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: var(--spacing-md);
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
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: var(--spacing-sm);
        }
        .quick-action h4 {
            margin: 0 0 var(--spacing-xs) 0;
            color: var(--text-dark);
        }
        .quick-action p {
            margin: 0;
            color: var(--text-light);
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3>GameStore Admin</h3>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="products.php"><i class="fas fa-box"></i> Quản lý sản phẩm</a></li>
                    <li><a href="orders.php"><i class="fas fa-shopping-bag"></i> Quản lý đơn hàng</a></li>
                    <li><a href="users.php"><i class="fas fa-users"></i> Quản lý người dùng</a></li>
                    <li><a href="categories.php"><i class="fas fa-tags"></i> Danh mục</a></li>
                    <li><a href="games.php"><i class="fas fa-gamepad"></i> Game</a></li>
                    <li><a href="reports.php"><i class="fas fa-chart-line"></i> Báo cáo</a></li>
                    <li><a href="../auth/dang-xuat.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-content">
            <header class="admin-header">
                <div class="header-left">
                    <h2>Dashboard</h2>
                    <p>Chào mừng trở lại, Admin!</p>
                </div>
                <div class="header-right">
                    <span><?php echo date('d/m/Y H:i'); ?></span>
                </div>
            </header>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <a href="products.php?action=add" class="quick-action">
                    <i class="fas fa-plus"></i>
                    <h4>Thêm sản phẩm</h4>
                    <p>Thêm sản phẩm mới</p>
                </a>
                <a href="users.php?action=add" class="quick-action">
                    <i class="fas fa-user-plus"></i>
                    <h4>Thêm người dùng</h4>
                    <p>Tạo tài khoản mới</p>
                </a>
                <a href="orders.php" class="quick-action">
                    <i class="fas fa-shopping-cart"></i>
                    <h4>Xem đơn hàng</h4>
                    <p>Quản lý đơn hàng</p>
                </a>
                <a href="reports.php" class="quick-action">
                    <i class="fas fa-chart-bar"></i>
                    <h4>Báo cáo</h4>
                    <p>Thống kê doanh thu</p>
                </a>
            </div>

            <!-- Stats Cards -->
            <div class="dashboard-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($stats['total_users']); ?></div>
                    <div class="stat-label">Tổng người dùng</div>
                    <div class="stat-change positive">+12% so với tháng trước</div>
                </div>
                <div class="stat-card products">
                    <div class="stat-number"><?php echo number_format($stats['total_products']); ?></div>
                    <div class="stat-label">Tổng sản phẩm</div>
                    <div class="stat-change positive">+8% so với tháng trước</div>
                </div>
                <div class="stat-card orders">
                    <div class="stat-number"><?php echo number_format($stats['total_orders']); ?></div>
                    <div class="stat-label">Tổng đơn hàng</div>
                    <div class="stat-change positive">+15% so với tháng trước</div>
                </div>
                <div class="stat-card revenue">
                    <div class="stat-number"><?php echo number_format($stats['total_revenue']); ?>đ</div>
                    <div class="stat-label">Tổng doanh thu</div>
                    <div class="stat-change positive">+22% so với tháng trước</div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="recent-section">
                <div class="recent-header">
                    <h3>Đơn hàng gần đây</h3>
                    <a href="orders.php" class="btn btn-outline btn-sm">Xem tất cả</a>
                </div>
                <div class="recent-content">
                    <?php if (!empty($recent_orders)): ?>
                        <?php foreach ($recent_orders as $order): ?>
                            <div class="recent-item">
                                <div class="recent-item-info">
                                    <div class="recent-item-title">#<?php echo htmlspecialchars($order['order_number']); ?></div>
                                    <div class="recent-item-meta">
                                        <?php echo htmlspecialchars($order['buyer_full_name'] ?: $order['buyer_name']); ?> • 
                                        <?php echo number_format($order['total_amount']); ?>đ • 
                                        <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                                    </div>
                                </div>
                                <span class="recent-item-status status-<?php echo $order['status']; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Chưa có đơn hàng nào</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="recent-section">
                <div class="recent-header">
                    <h3>Sản phẩm gần đây</h3>
                    <a href="products.php" class="btn btn-outline btn-sm">Xem tất cả</a>
                </div>
                <div class="recent-content">
                    <?php if (!empty($recent_products)): ?>
                        <?php foreach ($recent_products as $product): ?>
                            <div class="recent-item">
                                <div class="recent-item-info">
                                    <div class="recent-item-title"><?php echo htmlspecialchars($product['name']); ?></div>
                                    <div class="recent-item-meta">
                                        <?php echo htmlspecialchars($product['seller_name']); ?> • 
                                        <?php echo htmlspecialchars($product['game_name']); ?> • 
                                        <?php echo number_format($product['price']); ?>đ
                                    </div>
                                </div>
                                <span class="recent-item-status status-<?php echo $product['status']; ?>">
                                    <?php echo ucfirst($product['status']); ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Chưa có sản phẩm nào</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="recent-section">
                <div class="recent-header">
                    <h3>Người dùng mới</h3>
                    <a href="users.php" class="btn btn-outline btn-sm">Xem tất cả</a>
                </div>
                <div class="recent-content">
                    <?php if (!empty($recent_users)): ?>
                        <?php foreach ($recent_users as $user): ?>
                            <div class="recent-item">
                                <div class="recent-item-info">
                                    <div class="recent-item-title"><?php echo htmlspecialchars($user['full_name']); ?></div>
                                    <div class="recent-item-meta">
                                        @<?php echo htmlspecialchars($user['username']); ?> • 
                                        <?php echo ucfirst($user['role']); ?> • 
                                        <?php echo date('d/m/Y', strtotime($user['created_at'])); ?>
                                    </div>
                                </div>
                                <span class="recent-item-status status-<?php echo $user['status']; ?>">
                                    <?php echo ucfirst($user['status']); ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Chưa có người dùng nào</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/admin.js"></script>
</body>
</html>
