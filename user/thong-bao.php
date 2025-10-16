<?php
require_once '../config/session.php';
require_once '../config/database.php';

requireLogin();

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Lấy thông báo
    $stmt = $conn->prepare("
        SELECT * FROM notifications 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 50
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $notifications = $stmt->fetchAll();
    
    // Đếm thông báo chưa đọc
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM notifications 
        WHERE user_id = ? AND is_read = 0
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $unread_count = $stmt->fetch()['count'];
    
} catch (Exception $e) {
    error_log("Error loading notifications: " . $e->getMessage());
    $notifications = [];
    $unread_count = 0;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông báo - Game Trading Platform</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1>Thông báo</h1>
                <div class="header-actions">
                    <button id="markAllRead" class="btn btn-secondary">
                        <i class="fas fa-check-double"></i> Đánh dấu tất cả đã đọc
                    </button>
                </div>
            </div>
            
            <div class="notifications-container">
                <?php if (empty($notifications)): ?>
                <div class="no-notifications">
                    <i class="fas fa-bell-slash"></i>
                    <h3>Chưa có thông báo nào</h3>
                    <p>Khi có hoạt động mới, thông báo sẽ xuất hiện ở đây.</p>
                </div>
                <?php else: ?>
                <div class="notifications-list">
                    <?php foreach ($notifications as $notification): ?>
                    <div class="notification-item <?php echo $notification['is_read'] ? 'read' : 'unread'; ?>" 
                         data-id="<?php echo $notification['id']; ?>">
                        <div class="notification-icon">
                            <?php
                            switch ($notification['type']) {
                                case 'order':
                                    echo '<i class="fas fa-shopping-cart"></i>';
                                    break;
                                case 'message':
                                    echo '<i class="fas fa-comment"></i>';
                                    break;
                                case 'system':
                                    echo '<i class="fas fa-cog"></i>';
                                    break;
                                case 'product':
                                    echo '<i class="fas fa-box"></i>';
                                    break;
                                default:
                                    echo '<i class="fas fa-bell"></i>';
                            }
                            ?>
                        </div>
                        
                        <div class="notification-content">
                            <div class="notification-title">
                                <?php echo htmlspecialchars($notification['title']); ?>
                            </div>
                            <div class="notification-message">
                                <?php echo htmlspecialchars($notification['message']); ?>
                            </div>
                            <div class="notification-time">
                                <?php echo date('d/m/Y H:i', strtotime($notification['created_at'])); ?>
                            </div>
                        </div>
                        
                        <div class="notification-actions">
                            <?php if (!$notification['is_read']): ?>
                            <button class="mark-read-btn" data-id="<?php echo $notification['id']; ?>">
                                <i class="fas fa-check"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="../assets/js/notifications.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        // Mark as read
        document.querySelectorAll('.mark-read-btn').forEach(button => {
            button.addEventListener('click', function() {
                const notificationId = this.dataset.id;
                const notificationItem = this.closest('.notification-item');
                
                fetch('../api/notifications.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=mark_read&notification_id=${notificationId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        notificationItem.classList.remove('unread');
                        notificationItem.classList.add('read');
                        this.remove();
                        updateUnreadCount();
                    }
                });
            });
        });
        
        // Mark all as read
        document.getElementById('markAllRead').addEventListener('click', function() {
            fetch('../api/notifications.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=mark_all_read'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelectorAll('.notification-item').forEach(item => {
                        item.classList.remove('unread');
                        item.classList.add('read');
                    });
                    document.querySelectorAll('.mark-read-btn').forEach(btn => btn.remove());
                    updateUnreadCount();
                    NotificationManager.show('Đã đánh dấu tất cả đã đọc', 'success');
                }
            });
        });
        
        function updateUnreadCount() {
            fetch('../api/notifications.php?action=count_unread')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const badge = document.querySelector('.notification-badge');
                    if (badge) {
                        if (data.count > 0) {
                            badge.textContent = data.count;
                            badge.style.display = 'inline';
                        } else {
                            badge.style.display = 'none';
                        }
                    }
                }
            });
        }
        
        // Auto refresh every 30 seconds
        setInterval(updateUnreadCount, 30000);
    </script>
    
    <style>
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
        }
        
        .page-header h1 {
            margin: 0;
            color: #333;
        }
        
        .header-actions {
            display: flex;
            gap: 10px;
        }
        
        .notifications-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .no-notifications {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .no-notifications i {
            font-size: 3em;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .notifications-list {
            max-height: 600px;
            overflow-y: auto;
        }
        
        .notification-item {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
            transition: background 0.2s;
        }
        
        .notification-item:last-child {
            border-bottom: none;
        }
        
        .notification-item.unread {
            background: #f8f9ff;
            border-left: 4px solid #007bff;
        }
        
        .notification-item:hover {
            background: #f5f5f5;
        }
        
        .notification-icon {
            width: 40px;
            height: 40px;
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
        
        .notification-item.unread .notification-title {
            color: #007bff;
        }
        
        .notification-message {
            color: #666;
            line-height: 1.4;
            margin-bottom: 5px;
        }
        
        .notification-time {
            font-size: 0.85em;
            color: #999;
        }
        
        .notification-actions {
            margin-left: 15px;
        }
        
        .mark-read-btn {
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            padding: 8px;
            border-radius: 50%;
            transition: all 0.2s;
        }
        
        .mark-read-btn:hover {
            background: #007bff;
            color: white;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
            
            .notification-item {
                padding: 12px 15px;
            }
            
            .notification-icon {
                width: 35px;
                height: 35px;
                margin-right: 12px;
            }
        }
    </style>
</body>
</html>
