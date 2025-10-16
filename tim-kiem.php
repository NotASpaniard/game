<?php
require_once 'config/session.php';
require_once 'config/database.php';

$search_query = trim($_GET['q'] ?? '');
$game_id = intval($_GET['game_id'] ?? 0);
$min_price = intval($_GET['min_price'] ?? 0);
$max_price = intval($_GET['max_price'] ?? 0);
$rarity = trim($_GET['rarity'] ?? '');
$sort = trim($_GET['sort'] ?? 'newest');
$page = max(1, intval($_GET['page'] ?? 1));

$games = [];
$products = [];
$pagination = [];

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Get games for filter
    $stmt = $conn->query("SELECT * FROM games WHERE status = 'active' ORDER BY name");
    $games = $stmt->fetchAll();
    
    // Get search results via API
    $api_url = "api/search.php?" . http_build_query([
        'q' => $search_query,
        'game_id' => $game_id,
        'min_price' => $min_price,
        'max_price' => $max_price,
        'rarity' => $rarity,
        'sort' => $sort,
        'page' => $page
    ]);
    
    $response = file_get_contents($api_url);
    $data = json_decode($response, true);
    
    if ($data['success']) {
        $products = $data['products'];
        $pagination = $data['pagination'];
    }
    
} catch (Exception $e) {
    error_log("Search page error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tìm kiếm - GameStore</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/products.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
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
                
                <button class="mobile-menu-toggle" id="mobile-menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                
                <nav class="nav">
                    <a href="index.php" class="nav-link">Trang chủ</a>
                    <a href="san-pham/" class="nav-link">Sản phẩm</a>
                    <a href="san-pham/loai-san-pham.php" class="nav-link">Loại sản phẩm</a>
                    <a href="danh-muc/" class="nav-link">Danh mục</a>
                    <a href="lien-he.php" class="nav-link">Liên hệ</a>
                </nav>
                
                <div class="header-actions">
                    <div class="search-box">
                        <form action="tim-kiem.php" method="GET">
                            <input type="text" name="q" placeholder="Tìm kiếm vật phẩm..." 
                                   value="<?php echo htmlspecialchars($search_query); ?>" class="search-input">
                            <button type="submit" class="search-btn">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>
                    
                    <div class="user-actions">
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
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main">
        <div class="container">
            <div class="page-header">
                <h1>Tìm kiếm sản phẩm</h1>
                <p>Kết quả tìm kiếm cho: "<?php echo htmlspecialchars($search_query); ?>"</p>
            </div>

            <!-- Search Filters -->
            <div class="search-filters">
                <form method="GET" class="filter-form">
                    <input type="hidden" name="q" value="<?php echo htmlspecialchars($search_query); ?>">
                    
                    <div class="filter-row">
                        <div class="filter-group">
                            <label>Game:</label>
                            <select name="game_id" class="form-control">
                                <option value="">Tất cả game</option>
                                <?php foreach ($games as $game): ?>
                                    <option value="<?php echo $game['id']; ?>" 
                                            <?php echo $game_id == $game['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($game['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label>Giá từ:</label>
                            <input type="number" name="min_price" class="form-control" 
                                   value="<?php echo $min_price; ?>" placeholder="0">
                        </div>
                        
                        <div class="filter-group">
                            <label>Đến:</label>
                            <input type="number" name="max_price" class="form-control" 
                                   value="<?php echo $max_price; ?>" placeholder="10000000">
                        </div>
                        
                        <div class="filter-group">
                            <label>Độ hiếm:</label>
                            <select name="rarity" class="form-control">
                                <option value="">Tất cả</option>
                                <option value="common" <?php echo $rarity == 'common' ? 'selected' : ''; ?>>Common</option>
                                <option value="uncommon" <?php echo $rarity == 'uncommon' ? 'selected' : ''; ?>>Uncommon</option>
                                <option value="rare" <?php echo $rarity == 'rare' ? 'selected' : ''; ?>>Rare</option>
                                <option value="epic" <?php echo $rarity == 'epic' ? 'selected' : ''; ?>>Epic</option>
                                <option value="legendary" <?php echo $rarity == 'legendary' ? 'selected' : ''; ?>>Legendary</option>
                                <option value="mythic" <?php echo $rarity == 'mythic' ? 'selected' : ''; ?>>Mythic</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label>Sắp xếp:</label>
                            <select name="sort" class="form-control">
                                <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Mới nhất</option>
                                <option value="price_asc" <?php echo $sort == 'price_asc' ? 'selected' : ''; ?>>Giá tăng dần</option>
                                <option value="price_desc" <?php echo $sort == 'price_desc' ? 'selected' : ''; ?>>Giá giảm dần</option>
                                <option value="name_asc" <?php echo $sort == 'name_asc' ? 'selected' : ''; ?>>Tên A-Z</option>
                                <option value="name_desc" <?php echo $sort == 'name_desc' ? 'selected' : ''; ?>>Tên Z-A</option>
                            </select>
                        </div>
                        
                        <div class="filter-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Tìm kiếm
                            </button>
                            <a href="tim-kiem.php" class="btn btn-outline">
                                <i class="fas fa-refresh"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Search Results -->
            <div class="search-results">
                <div class="results-header">
                    <h2>Kết quả tìm kiếm</h2>
                    <p><?php echo $pagination['total_items'] ?? 0; ?> sản phẩm được tìm thấy</p>
                </div>
                
                <?php if (!empty($products)): ?>
                    <div class="products-grid">
                        <?php foreach ($products as $product): ?>
                            <div class="product-card">
                                <div class="product-image">
                                    <img src="<?php echo getProductImage($product['id'], 'assets/images/no-image.jpg', false); ?>" 
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
                                    <p class="product-game"><?php echo htmlspecialchars($product['game_name'] ?? 'Game'); ?></p>
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
                    
                    <!-- Pagination -->
                    <?php if ($pagination['total_pages'] > 1): ?>
                        <div class="pagination">
                            <?php if ($pagination['current_page'] > 1): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] - 1])); ?>" 
                                   class="pagination-btn">
                                    <i class="fas fa-chevron-left"></i> Trước
                                </a>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
                                   class="pagination-btn <?php echo $i == $pagination['current_page'] ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] + 1])); ?>" 
                                   class="pagination-btn">
                                    Sau <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <div class="no-results">
                        <i class="fas fa-search"></i>
                        <h3>Không tìm thấy sản phẩm nào</h3>
                        <p>Hãy thử tìm kiếm với từ khóa khác hoặc điều chỉnh bộ lọc</p>
                        <a href="san-pham/" class="btn btn-primary">Xem tất cả sản phẩm</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <p>&copy; 2025 GameStore. Tất cả quyền được bảo lưu.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/notifications.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
