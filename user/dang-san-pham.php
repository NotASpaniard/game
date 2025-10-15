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
    $product_condition = trim($_POST['product_condition'] ?? '');
    $delivery_method = trim($_POST['delivery_method'] ?? '');
    $accept_trade = isset($_POST['accept_trade']) ? 1 : 0;
    $accept_gold = isset($_POST['accept_gold']) ? 1 : 0;
    $accept_vnd = isset($_POST['accept_vnd']) ? 1 : 0;
    $trade_items = trim($_POST['trade_items'] ?? '');
    $gold_amount = intval($_POST['gold_amount'] ?? 0);
    $vnd_amount = intval($_POST['vnd_amount'] ?? 0);
    
    if (empty($name) || empty($description) || $price <= 0 || $game_id <= 0) {
        $error = 'Vui lòng điền đầy đủ thông tin bắt buộc';
    } else {
        try {
            $stmt = $conn->prepare("
                INSERT INTO products (
                    seller_id, name, description, price, game_id, product_condition, 
                    delivery_method, accept_trade, accept_gold, accept_vnd, 
                    trade_items, gold_amount, vnd_amount, status, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())
            ");
            $stmt->execute([
                $user['id'], $name, $description, $price, $game_id, $product_condition,
                $delivery_method, $accept_trade, $accept_gold, $accept_vnd,
                $trade_items, $gold_amount, $vnd_amount
            ]);
            
            $product_id = $conn->lastInsertId();
            
            // Xử lý upload ảnh
            if (!empty($_FILES['images']['name'][0])) {
                $upload_dir = '../images/products/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $uploaded_images = [];
                for ($i = 0; $i < count($_FILES['images']['name']); $i++) {
                    if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                        $file_extension = pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION);
                        $new_filename = "product_{$product_id}_{$i}." . $file_extension;
                        $upload_path = $upload_dir . $new_filename;
                        
                        if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $upload_path)) {
                            $uploaded_images[] = "images/products/{$new_filename}";
                        }
                    }
                }
                
                // Lưu ảnh vào database
                if (!empty($uploaded_images)) {
                    $stmt = $conn->prepare("INSERT INTO product_images (product_id, image_url, is_primary) VALUES (?, ?, ?)");
                    foreach ($uploaded_images as $index => $image_url) {
                        $stmt->execute([$product_id, $image_url, $index === 0 ? 1 : 0]);
                    }
                }
            }
            
            $success = 'Đăng sản phẩm thành công! <a href="san-pham-cua-toi.php">Xem sản phẩm của tôi</a>';
            
            // Clear form
            $_POST = [];
        } catch (Exception $e) {
            $error = 'Có lỗi xảy ra, vui lòng thử lại: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng sản phẩm - GameStore</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .form-container {
            max-width: 900px;
            margin: 0 auto;
            background: var(--white);
            padding: var(--spacing-xl);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
        }
        .form-section {
            margin-bottom: var(--spacing-2xl);
            padding: var(--spacing-lg);
            background: var(--bg-light);
            border-radius: var(--radius-md);
        }
        .form-section h3 {
            margin-bottom: var(--spacing-lg);
            color: var(--text-dark);
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: var(--spacing-sm);
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
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
            background: var(--white);
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
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: var(--spacing-md);
            margin-top: var(--spacing-md);
        }
        .preview-item {
            position: relative;
            border-radius: var(--radius-md);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }
        .preview-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }
        .preview-remove {
            position: absolute;
            top: var(--spacing-xs);
            right: var(--spacing-xs);
            background: var(--error-color);
            color: var(--white);
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .checkbox-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: var(--spacing-md);
            margin-top: var(--spacing-md);
        }
        .checkbox-item {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            padding: var(--spacing-md);
            background: var(--white);
            border-radius: var(--radius-md);
            border: 1px solid var(--border-color);
            transition: all var(--transition-fast);
        }
        .checkbox-item:hover {
            border-color: var(--primary-color);
        }
        .checkbox-item input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: var(--primary-color);
        }
        .checkbox-item label {
            margin: 0;
            cursor: pointer;
            flex-grow: 1;
        }
        .conditional-field {
            display: none;
            margin-top: var(--spacing-md);
            padding: var(--spacing-md);
            background: var(--white);
            border-radius: var(--radius-md);
            border: 1px solid var(--border-color);
        }
        .conditional-field.show {
            display: block;
        }
        .price-comparison {
            background: var(--white);
            padding: var(--spacing-md);
            border-radius: var(--radius-md);
            border: 1px solid var(--border-color);
            margin-top: var(--spacing-md);
        }
        .price-comparison h4 {
            margin: 0 0 var(--spacing-sm) 0;
            color: var(--text-dark);
        }
        .price-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--spacing-sm) 0;
            border-bottom: 1px solid var(--border-color);
        }
        .price-item:last-child {
            border-bottom: none;
        }
        .price-label {
            font-weight: 500;
            color: var(--text-dark);
        }
        .price-value {
            color: var(--cta-color);
            font-weight: 600;
        }
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            .checkbox-group {
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
                    <a href="../san-pham/loai-san-pham.php" class="nav-link">Loại sản phẩm</a>
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
                                <a href="dashboard.php">Dashboard</a>
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
                <h1>Đăng sản phẩm mới</h1>
                <p>Đăng bán vật phẩm game của bạn với giá tốt nhất</p>
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
                        <h3><i class="fas fa-info-circle"></i> Thông tin cơ bản</h3>
                        
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

                    <!-- Hình ảnh sản phẩm -->
                    <div class="form-section">
                        <h3><i class="fas fa-images"></i> Hình ảnh sản phẩm</h3>
                        <div class="image-upload" id="image-upload">
                            <i class="fas fa-cloud-upload-alt fa-3x" style="color: var(--text-light); margin-bottom: var(--spacing-md);"></i>
                            <p>Kéo thả hình ảnh vào đây hoặc click để chọn</p>
                            <p class="form-help">Hỗ trợ JPG, PNG, GIF. Tối đa 5MB mỗi ảnh. Có thể chọn nhiều ảnh</p>
                            <input type="file" id="images" name="images[]" multiple accept="image/*" style="display: none;">
                        </div>
                        <div id="image-preview" class="image-preview"></div>
                    </div>

                    <!-- Chi tiết sản phẩm -->
                    <div class="form-section">
                        <h3><i class="fas fa-cogs"></i> Chi tiết sản phẩm</h3>
                        
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

                    <!-- Phương thức thanh toán -->
                    <div class="form-section">
                        <h3><i class="fas fa-exchange-alt"></i> Phương thức thanh toán</h3>
                        <p>Chọn các phương thức thanh toán bạn chấp nhận:</p>
                        
                        <div class="checkbox-group">
                            <div class="checkbox-item">
                                <input type="checkbox" id="accept_vnd" name="accept_vnd" value="1" 
                                       <?php echo isset($_POST['accept_vnd']) ? 'checked' : ''; ?>>
                                <label for="accept_vnd">
                                    <strong>Tiền mặt (VNĐ)</strong>
                                    <br><small>Thanh toán bằng tiền mặt</small>
                                </label>
                            </div>
                            
                            <div class="checkbox-item">
                                <input type="checkbox" id="accept_trade" name="accept_trade" value="1" 
                                       <?php echo isset($_POST['accept_trade']) ? 'checked' : ''; ?>>
                                <label for="accept_trade">
                                    <strong>Đổi vật phẩm</strong>
                                    <br><small>Đổi bằng vật phẩm khác</small>
                                </label>
                            </div>
                            
                            <div class="checkbox-item">
                                <input type="checkbox" id="accept_gold" name="accept_gold" value="1" 
                                       <?php echo isset($_POST['accept_gold']) ? 'checked' : ''; ?>>
                                <label for="accept_gold">
                                    <strong>Gold/Coin</strong>
                                    <br><small>Thanh toán bằng gold/coin trong game</small>
                                </label>
                            </div>
                        </div>

                        <!-- Điều kiện đổi vật phẩm -->
                        <div class="conditional-field" id="trade-items-field">
                            <label for="trade_items">Vật phẩm muốn đổi:</label>
                            <textarea id="trade_items" name="trade_items" class="form-control" 
                                      placeholder="Mô tả chi tiết vật phẩm bạn muốn đổi..."><?php echo htmlspecialchars($_POST['trade_items'] ?? ''); ?></textarea>
                            <div class="form-help">Ví dụ: AK-47 Redline + M4A4 Howl, hoặc skin có giá trị tương đương</div>
                        </div>

                        <!-- Số lượng gold/coin -->
                        <div class="conditional-field" id="gold-amount-field">
                            <label for="gold_amount">Số lượng Gold/Coin:</label>
                            <input type="number" id="gold_amount" name="gold_amount" class="form-control" 
                                   value="<?php echo htmlspecialchars($_POST['gold_amount'] ?? ''); ?>" 
                                   min="0" placeholder="Nhập số lượng gold/coin">
                            <div class="form-help">Số lượng gold/coin bạn chấp nhận</div>
                        </div>

                        <!-- Giá VNĐ -->
                        <div class="conditional-field" id="vnd-amount-field">
                            <label for="vnd_amount">Giá VNĐ:</label>
                            <input type="number" id="vnd_amount" name="vnd_amount" class="form-control" 
                                   value="<?php echo htmlspecialchars($_POST['vnd_amount'] ?? ''); ?>" 
                                   min="0" placeholder="Nhập giá VNĐ">
                            <div class="form-help">Giá bằng tiền mặt (nếu khác với giá chính)</div>
                        </div>
                    </div>

                    <!-- Tóm tắt -->
                    <div class="form-section">
                        <h3><i class="fas fa-list-check"></i> Tóm tắt</h3>
                        <div class="price-comparison">
                            <h4>Phương thức thanh toán được chấp nhận:</h4>
                            <div id="payment-summary">
                                <p>Chưa chọn phương thức nào</p>
                            </div>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="form-section">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-upload"></i> Đăng sản phẩm
                        </button>
                        <a href="san-pham-cua-toi.php" class="btn btn-outline btn-lg">
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
                <p>&copy; 2024 GameStore. Tất cả quyền được bảo lưu.</p>
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
                        const previewItem = document.createElement('div');
                        previewItem.className = 'preview-item';
                        previewItem.innerHTML = `
                            <img src="${e.target.result}" alt="Preview">
                            <button type="button" class="preview-remove" onclick="removePreview(this)">
                                <i class="fas fa-times"></i>
                            </button>
                        `;
                        imagePreview.appendChild(previewItem);
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        function removePreview(button) {
            button.closest('.preview-item').remove();
        }

        // Conditional fields
        const acceptTrade = document.getElementById('accept_trade');
        const acceptGold = document.getElementById('accept_gold');
        const acceptVnd = document.getElementById('accept_vnd');
        const tradeItemsField = document.getElementById('trade-items-field');
        const goldAmountField = document.getElementById('gold-amount-field');
        const vndAmountField = document.getElementById('vnd-amount-field');

        function toggleConditionalFields() {
            tradeItemsField.classList.toggle('show', acceptTrade.checked);
            goldAmountField.classList.toggle('show', acceptGold.checked);
            vndAmountField.classList.toggle('show', acceptVnd.checked);
            updatePaymentSummary();
        }

        acceptTrade.addEventListener('change', toggleConditionalFields);
        acceptGold.addEventListener('change', toggleConditionalFields);
        acceptVnd.addEventListener('change', toggleConditionalFields);

        function updatePaymentSummary() {
            const summary = document.getElementById('payment-summary');
            const methods = [];
            
            if (acceptVnd.checked) {
                const vndAmount = document.getElementById('vnd_amount').value;
                methods.push(`💰 Tiền mặt: ${vndAmount ? vndAmount + 'đ' : 'Giá chính'}`);
            }
            
            if (acceptTrade.checked) {
                const tradeItems = document.getElementById('trade_items').value;
                methods.push(`🔄 Đổi vật phẩm: ${tradeItems || 'Chưa mô tả'}`);
            }
            
            if (acceptGold.checked) {
                const goldAmount = document.getElementById('gold_amount').value;
                methods.push(`🪙 Gold/Coin: ${goldAmount || 'Chưa nhập'}`);
            }
            
            if (methods.length === 0) {
                summary.innerHTML = '<p>Chưa chọn phương thức nào</p>';
            } else {
                summary.innerHTML = methods.map(method => `<div class="price-item"><span class="price-label">${method}</span></div>`).join('');
            }
        }

        // Update summary when values change
        document.getElementById('trade_items').addEventListener('input', updatePaymentSummary);
        document.getElementById('gold_amount').addEventListener('input', updatePaymentSummary);
        document.getElementById('vnd_amount').addEventListener('input', updatePaymentSummary);

        // Initialize
        toggleConditionalFields();
    </script>
</body>
</html>
