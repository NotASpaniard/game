php
require_once '../config/session.php';
require_once '../config/database.php';

$categories = [];
$games = [];
$featured_products = [];

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Lấy danh mục
    $stmt = $conn->query("SELECT * FROM game_categories WHERE status = 'active' ORDER BY name");
    $categories = $stmt->fetchAll();
    
    // Lấy game
    $stmt = $conn->query("SELECT * FROM games WHERE status = 'active' ORDER BY name");
    $games = $stmt->fetchAll();
    
    // Lấy sản phẩm nổi bật
    $stmt = $conn->query("
        SELECT p.*, g.name as game_name, gc.name as category_name
        FROM products p
        LEFT JOIN games g ON p.game_id = g.id
        LEFT JOIN game_categories gc ON g.category_id = gc.id
        WHERE p.status = 'active' AND p.featured = 1
        ORDER BY p.created_at DESC
        LIMIT 8
    ");
    $featured_products = $stmt->fetchAll();
    
} catch (Exception $e) {
    error_log("Categories page error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh mục - GameStore</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: var(--spacing-lg);
            margin-bottom: var(--spacing-2xl);
        }
        .category-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: all var(--transition-normal);
            border: 1px solid var(--border-color);
        }
        .category-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }
        .category-header {
            padding: var(--spacing-xl);
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: var(--white);
            text-align: center;
        }
        .category-icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto var(--spacing-md);
        }
        .category-content {
            padding: var(--spacing-lg);
        }
        .category-stats {
            display: flex;
            justify-content: space-around;
            margin: var(--spacing-lg) 0;
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
        .games-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: var(--spacing-lg);
            margin-bottom: var(--spacing-2xl);
        }
        .game-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: var(--spacing-lg);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            text-align: center;
            transition: all var(--transition-normal);
        }
        .game-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        .game-logo {
            width: 80px;
            height: 80px;
            background: var(--bg-gray);
            border-radius: var(--radius-lg);
            margin: 0 auto var(--spacing-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: var(--primary-color);
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
                    <a href="index.php" class="nav-link active">Danh mục</a>
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
            <div class="page-header">
                <h1>Danh mục game</h1>
                <p>Khám phá các thể loại game và sản phẩm phổ biến</p>
            </div>

            <!-- Danh mục game -->
            <section class="categories-section">
                <h2 class="section-title">Thể loại game</h2>
                <div class="categories-grid">
                    <?php foreach ($categories as $category): ?>
                        <div class="category-card">
                            <div class="category-header">
                                <div class="category-icon">
                                    <i class="fas fa-gamepad"></i>
                                </div>
                                <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                                <p><?php echo htmlspecialchars($category['description']); ?></p>
                            </div>
                            <div class="category-content">
                                <div class="category-stats">
                                    <div class="stat-item">
                                        <div class="stat-number"><?php echo rand(50, 200); ?></div>
                                        <div class="stat-label">Sản phẩm</div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-number"><?php echo rand(10, 50); ?></div>
                                        <div class="stat-label">Game</div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-number"><?php echo rand(100, 500); ?></div>
                                        <div class="stat-label">Giao dịch</div>
                                    </div>
                                </div>
                                <a href="../san-pham/?category=<?php echo $category['id']; ?>" class="btn btn-primary btn-block">
                                    <i class="fas fa-eye"></i> Xem sản phẩm
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- Danh sách game -->
            <section class="games-section">
                <h2 class="section-title">Game phổ biến</h2>
                <div class="games-grid">
                    <?php foreach ($games as $game): ?>
                        <div class="game-card">
                            <div class="game-logo">
                                <i class="fas fa-gamepad"></i>
                            </div>
                            <h3><?php echo htmlspecialchars($game['name']); ?></h3>
                            <p><?php echo htmlspecialchars($game['description']); ?></p>
                            <a href="../san-pham/?game=<?php echo $game['id']; ?>" class="btn btn-outline">
                                <i class="fas fa-shopping-bag"></i> Xem sản phẩm
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- Sản phẩm nổi bật -->
            <?php if (!empty($featured_products)): ?>
            <section class="featured-products">
                <h2 class="section-title">Sản phẩm nổi bật</h2>
                <div class="products-grid">
                    <?php foreach ($featured_products as $product): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <img src="<?php echo getProductImage($product['id'], 'assets/images/no-image.jpg', true); ?>" 
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
                                    <a href="../san-pham/chi-tiet.php?id=<?php echo $product['id']; ?>">
                                        <?php echo htmlspecialchars($product['name']); ?>
                                    </a>
                                </h3>
                                <p class="product-category"><?php echo htmlspecialchars($product['game_name']); ?></p>
                                <div class="product-price">
                                    <span class="price-current"><?php echo number_format($product['price']); ?>đ</span>
                                </div>
                                <button class="btn btn-primary add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>">
                                    <i class="fas fa-shopping-cart"></i> Thêm vào giỏ
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
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
                        <li><a href="index.php">Danh mục</a></li>
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
</body>
</html>
