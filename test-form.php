<?php
require_once 'config/database.php';

echo "<h2>Test Form Đăng Sản Phẩm</h2>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "<p style='color: green;'>✅ Kết nối database thành công</p>";
    
    // Kiểm tra games
    $stmt = $conn->query("SELECT * FROM games WHERE status = 'active' ORDER BY name");
    $games = $stmt->fetchAll();
    
    echo "<h3>Danh sách Games:</h3>";
    if (empty($games)) {
        echo "<p style='color: orange;'>⚠️ Chưa có game nào. Hãy chạy setup.php trước.</p>";
    } else {
        echo "<ul>";
        foreach ($games as $game) {
            echo "<li>" . htmlspecialchars($game['name']) . " (ID: " . $game['id'] . ")</li>";
        }
        echo "</ul>";
    }
    
    // Kiểm tra cấu trúc bảng products
    echo "<h3>Cấu trúc bảng products:</h3>";
    $stmt = $conn->query("DESCRIBE products");
    $columns = $stmt->fetchAll();
    
    $required_columns = ['currency', 'accept_trade', 'accept_gold', 'accept_vnd', 'trade_items', 'gold_amount', 'vnd_amount'];
    $missing_columns = [];
    
    $existing_columns = array_column($columns, 'Field');
    
    foreach ($required_columns as $col) {
        if (!in_array($col, $existing_columns)) {
            $missing_columns[] = $col;
        }
    }
    
    if (empty($missing_columns)) {
        echo "<p style='color: green;'>✅ Tất cả cột cần thiết đã có</p>";
    } else {
        echo "<p style='color: red;'>❌ Thiếu các cột: " . implode(', ', $missing_columns) . "</p>";
        echo "<p>Hãy chạy <a href='update-database.php'>update-database.php</a> để cập nhật</p>";
    }
    
    // Test currency enum
    echo "<h3>Test Currency Enum:</h3>";
    $stmt = $conn->query("SHOW COLUMNS FROM products LIKE 'currency'");
    $currency_info = $stmt->fetch();
    
    if ($currency_info) {
        echo "<p>Currency type: " . htmlspecialchars($currency_info['Type']) . "</p>";
        if (strpos($currency_info['Type'], 'GOLD') !== false && strpos($currency_info['Type'], 'ITEM') !== false) {
            echo "<p style='color: green;'>✅ Currency enum đã được cập nhật</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Currency enum chưa được cập nhật</p>";
        }
    }
    
    // Test form data
    echo "<h3>Test Form Data:</h3>";
    echo "<form method='POST' style='background: #f5f5f5; padding: 20px; border-radius: 8px;'>";
    echo "<h4>Test đăng sản phẩm:</h4>";
    
    echo "<div style='margin-bottom: 15px;'>";
    echo "<label>Đơn vị tiền tệ:</label><br>";
    echo "<select name='test_currency' style='padding: 5px; margin: 5px 0;'>";
    echo "<option value='VND'>VNĐ (Việt Nam Đồng)</option>";
    echo "<option value='GOLD'>Gold/Coin</option>";
    echo "<option value='ITEM'>Vật phẩm</option>";
    echo "</select>";
    echo "</div>";
    
    echo "<div style='margin-bottom: 15px;'>";
    echo "<label>Giá bán:</label><br>";
    echo "<input type='number' name='test_price' placeholder='Nhập giá' style='padding: 5px; margin: 5px 0;'>";
    echo "</div>";
    
    echo "<div style='margin-bottom: 15px;'>";
    echo "<label>Game:</label><br>";
    echo "<select name='test_game' style='padding: 5px; margin: 5px 0;'>";
    echo "<option value=''>Chọn game</option>";
    foreach ($games as $game) {
        echo "<option value='" . $game['id'] . "'>" . htmlspecialchars($game['name']) . "</option>";
    }
    echo "</select>";
    echo "</div>";
    
    echo "<button type='submit' style='background: #6366f1; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Test Submit</button>";
    echo "</form>";
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        echo "<h4>Kết quả test:</h4>";
        echo "<p>Currency: " . htmlspecialchars($_POST['test_currency'] ?? 'N/A') . "</p>";
        echo "<p>Price: " . htmlspecialchars($_POST['test_price'] ?? 'N/A') . "</p>";
        echo "<p>Game ID: " . htmlspecialchars($_POST['test_game'] ?? 'N/A') . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Lỗi: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='index.php'>Về trang chủ</a> | <a href='user/dang-san-pham.php'>Form đăng sản phẩm</a> | <a href='update-database.php'>Cập nhật database</a></p>";
?>
