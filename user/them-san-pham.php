<?php
require_once '../config/session.php';
require_once '../config/database.php';

requireLogin();

$user = getCurrentUser();
$success = '';
$error = '';

// Lấy danh sách game và danh mục
$games = [];
$categories = [];

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $stmt = $conn->query("SELECT * FROM games WHERE status = 'active' ORDER BY name");
    $games = $stmt->fetchAll();
    
    $stmt = $conn->query("SELECT * FROM game_categories WHERE status = 'active' ORDER BY name");
    $categories = $stmt->fetchAll();
    
} catch (Exception $e) {
    error_log("Add product page error: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $game_id = intval($_POST['game_id'] ?? 0);
    $category_id = intval($_POST['category_id'] ?? 0);
    $product_condition = trim($_POST['product_condition'] ?? '');
    $delivery_method = trim($_POST['delivery_method'] ?? '');
    
    if (empty($name) || empty($description) || $price <= 0 || $game_id <= 0) {
        $error = 'Vui lòng điền đầy đủ thông tin bắt buộc';
    } else {
        try {
            $stmt = $conn->prepare("
                INSERT INTO products (seller_id, name, description, price, game_id, product_condition, delivery_method, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'active', NOW())
            ");
            $stmt->execute([$user['id'], $name, $description, $price, $game_id, $product_condition, $delivery_method]);
            
            $product_id = $conn->lastInsertId();
            $success = 'Thêm sản phẩm thành công! <a href="san-pham-cua-toi.php">Xem sản phẩm của tôi</a>';
            
            // Clear form
            $_POST = [];
        } catch (Exception $e) {
            $error = 'Có lỗi xảy ra, vui lòng thử lại';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm sản phẩm - GameStore</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .form-container {
            max-width: 800px;
            margin: 0 auto;
            background: var(--white);
            padding: var(--spacing-xl);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
        }
        .form-section {
            margin-bottom: var(--spacing-xl);
        }
        .form-section h3 {
            margin-bottom: var(--spacing-lg);
            color: var(--text-dark);
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: var(--spacing-sm);
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--spacing-lg);
        }
        .form-group {
            margin-bottom: var(--spacing-lg);
        }
        .form-group label {
            display: block;
            margin-bottom: var(--spacing-sm);
            font-weight: 500;
            color: var(--text-dark);
        }
        .form-group label.required::after {
            content: ' *';
            color: #dc2626;
        }
        .form-control {
            width: 100%;
            padding: var(--spacing-sm) var(--spacing-md);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            font-size: 1rem;
            transition: all var(--transition-fast);
        }
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
        textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }
        .form-help {
            font-size: 0.875rem;
            color: var(--text-light);
            margin-top: var(--spacing-xs);
        }
        .image-upload {
            border: 2px dashed var(--border-color);
            border-radius: var(--radius-md);
            padding: var(--spacing-xl);
            text-align: center;
            transition: all var(--transition-fast);
        }
        .image-upload:hover {
            border-color: var(--primary-color);
            background: var(--bg-light);
        }
        .image-upload.dragover {
            border-color: var(--primary-color);
            background: rgba(99, 102, 241, 0.1);
        }
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="../index.php">
                        <i class="fas fa-gamepad"></i>
                        <span>GameStore</span>
                    </a>
                </div>
                
                <nav class="nav">
                    <a href="../index.php" class="nav-link">Trang chủ</a>
                    <a href="../san-pham/" class="nav-link">Sản phẩm</a>
                    <a href="../danh-muc/" class="nav-link">Danh mục</a>
                    <a href="../huong-dan.php" class="nav-link">Hướng dẫn</a>
                    <a href="../lien-he.php" class="nav-link">Liên hệ</a>
                </nav>
                
                <div class="header-actions">
                    <div class="search-box">
                        <form action="../san-pham/" method="GET">
                            <input type="text" name="search" placeholder="Tìm kiếm vật phẩm..." class="search-input">
                            <button type="submit" class="search-btn">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>
                    
                    <div class="user-actions">
                        <a href="gio-hang.php" class="cart-btn">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="cart-count" id="cart-count">0</span>
                        </a>
                        <div class="user-menu">
                            <button class="user-btn">
                                <i class="fas fa-user"></i>
                                <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>
                            </button>
                            <div class="user-dropdown">
                                <a href="tai-khoan.php">Tài khoản</a>
                                <a href="don-hang.php">Đơn hàng</a>
                                <a href="yeu-thich.php">Yêu thích</a>
                                <a href="../auth/dang-xuat.php">Đăng xuất</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main">
        <div class="container">
            <div class="page-header">
                <h1>Thêm sản phẩm mới</h1>
                <p>Đăng bán vật phẩm game của bạn</p>
            </div>

            <div class="form-container">
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <!-- Thông tin cơ bản -->
                    <div class="form-section">
                        <h3>Thông tin cơ bản</h3>
                        
                        <div class="form-group">
                            <label for="name" class="required">Tên sản phẩm</label>
                            <input type="text" id="name" name="name" class="form-control" 
                                   value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                            <div class="form-help">Tên sản phẩm rõ ràng, dễ hiểu</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="description" class="required">Mô tả sản phẩm</label>
                            <textarea id="description" name="description" class="form-control" required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                            <div class="form-help">Mô tả chi tiết về sản phẩm, tình trạng, cách sử dụng</div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="price" class="required">Giá bán (VNĐ)</label>
                                <input type="number" id="price" name="price" class="form-control" 
                                       value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>" 
                                       min="1000" step="1000" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="game_id" class="required">Game</label>
                                <select id="game_id" name="game_id" class="form-control" required>
                                    <option value="">Chọn game</option>
                                    <?php foreach ($games as $game): ?>
                                        <option value="<?php echo $game['id']; ?>" 
                                                <?php echo ($_POST['game_id'] ?? '') == $game['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($game['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Chi tiết sản phẩm -->
                    <div class="form-section">
                        <h3>Chi tiết sản phẩm</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="product_condition">Tình trạng</label>
                                <select id="product_condition" name="product_condition" class="form-control">
                                    <option value="">Chọn tình trạng</option>
                                    <option value="new" <?php echo ($_POST['product_condition'] ?? '') === 'new' ? 'selected' : ''; ?>>Mới</option>
                                    <option value="like_new" <?php echo ($_POST['product_condition'] ?? '') === 'like_new' ? 'selected' : ''; ?>>Như mới</option>
                                    <option value="good" <?php echo ($_POST['product_condition'] ?? '') === 'good' ? 'selected' : ''; ?>>Tốt</option>
                                    <option value="fair" <?php echo ($_POST['product_condition'] ?? '') === 'fair' ? 'selected' : ''; ?>>Khá</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="delivery_method">Phương thức giao hàng</label>
                                <select id="delivery_method" name="delivery_method" class="form-control">
                                    <option value="">Chọn phương thức</option>
                                    <option value="in_game" <?php echo ($_POST['delivery_method'] ?? '') === 'in_game' ? 'selected' : ''; ?>>Giao trong game</option>
                                    <option value="account_transfer" <?php echo ($_POST['delivery_method'] ?? '') === 'account_transfer' ? 'selected' : ''; ?>>Chuyển tài khoản</option>
                                    <option value="code" <?php echo ($_POST['delivery_method'] ?? '') === 'code' ? 'selected' : ''; ?>>Gửi code</option>
                                    <option value="other" <?php echo ($_POST['delivery_method'] ?? '') === 'other' ? 'selected' : ''; ?>>Khác</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Hình ảnh -->
                    <div class="form-section">
                        <h3>Hình ảnh sản phẩm</h3>
                        <div class="image-upload" id="image-upload">
                            <i class="fas fa-cloud-upload-alt fa-3x" style="color: var(--text-light); margin-bottom: var(--spacing-md);"></i>
                            <p>Kéo thả hình ảnh vào đây hoặc click để chọn</p>
                            <p class="form-help">Hỗ trợ JPG, PNG, GIF. Tối đa 5MB mỗi ảnh</p>
                            <input type="file" id="images" name="images[]" multiple accept="image/*" style="display: none;">
                        </div>
                        <div id="image-preview" class="image-preview"></div>
                    </div>

                    <!-- Lưu -->
                    <div class="form-section">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-save"></i> Đăng sản phẩm
                        </button>
                        <a href="san-pham-cua-toi.php" class="btn btn-outline btn-block">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>GameStore</h3>
                    <p>Nền tảng giao dịch vật phẩm game uy tín và an toàn</p>
                </div>
                <div class="footer-section">
                    <h4>Liên kết nhanh</h4>
                    <ul>
                        <li><a href="../san-pham/">Sản phẩm</a></li>
                        <li><a href="../danh-muc/">Danh mục</a></li>
                        <li><a href="../huong-dan.php">Hướng dẫn</a></li>
                        <li><a href="../lien-he.php">Liên hệ</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Hỗ trợ</h4>
                    <ul>
                        <li><a href="../tro-giup.php">Trợ giúp</a></li>
                        <li><a href="../dieu-khoan.php">Điều khoản</a></li>
                        <li><a href="../bao-mat.php">Bảo mật</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 GameStore. Tất cả quyền được bảo lưu.</p>
            </div>
        </div>
    </footer>

    <script src="../assets/js/main.js"></script>
    <script>
        // Image upload handling
        const imageUpload = document.getElementById('image-upload');
        const fileInput = document.getElementById('images');
        const imagePreview = document.getElementById('image-preview');

        imageUpload.addEventListener('click', () => fileInput.click());
        imageUpload.addEventListener('dragover', (e) => {
            e.preventDefault();
            imageUpload.classList.add('dragover');
        });
        imageUpload.addEventListener('dragleave', () => {
            imageUpload.classList.remove('dragover');
        });
        imageUpload.addEventListener('drop', (e) => {
            e.preventDefault();
            imageUpload.classList.remove('dragover');
            const files = e.dataTransfer.files;
            handleFiles(files);
        });

        fileInput.addEventListener('change', (e) => {
            handleFiles(e.target.files);
        });

        function handleFiles(files) {
            Array.from(files).forEach(file => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.style.width = '100px';
                        img.style.height = '100px';
                        img.style.objectFit = 'cover';
                        img.style.borderRadius = 'var(--radius-sm)';
                        img.style.margin = 'var(--spacing-xs)';
                        imagePreview.appendChild(img);
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    </script>
</body>
</html>
