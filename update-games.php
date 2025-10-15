<?php
require_once 'config/database.php';

echo "<h2>Cập nhật Games - Chỉ giữ World//Zero và Dragon Adventures</h2>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "<p style='color: green;'>✅ Kết nối database thành công</p>";
    
    // Xóa tất cả games hiện tại
    $conn->exec("DELETE FROM games");
    echo "<p style='color: orange;'>🗑️ Đã xóa tất cả games cũ</p>";
    
    // Thêm 2 games mới
    $games = [
        [
            'name' => 'World//Zero',
            'description' => 'Anime RPG game trên Roblox với hệ thống chiến đấu và nhân vật anime',
            'developer' => 'World // Zero',
            'platform' => 'Roblox',
            'game_url' => 'https://www.roblox.com/games/2727067538/World-Zero-Anime-RPG',
            'status' => 'active'
        ],
        [
            'name' => 'Dragon Adventures',
            'description' => 'Game nuôi rồng và phiêu lưu trên Roblox với hệ thống thu thập và chăm sóc rồng',
            'developer' => 'Sonar Studios',
            'platform' => 'Roblox', 
            'game_url' => 'https://www.roblox.com/games/3475397644/FIGHT-Dragon-Adventures',
            'status' => 'active'
        ]
    ];
    
    $stmt = $conn->prepare("
        INSERT INTO games (name, description, developer, platform, game_url, status, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $success_count = 0;
    foreach ($games as $game) {
        try {
            $stmt->execute([
                $game['name'],
                $game['description'], 
                $game['developer'],
                $game['platform'],
                $game['game_url'],
                $game['status']
            ]);
            echo "<p style='color: green;'>✅ Đã thêm game: " . htmlspecialchars($game['name']) . "</p>";
            $success_count++;
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Lỗi thêm game " . htmlspecialchars($game['name']) . ": " . $e->getMessage() . "</p>";
        }
    }
    
    // Hiển thị danh sách games hiện tại
    echo "<h3>Danh sách Games hiện tại:</h3>";
    $stmt = $conn->query("SELECT * FROM games ORDER BY name");
    $current_games = $stmt->fetchAll();
    
    if (empty($current_games)) {
        echo "<p style='color: orange;'>⚠️ Không có game nào</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
        echo "<tr style='background: #f5f5f5;'>";
        echo "<th style='padding: 10px;'>ID</th>";
        echo "<th style='padding: 10px;'>Tên Game</th>";
        echo "<th style='padding: 10px;'>Developer</th>";
        echo "<th style='padding: 10px;'>Platform</th>";
        echo "<th style='padding: 10px;'>Trạng thái</th>";
        echo "<th style='padding: 10px;'>Link</th>";
        echo "</tr>";
        
        foreach ($current_games as $game) {
            echo "<tr>";
            echo "<td style='padding: 10px;'>" . $game['id'] . "</td>";
            echo "<td style='padding: 10px;'><strong>" . htmlspecialchars($game['name']) . "</strong></td>";
            echo "<td style='padding: 10px;'>" . htmlspecialchars($game['developer']) . "</td>";
            echo "<td style='padding: 10px;'>" . htmlspecialchars($game['platform']) . "</td>";
            echo "<td style='padding: 10px;'>" . htmlspecialchars($game['status']) . "</td>";
            echo "<td style='padding: 10px;'><a href='" . htmlspecialchars($game['game_url']) . "' target='_blank'>Mở game</a></td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Cập nhật products để chỉ có 2 games này
    echo "<h3>Cập nhật sản phẩm:</h3>";
    
    // Lấy ID của 2 games mới
    $stmt = $conn->query("SELECT id, name FROM games ORDER BY name");
    $game_ids = $stmt->fetchAll();
    
    if (count($game_ids) >= 2) {
        // Cập nhật tất cả products có game_id không hợp lệ
        $stmt = $conn->prepare("UPDATE products SET game_id = ? WHERE game_id NOT IN (?, ?)");
        $stmt->execute([$game_ids[0]['id'], $game_ids[0]['id'], $game_ids[1]['id']]);
        
        $affected = $stmt->rowCount();
        echo "<p style='color: blue;'>ℹ️ Đã cập nhật $affected sản phẩm để sử dụng game mặc định</p>";
    }
    
    echo "<hr>";
    echo "<p><strong>Kết quả:</strong></p>";
    echo "<p style='color: green;'>✅ Thành công: $success_count games</p>";
    echo "<p style='color: blue;'>ℹ️ Chỉ còn 2 games: World//Zero và Dragon Adventures</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Lỗi: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='index.php'>Về trang chủ</a> | <a href='user/dang-san-pham.php'>Form đăng sản phẩm</a> | <a href='test-form.php'>Test form</a></p>";
?>
