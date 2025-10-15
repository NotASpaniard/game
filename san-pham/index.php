<?php
require_once '../config/session.php';
require_once '../config/database.php';
require_once '../config/image-helper.php';

// Lấy tham số
$page = max(1, intval($_GET['page'] ?? 1));
$search = trim($_GET['search'] ?? '');
$category = intval($_GET['category'] ?? 0);
$game = intval($_GET['game'] ?? 0);
$item_type = intval($_GET['item_type'] ?? 0);
$sort = $_GET['sort'] ?? 'newest';
$price_min = floatval($_GET['price_min'] ?? 0);
$price_max = floatval($_GET['price_max'] ?? 0);
$condition = $_GET['condition'] ?? '';
$rarity = $_GET['rarity'] ?? '';
$limit = 12;
$offset = ($page - 1) * $limit;

$products = [];
$total_products = 0;
$categories = [];
$games = [];
$item_types = [];

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Xây dựng query
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
    
    if ($item_type) {
        $where_conditions[] = "p.item_type_id = ?";
        $params[] = $item_type;
    }
    
    if ($price_min > 0) {
        $where_conditions[] = "p.price >= ?";
        $params[] = $price_min;
    }
    
    if ($price_max > 0) {
        $where_conditions[] = "p.price <= ?";
        $params[] = $price_max;
    }
    
    if ($condition) {
        $where_conditions[] = "p.condition = ?";
        $params[] = $condition;
    }
    
    if ($rarity) {
        $where_conditions[] = "p.rarity = ?";
        $params[] = $rarity;
    }
    
    $where_clause = implode(' AND ', $where_conditions);
    
    // Sắp xếp
    $order_by = match($sort) {
        'price_low' => 'p.price ASC',
        'price_high' => 'p.price DESC',
        'name' => 'p.name ASC',
        'oldest' => 'p.created_at ASC',
        'featured' => 'p.featured DESC, p.created_at DESC',
        default => 'p.created_at DESC'
    };
    
    // Đếm tổng số sản phẩm
    $count_sql = "
        SELECT COUNT(*) as total 
        FROM products p 
        LEFT JOIN games g ON p.game_id = g.id
        LEFT JOIN game_categories gc ON g.category_id = gc.id
        WHERE $where_clause
    ";
    $stmt = $conn->prepare($count_sql);
    $stmt->execute($params);
    $total_products = $stmt->fetch()['total'];
    
    // Lấy sản phẩm
    $sql = "
        SELECT p.*, g.name as game_name, gc.name as category_name, it.name as item_type_name,
               u.username as seller_name, u.avatar as seller_avatar
        FROM products p 
        LEFT JOIN games g ON p.game_id = g.id
        LEFT JOIN game_categories gc ON g.category_id = gc.id
        LEFT JOIN item_types it ON p.item_type_id = it.id
        LEFT JOIN users u ON p.seller_id = u.id
        WHERE $where_clause 
        ORDER BY $order_by
        LIMIT $limit OFFSET $offset
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
    
    // Lấy danh mục
    $stmt = $conn->query("SELECT * FROM game_categories WHERE status = 'active' ORDER BY name");
    $categories = $stmt->fetchAll();
    
    // Lấy game
    $stmt = $conn->query("SELECT * FROM games WHERE status = 'active' ORDER BY name");
    $games = $stmt->fetchAll();
    
    // Lấy loại vật phẩm
    $stmt = $conn->query("SELECT * FROM item_types WHERE status = 'active' ORDER BY name");
    $item_types = $stmt->fetchAll();
    
} catch (Exception $e) {
    error_log("Products page error: " . $e->getMessage());
    $products = [];
    $total_products = 0;
}

