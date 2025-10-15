<?php
require_once 'config/session.php';
require_once 'config/database.php';

// Tạo dữ liệu demo nếu chưa có
try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Kiểm tra xem đã có dữ liệu chưa
    $stmt = $conn->query("SELECT COUNT(*) as count FROM products");
    $product_count = $stmt->fetch()['count'];
    
    if ($product_count == 0) {
        echo "<h2>Tạo dữ liệu demo...</h2>";
        
        // Tạo danh mục demo
        $categories = [
            ['name' => 'FPS Games', 'description' => 'Game bắn súng góc nhìn thứ nhất'],
            ['name' => 'MOBA Games', 'description' => 'Game chiến thuật thời gian thực'],
            ['name' => 'Battle Royale', 'description' => 'Game sinh tồn đại chiến']
        ];
        
        foreach ($categories as $cat) {
            $stmt = $conn->prepare("INSERT INTO game_categories (name, description, status) VALUES (?, ?, 'active')");
            $stmt->execute([$cat['name'], $cat['description']]);
        }
        
        // Tạo game demo
        $games = [
            ['name' => 'Counter-Strike 2', 'description' => 'Game bắn súng nổi tiếng', 'category_id' => 1],
            ['name' => 'Valorant', 'description' => 'Game bắn súng chiến thuật', 'category_id' => 1],
            ['name' => 'League of Legends', 'description' => 'Game MOBA phổ biến', 'category_id' => 2],
            ['name' => 'PUBG', 'description' => 'Game battle royale', 'category_id' => 3]
        ];
        
        foreach ($games as $game) {
            $stmt = $conn->prepare("INSERT INTO games (name, description, category_id, status) VALUES (?, ?, ?, 'active')");
            $stmt->execute([$game['name'], $game['description'], $game['category_id']]);
        }
        
        // Tạo user demo
        $users = [
            ['username' => 'seller1', 'email' => 'seller1@demo.com', 'full_name' => 'Nguyễn Văn A', 'role' => 'seller'],
            ['username' => 'seller2', 'email' => 'seller2@demo.com', 'full_name' => 'Trần Thị B', 'role' => 'seller'],
            ['username' => 'seller3', 'email' => 'seller3@demo.com', 'full_name' => 'Lê Văn C', 'role' => 'seller']
        ];
        
        foreach ($users as $user) {
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$user['username'], $user['email'], password_hash('demo123', PASSWORD_DEFAULT), $user['full_name'], $user['role']]);
        }
        
        // Tạo sản phẩm demo cho World//Zero và Dragon Adventures
        $products = [
            // World//Zero Items
            ['name' => 'Legendary Sword', 'description' => 'Thanh kiếm huyền thoại trong World//Zero', 'price' => 150000, 'game_id' => 1, 'seller_id' => 1, 'product_condition' => 'new'],
            ['name' => 'Anime Character Skin', 'description' => 'Skin nhân vật anime hiếm', 'price' => 120000, 'game_id' => 1, 'seller_id' => 2, 'product_condition' => 'good'],
            ['name' => 'RPG Equipment Set', 'description' => 'Bộ trang bị RPG đầy đủ', 'price' => 200000, 'game_id' => 1, 'seller_id' => 3, 'product_condition' => 'new'],
            
            // Dragon Adventures Items
            ['name' => 'Rare Dragon Egg', 'description' => 'Trứng rồng hiếm trong Dragon Adventures', 'price' => 100000, 'game_id' => 2, 'seller_id' => 1, 'product_condition' => 'new'],
            ['name' => 'Legendary Dragon', 'description' => 'Rồng huyền thoại đã thuần hóa', 'price' => 300000, 'game_id' => 2, 'seller_id' => 2, 'product_condition' => 'excellent'],
            ['name' => 'Dragon Care Items', 'description' => 'Bộ chăm sóc rồng đầy đủ', 'price' => 80000, 'game_id' => 2, 'seller_id' => 3, 'product_condition' => 'new']
        ];
        
        foreach ($products as $product) {
            $stmt = $conn->prepare("INSERT INTO products (name, description, price, game_id, seller_id, product_condition, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'active', NOW())");
            $stmt->execute([$product['name'], $product['description'], $product['price'], $product['game_id'], $product['seller_id'], $product['product_condition']]);
        }
        
        echo "<p style='color: green;'>✅ Đã tạo dữ liệu demo thành công!</p>";
    } else {
        echo "<p style='color: blue;'>ℹ️ Dữ liệu demo đã tồn tại.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Lỗi: " . $e->getMessage() . "</p>";
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demo Sản phẩm - GameStore</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        .demo-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: var(--spacing-xl);
        }
        .demo-section {
            background: var(--white);
            padding: var(--spacing-xl);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            margin-bottom: var(--spacing-xl);
        }
        .demo-links {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: var(--spacing-lg);
        }
        .demo-link {
            background: var(--bg-light);
            padding: var(--spacing-lg);
            border-radius: var(--radius-md);
            text-align: center;
            text-decoration: none;
            color: var(--text-dark);
            transition: all var(--transition-fast);
        }
        .demo-link:hover {
            background: var(--primary-color);
            color: var(--white);
            transform: translateY(-2px);
        }
        .demo-link i {
            font-size: 2rem;
            margin-bottom: var(--spacing-md);
        }
    </style>
