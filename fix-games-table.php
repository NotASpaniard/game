<?php
require_once 'config/database.php';

echo "<h2>Sửa lỗi bảng Games - Thêm cột developer</h2>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "<p style='color: green;'>✅ Kết nối database thành công</p>";
    
    // Kiểm tra cấu trúc bảng games hiện tại
    echo "<h3>Cấu trúc bảng games hiện tại:</h3>";
    $stmt = $conn->query("DESCRIBE games");
    $columns = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr style='background: #f5f5f5;'>";
    echo "<th style='padding: 8px;'>Field</th>";
    echo "<th style='padding: 8px;'>Type</th>";
    echo "<th style='padding: 8px;'>Null</th>";
    echo "<th style='padding: 8px;'>Key</th>";
    echo "<th style='padding: 8px;'>Default</th>";
    echo "</tr>";
    
    $existing_columns = [];
    foreach ($columns as $column) {
        $existing_columns[] = $column['Field'];
        echo "<tr>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($column['Field']) . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($column['Type']) . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($column['Null']) . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($column['Key']) . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($column['Default']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Thêm các cột cần thiết
    $required_columns = [
        'developer' => "ALTER TABLE games ADD COLUMN developer VARCHAR(100)",
        'platform' => "ALTER TABLE games ADD COLUMN platform VARCHAR(50) DEFAULT 'PC'",
        'game_url' => "ALTER TABLE games ADD COLUMN game_url VARCHAR(500)",
        'icon_url' => "ALTER TABLE games ADD COLUMN icon_url VARCHAR(500)"
    ];
    
    $success_count = 0;
    $error_count = 0;
    
    echo "<h3>Thêm các cột cần thiết:</h3>";
    
    foreach ($required_columns as $column_name => $query) {
        if (in_array($column_name, $existing_columns)) {
            echo "<p style='color: orange;'>⚠️ Cột '$column_name' đã tồn tại</p>";
        } else {
            try {
                $conn->exec($query);
                echo "<p style='color: green;'>✅ Đã thêm cột '$column_name'</p>";
                $success_count++;
            } catch (PDOException $e) {
                echo "<p style='color: red;'>❌ Lỗi thêm cột '$column_name': " . $e->getMessage() . "</p>";
                $error_count++;
            }
        }
    }
    
    // Kiểm tra lại cấu trúc sau khi thêm
    echo "<h3>Cấu trúc bảng games sau khi cập nhật:</h3>";
    $stmt = $conn->query("DESCRIBE games");
    $new_columns = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr style='background: #f5f5f5;'>";
    echo "<th style='padding: 8px;'>Field</th>";
    echo "<th style='padding: 8px;'>Type</th>";
    echo "<th style='padding: 8px;'>Null</th>";
    echo "<th style='padding: 8px;'>Key</th>";
    echo "<th style='padding: 8px;'>Default</th>";
    echo "</tr>";
    
    foreach ($new_columns as $column) {
        $style = in_array($column['Field'], ['developer', 'platform', 'game_url', 'icon_url']) ? 'background: #d4edda;' : '';
        echo "<tr style='$style'>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($column['Field']) . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($column['Type']) . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($column['Null']) . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($column['Key']) . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($column['Default']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<hr>";
    echo "<p><strong>Kết quả:</strong></p>";
    echo "<p style='color: green;'>✅ Thành công: $success_count cột</p>";
    if ($error_count > 0) {
        echo "<p style='color: red;'>❌ Lỗi: $error_count cột</p>";
    }
    
    // Test thêm games
    echo "<h3>Test thêm games:</h3>";
    try {
        $stmt = $conn->prepare("
            INSERT INTO games (name, description, developer, platform, game_url, status, created_at) 
            VALUES (?, ?, ?, ?, ?, 'active', NOW())
        ");
        
        $test_games = [
            ['World//Zero', 'Anime RPG game', 'World // Zero', 'Roblox', 'https://www.roblox.com/games/2727067538/World-Zero-Anime-RPG'],
            ['Dragon Adventures', 'Game nuôi rồng', 'Sonar Studios', 'Roblox', 'https://www.roblox.com/games/3475397644/FIGHT-Dragon-Adventures']
        ];
        
        foreach ($test_games as $game) {
            $stmt->execute($game);
            echo "<p style='color: green;'>✅ Đã thêm game: " . htmlspecialchars($game[0]) . "</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Lỗi test thêm games: " . $e->getMessage() . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Lỗi: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='index.php'>Về trang chủ</a> | <a href='setup-games.php'>Setup Games</a> | <a href='test-games.php'>Test Games</a></p>";
?>