$total_pages = ceil($total_products / $limit);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sản phẩm - GameStore</title>
    <meta name="description" content="Khám phá các vật phẩm game chất lượng cao với giá tốt nhất">
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
                    <a href="index.php" class="nav-link active">Sản phẩm</a>
                    <a href="../danh-muc/" class="nav-link">Danh mục</a>
                    <a href="../huong-dan.php" class="nav-link">Hướng dẫn</a>
                    <a href="../lien-he.php" class="nav-link">Liên hệ</a>
                </nav>
                
                <div class="header-actions">
                    <div class="search-box">
                        <form action="index.php" method="GET">
                            <input type="text" name="search" placeholder="Tìm kiếm vật phẩm..." 
                                   class="search-input" value="<?php echo htmlspecialchars($search); ?>">
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
            <div class="products-layout">
                <!-- Sidebar Filters -->
                <aside class="filters-sidebar">
                    <div class="filters-card">
                        <h3>Bộ lọc</h3>
                        
                        <form method="GET" class="filters-form">
                            <!-- Search -->
                            <div class="filter-group">
                                <label>Tìm kiếm</label>
                                <input type="text" name="search" placeholder="Tên sản phẩm..." 
                                       value="<?php echo htmlspecialchars($search); ?>" class="form-control">
                            </div>
                            
                            <!-- Category -->
                            <div class="filter-group">
                                <label>Danh mục</label>
                                <select name="category" class="form-control">
                                    <option value="">Tất cả danh mục</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>" 
                                                <?php echo $category == $cat['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Game -->
                            <div class="filter-group">
                                <label>Game</label>
                                <select name="game" class="form-control">
                                    <option value="">Tất cả game</option>
                                    <?php foreach ($games as $game_item): ?>
                                        <option value="<?php echo $game_item['id']; ?>" 
                                                <?php echo $game == $game_item['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($game_item['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Item Type -->
                            <div class="filter-group">
                                <label>Loại vật phẩm</label>
                                <select name="item_type" class="form-control">
                                    <option value="">Tất cả loại</option>
                                    <?php foreach ($item_types as $type): ?>
                                        <option value="<?php echo $type['id']; ?>" 
                                                <?php echo $item_type == $type['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($type['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Price Range -->
                            <div class="filter-group">
                                <label>Khoảng giá</label>
                                <div class="price-range">
                                    <input type="number" name="price_min" placeholder="Từ" 
                                           value="<?php echo $price_min; ?>" class="form-control">
                                    <span>-</span>
                                    <input type="number" name="price_max" placeholder="Đến" 
                                           value="<?php echo $price_max; ?>" class="form-control">
                                </div>
                            </div>
                            
                            <!-- Condition -->
                            <div class="filter-group">
                                <label>Tình trạng</label>
                                <select name="condition" class="form-control">
                                    <option value="">Tất cả</option>
                                    <option value="new" <?php echo $condition === 'new' ? 'selected' : ''; ?>>Mới</option>
                                    <option value="excellent" <?php echo $condition === 'excellent' ? 'selected' : ''; ?>>Xuất sắc</option>
                                    <option value="good" <?php echo $condition === 'good' ? 'selected' : ''; ?>>Tốt</option>
                                    <option value="fair" <?php echo $condition === 'fair' ? 'selected' : ''; ?>>Khá</option>
                                </select>
                            </div>
                            
                            <!-- Rarity -->
                            <div class="filter-group">
                                <label>Độ hiếm</label>
                                <select name="rarity" class="form-control">
                                    <option value="">Tất cả</option>
                                    <option value="common" <?php echo $rarity === 'common' ? 'selected' : ''; ?>>Thường</option>
                                    <option value="uncommon" <?php echo $rarity === 'uncommon' ? 'selected' : ''; ?>>Hiếm</option>
                                    <option value="rare" <?php echo $rarity === 'rare' ? 'selected' : ''; ?>>Rất hiếm</option>
                                    <option value="epic" <?php echo $rarity === 'epic' ? 'selected' : ''; ?>>Huyền thoại</option>
                                    <option value="legendary" <?php echo $rarity === 'legendary' ? 'selected' : ''; ?>>Huyền thoại</option>
                                    <option value="mythic" <?php echo $rarity === 'mythic' ? 'selected' : ''; ?>>Thần thoại</option>
                                </select>
                            </div>
                            
                            <!-- Sort -->
                            <div class="filter-group">
                                <label>Sắp xếp</label>
                                <select name="sort" class="form-control">
                                    <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Mới nhất</option>
                                    <option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>Cũ nhất</option>
                                    <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Giá thấp đến cao</option>
                                    <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Giá cao đến thấp</option>
                                    <option value="name" <?php echo $sort === 'name' ? 'selected' : ''; ?>>Tên A-Z</option>
                                    <option value="featured" <?php echo $sort === 'featured' ? 'selected' : ''; ?>>Nổi bật</option>
                                </select>
                            </div>
                            
                            <div class="filter-actions">
                                <button type="submit" class="btn btn-primary btn-block">Áp dụng bộ lọc</button>
                                <a href="index.php" class="btn btn-outline btn-block">Xóa bộ lọc</a>
                            </div>
                        </form>
                    </div>
                </aside>

                <!-- Products Content -->
                <div class="products-content">
                    <div class="products-header">
                        <h1>Sản phẩm</h1>
                        <p><?php echo $total_products; ?> sản phẩm được tìm thấy</p>
                    </div>
                    
                    <div class="products-grid">
                        <?php if (!empty($products)): ?>
                            <?php foreach ($products as $product): ?>
                                <div class="product-card">
                                    <div class="product-image">
                                        <img src="<?php echo getProductImage($product['id']); ?>" 
                                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                                             loading="lazy">
                                        <?php if ($product['featured']): ?>
                                            <div class="product-badge">Nổi bật</div>
                                        <?php endif; ?>
                                        <?php if ($product['verified']): ?>
                                            <div class="product-badge verified">Đã xác thực</div>
                                        <?php endif; ?>
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
                                            <a href="chi-tiet.php?id=<?php echo $product['id']; ?>">
                                                <?php echo htmlspecialchars($product['name']); ?>
                                            </a>
                                        </h3>
                                        <p class="product-game"><?php echo htmlspecialchars($product['game_name']); ?></p>
                                        <p class="product-category"><?php echo htmlspecialchars($product['item_type_name']); ?></p>
                                        <div class="product-price">
                                            <span class="price-current"><?php echo number_format($product['price']); ?>đ</span>
                                            <?php if ($product['original_price']): ?>
                                                <span class="price-sale"><?php echo number_format($product['original_price']); ?>đ</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="product-seller">
                                            <img src="<?php echo $product['seller_avatar'] ?: '../assets/images/default-avatar.png'; ?>" 
                                                 alt="<?php echo htmlspecialchars($product['seller_name']); ?>" class="seller-avatar">
                                            <span class="seller-name"><?php echo htmlspecialchars($product['seller_name']); ?></span>
                                        </div>
                                        <div class="product-meta">
                                            <span class="condition condition-<?php echo $product['condition']; ?>">
                                                <?php echo ucfirst($product['condition']); ?>
                                            </span>
                                            <span class="rarity rarity-<?php echo $product['rarity']; ?>">
                                                <?php echo ucfirst($product['rarity']); ?>
                                            </span>
                                        </div>
                                        <button class="btn btn-primary add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>">
                                            <i class="fas fa-shopping-cart"></i> Thêm vào giỏ
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-products">
                                <i class="fas fa-search"></i>
                                <h3>Không có sản phẩm nào</h3>
                                <p>Không tìm thấy sản phẩm phù hợp với bộ lọc của bạn</p>
                                <a href="index.php" class="btn btn-primary">Xem tất cả sản phẩm</a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" 
                                   class="page-btn">
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
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" 
                                   class="page-btn">
                                    Sau <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
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
    <script src="../assets/js/products.js"></script>
</body>
</html>
