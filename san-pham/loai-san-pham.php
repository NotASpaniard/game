<?php
require_once '../config/session.php';
require_once '../config/database.php';
require_once '../config/image-helper.php';

$product_types = [];
$categories = [];
$games = [];
$total_products = 0;
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;
$search = trim($_GET['search'] ?? '');
$category = intval($_GET['category'] ?? 0);
$game = intval($_GET['game'] ?? 0);
$sort = trim($_GET['sort'] ?? 'popular');

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Lấy danh mục và game
    $stmt = $conn->query("SELECT * FROM game_categories WHERE status = 'active' ORDER BY name");
    $categories = $stmt->fetchAll();
    
    $stmt = $conn->query("SELECT * FROM games WHERE status = 'active' ORDER BY name");
    $games = $stmt->fetchAll();
    
    // Xây dựng query cho loại sản phẩm
    $where_conditions = ["p.status = 'active'"];
    $params = [];
    
    if ($search) {
        $where_conditions[] = "(p.name LIKE ? OR p.description LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    if ($category) {
        $where_conditions[] = "gc.id = ?";
        $params[] = $category;
    }
    
    if ($game) {
        $where_conditions[] = "p.game_id = ?";
        $params[] = $game;
    }
    
    $where_clause = implode(' AND ', $where_conditions);
    
    // Xác định sort order
    $order_by = "total_orders DESC, seller_count DESC";
    switch ($sort) {
        case 'price-low':
            $order_by = "min_price ASC";
            break;
        case 'price-high':
            $order_by = "max_price DESC";
            break;
        case 'newest':
            $order_by = "latest_date DESC";
            break;
        case 'name':
            $order_by = "p.name ASC";
            break;
    }
    
    // Query để lấy các loại sản phẩm
    $sql = "
        SELECT p.name, p.description, g.name as game_name, gc.name as category_name, gc.id as category_id,
               COUNT(DISTINCT p.id) as seller_count,
               MIN(p.price) as min_price,
               MAX(p.price) as max_price,
               AVG(p.price) as avg_price,
               COUNT(DISTINCT o.id) as total_orders,
               MAX(p.created_at) as latest_date,
               GROUP_CONCAT(DISTINCT p.condition) as conditions
        FROM products p
        LEFT JOIN games g ON p.game_id = g.id
        LEFT JOIN game_categories gc ON g.category_id = gc.id
        LEFT JOIN order_items oi ON p.id = oi.product_id
        LEFT JOIN orders o ON oi.order_id = o.id AND o.status = 'completed'
        WHERE $where_clause
        GROUP BY p.name, g.id, gc.id
        ORDER BY $order_by
        LIMIT $limit OFFSET $offset
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $product_types = $stmt->fetchAll();
    
    // Đếm tổng số loại sản phẩm
    $count_sql = "
        SELECT COUNT(DISTINCT CONCAT(p.name, '-', g.id)) as total
        FROM products p
        LEFT JOIN games g ON p.game_id = g.id
        LEFT JOIN game_categories gc ON g.category_id = gc.id
        WHERE $where_clause
    ";
    $stmt = $conn->prepare($count_sql);
    $stmt->execute($params);
    $total_products = $stmt->fetch()['total'];
    
} catch (Exception $e) {
    error_log("Product types page error: " . $e->getMessage());
}

