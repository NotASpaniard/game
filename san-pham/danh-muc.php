<?php
require_once '../config/session.php';
require_once '../config/database.php';
require_once '../config/image-helper.php';

$category_id = intval($_GET['category'] ?? 0);
$product_type = trim($_GET['type'] ?? '');
$products = [];
$category_info = null;
$error = '';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    if ($category_id > 0) {
        // Lấy thông tin danh mục
        $stmt = $conn->prepare("SELECT * FROM game_categories WHERE id = ? AND status = 'active'");
        $stmt->execute([$category_id]);
        $category_info = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($category_info) {
            // Lấy sản phẩm theo danh mục
            $stmt = $conn->prepare("
                SELECT p.*, g.name as game_name, u.username as seller_name, u.full_name as seller_full_name,
                       COUNT(DISTINCT o.id) as total_orders,
                       AVG(oi.product_price) as avg_price
                FROM products p
                LEFT JOIN games g ON p.game_id = g.id
                LEFT JOIN users u ON p.seller_id = u.id
                LEFT JOIN order_items oi ON p.id = oi.product_id
                LEFT JOIN orders o ON oi.order_id = o.id AND o.status = 'completed'
                WHERE g.category_id = ? AND p.status = 'active'
                GROUP BY p.id
                ORDER BY p.price ASC, p.created_at DESC
            ");
            $stmt->execute([$category_id]);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $error = 'Danh mục không tồn tại';
        }
    } elseif (!empty($product_type)) {
        // Lấy sản phẩm theo loại sản phẩm
        $stmt = $conn->prepare("
            SELECT p.*, g.name as game_name, u.username as seller_name, u.full_name as seller_full_name,
                   COUNT(DISTINCT o.id) as total_orders,
                   AVG(oi.product_price) as avg_price
            FROM products p
            LEFT JOIN games g ON p.game_id = g.id
            LEFT JOIN users u ON p.seller_id = u.id
            LEFT JOIN order_items oi ON p.id = oi.product_id
            LEFT JOIN orders o ON oi.order_id = o.id AND o.status = 'completed'
            WHERE p.name LIKE ? AND p.status = 'active'
            GROUP BY p.id
            ORDER BY p.price ASC, p.created_at DESC
        ");
        $stmt->execute(["%$product_type%"]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $error = 'Vui lòng chọn danh mục hoặc loại sản phẩm';
    }
    
} catch (Exception $e) {
    $error = 'Lỗi khi tải dữ liệu: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $category_info ? htmlspecialchars($category_info['name']) : 'Sản phẩm'; ?> - GameStore</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/products.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .product-comparison {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: var(--spacing-lg);
            margin-bottom: var(--spacing-xl);
        }
        .seller-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            padding: var(--spacing-lg);
            border: 1px solid var(--border-color);
            transition: all var(--transition-normal);
            position: relative;
        }
        .seller-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            border-color: var(--primary-color);
        }
        .seller-header {
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
            margin-bottom: var(--spacing-md);
            padding-bottom: var(--spacing-md);
            border-bottom: 1px solid var(--border-color);
        }
        .seller-avatar {
            width: 50px;
            height: 50px;
            background: var(--primary-color);
            color: var(--white);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.2rem;
        }
        .seller-info h4 {
            margin: 0 0 var(--spacing-xs) 0;
            color: var(--text-dark);
        }
        .seller-info p {
            margin: 0;
            color: var(--text-light);
            font-size: 0.875rem;
        }
        .product-details {
            margin-bottom: var(--spacing-md);
        }
        .product-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: var(--spacing-sm);
        }
        .product-description {
            color: var(--text-light);
            font-size: 0.875rem;
            margin-bottom: var(--spacing-sm);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .product-condition {
            display: inline-block;
            padding: 2px 8px;
            background: var(--bg-light);
            color: var(--text-light);
            border-radius: var(--radius-sm);
            font-size: 0.75rem;
            margin-bottom: var(--spacing-sm);
        }
        .price-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--spacing-md);
        }
        .price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--cta-color);
        }
        .price-comparison {
            font-size: 0.875rem;
            color: var(--text-light);
        }
        .seller-stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: var(--spacing-md);
            padding: var(--spacing-sm);
            background: var(--bg-light);
            border-radius: var(--radius-sm);
        }
        .stat-item {
            text-align: center;
        }
        .stat-number {
            font-weight: 600;
            color: var(--text-dark);
        }
        .stat-label {
            font-size: 0.75rem;
            color: var(--text-light);
        }
        .product-actions {
            display: flex;
            gap: var(--spacing-sm);
        }
        .btn-contact {
            flex: 1;
            background: var(--primary-color);
            color: var(--white);
            border: none;
            padding: var(--spacing-sm) var(--spacing-md);
            border-radius: var(--radius-md);
            text-decoration: none;
            text-align: center;
            font-weight: 500;
            transition: all var(--transition-fast);
        }
        .btn-contact:hover {
            background: var(--primary-dark);
            color: var(--white);
        }
        .btn-favorite {
            background: var(--white);
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
            padding: var(--spacing-sm);
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: all var(--transition-fast);
        }
        .btn-favorite:hover {
            background: var(--primary-color);
            color: var(--white);
        }
        .best-price {
            position: absolute;
            top: var(--spacing-md);
            right: var(--spacing-md);
            background: var(--cta-color);
            color: var(--white);
            padding: 4px 8px;
            border-radius: var(--radius-sm);
            font-size: 0.75rem;
            font-weight: 600;
        }
        .no-products {
            text-align: center;
            padding: var(--spacing-2xl);
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
        }
        .no-products i {
            font-size: 4rem;
            color: var(--text-muted);
            margin-bottom: var(--spacing-lg);
        }
        .sort-options {
            display: flex;
            gap: var(--spacing-sm);
            margin-bottom: var(--spacing-lg);
            background: var(--white);
            padding: var(--spacing-md);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
        }
        .sort-btn {
            padding: var(--spacing-sm) var(--spacing-md);
            border: 1px solid var(--border-color);
            background: var(--white);
            color: var(--text-dark);
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: all var(--transition-fast);
        }
        .sort-btn.active,
        .sort-btn:hover {
            background: var(--primary-color);
            color: var(--white);
            border-color: var(--primary-color);
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
            <!-- Breadcrumb -->
            <nav class="breadcrumb">
                <a href="../index.php">Trang chủ</a>
                <i class="fas fa-chevron-right"></i>
                <a href="index.php">Sản phẩm</a>
                <?php if ($category_info): ?>
                    <i class="fas fa-chevron-right"></i>
                    <span><?php echo htmlspecialchars($category_info['name']); ?></span>
                <?php endif; ?>
            </nav>

            <?php if ($error): ?>
                <div class="error-page">
                    <i class="fas fa-exclamation-triangle fa-3x"></i>
                    <h2><?php echo htmlspecialchars($error); ?></h2>
                    <a href="index.php" class="btn btn-primary">Quay lại danh sách</a>
                </div>
            <?php else: ?>
                <!-- Page Header -->
                <div class="page-header">
                    <h1>
                        <?php if ($category_info): ?>
                            <?php echo htmlspecialchars($category_info['name']); ?>
                        <?php else: ?>
                            Sản phẩm: <?php echo htmlspecialchars($product_type); ?>
                        <?php endif; ?>
                    </h1>
                    <p>
                        <?php if ($category_info): ?>
                            <?php echo htmlspecialchars($category_info['description']); ?>
                        <?php else: ?>
                            So sánh giá từ nhiều người bán khác nhau
                        <?php endif; ?>
                    </p>
                </div>

                <!-- Sort Options -->
                <div class="sort-options">
                    <button class="sort-btn active" data-sort="price-asc">
                        <i class="fas fa-sort-amount-up"></i> Giá thấp nhất
                    </button>
                    <button class="sort-btn" data-sort="price-desc">
                        <i class="fas fa-sort-amount-down"></i> Giá cao nhất
                    </button>
                    <button class="sort-btn" data-sort="newest">
                        <i class="fas fa-clock"></i> Mới nhất
                    </button>
                    <button class="sort-btn" data-sort="popular">
                        <i class="fas fa-fire"></i> Phổ biến
                    </button>
                </div>

                <?php if (!empty($products)): ?>
                    <div class="product-comparison" id="product-comparison">
                        <?php 
                        $min_price = min(array_column($products, 'price'));
                        foreach ($products as $index => $product): 
                        ?>
                            <div class="seller-card" data-price="<?php echo $product['price']; ?>" data-date="<?php echo strtotime($product['created_at']); ?>" data-orders="<?php echo $product['total_orders']; ?>">
                                <?php if ($product['price'] == $min_price): ?>
                                    <div class="best-price">Giá tốt nhất</div>
                                <?php endif; ?>
                                
                                <div class="seller-header">
                                    <div class="seller-avatar">
                                        <?php echo strtoupper(substr($product['seller_name'], 0, 1)); ?>
                                    </div>
                                    <div class="seller-info">
                                        <h4><?php echo htmlspecialchars($product['seller_full_name'] ?: $product['seller_name']); ?></h4>
                                        <p>@<?php echo htmlspecialchars($product['seller_name']); ?></p>
                                    </div>
                                </div>

                                <div class="product-details">
                                    <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                                    <div class="product-description"><?php echo htmlspecialchars($product['description']); ?></div>
                                    <div class="product-condition">Tình trạng: <?php echo ucfirst($product['condition']); ?></div>
                                </div>

                                <div class="price-section">
                                    <div class="price"><?php echo number_format($product['price']); ?>đ</div>
                                    <div class="price-comparison">
                                        <?php if ($product['avg_price'] && $product['avg_price'] != $product['price']): ?>
                                            Trung bình: <?php echo number_format($product['avg_price']); ?>đ
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="seller-stats">
                                    <div class="stat-item">
                                        <div class="stat-number"><?php echo $product['total_orders']; ?></div>
                                        <div class="stat-label">Đã bán</div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-number"><?php echo date('d/m', strtotime($product['created_at'])); ?></div>
                                        <div class="stat-label">Đăng ngày</div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-number"><?php echo htmlspecialchars($product['game_name']); ?></div>
                                        <div class="stat-label">Game</div>
                                    </div>
                                </div>

                                <div class="product-actions">
                                    <a href="chi-tiet.php?id=<?php echo $product['id']; ?>" class="btn-contact">
                                        <i class="fas fa-eye"></i> Xem chi tiết
                                    </a>
                                    <button class="btn-favorite" data-product-id="<?php echo $product['id']; ?>">
                                        <i class="fas fa-heart"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-products">
                        <i class="fas fa-search"></i>
                        <h3>Không tìm thấy sản phẩm</h3>
                        <p>Hiện tại chưa có ai bán sản phẩm này. Hãy thử tìm kiếm sản phẩm khác!</p>
                        <a href="index.php" class="btn btn-primary">Tìm sản phẩm khác</a>
                    </div>
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
        // Sort functionality
        document.querySelectorAll('.sort-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                // Remove active class from all buttons
                document.querySelectorAll('.sort-btn').forEach(b => b.classList.remove('active'));
                // Add active class to clicked button
                this.classList.add('active');
                
                const sortType = this.dataset.sort;
                const container = document.getElementById('product-comparison');
                const cards = Array.from(container.children);
                
                cards.sort((a, b) => {
                    switch(sortType) {
                        case 'price-asc':
                            return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
                        case 'price-desc':
                            return parseFloat(b.dataset.price) - parseFloat(a.dataset.price);
                        case 'newest':
                            return parseFloat(b.dataset.date) - parseFloat(a.dataset.date);
                        case 'popular':
                            return parseFloat(b.dataset.orders) - parseFloat(a.dataset.orders);
                        default:
                            return 0;
                    }
                });
                
                // Re-append sorted cards
                cards.forEach(card => container.appendChild(card));
            });
        });

        // Wishlist functionality
        document.querySelectorAll('.btn-favorite').forEach(btn => {
            btn.addEventListener('click', function() {
                const productId = this.dataset.productId;
                // Implement wishlist functionality
                console.log('Add to wishlist:', productId);
            });
        });
    </script>
</body>
</html>
