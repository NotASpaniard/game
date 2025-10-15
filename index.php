<?php
require_once 'config/session.php';
require_once 'config/database.php';
require_once 'config/image-helper.php';

// Lấy sản phẩm nổi bật
$featured_products = [];
$recent_products = [];
$categories = [];

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Lấy sản phẩm nổi bật
    $stmt = $conn->query("
        SELECT p.*, b.name as brand_name, c.name as category_name
        FROM products p 
        LEFT JOIN brands b ON p.brand_id = b.id 
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.status = 'active' AND p.featured = 1
        ORDER BY p.created_at DESC 
        LIMIT 8
    ");
    $featured_products = $stmt->fetchAll();
    
    // Lấy sản phẩm mới nhất
    $stmt = $conn->query("
        SELECT p.*, b.name as brand_name, c.name as category_name
        FROM products p 
        LEFT JOIN brands b ON p.brand_id = b.id 
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.status = 'active'
        ORDER BY p.created_at DESC 
        LIMIT 12
    ");
    $recent_products = $stmt->fetchAll();
    
    // Lấy danh mục
    $stmt = $conn->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name LIMIT 6");
    $categories = $stmt->fetchAll();
    
} catch (Exception $e) {
    $featured_products = [];
    $recent_products = [];
    $categories = [];
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GameStore - Giao dịch vật phẩm game uy tín</title>
    <meta name="description" content="Nền tảng giao dịch vật phẩm game an toàn, nhanh chóng và uy tín. Mua bán skin, tài khoản, vật phẩm game với giá tốt nhất.">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                    <a href="index.php" class="nav-link active">Trang chủ</a>
                    <a href="san-pham/" class="nav-link">Sản phẩm</a>
                    <a href="danh-muc/" class="nav-link">Danh mục</a>
                    <a href="huong-dan.php" class="nav-link">Hướng dẫn</a>
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

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Giao dịch vật phẩm game an toàn</h1>
            <p>Nền tảng uy tín cho việc mua bán skin, tài khoản và vật phẩm game với giá tốt nhất</p>
            <div class="hero-actions">
                <a href="san-pham/" class="btn btn-primary btn-lg">Khám phá ngay</a>
                <a href="huong-dan.php" class="btn btn-outline btn-lg">Hướng dẫn</a>
            </div>
        </div>
        <div class="hero-stats">
            <div class="stat-item">
                <span class="stat-number">10,000+</span>
                <span class="stat-label">Giao dịch thành công</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">5,000+</span>
                <span class="stat-label">Người dùng tin tưởng</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">99.9%</span>
                <span class="stat-label">Tỷ lệ thành công</span>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="categories-section">
        <div class="container">
            <h2 class="section-title">Danh mục phổ biến</h2>
            <div class="categories-grid">
                <?php foreach ($categories as $category): ?>
                    <div class="category-card">
                        <div class="category-icon">
                            <i class="fas fa-gamepad"></i>
                        </div>
                        <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                        <p><?php echo htmlspecialchars($category['description']); ?></p>
                        <a href="san-pham/?category=<?php echo $category['id']; ?>" class="btn btn-outline">Xem sản phẩm</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="featured-products">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Sản phẩm nổi bật</h2>
                <a href="san-pham/?featured=1" class="btn btn-outline">Xem tất cả</a>
            </div>
            
            <div class="products-grid">
                <?php foreach ($featured_products as $product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <img src="<?php echo getProductImage($product['id']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                                 loading="lazy">
                            <div class="product-badge">Nổi bật</div>
                            <div class="product-actions">
                                <button class="action-btn wishlist-btn" data-product-id="<?php echo $product['id']; ?>">
                                    <i class="fas fa-heart"></i>
                                </button>
                                <button class="action-btn quick-view-btn" data-product-id="<?php echo $product['id']; ?>">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="product-info">
                            <h3 class="product-title">
                                <a href="san-pham/chi-tiet.php?id=<?php echo $product['id']; ?>">
                                    <?php echo htmlspecialchars($product['name']); ?>
                                </a>
                            </h3>
                            <p class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></p>
                            <div class="product-price">
                                <span class="price-current"><?php echo number_format($product['price']); ?>đ</span>
                                <?php if ($product['sale_price']): ?>
                                    <span class="price-sale"><?php echo number_format($product['sale_price']); ?>đ</span>
                                <?php endif; ?>
                            </div>
                            <button class="btn btn-primary add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>">
                                <i class="fas fa-shopping-cart"></i> Thêm vào giỏ
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Recent Products -->
    <section class="recent-products">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Sản phẩm mới nhất</h2>
                <a href="san-pham/" class="btn btn-outline">Xem tất cả</a>
            </div>
            
            <div class="products-grid">
                <?php foreach ($recent_products as $product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <img src="<?php echo getProductImage($product['id']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                                 loading="lazy">
                            <div class="product-actions">
                                <button class="action-btn wishlist-btn" data-product-id="<?php echo $product['id']; ?>">
                                    <i class="fas fa-heart"></i>
                                </button>
                                <button class="action-btn quick-view-btn" data-product-id="<?php echo $product['id']; ?>">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="product-info">
                            <h3 class="product-title">
                                <a href="san-pham/chi-tiet.php?id=<?php echo $product['id']; ?>">
                                    <?php echo htmlspecialchars($product['name']); ?>
                                </a>
                            </h3>
                            <p class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></p>
                            <div class="product-price">
                                <span class="price-current"><?php echo number_format($product['price']); ?>đ</span>
                                <?php if ($product['sale_price']): ?>
                                    <span class="price-sale"><?php echo number_format($product['sale_price']); ?>đ</span>
                                <?php endif; ?>
                            </div>
                            <button class="btn btn-primary add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>">
                                <i class="fas fa-shopping-cart"></i> Thêm vào giỏ
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <h2 class="section-title">Tại sao chọn GameStore?</h2>
            <div class="features-grid">
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Bảo mật cao</h3>
                    <p>Hệ thống bảo mật đa lớp, đảm bảo giao dịch an toàn tuyệt đối</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h3>Giao dịch nhanh</h3>
                    <p>Xử lý giao dịch trong vài phút, nhận vật phẩm ngay lập tức</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3>Hỗ trợ 24/7</h3>
                    <p>Đội ngũ hỗ trợ chuyên nghiệp, sẵn sàng giải đáp mọi thắc mắc</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <h3>Uy tín hàng đầu</h3>
                    <p>Hơn 10,000 giao dịch thành công, được cộng đồng tin tưởng</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>GameStore</h3>
                    <p>Nền tảng giao dịch vật phẩm game uy tín và an toàn</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
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
                        <li><a href="hoi-dap.php">Hỏi đáp</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Liên hệ</h4>
                    <p><i class="fas fa-envelope"></i> support@gamestore.vn</p>
                    <p><i class="fas fa-phone"></i> 1900 1234</p>
                    <p><i class="fas fa-map-marker-alt"></i> Hà Nội, Việt Nam</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 GameStore. Tất cả quyền được bảo lưu.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
    <script src="assets/js/home.js"></script>
</body>
</html>
