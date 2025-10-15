<?php
require_once 'config/database.php';

echo "<h2>Setup Games - World//Zero & Dragon Adventures</h2>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "<p style='color: green;'>✅ Kết nối database thành công</p>";
    
    // Xóa tất cả games cũ
    $conn->exec("DELETE FROM games");
    echo "<p style='color: orange;'>🗑️ Đã xóa tất cả games cũ</p>";
    
    // Thêm 2 games mới
    $games = [
        [
            'name' => 'World//Zero',
            'description' => 'Anime RPG game trên Roblox với hệ thống chiến đấu và nhân vật anime. Game có gameplay RPG với các nhân vật anime đẹp mắt và hệ thống chiến đấu hấp dẫn.',
            'developer' => 'World // Zero',
            'platform' => 'Roblox',
            'game_url' => 'https://www.roblox.com/games/2727067538/World-Zero-Anime-RPG',
            'status' => 'active',
            'icon_url' => 'https://tr.rbxcdn.com/8b5a3b3b3b3b3b3b3b3b3b3b3b3b3b3b/512/512/Image/Png'
        ],
        [
            'name' => 'Dragon Adventures',
            'description' => 'Game nuôi rồng và phiêu lưu trên Roblox với hệ thống thu thập và chăm sóc rồng. Người chơi có thể thu thập, nuôi dưỡng và chăm sóc các loài rồng khác nhau.',
            'developer' => 'Sonar Studios',
            'platform' => 'Roblox',
            'game_url' => 'https://www.roblox.com/games/3475397644/FIGHT-Dragon-Adventures',
            'status' => 'active',
            'icon_url' => 'https://tr.rbxcdn.com/8b5a3b3b3b3b3b3b3b3b3b3b3b3b3b3b/512/512/Image/Png'
        ]
    ];
    
    $stmt = $conn->prepare("
        INSERT INTO games (name, description, developer, platform, game_url, status, icon_url, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
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
                $game['status'],
                $game['icon_url']
            ]);
            echo "<p style='color: green;'>✅ Đã thêm game: " . htmlspecialchars($game['name']) . "</p>";
            $success_count++;
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Lỗi thêm game " . htmlspecialchars($game['name']) . ": " . $e->getMessage() . "</p>";
        }
    }
    
    // Hiển thị danh sách games
    echo "<h3>Danh sách Games hiện tại:</h3>";
    $stmt = $conn->query("SELECT * FROM games ORDER BY name");
    $current_games = $stmt->fetchAll();
    
    if (empty($current_games)) {
        echo "<p style='color: orange;'>⚠️ Không có game nào</p>";
    } else {
        echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px; margin: 20px 0;'>";
        
        foreach ($current_games as $game) {
            echo "<div style='border: 1px solid #ddd; border-radius: 8px; padding: 20px; background: #f9f9f9;'>";
            echo "<h4 style='margin: 0 0 10px 0; color: #333;'>" . htmlspecialchars($game['name']) . "</h4>";
            echo "<p style='margin: 5px 0; color: #666;'><strong>Developer:</strong> " . htmlspecialchars($game['developer']) . "</p>";
            echo "<p style='margin: 5px 0; color: #666;'><strong>Platform:</strong> " . htmlspecialchars($game['platform']) . "</p>";
            echo "<p style='margin: 5px 0; color: #666;'><strong>Status:</strong> " . htmlspecialchars($game['status']) . "</p>";
            echo "<p style='margin: 10px 0; color: #555;'>" . htmlspecialchars($game['description']) . "</p>";
            echo "<a href='" . htmlspecialchars($game['game_url']) . "' target='_blank' style='background: #007bff; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; display: inline-block;'>Mở game</a>";
            echo "</div>";
        }
        echo "</div>";
    }
    
    // Cập nhật products để sử dụng game_id mới
    echo "<h3>Cập nhật sản phẩm:</h3>";
    
    // Lấy ID của games mới
    $stmt = $conn->query("SELECT id, name FROM games ORDER BY name");
    $game_ids = $stmt->fetchAll();
    
    if (count($game_ids) >= 2) {
        // Cập nhật products có game_id không hợp lệ
        $stmt = $conn->prepare("UPDATE products SET game_id = ? WHERE game_id NOT IN (?, ?)");
        $stmt->execute([$game_ids[0]['id'], $game_ids[0]['id'], $game_ids[1]['id']]);
        
        $affected = $stmt->rowCount();
        echo "<p style='color: blue;'>ℹ️ Đã cập nhật $affected sản phẩm để sử dụng game mặc định</p>";
        
        // Hiển thị thống kê sản phẩm theo game
        $stmt = $conn->query("
            SELECT g.name as game_name, COUNT(p.id) as product_count 
            FROM games g 
            LEFT JOIN products p ON g.id = p.game_id 
            GROUP BY g.id, g.name 
            ORDER BY g.name
        ");
        $stats = $stmt->fetchAll();
        
        echo "<h4>Thống kê sản phẩm theo game:</h4>";
        echo "<ul>";
        foreach ($stats as $stat) {
            echo "<li><strong>" . htmlspecialchars($stat['game_name']) . ":</strong> " . $stat['product_count'] . " sản phẩm</li>";
        }
        echo "</ul>";
    }
    
    echo "<hr>";
    echo "<p><strong>Kết quả:</strong></p>";
    echo "<p style='color: green;'>✅ Thành công: $success_count games</p>";
    echo "<p style='color: blue;'>ℹ️ Chỉ còn 2 games: World//Zero và Dragon Adventures</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Lỗi: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='index.php'>Về trang chủ</a> | <a href='user/dang-san-pham.php'>Form đăng sản phẩm</a> | <a href='demo-san-pham.php'>Demo sản phẩm</a></p>";
?>
