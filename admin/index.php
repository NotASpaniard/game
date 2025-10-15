<?php
require_once '../config/session.php';
require_once '../config/database.php';

requireAdmin();

// Lấy thống kê tổng quan
$stats = [];
$recent_orders = [];
$recent_products = [];
$recent_users = [];

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Thống kê tổng quan
    $queries = [
        'total_users' => "SELECT COUNT(*) as count FROM users WHERE status = 'active'",
        'total_products' => "SELECT COUNT(*) as count FROM products WHERE status = 'active'",
        'total_orders' => "SELECT COUNT(*) as count FROM orders",
        'total_revenue' => "SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE status = 'completed'",
        'pending_orders' => "SELECT COUNT(*) as count FROM orders WHERE status = 'pending'",
        'new_users_today' => "SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = CURDATE()",
        'new_products_today' => "SELECT COUNT(*) as count FROM products WHERE DATE(created_at) = CURDATE()",
        'orders_today' => "SELECT COUNT(*) as count FROM orders WHERE DATE(created_at) = CURDATE()"
    ];
    
    foreach ($queries as $key => $query) {
        $stmt = $conn->query($query);
        $result = $stmt->fetch();
        $stats[$key] = $result['count'] ?? $result['total'] ?? 0;
    }
    
    // Đơn hàng gần đây
    $stmt = $conn->query("
        SELECT o.*, u.username as buyer_name, s.username as seller_name
        FROM orders o
        LEFT JOIN users u ON o.buyer_id = u.id
        LEFT JOIN users s ON o.seller_id = s.id
        ORDER BY o.created_at DESC
        LIMIT 10
    ");
    $recent_orders = $stmt->fetchAll();
    
    // Sản phẩm gần đây
    $stmt = $conn->query("
        SELECT p.*, u.username as seller_name, g.name as game_name
        FROM products p
        LEFT JOIN users u ON p.seller_id = u.id
        LEFT JOIN games g ON p.game_id = g.id
        ORDER BY p.created_at DESC
        LIMIT 10
    ");
    $recent_products = $stmt->fetchAll();
    
    // Người dùng gần đây
    $stmt = $conn->query("
        SELECT * FROM users
        ORDER BY created_at DESC
        LIMIT 10
    ");
    $recent_users = $stmt->fetchAll();
    
} catch (Exception $e) {
    error_log("Admin dashboard error: " . $e->getMessage());
    $stats = array_fill_keys(array_keys($queries), 0);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang quản trị - GameStore</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="admin-body">
    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <i class="fas fa-gamepad"></i>
                <span>GameStore Admin</span>
            </div>
        </div>
        
        <nav class="sidebar-nav">
            <a href="index.php" class="nav-item active">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            <a href="products.php" class="nav-item">
                <i class="fas fa-box"></i>
                <span>Sản phẩm</span>
            </a>
            <a href="orders.php" class="nav-item">
                <i class="fas fa-shopping-cart"></i>
                <span>Đơn hàng</span>
            </a>
            <a href="users.php" class="nav-item">
                <i class="fas fa-users"></i>
                <span>Người dùng</span>
            </a>
            <a href="categories.php" class="nav-item">
                <i class="fas fa-tags"></i>
                <span>Danh mục</span>
            </a>
            <a href="games.php" class="nav-item">
                <i class="fas fa-gamepad"></i>
                <span>Game</span>
            </a>
            <a href="reports.php" class="nav-item">
                <i class="fas fa-chart-bar"></i>
                <span>Báo cáo</span>
            </a>
            <a href="settings.php" class="nav-item">
                <i class="fas fa-cog"></i>
                <span>Cài đặt</span>
            </a>
        </nav>
        
        <div class="sidebar-footer">
            <a href="../index.php" class="nav-item">
                <i class="fas fa-home"></i>
                <span>Về trang chủ</span>
            </a>
            <a href="../auth/dang-xuat.php" class="nav-item">
                <i class="fas fa-sign-out-alt"></i>
                <span>Đăng xuất</span>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="admin-main">
        <!-- Header -->
        <header class="admin-header">
            <div class="header-left">
                <button class="sidebar-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>Dashboard</h1>
            </div>
            
            <div class="header-right">
                <div class="user-info">
                    <span>Xin chào, <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                </div>
            </div>
        </header>

        <!-- Content -->
        <div class="admin-content">
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['total_users']); ?></h3>
                        <p>Tổng người dùng</p>
                        <span class="stat-change positive">+<?php echo $stats['new_users_today']; ?> hôm nay</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['total_products']); ?></h3>
                        <p>Tổng sản phẩm</p>
                        <span class="stat-change positive">+<?php echo $stats['new_products_today']; ?> hôm nay</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['total_orders']); ?></h3>
                        <p>Tổng đơn hàng</p>
                        <span class="stat-change positive">+<?php echo $stats['orders_today']; ?> hôm nay</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['total_revenue']); ?>đ</h3>
                        <p>Doanh thu</p>
                        <span class="stat-change positive">Đã hoàn thành</span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <h2>Thao tác nhanh</h2>
                <div class="actions-grid">
                    <a href="products.php?action=add" class="action-card">
                        <i class="fas fa-plus"></i>
                        <span>Thêm sản phẩm</span>
                    </a>
                    <a href="orders.php?status=pending" class="action-card">
                        <i class="fas fa-clock"></i>
                        <span>Đơn hàng chờ xử lý (<?php echo $stats['pending_orders']; ?>)</span>
                    </a>
                    <a href="users.php?action=add" class="action-card">
                        <i class="fas fa-user-plus"></i>
                        <span>Thêm người dùng</span>
                    </a>
                    <a href="reports.php" class="action-card">
                        <i class="fas fa-chart-line"></i>
                        <span>Xem báo cáo</span>
                    </a>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="activity-grid">
                <!-- Recent Orders -->
                <div class="activity-card">
                    <div class="card-header">
                        <h3>Đơn hàng gần đây</h3>
                        <a href="orders.php" class="btn btn-outline btn-sm">Xem tất cả</a>
                    </div>
                    <div class="card-content">
                        <?php if (!empty($recent_orders)): ?>
                            <div class="activity-list">
                                <?php foreach ($recent_orders as $order): ?>
                                    <div class="activity-item">
                                        <div class="activity-info">
                                            <h4>Đơn hàng #<?php echo $order['order_number']; ?></h4>
                                            <p><?php echo htmlspecialchars($order['buyer_name']); ?> → <?php echo htmlspecialchars($order['seller_name']); ?></p>
                                            <span class="activity-time"><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></span>
                                        </div>
                                        <div class="activity-status">
                                            <span class="status status-<?php echo $order['status']; ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                            <span class="activity-amount"><?php echo number_format($order['total_amount']); ?>đ</span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-shopping-cart"></i>
                                <p>Chưa có đơn hàng nào</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Products -->
                <div class="activity-card">
                    <div class="card-header">
                        <h3>Sản phẩm mới</h3>
                        <a href="products.php" class="btn btn-outline btn-sm">Xem tất cả</a>
                    </div>
                    <div class="card-content">
                        <?php if (!empty($recent_products)): ?>
                            <div class="activity-list">
                                <?php foreach ($recent_products as $product): ?>
                                    <div class="activity-item">
                                        <div class="activity-info">
                                            <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                                            <p><?php echo htmlspecialchars($product['seller_name']); ?> - <?php echo htmlspecialchars($product['game_name']); ?></p>
                                            <span class="activity-time"><?php echo date('d/m/Y H:i', strtotime($product['created_at'])); ?></span>
                                        </div>
                                        <div class="activity-status">
                                            <span class="status status-<?php echo $product['status']; ?>">
                                                <?php echo ucfirst($product['status']); ?>
                                            </span>
                                            <span class="activity-amount"><?php echo number_format($product['price']); ?>đ</span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-box"></i>
                                <p>Chưa có sản phẩm nào</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Users -->
                <div class="activity-card">
                    <div class="card-header">
                        <h3>Người dùng mới</h3>
                        <a href="users.php" class="btn btn-outline btn-sm">Xem tất cả</a>
                    </div>
                    <div class="card-content">
                        <?php if (!empty($recent_users)): ?>
                            <div class="activity-list">
                                <?php foreach ($recent_users as $user): ?>
                                    <div class="activity-item">
                                        <div class="activity-info">
                                            <h4><?php echo htmlspecialchars($user['full_name']); ?></h4>
                                            <p>@<?php echo htmlspecialchars($user['username']); ?></p>
                                            <span class="activity-time"><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></span>
                                        </div>
                                        <div class="activity-status">
                                            <span class="status status-<?php echo $user['status']; ?>">
                                                <?php echo ucfirst($user['status']); ?>
                                            </span>
                                            <span class="role role-<?php echo $user['role']; ?>">
                                                <?php echo ucfirst($user['role']); ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-users"></i>
                                <p>Chưa có người dùng nào</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/admin.js"></script>
</body>
</html>
