<?php
require_once 'config/database.php';

echo "<h2>Test Games - World//Zero & Dragon Adventures</h2>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "<p style='color: green;'>✅ Kết nối database thành công</p>";
    
    // Kiểm tra games
    $stmt = $conn->query("SELECT * FROM games WHERE status = 'active' ORDER BY name");
    $games = $stmt->fetchAll();
    
    echo "<h3>Danh sách Games hiện tại:</h3>";
    if (empty($games)) {
        echo "<p style='color: orange;'>⚠️ Chưa có game nào. Hãy chạy <a href='setup-games.php'>setup-games.php</a> trước.</p>";
    } else {
        echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0;'>";
        
        foreach ($games as $game) {
            echo "<div style='border: 2px solid #007bff; border-radius: 8px; padding: 20px; background: linear-gradient(135deg, #f8f9fa, #e9ecef);'>";
            echo "<h4 style='margin: 0 0 15px 0; color: #007bff; display: flex; align-items: center;'>";
            echo "<i class='fas fa-gamepad' style='margin-right: 10px;'></i>";
            echo htmlspecialchars($game['name']);
            echo "</h4>";
            echo "<p style='margin: 5px 0; color: #666;'><strong>Developer:</strong> " . htmlspecialchars($game['developer']) . "</p>";
            echo "<p style='margin: 5px 0; color: #666;'><strong>Platform:</strong> " . htmlspecialchars($game['platform']) . "</p>";
            echo "<p style='margin: 10px 0; color: #555; font-size: 14px;'>" . htmlspecialchars($game['description']) . "</p>";
            echo "<a href='" . htmlspecialchars($game['game_url']) . "' target='_blank' style='background: #007bff; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; display: inline-block; margin-top: 10px;'>";
            echo "<i class='fas fa-external-link-alt' style='margin-right: 5px;'></i>Mở game";
            echo "</a>";
            echo "</div>";
        }
        echo "</div>";
    }
    
    // Test form với games mới
    echo "<h3>Test Form Đăng Sản Phẩm:</h3>";
    echo "<form method='POST' style='background: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #dee2e6;'>";
    echo "<h4>Test đăng sản phẩm cho 2 games:</h4>";
    
    echo "<div style='margin-bottom: 15px;'>";
    echo "<label style='display: block; margin-bottom: 5px; font-weight: bold;'>Tên sản phẩm:</label>";
    echo "<input type='text' name='test_name' placeholder='Ví dụ: Legendary Sword' style='width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;'>";
    echo "</div>";
    
    echo "<div style='margin-bottom: 15px;'>";
    echo "<label style='display: block; margin-bottom: 5px; font-weight: bold;'>Game:</label>";
    echo "<select name='test_game' style='width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;'>";
    echo "<option value=''>Chọn game</option>";
    foreach ($games as $game) {
        echo "<option value='" . $game['id'] . "'>" . htmlspecialchars($game['name']) . "</option>";
    }
    echo "</select>";
    echo "</div>";
    
    echo "<div style='margin-bottom: 15px;'>";
    echo "<label style='display: block; margin-bottom: 5px; font-weight: bold;'>Phương thức thanh toán:</label>";
    echo "<div style='display: flex; gap: 20px; flex-wrap: wrap;'>";
    echo "<label style='display: flex; align-items: center; gap: 5px;'><input type='checkbox' name='test_vnd' value='1'> VNĐ</label>";
    echo "<label style='display: flex; align-items: center; gap: 5px;'><input type='checkbox' name='test_gold' value='1'> Gold/Coin</label>";
    echo "<label style='display: flex; align-items: center; gap: 5px;'><input type='checkbox' name='test_trade' value='1'> Đổi vật phẩm</label>";
    echo "</div>";
    echo "</div>";
    
    echo "<button type='submit' style='background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px;'>";
    echo "<i class='fas fa-upload' style='margin-right: 5px;'></i>Test Submit";
    echo "</button>";
    echo "</form>";
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        echo "<h4>Kết quả test:</h4>";
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; padding: 15px; margin: 10px 0;'>";
        echo "<p><strong>Tên sản phẩm:</strong> " . htmlspecialchars($_POST['test_name'] ?? 'N/A') . "</p>";
        echo "<p><strong>Game ID:</strong> " . htmlspecialchars($_POST['test_game'] ?? 'N/A') . "</p>";
        echo "<p><strong>Phương thức:</strong> ";
        $methods = [];
        if (isset($_POST['test_vnd'])) $methods[] = 'VNĐ';
        if (isset($_POST['test_gold'])) $methods[] = 'Gold/Coin';
        if (isset($_POST['test_trade'])) $methods[] = 'Đổi vật phẩm';
        echo implode(', ', $methods) ?: 'Chưa chọn';
        echo "</p>";
        echo "</div>";
    }
    
    // Hiển thị thống kê
    echo "<h3>Thống kê hệ thống:</h3>";
    
    $stats = [];
    $stats['total_games'] = $conn->query("SELECT COUNT(*) FROM games")->fetchColumn();
    $stats['total_products'] = $conn->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $stats['total_users'] = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
    
    echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin: 20px 0;'>";
    echo "<div style='background: #007bff; color: white; padding: 20px; border-radius: 8px; text-align: center;'>";
    echo "<h3 style='margin: 0; font-size: 2em;'>" . $stats['total_games'] . "</h3>";
    echo "<p style='margin: 5px 0 0 0;'>Games</p>";
    echo "</div>";
    echo "<div style='background: #28a745; color: white; padding: 20px; border-radius: 8px; text-align: center;'>";
    echo "<h3 style='margin: 0; font-size: 2em;'>" . $stats['total_products'] . "</h3>";
    echo "<p style='margin: 5px 0 0 0;'>Sản phẩm</p>";
    echo "</div>";
    echo "<div style='background: #ffc107; color: #212529; padding: 20px; border-radius: 8px; text-align: center;'>";
    echo "<h3 style='margin: 0; font-size: 2em;'>" . $stats['total_users'] . "</h3>";
    echo "<p style='margin: 5px 0 0 0;'>Users</p>";
    echo "</div>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Lỗi: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='index.php'>Về trang chủ</a> | <a href='setup-games.php'>Setup Games</a> | <a href='user/dang-san-pham.php'>Form đăng sản phẩm</a> | <a href='demo-san-pham.php'>Demo sản phẩm</a></p>";
?>
