<?php
require_once '../config/session.php';
require_once '../config/database.php';
require_once '../config/image-helper.php';

requireLogin();

$products = [];
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Lấy sản phẩm của user
    $stmt = $conn->prepare("
        SELECT p.*, g.name as game_name, gc.name as category_name
        FROM products p
        LEFT JOIN games g ON p.game_id = g.id
        LEFT JOIN game_categories gc ON g.category_id = gc.id
        WHERE p.seller_id = ?
        ORDER BY p.created_at DESC
        LIMIT $limit OFFSET $offset
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $products = $stmt->fetchAll();
    
    // Đếm tổng số sản phẩm
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM products WHERE seller_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $total_products = $stmt->fetch()['total'];
    
} catch (Exception $e) {
    error_log("My products page error: " . $e->getMessage());
}

$total_pages = ceil($total_products / $limit);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sản phẩm của tôi - GameStore</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/products.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .product-actions {
            display: flex;
            gap: var(--spacing-sm);
            margin-top: var(--spacing-md);
        }
        .btn-sm {
            padding: var(--spacing-xs) var(--spacing-sm);
            font-size: 0.75rem;
        }
        .product-stats {
            display: flex;
            justify-content: space-around;
            margin: var(--spacing-md) 0;
            padding: var(--spacing-md);
            background: var(--bg-light);
            border-radius: var(--radius-md);
        }
        .stat-item {
            text-align: center;
        }
        .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        .stat-label {
            font-size: 0.875rem;
            color: var(--text-light);
        }
        .add-product-btn {
            margin-bottom: var(--spacing-lg);
        }
        .action-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 35px;
            height: 35px;
            border: none;
            border-radius: 50%;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            z-index: 2;
        }
        .action-btn:hover {
            background: rgba(0, 0, 0, 0.9);
            transform: scale(1.1);
        }
        .edit-btn {
            right: 50px;
        }
        .delete-btn {
            background: rgba(220, 53, 69, 0.8);
        }
        .delete-btn:hover {
            background: rgba(220, 53, 69, 1);
        }
        .product-image {
            position: relative;
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
                <h1>Sản phẩm của tôi</h1>
                <p>Quản lý sản phẩm bạn đang bán</p>
            </div>

            <!-- Add Product Button -->
            <div class="add-product-btn">
                <a href="them-san-pham.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Thêm sản phẩm mới
                </a>
            </div>

            <!-- Product Stats -->
            <div class="product-stats">
                <div class="stat-item">
                    <div class="stat-number"><?php echo count($products); ?></div>
                    <div class="stat-label">Sản phẩm</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">0</div>
                    <div class="stat-label">Đã bán</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">0</div>
                    <div class="stat-label">Đang bán</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">0đ</div>
                    <div class="stat-label">Doanh thu</div>
                </div>
            </div>

            <?php if (!empty($products)): ?>
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <img src="<?php echo getProductImage($product['id'], 'assets/images/no-image.jpg', true); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                     loading="lazy">
                                <div class="product-badge">Của tôi</div>
                                <div class="product-actions">
                                    <button class="action-btn edit-btn" data-product-id="<?php echo $product['id']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="action-btn delete-btn" data-product-id="<?php echo $product['id']; ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="product-info">
                                <h3 class="product-title">
                                    <a href="../san-pham/chi-tiet.php?id=<?php echo $product['id']; ?>">
                                        <?php echo htmlspecialchars($product['name']); ?>
                                    </a>
                                </h3>
                                <p class="product-game"><?php echo htmlspecialchars($product['game_name']); ?></p>
                                <div class="product-price">
                                    <span class="price-current"><?php echo number_format($product['price']); ?>đ</span>
                                </div>
                                <div class="product-status">
                                    <span class="status status-<?php echo $product['status']; ?>">
                                        <?php echo ucfirst($product['status']); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
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
                    <i class="fas fa-box"></i>
                    <h3>Chưa có sản phẩm nào</h3>
                    <p>Bạn chưa đăng bán sản phẩm nào. Hãy bắt đầu thêm sản phẩm đầu tiên!</p>
                    <a href="them-san-pham.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Thêm sản phẩm đầu tiên
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
        // Edit product
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.dataset.productId;
                window.location.href = `sua-san-pham.php?id=${productId}`;
            });
        });

        // Delete product
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.dataset.productId;
                
                if (confirm('Bạn có chắc muốn xóa sản phẩm này? Hành động này không thể hoàn tác.')) {
                    // Show loading state
                    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                    this.disabled = true;
                    
                    fetch('../api/products.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            action: 'delete',
                            product_id: productId
                        })
                    })
                    .then(response => {
                        console.log('Response status:', response.status);
                        return response.text(); // Get raw response first
                    })
                    .then(text => {
                        console.log('Raw response:', text);
                        try {
                            const data = JSON.parse(text);
                            if (data.success) {
                                this.closest('.product-card').remove();
                                // Show success message
                                alert('Đã xóa sản phẩm thành công!');
                                // Reload page to update stats
                                location.reload();
                            } else {
                                alert(data.message || 'Có lỗi xảy ra khi xóa sản phẩm');
                            }
                        } catch (e) {
                            console.error('JSON parse error:', e);
                            console.error('Response text:', text);
                            alert('Có lỗi xảy ra khi xử lý phản hồi từ server');
                        }
                    })
                    .catch(error => {
                        console.error('Fetch error:', error);
                        alert('Có lỗi xảy ra khi kết nối đến server');
                    });
                }
            });
        });
    </script>
</body>
</html>