$total_pages = ceil($total_products / $limit);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loại sản phẩm - GameStore</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/products.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .product-type-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            padding: var(--spacing-lg);
            border: 1px solid var(--border-color);
            transition: all var(--transition-normal);
            position: relative;
            overflow: hidden;
        }
        .product-type-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-color);
        }
        .product-type-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: var(--spacing-md);
        }
        .product-type-info h3 {
            margin: 0 0 var(--spacing-xs) 0;
            color: var(--text-dark);
            font-size: 1.25rem;
        }
        .product-type-meta {
            display: flex;
            gap: var(--spacing-sm);
            margin-bottom: var(--spacing-sm);
        }
        .meta-tag {
            background: var(--bg-light);
            color: var(--text-light);
            padding: 2px 8px;
            border-radius: var(--radius-sm);
            font-size: 0.75rem;
        }
        .product-type-description {
            color: var(--text-light);
            font-size: 0.875rem;
            margin-bottom: var(--spacing-md);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .price-range {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--spacing-md);
            padding: var(--spacing-sm);
            background: var(--bg-light);
            border-radius: var(--radius-sm);
        }
        .price-info {
            text-align: center;
        }
        .price-label {
            font-size: 0.75rem;
            color: var(--text-light);
            margin-bottom: 2px;
        }
        .price-value {
            font-weight: 600;
            color: var(--text-dark);
        }
        .min-price {
            color: var(--cta-color);
            font-size: 1.1rem;
        }
        .max-price {
            color: var(--text-light);
        }
        .avg-price {
            color: var(--primary-color);
        }
        .seller-stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: var(--spacing-md);
            padding: var(--spacing-sm);
            background: var(--white);
            border-radius: var(--radius-sm);
            border: 1px solid var(--border-color);
        }
        .stat-item {
            text-align: center;
            flex: 1;
        }
        .stat-number {
            font-weight: 600;
            color: var(--text-dark);
            font-size: 1.1rem;
        }
        .stat-label {
            font-size: 0.75rem;
            color: var(--text-light);
        }
        .product-type-actions {
            display: flex;
            gap: var(--spacing-sm);
        }
        .btn-compare {
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
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--spacing-xs);
        }
        .btn-compare:hover {
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
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .btn-favorite:hover {
            background: var(--primary-color);
            color: var(--white);
        }
        .popular-badge {
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
        .sort-options {
            display: flex;
            gap: var(--spacing-sm);
            margin-bottom: var(--spacing-lg);
            background: var(--white);
            padding: var(--spacing-md);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
            flex-wrap: wrap;
        }
        .sort-btn {
            padding: var(--spacing-sm) var(--spacing-md);
            border: 1px solid var(--border-color);
            background: var(--white);
            color: var(--text-dark);
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: all var(--transition-fast);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-xs);
        }
        .sort-btn.active,
        .sort-btn:hover {
            background: var(--primary-color);
            color: var(--white);
            border-color: var(--primary-color);
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
                    <a href="loai-san-pham.php" class="nav-link active">Loại sản phẩm</a>
                    <a href="../danh-muc/" class="nav-link">Danh mục</a>
                    <a href="../huong-dan.php" class="nav-link">Hướng dẫn</a>
                    <a href="../lien-he.php" class="nav-link">Liên hệ</a>
                </nav>
                
                <div class="header-actions">
                    <div class="search-box">
                        <form action="loai-san-pham.php" method="GET">
                            <input type="text" name="search" placeholder="Tìm kiếm loại sản phẩm..." class="search-input" value="<?php echo htmlspecialchars($search); ?>">
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
                <h1>Loại sản phẩm</h1>
                <p>Khám phá các loại vật phẩm game và so sánh giá từ nhiều người bán</p>
            </div>

            <!-- Filters -->
            <div class="filters-section">
                <form method="GET" class="filters-form">
                    <div class="filter-group">
                        <label>Tìm kiếm:</label>
                        <input type="text" name="search" placeholder="Tên sản phẩm..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label>Danh mục:</label>
                        <select name="category">
                            <option value="">Tất cả danh mục</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $category == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label>Game:</label>
                        <select name="game">
                            <option value="">Tất cả game</option>
                            <?php foreach ($games as $g): ?>
                                <option value="<?php echo $g['id']; ?>" <?php echo $game == $g['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($g['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Lọc</button>
                </form>
            </div>

            <!-- Sort Options -->
            <div class="sort-options">
                <a href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'popular'])); ?>" 
                   class="sort-btn <?php echo $sort === 'popular' ? 'active' : ''; ?>">
                    <i class="fas fa-fire"></i> Phổ biến
                </a>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'price-low'])); ?>" 
                   class="sort-btn <?php echo $sort === 'price-low' ? 'active' : ''; ?>">
                    <i class="fas fa-sort-amount-up"></i> Giá thấp
                </a>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'price-high'])); ?>" 
                   class="sort-btn <?php echo $sort === 'price-high' ? 'active' : ''; ?>">
                    <i class="fas fa-sort-amount-down"></i> Giá cao
                </a>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'newest'])); ?>" 
                   class="sort-btn <?php echo $sort === 'newest' ? 'active' : ''; ?>">
                    <i class="fas fa-clock"></i> Mới nhất
                </a>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'name'])); ?>" 
                   class="sort-btn <?php echo $sort === 'name' ? 'active' : ''; ?>">
                    <i class="fas fa-sort-alpha-down"></i> Tên A-Z
                </a>
            </div>

            <?php if (!empty($product_types)): ?>
                <div class="products-grid">
                    <?php foreach ($product_types as $index => $type): ?>
                        <div class="product-type-card">
                            <?php if ($index < 3): ?>
                                <div class="popular-badge">Phổ biến</div>
                            <?php endif; ?>
                            
                            <div class="product-type-header">
                                <div class="product-type-info">
                                    <h3><?php echo htmlspecialchars($type['name']); ?></h3>
                                    <div class="product-type-meta">
                                        <span class="meta-tag"><?php echo htmlspecialchars($type['game_name']); ?></span>
                                        <span class="meta-tag"><?php echo htmlspecialchars($type['category_name']); ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="product-type-description">
                                <?php echo htmlspecialchars($type['description']); ?>
                            </div>

                            <div class="price-range">
                                <div class="price-info">
                                    <div class="price-label">Giá thấp nhất</div>
                                    <div class="price-value min-price"><?php echo number_format($type['min_price']); ?>đ</div>
                                </div>
                                <div class="price-info">
                                    <div class="price-label">Giá cao nhất</div>
                                    <div class="price-value max-price"><?php echo number_format($type['max_price']); ?>đ</div>
                                </div>
                                <div class="price-info">
                                    <div class="price-label">Giá trung bình</div>
                                    <div class="price-value avg-price"><?php echo number_format($type['avg_price']); ?>đ</div>
                                </div>
                            </div>

                            <div class="seller-stats">
                                <div class="stat-item">
                                    <div class="stat-number"><?php echo $type['seller_count']; ?></div>
                                    <div class="stat-label">Người bán</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-number"><?php echo $type['total_orders']; ?></div>
                                    <div class="stat-label">Đã bán</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-number"><?php echo date('d/m', strtotime($type['latest_date'])); ?></div>
                                    <div class="stat-label">Cập nhật</div>
                                </div>
                            </div>

                            <div class="product-type-actions">
                                <a href="danh-muc.php?category=<?php echo $type['category_id']; ?>&type=<?php echo urlencode($type['name']); ?>" 
                                   class="btn-compare">
                                    <i class="fas fa-balance-scale"></i> So sánh giá
                                </a>
                                <button class="btn-favorite" data-product-type="<?php echo htmlspecialchars($type['name']); ?>">
                                    <i class="fas fa-heart"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" class="page-btn">
                                <i class="fas fa-chevron-left"></i> Trước
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
                               class="page-btn <?php echo $i === $page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" class="page-btn">
                                Sau <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-search"></i>
                    <h3>Không tìm thấy loại sản phẩm nào</h3>
                    <p>Hãy thử tìm kiếm với từ khóa khác hoặc chọn danh mục khác.</p>
                    <a href="loai-san-pham.php" class="btn btn-primary">Xem tất cả</a>
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
    <script>
        // Wishlist functionality for product types
        document.querySelectorAll('.btn-favorite').forEach(btn => {
            btn.addEventListener('click', function() {
                const productType = this.dataset.productType;
                // Implement wishlist functionality for product types
                console.log('Add product type to wishlist:', productType);
            });
        });
    </script>
</body>
</html>
