<?php
require_once __DIR__ . '/../config/session.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Game Trading Platform'; ?></title>
    <link rel="stylesheet" href="<?php echo $base_url ?? ''; ?>assets/css/main.css">
    <link rel="stylesheet" href="<?php echo $base_url ?? ''; ?>assets/css/home.css">
    <link rel="stylesheet" href="<?php echo $base_url ?? ''; ?>assets/css/products.css">
    <link rel="stylesheet" href="<?php echo $base_url ?? ''; ?>assets/css/cart.css">
    <link rel="stylesheet" href="<?php echo $base_url ?? ''; ?>assets/css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <!-- Logo -->
                <div class="logo">
                    <a href="<?php echo $base_url ?? ''; ?>index.php">
                        <i class="fas fa-gamepad"></i>
                        <span>Game Trading</span>
                    </a>
                </div>
                
                <!-- Navigation -->
                <nav class="nav">
                    <ul class="nav-list">
                        <li><a href="<?php echo $base_url ?? ''; ?>index.php">Trang chủ</a></li>
                        <li><a href="<?php echo $base_url ?? ''; ?>san-pham/index.php">Sản phẩm</a></li>
                        <li><a href="<?php echo $base_url ?? ''; ?>danh-muc/index.php">Danh mục</a></li>
                        <li><a href="<?php echo $base_url ?? ''; ?>tim-kiem.php">Tìm kiếm</a></li>
                    </ul>
                </nav>
                
                <!-- User Actions -->
                <div class="user-actions">
                    <?php if (isLoggedIn()): ?>
                        <!-- Notifications -->
                        <div class="notification-dropdown">
                            <button class="notification-btn" id="notificationBtn">
                                <i class="fas fa-bell"></i>
                                <span class="notification-badge" id="notificationBadge" style="display: none;">0</span>
                            </button>
                            <div class="notification-dropdown-content" id="notificationDropdown">
                                <div class="notification-header">
                                    <h4>Thông báo</h4>
                                    <button id="markAllRead" class="btn-small">Đánh dấu tất cả đã đọc</button>
                                </div>
                                <div class="notification-list" id="notificationList">
                                    <div class="loading">Đang tải...</div>
                                </div>
                                <div class="notification-footer">
                                    <a href="<?php echo $base_url ?? ''; ?>user/thong-bao.php">Xem tất cả</a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Messages -->
                        <div class="message-dropdown">
                            <a href="<?php echo $base_url ?? ''; ?>user/tin-nhan.php" class="message-btn">
                                <i class="fas fa-comments"></i>
                                <span class="message-badge" id="messageBadge" style="display: none;">0</span>
                            </a>
                        </div>
                        
                        <!-- Cart -->
                        <div class="cart-dropdown">
                            <button class="cart-btn" id="cartBtn">
                                <i class="fas fa-shopping-cart"></i>
                                <span class="cart-badge" id="cartBadge">0</span>
                            </button>
                            <div class="cart-dropdown-content" id="cartDropdown">
                                <div class="cart-header">
                                    <h4>Giỏ hàng</h4>
                                </div>
                                <div class="cart-list" id="cartList">
                                    <div class="loading">Đang tải...</div>
                                </div>
                                <div class="cart-footer">
                                    <a href="<?php echo $base_url ?? ''; ?>user/gio-hang.php" class="btn btn-primary">Xem giỏ hàng</a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- User Menu -->
                        <div class="user-dropdown">
                            <button class="user-btn">
                                <i class="fas fa-user"></i>
                                <span><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></span>
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            <div class="user-dropdown-content">
                                <a href="<?php echo $base_url ?? ''; ?>user/dashboard.php">
                                    <i class="fas fa-tachometer-alt"></i> Dashboard
                                </a>
                                <a href="<?php echo $base_url ?? ''; ?>user/san-pham-cua-toi.php">
                                    <i class="fas fa-box"></i> Sản phẩm của tôi
                                </a>
                                <a href="<?php echo $base_url ?? ''; ?>user/don-hang.php">
                                    <i class="fas fa-receipt"></i> Đơn hàng
                                </a>
                                <a href="<?php echo $base_url ?? ''; ?>user/yeu-thich.php">
                                    <i class="fas fa-heart"></i> Yêu thích
                                </a>
                                <a href="<?php echo $base_url ?? ''; ?>user/tai-khoan.php">
                                    <i class="fas fa-user-cog"></i> Tài khoản
                                </a>
                                <div class="dropdown-divider"></div>
                                <a href="<?php echo $base_url ?? ''; ?>auth/dang-xuat.php">
                                    <i class="fas fa-sign-out-alt"></i> Đăng xuất
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Guest Actions -->
                        <div class="guest-actions">
                            <a href="<?php echo $base_url ?? ''; ?>auth/dang-nhap.php" class="btn btn-outline">Đăng nhập</a>
                            <a href="<?php echo $base_url ?? ''; ?>auth/dang-ky.php" class="btn btn-primary">Đăng ký</a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Mobile Menu Toggle -->
                <button class="mobile-menu-toggle" id="mobileMenuToggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </header>
    
    <!-- Mobile Menu -->
    <div class="mobile-menu" id="mobileMenu">
        <div class="mobile-menu-content">
            <div class="mobile-menu-header">
                <h3>Menu</h3>
                <button class="mobile-menu-close" id="mobileMenuClose">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <nav class="mobile-nav">
                <ul>
                    <li><a href="<?php echo $base_url ?? ''; ?>index.php">Trang chủ</a></li>
                    <li><a href="<?php echo $base_url ?? ''; ?>san-pham/index.php">Sản phẩm</a></li>
                    <li><a href="<?php echo $base_url ?? ''; ?>danh-muc/index.php">Danh mục</a></li>
                    <li><a href="<?php echo $base_url ?? ''; ?>tim-kiem.php">Tìm kiếm</a></li>
                    <?php if (isLoggedIn()): ?>
                        <li><a href="<?php echo $base_url ?? ''; ?>user/dashboard.php">Dashboard</a></li>
                        <li><a href="<?php echo $base_url ?? ''; ?>user/san-pham-cua-toi.php">Sản phẩm của tôi</a></li>
                        <li><a href="<?php echo $base_url ?? ''; ?>user/gio-hang.php">Giỏ hàng</a></li>
                        <li><a href="<?php echo $base_url ?? ''; ?>user/yeu-thich.php">Yêu thích</a></li>
                        <li><a href="<?php echo $base_url ?? ''; ?>user/tin-nhan.php">Tin nhắn</a></li>
                        <li><a href="<?php echo $base_url ?? ''; ?>user/thong-bao.php">Thông báo</a></li>
                        <li><a href="<?php echo $base_url ?? ''; ?>auth/dang-xuat.php">Đăng xuất</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo $base_url ?? ''; ?>auth/dang-nhap.php">Đăng nhập</a></li>
                        <li><a href="<?php echo $base_url ?? ''; ?>auth/dang-ky.php">Đăng ký</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </div>
    
    <script>
        // Mobile menu toggle
        document.getElementById('mobileMenuToggle').addEventListener('click', function() {
            document.getElementById('mobileMenu').classList.add('active');
        });
        
        document.getElementById('mobileMenuClose').addEventListener('click', function() {
            document.getElementById('mobileMenu').classList.remove('active');
        });
        
        // Close mobile menu when clicking outside
        document.addEventListener('click', function(e) {
            const mobileMenu = document.getElementById('mobileMenu');
            const toggle = document.getElementById('mobileMenuToggle');
            
            if (!mobileMenu.contains(e.target) && !toggle.contains(e.target)) {
                mobileMenu.classList.remove('active');
            }
        });
        
        // Load notifications
        function loadNotifications() {
            fetch('<?php echo $base_url ?? ''; ?>api/notifications.php?action=get')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const container = document.getElementById('notificationList');
                    const badge = document.getElementById('notificationBadge');
                    
                    if (data.notifications.length === 0) {
                        container.innerHTML = '<div class="no-notifications">Chưa có thông báo</div>';
                    } else {
                        container.innerHTML = data.notifications.slice(0, 5).map(notification => `
                            <div class="notification-item ${notification.is_read ? 'read' : 'unread'}">
                                <div class="notification-icon">
                                    <i class="fas fa-${notification.type === 'order' ? 'shopping-cart' : notification.type === 'message' ? 'comment' : 'bell'}"></i>
                                </div>
                                <div class="notification-content">
                                    <div class="notification-title">${notification.title}</div>
                                    <div class="notification-message">${notification.message}</div>
                                </div>
                            </div>
                        `).join('');
                    }
                    
                    // Update badge
                    const unreadCount = data.notifications.filter(n => !n.is_read).length;
                    if (unreadCount > 0) {
                        badge.textContent = unreadCount;
                        badge.style.display = 'inline';
                    } else {
                        badge.style.display = 'none';
                    }
                }
            });
        }
        
        // Load cart
        function loadCart() {
            fetch('<?php echo $base_url ?? ''; ?>api/cart.php?action=get')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const container = document.getElementById('cartList');
                    const badge = document.getElementById('cartBadge');
                    
                    if (data.items.length === 0) {
                        container.innerHTML = '<div class="no-items">Giỏ hàng trống</div>';
                    } else {
                        container.innerHTML = data.items.map(item => `
                            <div class="cart-item">
                                <div class="cart-item-image">
                                    <img src="${item.image}" alt="${item.name}">
                                </div>
                                <div class="cart-item-info">
                                    <div class="cart-item-name">${item.name}</div>
                                    <div class="cart-item-price">${item.price.toLocaleString()}đ</div>
                                    <div class="cart-item-quantity">Số lượng: ${item.quantity}</div>
                                </div>
                            </div>
                        `).join('');
                    }
                    
                    badge.textContent = data.items.length;
                }
            });
        }
        
        // Load message count
        function loadMessageCount() {
            fetch('<?php echo $base_url ?? ''; ?>api/messages.php?action=get_unread_count')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const badge = document.getElementById('messageBadge');
                    if (data.count > 0) {
                        badge.textContent = data.count;
                        badge.style.display = 'inline';
                    } else {
                        badge.style.display = 'none';
                    }
                }
            });
        }
        
        // Initialize
        if (<?php echo isLoggedIn() ? 'true' : 'false'; ?>) {
            loadNotifications();
            loadCart();
            loadMessageCount();
            
            // Auto refresh every 30 seconds
            setInterval(() => {
                loadNotifications();
                loadMessageCount();
            }, 30000);
        }
        
        // Dropdown toggles
        document.getElementById('notificationBtn').addEventListener('click', function(e) {
            e.stopPropagation();
            document.getElementById('notificationDropdown').classList.toggle('show');
        });
        
        document.getElementById('cartBtn').addEventListener('click', function(e) {
            e.stopPropagation();
            document.getElementById('cartDropdown').classList.toggle('show');
        });
        
        // Close dropdowns when clicking outside
        document.addEventListener('click', function() {
            document.getElementById('notificationDropdown').classList.remove('show');
            document.getElementById('cartDropdown').classList.remove('show');
        });
    </script>
    
    <style>
        .header {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 0;
        }
        
        .logo a {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: #333;
            font-size: 1.5em;
            font-weight: bold;
        }
        
        .logo i {
            color: #007bff;
        }
        
        .nav-list {
            display: flex;
            list-style: none;
            gap: 30px;
            margin: 0;
            padding: 0;
        }
        
        .nav-list a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: color 0.2s;
        }
        
        .nav-list a:hover {
            color: #007bff;
        }
        
        .user-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .notification-btn,
        .message-btn,
        .cart-btn {
            position: relative;
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            padding: 10px;
            border-radius: 50%;
            transition: all 0.2s;
        }
        
        .notification-btn:hover,
        .cart-btn:hover {
            background: #f5f5f5;
            color: #007bff;
        }
        
        .notification-badge,
        .message-badge,
        .cart-badge {
            position: absolute;
            top: 5px;
            right: 5px;
            background: #e74c3c;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7em;
            font-weight: bold;
        }
        
        .notification-dropdown,
        .cart-dropdown {
            position: relative;
        }
        
        .notification-dropdown-content,
        .cart-dropdown-content {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            min-width: 300px;
            max-height: 400px;
            overflow-y: auto;
            display: none;
            z-index: 1001;
        }
        
        .notification-dropdown-content.show,
        .cart-dropdown-content.show {
            display: block;
        }
        
        .notification-header,
        .cart-header {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .notification-list,
        .cart-list {
            max-height: 300px;
            overflow-y: auto;
        }
        
        .notification-item {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .notification-item.unread {
            background: #f8f9ff;
        }
        
        .notification-icon {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: #666;
        }
        
        .notification-item.unread .notification-icon {
            background: #007bff;
            color: white;
        }
        
        .notification-content {
            flex: 1;
        }
        
        .notification-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        
        .notification-message {
            color: #666;
            font-size: 0.9em;
            line-height: 1.4;
        }
        
        .cart-item {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .cart-item-image {
            width: 50px;
            height: 50px;
            border-radius: 5px;
            overflow: hidden;
            margin-right: 15px;
        }
        
        .cart-item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .cart-item-info {
            flex: 1;
        }
        
        .cart-item-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        
        .cart-item-price {
            color: #e74c3c;
            font-weight: 600;
        }
        
        .cart-item-quantity {
            color: #666;
            font-size: 0.9em;
        }
        
        .notification-footer,
        .cart-footer {
            padding: 15px 20px;
            border-top: 1px solid #eee;
            text-align: center;
        }
        
        .notification-footer a,
        .cart-footer a {
            color: #007bff;
            text-decoration: none;
        }
        
        .user-dropdown {
            position: relative;
        }
        
        .user-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            background: none;
            border: none;
            color: #333;
            cursor: pointer;
            padding: 8px 15px;
            border-radius: 20px;
            transition: all 0.2s;
        }
        
        .user-btn:hover {
            background: #f5f5f5;
        }
        
        .user-dropdown-content {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            min-width: 200px;
            display: none;
            z-index: 1001;
        }
        
        .user-dropdown:hover .user-dropdown-content {
            display: block;
        }
        
        .user-dropdown-content a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 20px;
            color: #333;
            text-decoration: none;
            transition: background 0.2s;
        }
        
        .user-dropdown-content a:hover {
            background: #f5f5f5;
        }
        
        .dropdown-divider {
            height: 1px;
            background: #eee;
            margin: 5px 0;
        }
        
        .guest-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
        }
        
        .btn-outline {
            background: transparent;
            color: #007bff;
            border: 1px solid #007bff;
        }
        
        .btn-small {
            background: none;
            border: none;
            color: #007bff;
            cursor: pointer;
            font-size: 0.8em;
        }
        
        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            color: #333;
            cursor: pointer;
            padding: 10px;
            font-size: 1.2em;
        }
        
        .mobile-menu {
            position: fixed;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 2000;
            transition: left 0.3s;
        }
        
        .mobile-menu.active {
            left: 0;
        }
        
        .mobile-menu-content {
            position: absolute;
            top: 0;
            left: 0;
            width: 80%;
            max-width: 300px;
            height: 100%;
            background: white;
            transform: translateX(-100%);
            transition: transform 0.3s;
        }
        
        .mobile-menu.active .mobile-menu-content {
            transform: translateX(0);
        }
        
        .mobile-menu-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .mobile-menu-close {
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            font-size: 1.2em;
        }
        
        .mobile-nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .mobile-nav li {
            border-bottom: 1px solid #f0f0f0;
        }
        
        .mobile-nav a {
            display: block;
            padding: 15px 20px;
            color: #333;
            text-decoration: none;
            transition: background 0.2s;
        }
        
        .mobile-nav a:hover {
            background: #f5f5f5;
        }
        
        .no-notifications,
        .no-items {
            text-align: center;
            padding: 20px;
            color: #666;
        }
        
        .loading {
            text-align: center;
            padding: 20px;
            color: #666;
        }
        
        @media (max-width: 768px) {
            .nav {
                display: none;
            }
            
            .mobile-menu-toggle {
                display: block;
            }
            
            .user-actions {
                gap: 10px;
            }
            
            .notification-dropdown-content,
            .cart-dropdown-content {
                min-width: 250px;
            }
        }
    </style>
</body>
</html>
