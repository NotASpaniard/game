<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Ảnh Thực Tế</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .product-card { 
            border: 1px solid #ddd; 
            padding: 15px; 
            margin: 10px; 
            display: inline-block; 
            width: 200px;
            text-align: center;
        }
        .product-image { 
            max-width: 150px; 
            max-height: 150px; 
            border: 1px solid #ccc; 
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <h1>Test Ảnh Sản Phẩm Thực Tế</h1>
    
    <?php
    require_once 'config/database.php';
    require_once 'config/image-helper.php';
    
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        // Lấy 5 sản phẩm mới nhất
        $stmt = $conn->query("
            SELECT p.*, g.name as game_name, u.username as seller_name
            FROM products p
            LEFT JOIN games g ON p.game_id = g.id
            LEFT JOIN users u ON p.seller_id = u.id
            ORDER BY p.created_at DESC
            LIMIT 5
        ");
        $products = $stmt->fetchAll();
        
        echo "<h2>5 sản phẩm mới nhất:</h2>";
        
        foreach ($products as $product) {
            $image_url = getProductImage($product['id']);
            echo "<div class='product-card'>";
            echo "<h3>" . htmlspecialchars($product['name']) . "</h3>";
            echo "<p>Game: " . htmlspecialchars($product['game_name']) . "</p>";
            echo "<p>Seller: " . htmlspecialchars($product['seller_name']) . "</p>";
            echo "<p>Price: " . number_format($product['price']) . "đ</p>";
            echo "<p>Image URL: " . htmlspecialchars($image_url) . "</p>";
            echo "<p>File exists: " . (file_exists($image_url) ? "✅ Yes" : "❌ No") . "</p>";
            
            if (file_exists($image_url)) {
                echo "<img src='" . htmlspecialchars($image_url) . "' class='product-image' alt='" . htmlspecialchars($product['name']) . "'>";
            } else {
                echo "<p style='color: red;'>❌ Không thể hiển thị ảnh</p>";
            }
            echo "</div>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Lỗi: " . $e->getMessage() . "</p>";
    }
    ?>
    
    <h2>Test Links:</h2>
    <p><a href="user/san-pham-cua-toi.php" target="_blank">Sản phẩm của tôi</a></p>
    <p><a href="san-pham/index.php" target="_blank">Danh mục sản phẩm</a></p>
    <p><a href="debug-test.php" target="_blank">Debug Test</a></p>
</body>
</html>