</head>
<body>
    <div class="demo-container">
        <h1>🎮 Demo GameStore - Hệ thống so sánh giá</h1>
        
        <div class="demo-section">
            <h2>📊 Dữ liệu demo đã được tạo</h2>
            <p>Hệ thống đã tạo sẵn dữ liệu demo với:</p>
            <ul>
                <li>3 danh mục game (FPS, MOBA, Battle Royale)</li>
                <li>4 game phổ biến (CS2, Valorant, LoL, PUBG)</li>
                <li>3 người bán demo</li>
                <li>9 sản phẩm với giá khác nhau để so sánh</li>
            </ul>
        </div>

        <div class="demo-section">
            <h2>🔗 Các trang demo</h2>
            <div class="demo-links">
                <a href="san-pham/loai-san-pham.php" class="demo-link">
                    <i class="fas fa-balance-scale"></i>
                    <h3>Loại sản phẩm</h3>
                    <p>Xem các loại sản phẩm và so sánh giá từ nhiều người bán</p>
                </a>
                
                <a href="san-pham/danh-muc.php?category=1" class="demo-link">
                    <i class="fas fa-gamepad"></i>
                    <h3>FPS Games</h3>
                    <p>Xem sản phẩm CS2 và Valorant với giá so sánh</p>
                </a>
                
                <a href="san-pham/danh-muc.php?category=2" class="demo-link">
                    <i class="fas fa-chess"></i>
                    <h3>MOBA Games</h3>
                    <p>Xem sản phẩm League of Legends</p>
                </a>
                
                <a href="san-pham/danh-muc.php?category=3" class="demo-link">
                    <i class="fas fa-crosshairs"></i>
                    <h3>Battle Royale</h3>
                    <p>Xem sản phẩm PUBG</p>
                </a>
                
                <a href="index.php" class="demo-link">
                    <i class="fas fa-home"></i>
                    <h3>Trang chủ</h3>
                    <p>Quay lại trang chủ với navigation mới</p>
                </a>
                
                <a href="setup.php" class="demo-link">
                    <i class="fas fa-database"></i>
                    <h3>Setup Database</h3>
                    <p>Khởi tạo lại database nếu cần</p>
                </a>
            </div>
        </div>

        <div class="demo-section">
            <h2>🎯 Tính năng chính</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: var(--spacing-lg);">
                <div>
                    <h4>🔍 So sánh giá</h4>
                    <p>Xem cùng một sản phẩm từ nhiều người bán với giá khác nhau</p>
                </div>
                <div>
                    <h4>📊 Thống kê</h4>
                    <p>Hiển thị số người bán, giá thấp/cao nhất, trung bình</p>
                </div>
                <div>
                    <h4>🏷️ Phân loại</h4>
                    <p>Lọc theo danh mục, game, tình trạng sản phẩm</p>
                </div>
                <div>
                    <h4>📈 Sắp xếp</h4>
                    <p>Sắp xếp theo giá, độ phổ biến, mới nhất</p>
                </div>
            </div>
        </div>

        <div class="demo-section">
            <h2>🚀 Hướng dẫn sử dụng</h2>
            <ol>
                <li><strong>Xem loại sản phẩm:</strong> Truy cập "Loại sản phẩm" để xem tất cả các loại vật phẩm</li>
                <li><strong>So sánh giá:</strong> Click "So sánh giá" để xem tất cả người bán cùng sản phẩm</li>
                <li><strong>Lọc và sắp xếp:</strong> Sử dụng các bộ lọc để tìm sản phẩm phù hợp</li>
                <li><strong>Chọn người bán:</strong> So sánh giá, đánh giá và chọn người bán tốt nhất</li>
            </ol>
        </div>
    </div>
</body>
</html>
