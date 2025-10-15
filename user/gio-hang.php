<?php
require_once '../config/session.php';
require_once '../config/database.php';
require_once '../config/image-helper.php';

requireLogin();

$cart_items = [];
$total_amount = 0;

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Lấy giỏ hàng
    $stmt = $conn->prepare("
        SELECT c.*, p.name, p.price, p.description, p.seller_id, u.username as seller_name
        FROM cart c
        LEFT JOIN products p ON c.product_id = p.id
        LEFT JOIN users u ON p.seller_id = u.id
        WHERE c.user_id = ?
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $cart_items = $stmt->fetchAll();
    
    // Tính tổng tiền
    foreach ($cart_items as &$item) {
        $item['total_price'] = $item['price'] * $item['quantity'];
        $total_amount += $item['total_price'];
    }
    
} catch (Exception $e) {
    error_log("Cart page error: " . $e->getMessage());
    $cart_items = [];
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng - GameStore</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/cart.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                    <a href="gio-hang.php" class="cart-btn active">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count"><?php echo count($cart_items); ?></span>
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
    </header>

    <!-- Main Content -->
    <main class="main">
        <div class="container">
            <div class="page-header">
                <h1>Giỏ hàng</h1>
                <p>Kiểm tra và thanh toán các sản phẩm đã chọn</p>
            </div>
            
            <?php if (!empty($cart_items)): ?>
                <div class="cart-layout">
                    <!-- Cart Items -->
                    <div class="cart-items">
                        <div class="cart-header">
                            <h2>Sản phẩm trong giỏ hàng (<?php echo count($cart_items); ?>)</h2>
                        </div>
                        
                        <div class="cart-list">
                            <?php foreach ($cart_items as $item): ?>
                                <div class="cart-item" data-product-id="<?php echo $item['product_id']; ?>">
                                    <div class="item-image">
                                        <img src="<?php echo getProductImage($item['product_id']); ?>" 
                                             alt="<?php echo htmlspecialchars($item['name']); ?>">
                                    </div>
                                    
                                    <div class="item-details">
                                        <h3 class="item-name">
                                            <a href="../san-pham/chi-tiet.php?id=<?php echo $item['product_id']; ?>">
                                                <?php echo htmlspecialchars($item['name']); ?>
                                            </a>
                                        </h3>
                                        <p class="item-seller">Người bán: <?php echo htmlspecialchars($item['seller_name']); ?></p>
                                        <p class="item-description"><?php echo htmlspecialchars($item['description']); ?></p>
                                        
                                        <div class="item-actions">
                                            <button class="btn btn-outline btn-sm remove-item" data-product-id="<?php echo $item['product_id']; ?>">
                                                <i class="fas fa-trash"></i> Xóa
                                            </button>
                                            <button class="btn btn-outline btn-sm move-to-wishlist" data-product-id="<?php echo $item['product_id']; ?>">
                                                <i class="fas fa-heart"></i> Yêu thích
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="item-price">
                                        <div class="price-info">
                                            <span class="price-per-unit"><?php echo number_format($item['price']); ?>đ</span>
                                            <span class="price-label">mỗi sản phẩm</span>
                                        </div>
                                        
                                        <div class="quantity-controls">
                                            <button class="quantity-btn decrease" data-product-id="<?php echo $item['product_id']; ?>">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                            <input type="number" class="quantity-input" 
                                                   value="<?php echo $item['quantity']; ?>" 
                                                   min="1" max="99"
                                                   data-product-id="<?php echo $item['product_id']; ?>">
                                            <button class="quantity-btn increase" data-product-id="<?php echo $item['product_id']; ?>">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                        
                                        <div class="item-total">
                                            <span class="total-price"><?php echo number_format($item['total_price']); ?>đ</span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="cart-actions">
                            <button class="btn btn-outline" id="clear-cart">
                                <i class="fas fa-trash"></i> Xóa tất cả
                            </button>
                            <a href="../san-pham/" class="btn btn-outline">
                                <i class="fas fa-arrow-left"></i> Tiếp tục mua sắm
                            </a>
                        </div>
                    </div>
                    
                    <!-- Order Summary -->
                    <div class="order-summary">
                        <div class="summary-card">
                            <h3>Tóm tắt đơn hàng</h3>
                            
                            <div class="summary-details">
                                <div class="summary-row">
                                    <span>Tạm tính:</span>
                                    <span id="subtotal"><?php echo number_format($total_amount); ?>đ</span>
                                </div>
                                <div class="summary-row">
                                    <span>Phí giao dịch:</span>
                                    <span>Miễn phí</span>
                                </div>
                                <div class="summary-row">
                                    <span>Phí vận chuyển:</span>
                                    <span>Miễn phí</span>
                                </div>
                                <div class="summary-row total">
                                    <span>Tổng cộng:</span>
                                    <span id="total-amount"><?php echo number_format($total_amount); ?>đ</span>
                                </div>
                            </div>
                            
                            <div class="checkout-section">
                                <h4>Thông tin giao hàng</h4>
                                <form id="checkout-form">
                                    <div class="form-group">
                                        <label>Địa chỉ giao hàng *</label>
                                        <textarea name="shipping_address" class="form-control" 
                                                  placeholder="Nhập địa chỉ đầy đủ..." required></textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Số điện thoại *</label>
                                        <input type="tel" name="shipping_phone" class="form-control" 
                                               placeholder="Số điện thoại liên hệ..." required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Phương thức thanh toán</label>
                                        <select name="payment_method" class="form-control">
                                            <option value="cod">Thanh toán khi nhận hàng (COD)</option>
                                            <option value="bank_transfer">Chuyển khoản ngân hàng</option>
                                            <option value="momo">Ví MoMo</option>
                                            <option value="vnpay">VNPay</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Ghi chú (tùy chọn)</label>
                                        <textarea name="notes" class="form-control" 
                                                  placeholder="Ghi chú cho người bán..."></textarea>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary btn-block btn-lg" id="checkout-btn">
                                        <i class="fas fa-credit-card"></i> 
                                        Đặt hàng ngay
                                    </button>
                                </form>
                            </div>
                            
                            <div class="security-info">
                                <div class="security-item">
                                    <i class="fas fa-shield-alt"></i>
                                    <span>Giao dịch an toàn</span>
                                </div>
                                <div class="security-item">
                                    <i class="fas fa-undo"></i>
                                    <span>Hoàn tiền 100%</span>
                                </div>
                                <div class="security-item">
                                    <i class="fas fa-headset"></i>
                                    <span>Hỗ trợ 24/7</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="empty-cart">
                    <div class="empty-cart-content">
                        <i class="fas fa-shopping-cart"></i>
                        <h2>Giỏ hàng trống</h2>
                        <p>Bạn chưa có sản phẩm nào trong giỏ hàng</p>
                        <a href="../san-pham/" class="btn btn-primary btn-lg">
                            <i class="fas fa-shopping-bag"></i> Bắt đầu mua sắm
                        </a>
                    </div>
                </div>
            <?php endif; ?>
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
    <script src="../assets/js/cart.js"></script>
</body>
</html>
