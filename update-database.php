<?php
require_once 'config/database.php';

echo "<h2>Cập nhật Database</h2>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "<p style='color: green;'>✅ Kết nối database thành công</p>";
    
    // Thêm các cột mới vào bảng products
    $alter_queries = [
        "ALTER TABLE products ADD COLUMN accept_trade BOOLEAN DEFAULT FALSE",
        "ALTER TABLE products ADD COLUMN accept_gold BOOLEAN DEFAULT FALSE", 
        "ALTER TABLE products ADD COLUMN accept_vnd BOOLEAN DEFAULT TRUE",
        "ALTER TABLE products ADD COLUMN trade_items TEXT",
        "ALTER TABLE products ADD COLUMN gold_amount INT DEFAULT 0",
        "ALTER TABLE products ADD COLUMN vnd_amount INT DEFAULT 0"
    ];
    
    // Cập nhật currency enum
    $update_queries = [
        "ALTER TABLE products MODIFY COLUMN currency ENUM('VND', 'USD', 'GOLD', 'ITEM') DEFAULT 'VND'"
    ];
    
    // Thêm cột cho bảng games
    $games_alter_queries = [
        "ALTER TABLE games ADD COLUMN developer VARCHAR(100)",
        "ALTER TABLE games ADD COLUMN platform VARCHAR(50) DEFAULT 'PC'",
        "ALTER TABLE games ADD COLUMN game_url VARCHAR(500)",
        "ALTER TABLE games ADD COLUMN icon_url VARCHAR(500)"
    ];
    
    $success_count = 0;
    $error_count = 0;
    
    foreach ($alter_queries as $query) {
        try {
            $conn->exec($query);
            echo "<p style='color: green;'>✅ " . $query . "</p>";
            $success_count++;
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                echo "<p style='color: orange;'>⚠️ Cột đã tồn tại: " . $query . "</p>";
            } else {
                echo "<p style='color: red;'>❌ Lỗi: " . $e->getMessage() . "</p>";
                $error_count++;
            }
        }
    }
    
    // Cập nhật currency enum
    foreach ($update_queries as $query) {
        try {
            $conn->exec($query);
            echo "<p style='color: green;'>✅ " . $query . "</p>";
            $success_count++;
        } catch (PDOException $e) {
            echo "<p style='color: red;'>❌ Lỗi: " . $e->getMessage() . "</p>";
            $error_count++;
        }
    }
    
    // Thêm cột cho bảng games
    foreach ($games_alter_queries as $query) {
        try {
            $conn->exec($query);
            echo "<p style='color: green;'>✅ " . $query . "</p>";
            $success_count++;
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                echo "<p style='color: orange;'>⚠️ Cột đã tồn tại: " . $query . "</p>";
            } else {
                echo "<p style='color: red;'>❌ Lỗi: " . $e->getMessage() . "</p>";
                $error_count++;
            }
        }
    }
    
    echo "<hr>";
    echo "<p><strong>Kết quả:</strong></p>";
    echo "<p style='color: green;'>✅ Thành công: $success_count câu lệnh</p>";
    if ($error_count > 0) {
        echo "<p style='color: red;'>❌ Lỗi: $error_count câu lệnh</p>";
    }
    
    // Kiểm tra cấu trúc bảng
    echo "<h3>Cấu trúc bảng products:</h3>";
    $stmt = $conn->query("DESCRIBE products");
    $columns = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Tên cột</th><th>Kiểu dữ liệu</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($column['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Default']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Lỗi: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='index.php'>Về trang chủ</a> | <a href='test-database.php'>Test Database</a></p>";
?>
