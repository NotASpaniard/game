<?php
require_once '../config/session.php';
require_once '../config/database.php';
require_once '../config/image-helper.php';

requireLogin();

$wishlist_items = [];
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Lấy danh sách yêu thích
    $stmt = $conn->prepare("
        SELECT w.*, p.name, p.price, p.description, p.seller_id, u.username as seller_name, g.name as game_name
        FROM wishlist w
        LEFT JOIN products p ON w.product_id = p.id
        LEFT JOIN users u ON p.seller_id = u.id
        LEFT JOIN games g ON p.game_id = g.id
        WHERE w.user_id = ?
        ORDER BY w.created_at DESC
        LIMIT $limit OFFSET $offset
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $wishlist_items = $stmt->fetchAll();
    
    // Đếm tổng số item
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM wishlist WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $total_items = $stmt->fetch()['total'];
    
} catch (Exception $e) {
    error_log("Wishlist page error: " . $e->getMessage());
}

$total_pages = ceil($total_items / $limit);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách yêu thích - GameStore</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/products.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                <h1>Danh sách yêu thích</h1>
                <p>Sản phẩm bạn đã thêm vào danh sách yêu thích</p>
            </div>

            <?php if (!empty($wishlist_items)): ?>
                <div class="wishlist-actions" style="margin-bottom: var(--spacing-lg); display: flex; gap: var(--spacing-md); justify-content: space-between; align-items: center;">
                    <div>
                        <span>Tổng cộng: <strong><?php echo count($wishlist_items); ?></strong> sản phẩm</span>
                    </div>
                    <div>
                        <button class="btn btn-outline" id="clear-wishlist">
                            <i class="fas fa-trash"></i> Xóa tất cả
                        </button>
                        <button class="btn btn-primary" id="add-all-to-cart">
                            <i class="fas fa-shopping-cart"></i> Thêm tất cả vào giỏ
                        </button>
                    </div>
                </div>

                <div class="products-grid">
                    <?php foreach ($wishlist_items as $item): ?>
                        <div class="product-card wishlist-item" data-product-id="<?php echo $item['product_id']; ?>">
                            <div class="product-image">
                                <img src="<?php echo getProductImage($item['product_id'], 'assets/images/no-image.jpg', true); ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>"
                                     loading="lazy">
                                <div class="product-actions">
                                    <button class="action-btn wishlist-btn active" data-product-id="<?php echo $item['product_id']; ?>">
                                        <i class="fas fa-heart"></i>
                                    </button>
                                    <button class="action-btn quick-view-btn" data-product-id="<?php echo $item['product_id']; ?>">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="product-info">
                                <h3 class="product-title">
                                    <a href="../san-pham/chi-tiet.php?id=<?php echo $item['product_id']; ?>">
                                        <?php echo htmlspecialchars($item['name']); ?>
                                    </a>
                                </h3>
                                <p class="product-game"><?php echo htmlspecialchars($item['game_name']); ?></p>
                                <p class="product-seller">Người bán: <?php echo htmlspecialchars($item['seller_name']); ?></p>
                                <div class="product-price">
                                    <span class="price-current"><?php echo number_format($item['price']); ?>đ</span>
                                </div>
                                <div class="product-actions-bottom">
                                    <button class="btn btn-primary add-to-cart-btn" data-product-id="<?php echo $item['product_id']; ?>">
                                        <i class="fas fa-shopping-cart"></i> Thêm vào giỏ
                                    </button>
                                    <button class="btn btn-outline remove-from-wishlist" data-product-id="<?php echo $item['product_id']; ?>">
                                        <i class="fas fa-trash"></i> Xóa
                                    </button>
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
                    <i class="fas fa-heart"></i>
                    <h3>Danh sách yêu thích trống</h3>
                    <p>Bạn chưa thêm sản phẩm nào vào danh sách yêu thích</p>
                    <a href="../san-pham/" class="btn btn-primary">
                        <i class="fas fa-shopping-bag"></i> Khám phá sản phẩm
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
        // Remove from wishlist
        document.querySelectorAll('.remove-from-wishlist').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.dataset.productId;
                const item = this.closest('.wishlist-item');
                
                if (confirm('Bạn có chắc muốn xóa sản phẩm này khỏi danh sách yêu thích?')) {
                    // Remove from wishlist via API
                    fetch('../api/wishlist.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            action: 'remove',
                            product_id: productId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            item.remove();
                            NotificationManager.show('Đã xóa khỏi danh sách yêu thích', 'success');
                            
                            // Check if wishlist is empty
                            if (document.querySelectorAll('.wishlist-item').length === 0) {
                                location.reload();
                            }
                        } else {
                            NotificationManager.show(data.message || 'Có lỗi xảy ra', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        NotificationManager.show('Có lỗi xảy ra', 'error');
                    });
                }
            });
        });

        // Clear all wishlist
        document.getElementById('clear-wishlist')?.addEventListener('click', function() {
            if (confirm('Bạn có chắc muốn xóa tất cả sản phẩm khỏi danh sách yêu thích?')) {
                // Implement clear all functionality
                console.log('Clear all wishlist');
            }
        });

        // Add all to cart
        document.getElementById('add-all-to-cart')?.addEventListener('click', function() {
            const productIds = Array.from(document.querySelectorAll('.wishlist-item')).map(item => 
                item.dataset.productId
            );
            
            if (productIds.length === 0) {
                NotificationManager.show('Không có sản phẩm nào để thêm', 'warning');
                return;
            }
            
            // Add all products to cart
            productIds.forEach(productId => {
                CartManager.addToCart(productId);
            });
            
            NotificationManager.show(`Đã thêm ${productIds.length} sản phẩm vào giỏ hàng`, 'success');
        });
    </script>
</body>
</html>
