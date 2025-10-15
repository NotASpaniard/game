<?php
require_once 'config/database.php';

echo "<h2>Test Database Connection</h2>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    if ($conn) {
        echo "<p style='color: green;'>✅ Database connection: OK</p>";
        
        // Test tạo bảng
        echo "<h3>Testing table creation...</h3>";
        
        // Đọc và thực thi schema
        $sql_schema = file_get_contents('database/schema.sql');
        if ($sql_schema === false) {
            echo "<p style='color: red;'>❌ Không thể đọc file schema.sql</p>";
        } else {
            // Tách các câu lệnh SQL
            $statements = explode(';', $sql_schema);
            $success_count = 0;
            $error_count = 0;
            
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (!empty($statement)) {
                    try {
                        $conn->exec($statement);
                        $success_count++;
                    } catch (PDOException $e) {
                        if (strpos($e->getMessage(), 'already exists') === false) {
                            echo "<p style='color: orange;'>⚠️ " . $e->getMessage() . "</p>";
                            $error_count++;
                        }
                    }
                }
            }
            
            echo "<p style='color: green;'>✅ Đã thực thi $success_count câu lệnh SQL thành công</p>";
            if ($error_count > 0) {
                echo "<p style='color: orange;'>⚠️ Có $error_count lỗi (có thể do bảng đã tồn tại)</p>";
            }
        }
        
        // Test tạo dữ liệu mẫu
        echo "<h3>Testing sample data...</h3>";
        
        // Kiểm tra xem đã có dữ liệu chưa
        $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
        $user_count = $stmt->fetch()['count'];
        
        if ($user_count == 0) {
            // Tạo admin user
            $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, 'admin')");
            $stmt->execute(['admin', 'admin@gamestore.com', $admin_password, 'Admin GameStore']);
            
            echo "<p style='color: green;'>✅ Đã tạo tài khoản admin (username: admin, password: admin123)</p>";
        } else {
            echo "<p style='color: blue;'>ℹ️ Database đã có dữ liệu ($user_count users)</p>";
        }
        
        // Hiển thị thông tin database
        echo "<h3>Database Info:</h3>";
        $stmt = $conn->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "<p>Tables: " . implode(', ', $tables) . "</p>";
        
    } else {
        echo "<p style='color: red;'>❌ Database connection: FAILED</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='index.php'>Go to Homepage</a> | <a href='clear-session.php'>Clear Session</a></p>";
?>
