<!DOCTYPE html>
<html>
<head>
    <title>Full Website Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>🌐 FULL WEBSITE TEST - Kiểm tra toàn bộ trang web</h1>
    
    <?php
    // Test tất cả components
    echo "<div class='section'>";
    echo "<h2>1. Database & Data Test</h2>";
    
    try {
        require_once 'config/database.php';
        $db = new Database();
        $conn = $db->getConnection();
        
        // Test games
        $stmt = $conn->query("SELECT COUNT(*) as count FROM games");
        $games_count = $stmt->fetch()['count'];
        echo "<p class='success'>✅ Games: {$games_count}</p>";
        
        // Test users
        $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
        $users_count = $stmt->fetch()['count'];
        echo "<p class='success'>✅ Users: {$users_count}</p>";
        
        // Test products
        $stmt = $conn->query("SELECT COUNT(*) as count FROM products");
        $products_count = $stmt->fetch()['count'];
        echo "<p class='success'>✅ Products: {$products_count}</p>";
        
        // Test featured products
        $stmt = $conn->query("SELECT COUNT(*) as count FROM products WHERE featured = 1");
        $featured_count = $stmt->fetch()['count'];
        echo "<p class='success'>✅ Featured: {$featured_count}</p>";
        
        // Test active products
        $stmt = $conn->query("SELECT COUNT(*) as count FROM products WHERE status = 'active'");
        $active_count = $stmt->fetch()['count'];
        echo "<p class='success'>✅ Active: {$active_count}</p>";
        
    } catch (Exception $e) {
        echo "<p class='error'>❌ Database error: " . $e->getMessage() . "</p>";
    }
    echo "</div>";
    
    echo "<div class='section'>";
    echo "<h2>2. Core Pages Test</h2>";
    
    $pages = [
        'index.php' => 'Trang chủ',
        'tim-kiem.php' => 'Tìm kiếm',
        'san-pham/index.php' => 'Sản phẩm',
        'danh-muc/index.php' => 'Danh mục',
        'user/gio-hang.php' => 'Giỏ hàng',
        'user/san-pham-cua-toi.php' => 'Sản phẩm của tôi',
        'user/yeu-thich.php' => 'Yêu thích',
        'user/don-hang.php' => 'Đơn hàng',
        'user/them-san-pham.php' => 'Thêm sản phẩm',
        'user/sua-san-pham.php' => 'Sửa sản phẩm',
        'auth/dang-nhap.php' => 'Đăng nhập',
        'auth/dang-ky.php' => 'Đăng ký'
    ];
    
    foreach ($pages as $page => $name) {
        if (file_exists($page)) {
            echo "<p class='success'>✅ {$name}: EXISTS</p>";
        } else {
            echo "<p class='error'>❌ {$name}: MISSING</p>";
        }
    }
    echo "</div>";
    
    echo "<div class='section'>";
    echo "<h2>3. API Endpoints Test</h2>";
    
    $apis = [
        'api/cart.php' => 'Cart API',
        'api/search.php' => 'Search API',
        'api/wishlist.php' => 'Wishlist API',
        'api/products.php' => 'Products API',
        'api/orders.php' => 'Orders API'
    ];
    
    foreach ($apis as $api => $name) {
        if (file_exists($api)) {
            echo "<p class='success'>✅ {$name}: EXISTS</p>";
        } else {
            echo "<p class='error'>❌ {$name}: MISSING</p>";
        }
    }
    echo "</div>";
    
    echo "<div class='section'>";
    echo "<h2>4. Assets Test</h2>";
    
    $css_files = [
        'assets/css/main.css',
        'assets/css/home.css',
        'assets/css/products.css',
        'assets/css/cart.css',
        'assets/css/responsive.css'
    ];
    
    foreach ($css_files as $file) {
        if (file_exists($file)) {
            $size = filesize($file);
            echo "<p class='success'>✅ " . basename($file) . ": {$size} bytes</p>";
        } else {
            echo "<p class='error'>❌ " . basename($file) . ": MISSING</p>";
        }
    }
    
    $js_files = [
        'assets/js/main.js',
        'assets/js/notifications.js',
        'assets/js/home.js',
        'assets/js/cart.js',
        'assets/js/products.js'
    ];
    
    foreach ($js_files as $file) {
        if (file_exists($file)) {
            $size = filesize($file);
            echo "<p class='success'>✅ " . basename($file) . ": {$size} bytes</p>";
        } else {
            echo "<p class='error'>❌ " . basename($file) . ": MISSING</p>";
        }
    }
    echo "</div>";
    
    echo "<div class='section'>";
    echo "<h2>5. Images Test</h2>";
    
    if (file_exists('assets/images/no-image.jpg')) {
        echo "<p class='success'>✅ Default image: EXISTS</p>";
    } else {
        echo "<p class='error'>❌ Default image: MISSING</p>";
    }
    
    if (file_exists('images/products/')) {
        $product_images = glob('images/products/*');
        echo "<p class='success'>✅ Product images: " . count($product_images) . " files</p>";
    } else {
        echo "<p class='warning'>⚠️ Product images directory: MISSING</p>";
    }
    echo "</div>";
    
    echo "<div class='section'>";
    echo "<h2>6. Database Tables Test</h2>";
    
    $tables = [
        'users' => 'Users table',
        'products' => 'Products table',
        'games' => 'Games table',
        'game_categories' => 'Game categories table',
        'cart' => 'Cart table',
        'wishlist' => 'Wishlist table',
        'orders' => 'Orders table',
        'order_items' => 'Order items table',
        'product_images' => 'Product images table'
    ];
    
    foreach ($tables as $table => $name) {
        try {
            $stmt = $conn->query("SELECT COUNT(*) as count FROM {$table}");
            $count = $stmt->fetch()['count'];
            echo "<p class='success'>✅ {$name}: {$count} records</p>";
        } catch (Exception $e) {
            echo "<p class='error'>❌ {$name}: " . $e->getMessage() . "</p>";
        }
    }
    echo "</div>";
    
    echo "<div class='section'>";
    echo "<h2>7. Functionality Test</h2>";
    
    // Test getProductImage function
    try {
        require_once 'config/image-helper.php';
        $test_image = getProductImage(1, 'assets/images/no-image.jpg', false);
        echo "<p class='success'>✅ getProductImage function: OK</p>";
    } catch (Exception $e) {
        echo "<p class='error'>❌ getProductImage function: " . $e->getMessage() . "</p>";
    }
    
    // Test session functions
    try {
        require_once 'config/session.php';
        echo "<p class='success'>✅ Session functions: OK</p>";
    } catch (Exception $e) {
        echo "<p class='error'>❌ Session functions: " . $e->getMessage() . "</p>";
    }
    echo "</div>";
    
    echo "<div class='section'>";
    echo "<h2>8. Recent Products Test</h2>";
    
    try {
        $stmt = $conn->query("
            SELECT p.*, g.name as game_name
            FROM products p 
            LEFT JOIN games g ON p.game_id = g.id
            WHERE p.status = 'active'
            ORDER BY p.created_at DESC 
            LIMIT 5
        ");
        $recent_products = $stmt->fetchAll();
        echo "<p class='success'>✅ Recent products: " . count($recent_products) . " results</p>";
        
        foreach ($recent_products as $product) {
            echo "<p>  - {$product['name']} - {$product['game_name']} - " . number_format($product['price']) . "đ</p>";
        }
        
    } catch (Exception $e) {
        echo "<p class='error'>❌ Recent products test: " . $e->getMessage() . "</p>";
    }
    echo "</div>";
    
    echo "<div class='section'>";
    echo "<h2>9. Search Functionality Test</h2>";
    
    try {
        $stmt = $conn->prepare("
            SELECT p.*, g.name as game_name, u.username as seller_name
            FROM products p
            LEFT JOIN games g ON p.game_id = g.id
            LEFT JOIN users u ON p.seller_id = u.id
            WHERE p.status = 'active' AND (p.name LIKE ? OR p.description LIKE ?)
            ORDER BY p.created_at DESC
            LIMIT 3
        ");
        $search_term = "%AK%";
        $stmt->execute([$search_term, $search_term]);
        $search_results = $stmt->fetchAll();
        echo "<p class='success'>✅ Search results for 'AK': " . count($search_results) . " results</p>";
        
    } catch (Exception $e) {
        echo "<p class='error'>❌ Search test: " . $e->getMessage() . "</p>";
    }
    echo "</div>";
    
    echo "<div class='section'>";
    echo "<h2>10. Performance Test</h2>";
    
    $start_time = microtime(true);
    
    // Test database performance
    $stmt = $conn->query("SELECT COUNT(*) FROM products WHERE status = 'active'");
    $result = $stmt->fetch();
    
    $end_time = microtime(true);
    $execution_time = ($end_time - $start_time) * 1000;
    
    echo "<p class='success'>✅ Database query time: " . round($execution_time, 2) . "ms</p>";
    
    if ($execution_time < 100) {
        echo "<p class='success'>✅ Performance: EXCELLENT</p>";
    } elseif ($execution_time < 500) {
        echo "<p class='success'>✅ Performance: GOOD</p>";
    } else {
        echo "<p class='warning'>⚠️ Performance: SLOW</p>";
    }
    echo "</div>";
    
    echo "<h2>🎉 FULL WEBSITE TEST COMPLETED!</h2>";
    ?>
</body>
</html>
