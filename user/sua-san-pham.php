<?php
require_once '../config/session.php';
require_once '../config/database.php';

requireLogin();

$user = getCurrentUser();
$success = '';
$error = '';
$product = null;

// Lấy ID sản phẩm từ URL
$product_id = intval($_GET['id'] ?? 0);

if ($product_id <= 0) {
    header('Location: san-pham-cua-toi.php');
    exit();
}

// Lấy thông tin sản phẩm
try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("
        SELECT p.*, g.name as game_name
        FROM products p
        LEFT JOIN games g ON p.game_id = g.id
        WHERE p.id = ? AND p.seller_id = ?
    ");
    $stmt->execute([$product_id, $user['id']]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        header('Location: san-pham-cua-toi.php');
        exit();
    }
    
} catch (Exception $e) {
    error_log("Edit product page error: " . $e->getMessage());
    header('Location: san-pham-cua-toi.php');
    exit();
}

// Lấy danh sách game
$games = [];
try {
    $stmt = $conn->query("SELECT * FROM games WHERE status = 'active' ORDER BY name");
    $games = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Get games error: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $game_id = intval($_POST['game_id'] ?? 0);
    $accept_trade = isset($_POST['accept_trade']) ? 1 : 0;
    $accept_gold = isset($_POST['accept_gold']) ? 1 : 0;
    $accept_vnd = isset($_POST['accept_vnd']) ? 1 : 0;
    $trade_items = trim($_POST['trade_items'] ?? '');
    $gold_amount = intval($_POST['gold_amount'] ?? 0);
    $vnd_amount = intval($_POST['vnd_amount'] ?? 0);
    
    // Tạo mô tả từ thông tin thanh toán
    $description_parts = [];
    if ($accept_vnd && $vnd_amount > 0) {
        $description_parts[] = "Giá VNĐ: " . number_format($vnd_amount) . "đ";
    }
    if ($accept_gold && $gold_amount > 0) {
        $description_parts[] = "Giá Gold: " . number_format($gold_amount);
    }
    if ($accept_trade && !empty($trade_items)) {
        $description_parts[] = "Đổi: " . $trade_items;
    }
    $description = implode(" | ", $description_parts);
    
    if (empty($name) || $game_id <= 0 || (!$accept_vnd && !$accept_gold && !$accept_trade)) {
        $error = 'Vui lòng điền đầy đủ thông tin bắt buộc và chọn ít nhất một phương thức thanh toán';
    } else {
        try {
            // Xác định currency và price chính
            $currency = 'VND';
            $price = 0;
            if ($accept_vnd && $vnd_amount > 0) {
                $currency = 'VND';
                $price = $vnd_amount;
            } elseif ($accept_gold && $gold_amount > 0) {
                $currency = 'GOLD';
                $price = $gold_amount;
            } elseif ($accept_trade && !empty($trade_items)) {
                $currency = 'ITEM';
                $price = 0;
            }
            
            $stmt = $conn->prepare("
                UPDATE products SET 
                    name = ?, description = ?, price = ?, currency = ?, game_id = ?,
                    accept_trade = ?, accept_gold = ?, accept_vnd = ?,
                    trade_items = ?, gold_amount = ?, vnd_amount = ?, updated_at = NOW()
                WHERE id = ? AND seller_id = ?
            ");
            $stmt->execute([
                $name, $description, $price, $currency, $game_id,
                $accept_trade, $accept_gold, $accept_vnd,
                $trade_items, $gold_amount, $vnd_amount,
                $product_id, $user['id']
            ]);
            
            // Xử lý upload ảnh mới
            if (!empty($_FILES['images']['name'][0])) {
                $upload_dir = '../images/products/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $uploaded_images = [];
                for ($i = 0; $i < count($_FILES['images']['name']); $i++) {
                    if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                        $file_extension = pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION);
                        $new_filename = "product_{$product_id}_{$i}_" . time() . "." . $file_extension;
                        $upload_path = $upload_dir . $new_filename;
                        
                        if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $upload_path)) {
                            $uploaded_images[] = "images/products/{$new_filename}";
                        }
                    }
                }
                
                // Lưu ảnh mới vào database
                if (!empty($uploaded_images)) {
                    $stmt = $conn->prepare("INSERT INTO product_images (product_id, image_url, is_primary) VALUES (?, ?, ?)");
                    foreach ($uploaded_images as $index => $image_url) {
                        $stmt->execute([$product_id, $image_url, 0]); // Không set làm ảnh chính
                    }
                }
            }
            
            $success = 'Đã cập nhật sản phẩm thành công!';
            
            // Cập nhật lại thông tin sản phẩm
            $stmt = $conn->prepare("
                SELECT p.*, g.name as game_name
                FROM products p
                LEFT JOIN games g ON p.game_id = g.id
                WHERE p.id = ? AND p.seller_id = ?
            ");
            $stmt->execute([$product_id, $user['id']]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            $error = 'Có lỗi xảy ra khi cập nhật sản phẩm: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa sản phẩm - GameStore</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/products.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .form-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-dark);
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 1rem;
        }
        .form-group textarea {
            height: 100px;
            resize: vertical;
        }
        .payment-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin: 1rem 0;
        }
        .payment-method {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 1rem;
            background: var(--bg-light);
        }
        .payment-method.active {
            border-color: var(--primary-color);
            background: var(--primary-light);
        }
        .payment-method input[type="checkbox"] {
            margin-right: 0.5rem;
        }
        .payment-method label {
            font-weight: 600;
            cursor: pointer;
        }
        .payment-input {
            margin-top: 0.5rem;
        }
        .payment-input input {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid var(--border-color);
            border-radius: 4px;
        }
        .btn-group {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
        .btn-group .btn {
            flex: 1;
        }
        .image-upload {
            border: 2px dashed var(--border-color);
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .image-upload:hover {
            border-color: var(--primary-color);
            background: var(--bg-light);
        }
        .image-upload.dragover {
            border-color: var(--primary-color);
            background: rgba(99, 102, 241, 0.1);
        }
        .image-preview {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
        }
        .image-preview img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid var(--border-color);
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
                    <div class="user-actions">
                        <div class="user-menu">
                            <button class="user-btn">
                                <i class="fas fa-user"></i>
                                <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>
                            </button>
                            <div class="user-dropdown">
                                <a href="tai-khoan.php">Tài khoản</a>
                                <a href="san-pham-cua-toi.php">Sản phẩm của tôi</a>
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
                <h1>Sửa sản phẩm</h1>
                <p>Cập nhật thông tin sản phẩm của bạn</p>
            </div>

            <div class="form-container">
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="name">Tên sản phẩm *</label>
                        <input type="text" id="name" name="name" 
                               value="<?php echo htmlspecialchars($product['name']); ?>" 
                               required>
                    </div>

                    <div class="form-group">
                        <label for="game_id">Game *</label>
                        <select id="game_id" name="game_id" required>
                            <option value="">Chọn game</option>
                            <?php foreach ($games as $game): ?>
                                <option value="<?php echo $game['id']; ?>" 
                                        <?php echo $game['id'] == $product['game_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($game['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Phương thức thanh toán *</label>
                        <div class="payment-methods">
                            <div class="payment-method" id="vnd-method">
                                <label>
                                    <input type="checkbox" name="accept_vnd" value="1" 
                                           <?php echo $product['accept_vnd'] ? 'checked' : ''; ?>>
                                    Thanh toán bằng VNĐ
                                </label>
                                <div class="payment-input">
                                    <input type="number" name="vnd_amount" 
                                           value="<?php echo $product['vnd_amount']; ?>" 
                                           placeholder="Nhập số tiền VNĐ">
                                </div>
                            </div>

                            <div class="payment-method" id="gold-method">
                                <label>
                                    <input type="checkbox" name="accept_gold" value="1" 
                                           <?php echo $product['accept_gold'] ? 'checked' : ''; ?>>
                                    Thanh toán bằng Gold/Coin
                                </label>
                                <div class="payment-input">
                                    <input type="number" name="gold_amount" 
                                           value="<?php echo $product['gold_amount']; ?>" 
                                           placeholder="Nhập số lượng Gold">
                                </div>
                            </div>

                            <div class="payment-method" id="trade-method">
                                <label>
                                    <input type="checkbox" name="accept_trade" value="1" 
                                           <?php echo $product['accept_trade'] ? 'checked' : ''; ?>>
                                    Đổi vật phẩm
                                </label>
                                <div class="payment-input">
                                    <textarea name="trade_items" 
                                              placeholder="Mô tả vật phẩm muốn đổi"><?php echo htmlspecialchars($product['trade_items']); ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Thêm ảnh mới</label>
                        <div class="image-upload" id="image-upload">
                            <i class="fas fa-cloud-upload-alt fa-2x" style="color: var(--text-light); margin-bottom: 10px;"></i>
                            <p>Kéo thả hình ảnh vào đây hoặc click để chọn</p>
                            <p style="font-size: 0.875rem; color: var(--text-light);">Hỗ trợ JPG, PNG, GIF. Tối đa 5MB mỗi ảnh</p>
                            <input type="file" id="images" name="images[]" multiple accept="image/*" style="display: none;">
                        </div>
                        <div id="image-preview" class="image-preview"></div>
                    </div>

                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Cập nhật sản phẩm
                        </button>
                        <a href="san-pham-cua-toi.php" class="btn btn-outline">
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
        // Toggle payment methods
        document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const method = this.closest('.payment-method');
                if (this.checked) {
                    method.classList.add('active');
                } else {
                    method.classList.remove('active');
                }
            });
        });

        // Initialize active states
        document.querySelectorAll('input[type="checkbox"]:checked').forEach(checkbox => {
            checkbox.closest('.payment-method').classList.add('active');
        });

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
                        img.style.borderRadius = '4px';
                        img.style.margin = '5px';
                        imagePreview.appendChild(img);
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    </script>
</body>
</html>
