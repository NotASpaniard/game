<?php
require_once '../config/session.php';
require_once '../config/database.php';
require_once '../config/image-helper.php';

$product = null;
$related_products = [];
$error = '';

$product_id = intval($_GET['id'] ?? 0);

if ($product_id <= 0) {
    $error = 'Sản phẩm không tồn tại';
} else {
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        // Lấy thông tin sản phẩm
        $stmt = $conn->prepare("
            SELECT p.*, g.name as game_name, u.username as seller_name, u.full_name as seller_full_name
            FROM products p
            LEFT JOIN games g ON p.game_id = g.id
            LEFT JOIN users u ON p.seller_id = u.id
            WHERE p.id = ? AND p.status = 'active'
        ");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$product) {
            $error = 'Sản phẩm không tồn tại hoặc đã bị xóa';
        } else {
            // Lấy sản phẩm liên quan
            $stmt = $conn->prepare("
                SELECT p.*, g.name as game_name, u.username as seller_name
                FROM products p
                LEFT JOIN games g ON p.game_id = g.id
                LEFT JOIN users u ON p.seller_id = u.id
                WHERE p.game_id = ? AND p.id != ? AND p.status = 'active'
                ORDER BY p.created_at DESC
                LIMIT 4
            ");
            $stmt->execute([$product['game_id'], $product_id]);
            $related_products = $stmt->fetchAll();
        }
        
    } catch (Exception $e) {
        $error = 'Lỗi khi tải sản phẩm: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product ? htmlspecialchars($product['name']) : 'Sản phẩm không tồn tại'; ?> - GameStore</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/product-detail.css">
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
                    <a href="index.php" class="nav-link">Sản phẩm</a>
                    <a href="../danh-muc/" class="nav-link">Danh mục</a>
                    <a href="../huong-dan.php" class="nav-link">Hướng dẫn</a>
                    <a href="../lien-he.php" class="nav-link">Liên hệ</a>
                </nav>
                
                <div class="header-actions">
                    <div class="search-box">
                        <form action="index.php" method="GET">
                            <input type="text" name="search" placeholder="Tìm kiếm vật phẩm..." class="search-input">
                            <button type="submit" class="search-btn">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>
                    
                    <div class="user-actions">
                        <?php if (isLoggedIn()): ?>
                            <a href="../user/gio-hang.php" class="cart-btn">
                                <i class="fas fa-shopping-cart"></i>
                                <span class="cart-count" id="cart-count">0</span>
                            </a>
                            <div class="user-menu">
                                <button class="user-btn">
                                    <i class="fas fa-user"></i>
                                    <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>
                                </button>
                                <div class="user-dropdown">
                                    <a href="../user/tai-khoan.php">Tài khoản</a>
                                    <a href="../user/don-hang.php">Đơn hàng</a>
                                    <a href="../user/yeu-thich.php">Yêu thích</a>
                                    <a href="../auth/dang-xuat.php">Đăng xuất</a>
                                </div>
                            </div>
                        <?php else: ?>
                            <a href="../auth/dang-nhap.php" class="btn btn-outline">Đăng nhập</a>
                            <a href="../auth/dang-ky.php" class="btn btn-primary">Đăng ký</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main">
        <div class="container">
            <?php if ($error): ?>
                <div class="error-page">
                    <i class="fas fa-exclamation-triangle fa-3x"></i>
                    <h2><?php echo htmlspecialchars($error); ?></h2>
                    <a href="index.php" class="btn btn-primary">Quay lại danh sách</a>
                </div>
            <?php else: ?>
                <!-- Breadcrumb -->
                <nav class="breadcrumb">
                    <a href="../index.php">Trang chủ</a>
                    <i class="fas fa-chevron-right"></i>
                    <a href="index.php">Sản phẩm</a>
                    <i class="fas fa-chevron-right"></i>
                    <span><?php echo htmlspecialchars($product['name']); ?></span>
                </nav>

                <!-- Product Detail -->
                <div class="product-detail">
                    <div class="product-images">
                        <div class="main-image">
                            <img src="<?php echo getProductImage($product['id']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                 id="main-image">
                        </div>
                        <div class="thumbnail-images">
                            <img src="<?php echo getProductImage($product['id']); ?>" 
                                 alt="Thumbnail" class="thumbnail active">
                        </div>
                    </div>

                    <div class="product-info">
                        <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                        
                        <div class="product-meta">
                            <div class="meta-item">
                                <i class="fas fa-gamepad"></i>
                                <span>Game: <?php echo htmlspecialchars($product['game_name']); ?></span>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-user"></i>
                                <span>Người bán: <?php echo htmlspecialchars($product['seller_name']); ?></span>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-calendar"></i>
                                <span>Đăng ngày: <?php echo date('d/m/Y', strtotime($product['created_at'])); ?></span>
                            </div>
                        </div>

                        <div class="product-price">
                            <span class="price-current"><?php echo number_format($product['price']); ?>đ</span>
                        </div>

                        <div class="product-description">
                            <h3>Mô tả sản phẩm</h3>
                            <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                        </div>

                        <div class="product-details">
                            <h3>Chi tiết</h3>
                            <div class="detail-grid">
                                <div class="detail-item">
                                    <strong>Tình trạng:</strong>
                                    <span><?php echo ucfirst($product['condition']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <strong>Phương thức giao hàng:</strong>
                                    <span><?php echo ucfirst($product['delivery_method']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <strong>Trạng thái:</strong>
                                    <span class="status status-<?php echo $product['status']; ?>">
                                        <?php echo ucfirst($product['status']); ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="product-actions">
                            <?php if (isLoggedIn()): ?>
                                <button class="btn btn-primary btn-lg add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>">
                                    <i class="fas fa-shopping-cart"></i> Thêm vào giỏ hàng
                                </button>
                                <button class="btn btn-outline btn-lg wishlist-btn" data-product-id="<?php echo $product['id']; ?>">
                                    <i class="fas fa-heart"></i> Yêu thích
                                </button>
                            <?php else: ?>
                                <a href="../auth/dang-nhap.php" class="btn btn-primary btn-lg">
                                    <i class="fas fa-sign-in-alt"></i> Đăng nhập để mua
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Related Products -->
                <?php if (!empty($related_products)): ?>
                <section class="related-products">
                    <h2>Sản phẩm liên quan</h2>
                    <div class="products-grid">
                        <?php foreach ($related_products as $related): ?>
                            <div class="product-card">
                                <div class="product-image">
                                    <img src="<?php echo getProductImage($related['id']); ?>" 
                                         alt="<?php echo htmlspecialchars($related['name']); ?>"
                                         loading="lazy">
                                    <div class="product-actions">
                                        <button class="action-btn wishlist-btn" data-product-id="<?php echo $related['id']; ?>">
                                            <i class="fas fa-heart"></i>
                                        </button>
                                        <button class="action-btn quick-view-btn" data-product-id="<?php echo $related['id']; ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="product-info">
                                    <h3 class="product-title">
                                        <a href="chi-tiet.php?id=<?php echo $related['id']; ?>">
                                            <?php echo htmlspecialchars($related['name']); ?>
                                        </a>
                                    </h3>
                                    <p class="product-game"><?php echo htmlspecialchars($related['game_name']); ?></p>
                                    <div class="product-price">
                                        <span class="price-current"><?php echo number_format($related['price']); ?>đ</span>
                                    </div>
                                    <button class="btn btn-primary add-to-cart-btn" data-product-id="<?php echo $related['id']; ?>">
                                        <i class="fas fa-shopping-cart"></i> Thêm vào giỏ
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
                <?php endif; ?>
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
    <script>
        // Image gallery
        document.querySelectorAll('.thumbnail').forEach(thumb => {
            thumb.addEventListener('click', function() {
                const mainImage = document.getElementById('main-image');
                mainImage.src = this.src;
                
                document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Add to cart
        document.querySelectorAll('.add-to-cart-btn').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.dataset.productId;
                CartManager.addToCart(productId);
            });
        });

        // Wishlist
        document.querySelectorAll('.wishlist-btn').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.dataset.productId;
                // Implement wishlist functionality
                console.log('Add to wishlist:', productId);
            });
        });
    </script>
</body>
</html>
