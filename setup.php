<?php
// Setup script for GameStore
// Chạy file này để thiết lập database và dữ liệu mẫu

require_once 'config/database.php';

$step = $_GET['step'] ?? 1;
$error = '';
$success = '';

// Kiểm tra kết nối database
try {
    $db = new Database();
    $conn = $db->getConnection();
    $db_connected = true;
} catch (Exception $e) {
    $db_connected = false;
    $error = "Không thể kết nối database: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step == 1) {
        // Tạo database
        try {
            $conn = new PDO("mysql:host=localhost", 'root', '');
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $sql = file_get_contents('database/schema.sql');
            $conn->exec($sql);
            
            $success = "Database đã được tạo thành công!";
            $step = 2;
        } catch (Exception $e) {
            $error = "Lỗi tạo database: " . $e->getMessage();
        }
    } elseif ($step == 2) {
        // Tạo admin user
        try {
            $username = $_POST['username'] ?? 'admin';
            $email = $_POST['email'] ?? 'admin@gamestore.vn';
            $password = $_POST['password'] ?? 'admin123';
            $full_name = $_POST['full_name'] ?? 'Administrator';
            
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("
                INSERT INTO users (username, email, password, full_name, role, status, email_verified, created_at) 
                VALUES (?, ?, ?, ?, 'admin', 'active', 1, NOW())
            ");
            $stmt->execute([$username, $email, $hashed_password, $full_name]);
            
            $success = "Tài khoản admin đã được tạo thành công!";
            $step = 3;
        } catch (Exception $e) {
            $error = "Lỗi tạo admin user: " . $e->getMessage();
        }
    } elseif ($step == 3) {
        // Tạo dữ liệu mẫu
        try {
            // Tạo thêm users mẫu
            $sample_users = [
                ['seller1', 'seller1@gamestore.vn', 'seller123', 'Nguyễn Văn A', 'seller'],
                ['seller2', 'seller2@gamestore.vn', 'seller123', 'Trần Thị B', 'seller'],
                ['user1', 'user1@gamestore.vn', 'user123', 'Lê Văn C', 'user'],
                ['user2', 'user2@gamestore.vn', 'user123', 'Phạm Thị D', 'user']
            ];
            
            foreach ($sample_users as $user) {
                $hashed_password = password_hash($user[2], PASSWORD_DEFAULT);
                $stmt = $conn->prepare("
                    INSERT INTO users (username, email, password, full_name, role, status, email_verified, created_at) 
                    VALUES (?, ?, ?, ?, ?, 'active', 1, NOW())
                ");
                $stmt->execute([$user[0], $user[1], $hashed_password, $user[3], $user[4]]);
            }
            
            // Tạo sản phẩm mẫu
            $sample_products = [
                [
                    'name' => 'Ak-47 Redline (Field-Tested)',
                    'description' => 'Skin AK-47 Redline chất lượng cao, đã được kiểm tra kỹ lưỡng',
                    'game_id' => 2, // Counter-Strike 2
                    'item_type_id' => 4, // Skin
                    'seller_id' => 2,
                    'price' => 150000,
                    'condition' => 'good',
                    'rarity' => 'rare'
                ],
                [
                    'name' => 'AWP Dragon Lore (Factory New)',
                    'description' => 'Skin AWP Dragon Lore cực hiếm, chất lượng xuất sắc',
                    'game_id' => 2,
                    'item_type_id' => 4,
                    'seller_id' => 2,
                    'price' => 5000000,
                    'condition' => 'new',
                    'rarity' => 'legendary'
                ],
                [
                    'name' => 'Tài khoản LOL Level 30',
                    'description' => 'Tài khoản League of Legends level 30, đầy đủ tướng',
                    'game_id' => 1, // League of Legends
                    'item_type_id' => 2, // Account
                    'seller_id' => 3,
                    'price' => 200000,
                    'condition' => 'excellent',
                    'rarity' => 'uncommon'
                ],
                [
                    'name' => 'Skin Yasuo High Noon',
                    'description' => 'Skin Yasuo High Noon cực đẹp',
                    'game_id' => 1,
                    'item_type_id' => 1, // Skin
                    'seller_id' => 3,
                    'price' => 50000,
                    'condition' => 'new',
                    'rarity' => 'epic'
                ]
            ];
            
            foreach ($sample_products as $product) {
                $stmt = $conn->prepare("
                    INSERT INTO products (name, description, game_id, item_type_id, seller_id, price, condition, rarity, status, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())
                ");
                $stmt->execute([
                    $product['name'], $product['description'], $product['game_id'], 
                    $product['item_type_id'], $product['seller_id'], $product['price'],
                    $product['condition'], $product['rarity']
                ]);
            }
            
            $success = "Dữ liệu mẫu đã được tạo thành công! Website đã sẵn sàng sử dụng.";
            $step = 4;
        } catch (Exception $e) {
            $error = "Lỗi tạo dữ liệu mẫu: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thiết lập GameStore</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .setup-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
        }
        .setup-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            padding: 2rem;
        }
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }
        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--border-color);
            color: var(--text-light);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 1rem;
            position: relative;
        }
        .step.active {
            background: var(--primary-color);
            color: var(--white);
        }
        .step.completed {
            background: var(--success-color);
            color: var(--white);
        }
        .step::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 100%;
            width: 2rem;
            height: 2px;
            background: var(--border-color);
            transform: translateY(-50%);
        }
        .step:last-child::after {
            display: none;
        }
        .step.completed::after {
            background: var(--success-color);
        }
        .setup-content {
            text-align: center;
        }
        .setup-content h1 {
            margin-bottom: 1rem;
            color: var(--text-dark);
        }
        .setup-content p {
            color: var(--text-light);
            margin-bottom: 2rem;
        }
        .form-group {
            text-align: left;
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-dark);
        }
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            font-size: 1rem;
        }
        .btn-setup {
            background: var(--primary-color);
            color: var(--white);
            padding: 0.75rem 2rem;
            border: none;
            border-radius: var(--radius-md);
            font-size: 1rem;
            cursor: pointer;
            transition: all var(--transition-fast);
        }
        .btn-setup:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }
        .setup-info {
            background: var(--secondary-color);
            padding: 1rem;
            border-radius: var(--radius-md);
            margin-bottom: 2rem;
            text-align: left;
        }
        .setup-info h3 {
            margin-bottom: 0.5rem;
            color: var(--text-dark);
        }
        .setup-info ul {
            margin: 0;
            padding-left: 1.5rem;
        }
        .setup-info li {
            margin-bottom: 0.25rem;
            color: var(--text-light);
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <div class="setup-card">
            <div class="step-indicator">
                <div class="step <?php echo $step >= 1 ? ($step > 1 ? 'completed' : 'active') : ''; ?>">1</div>
                <div class="step <?php echo $step >= 2 ? ($step > 2 ? 'completed' : 'active') : ''; ?>">2</div>
                <div class="step <?php echo $step >= 3 ? ($step > 3 ? 'completed' : 'active') : ''; ?>">3</div>
                <div class="step <?php echo $step >= 4 ? 'active' : ''; ?>">4</div>
            </div>
            
            <div class="setup-content">
                <?php if ($step == 1): ?>
                    <h1>Chào mừng đến với GameStore!</h1>
                    <p>Chúng tôi sẽ giúp bạn thiết lập website bán vật phẩm game một cách dễ dàng.</p>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="setup-info">
                        <h3>Yêu cầu hệ thống:</h3>
                        <ul>
                            <li>PHP 7.4 trở lên</li>
                            <li>MySQL 5.7 trở lên</li>
                            <li>Web server (Apache/Nginx)</li>
                            <li>Extension: PDO, PDO_MySQL</li>
                        </ul>
                    </div>
                    
                    <div class="setup-info">
                        <h3>Thông tin database:</h3>
                        <ul>
                            <li>Host: localhost</li>
                            <li>Database: gamestore_db</li>
                            <li>Username: root</li>
                            <li>Password: (để trống)</li>
                        </ul>
                    </div>
                    
                    <form method="POST">
                        <button type="submit" class="btn-setup">
                            <i class="fas fa-database"></i> Tạo Database
                        </button>
                    </form>
                    
                <?php elseif ($step == 2): ?>
                    <h1>Tạo tài khoản Admin</h1>
                    <p>Vui lòng tạo tài khoản quản trị viên cho website.</p>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <?php echo htmlspecialchars($success); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="form-group">
                            <label for="username">Tên đăng nhập:</label>
                            <input type="text" id="username" name="username" class="form-control" 
                                   value="admin" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email" class="form-control" 
                                   value="admin@gamestore.vn" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Mật khẩu:</label>
                            <input type="password" id="password" name="password" class="form-control" 
                                   value="admin123" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="full_name">Họ và tên:</label>
                            <input type="text" id="full_name" name="full_name" class="form-control" 
                                   value="Administrator" required>
                        </div>
                        
                        <button type="submit" class="btn-setup">
                            <i class="fas fa-user-plus"></i> Tạo Admin
                        </button>
                    </form>
                    
                <?php elseif ($step == 3): ?>
                    <h1>Tạo dữ liệu mẫu</h1>
                    <p>Chúng tôi sẽ tạo một số dữ liệu mẫu để bạn có thể khám phá website.</p>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <?php echo htmlspecialchars($success); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="setup-info">
                        <h3>Dữ liệu mẫu sẽ bao gồm:</h3>
                        <ul>
                            <li>4 tài khoản người dùng (2 seller, 2 user)</li>
                            <li>4 sản phẩm game mẫu</li>
                            <li>Danh mục và game mặc định</li>
                            <li>Cấu hình hệ thống cơ bản</li>
                        </ul>
                    </div>
                    
                    <form method="POST">
                        <button type="submit" class="btn-setup">
                            <i class="fas fa-database"></i> Tạo dữ liệu mẫu
                        </button>
                    </form>
                    
                <?php elseif ($step == 4): ?>
                    <h1>Hoàn thành thiết lập!</h1>
                    <p>Website GameStore đã được thiết lập thành công và sẵn sàng sử dụng.</p>
                    
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                    
                    <div class="setup-info">
                        <h3>Thông tin đăng nhập:</h3>
                        <ul>
                            <li><strong>Admin:</strong> admin / admin123</li>
                            <li><strong>Seller 1:</strong> seller1 / seller123</li>
                            <li><strong>Seller 2:</strong> seller2 / seller123</li>
                            <li><strong>User 1:</strong> user1 / user123</li>
                            <li><strong>User 2:</strong> user2 / user123</li>
                        </ul>
                    </div>
                    
                    <div style="margin-top: 2rem;">
                        <a href="index.php" class="btn-setup" style="text-decoration: none; display: inline-block;">
                            <i class="fas fa-home"></i> Truy cập Website
                        </a>
                        <a href="admin/" class="btn-setup" style="text-decoration: none; display: inline-block; margin-left: 1rem; background: var(--success-color);">
                            <i class="fas fa-cog"></i> Trang Quản Trị
                        </a>
                    </div>
                    
                    <div class="setup-info" style="margin-top: 2rem;">
                        <h3>Lưu ý quan trọng:</h3>
                        <ul>
                            <li>Hãy đổi mật khẩu admin ngay sau khi đăng nhập</li>
                            <li>Xóa file setup.php sau khi hoàn thành</li>
                            <li>Kiểm tra cấu hình bảo mật trước khi đưa lên production</li>
                            <li>Backup database thường xuyên</li>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
