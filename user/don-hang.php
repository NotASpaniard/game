<?php
require_once '../config/session.php';
require_once '../config/database.php';

requireLogin();

$orders = [];
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Lấy đơn hàng của user
    $stmt = $conn->prepare("
        SELECT o.*, u.username as seller_name
        FROM orders o
        LEFT JOIN users u ON o.seller_id = u.id
        WHERE o.buyer_id = ?
        ORDER BY o.created_at DESC
        LIMIT $limit OFFSET $offset
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $orders = $stmt->fetchAll();
    
    // Đếm tổng số đơn hàng
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM orders WHERE buyer_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $total_orders = $stmt->fetch()['total'];
    
} catch (Exception $e) {
    error_log("Orders page error: " . $e->getMessage());
}

$total_pages = ceil($total_orders / $limit);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đơn hàng của tôi - GameStore</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            background: var(--white);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }
        .orders-table th,
        .orders-table td {
            padding: var(--spacing-md);
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        .orders-table th {
            background: var(--bg-gray);
            font-weight: 600;
            color: var(--text-dark);
        }
        .orders-table tr:hover {
            background: var(--bg-light);
        }
        .status {
            padding: 4px 8px;
            border-radius: var(--radius-sm);
            font-size: 0.875rem;
            font-weight: 500;
            text-transform: uppercase;
        }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-confirmed { background: #dbeafe; color: #1e40af; }
        .status-processing { background: #e0e7ff; color: #3730a3; }
        .status-delivered { background: #d1fae5; color: #065f46; }
        .status-completed { background: #d1fae5; color: #065f46; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }
        .order-actions {
            display: flex;
            gap: var(--spacing-sm);
        }
        .btn-sm {
            padding: var(--spacing-xs) var(--spacing-sm);
            font-size: 0.75rem;
        }
        .empty-state {
            text-align: center;
            padding: var(--spacing-2xl);
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
        }
        .empty-state i {
            font-size: 4rem;
            color: var(--text-muted);
            margin-bottom: var(--spacing-lg);
        }
        .filter-tabs {
            display: flex;
            gap: var(--spacing-sm);
            margin-bottom: var(--spacing-lg);
            background: var(--white);
            padding: var(--spacing-sm);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
        }
        .filter-tab {
            padding: var(--spacing-sm) var(--spacing-md);
            border: none;
            background: none;
            color: var(--text-light);
            cursor: pointer;
            border-radius: var(--radius-sm);
            transition: all var(--transition-fast);
        }
        .filter-tab.active {
            background: var(--primary-color);
            color: var(--white);
        }
        @media (max-width: 768px) {
            .orders-table {
                font-size: 0.875rem;
            }
            .orders-table th,
            .orders-table td {
                padding: var(--spacing-sm);
            }
            .order-actions {
                flex-direction: column;
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
            <div class="page-header">
                <h1>Đơn hàng của tôi</h1>
                <p>Quản lý và theo dõi đơn hàng</p>
            </div>

            <!-- Filter Tabs -->
            <div class="filter-tabs">
                <button class="filter-tab active" data-status="">Tất cả</button>
                <button class="filter-tab" data-status="pending">Chờ xử lý</button>
                <button class="filter-tab" data-status="confirmed">Đã xác nhận</button>
                <button class="filter-tab" data-status="processing">Đang xử lý</button>
                <button class="filter-tab" data-status="delivered">Đã giao</button>
                <button class="filter-tab" data-status="completed">Hoàn thành</button>
                <button class="filter-tab" data-status="cancelled">Đã hủy</button>
            </div>

            <?php if (!empty($orders)): ?>
                <div class="orders-container">
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>Mã đơn hàng</th>
                                <th>Người bán</th>
                                <th>Tổng tiền</th>
                                <th>Trạng thái</th>
                                <th>Ngày đặt</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td>
                                        <strong>#<?php echo htmlspecialchars($order['order_number']); ?></strong>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($order['seller_name']); ?>
                                    </td>
                                    <td>
                                        <strong><?php echo number_format($order['total_amount']); ?>đ</strong>
                                    </td>
                                    <td>
                                        <span class="status status-<?php echo $order['status']; ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                                    </td>
                                    <td>
                                        <div class="order-actions">
                                            <a href="chi-tiet-don-hang.php?id=<?php echo $order['id']; ?>" 
                                               class="btn btn-outline btn-sm">
                                                <i class="fas fa-eye"></i> Xem
                                            </a>
                                            <?php if ($order['status'] === 'pending'): ?>
                                                <button class="btn btn-error btn-sm cancel-order" 
                                                        data-order-id="<?php echo $order['id']; ?>">
                                                    <i class="fas fa-times"></i> Hủy
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>" class="page-btn">
                                <i class="fas fa-chevron-left"></i> Trước
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <a href="?page=<?php echo $i; ?>" 
                               class="page-btn <?php echo $i === $page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?>" class="page-btn">
                                Sau <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-shopping-cart"></i>
                    <h3>Chưa có đơn hàng nào</h3>
                    <p>Bạn chưa có đơn hàng nào. Hãy bắt đầu mua sắm ngay!</p>
                    <a href="../san-pham/" class="btn btn-primary">
                        <i class="fas fa-shopping-bag"></i> Mua sắm ngay
                    </a>
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
                <p>&copy; 2025 GameStore. Tất cả quyền được bảo lưu.</p>
            </div>
        </div>
    </footer>

    <script src="../assets/js/main.js"></script>
    <script>
        // Filter orders by status
        document.querySelectorAll('.filter-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                // Remove active class from all tabs
                document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
                // Add active class to clicked tab
                this.classList.add('active');
                
                // Filter orders (this would need AJAX implementation)
                const status = this.dataset.status;
                console.log('Filter by status:', status);
            });
        });

        // Cancel order
        document.querySelectorAll('.cancel-order').forEach(button => {
            button.addEventListener('click', function() {
                if (confirm('Bạn có chắc muốn hủy đơn hàng này?')) {
                    const orderId = this.dataset.orderId;
                    // Implement cancel order functionality
                    console.log('Cancel order:', orderId);
                }
            });
        });
    </script>
</body>
</html>
