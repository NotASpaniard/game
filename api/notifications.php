<?php
require_once '../config/session.php';
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
    exit();
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    switch ($action) {
        case 'get':
            $stmt = $conn->prepare("
                SELECT * FROM notifications 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT 20
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $notifications = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true,
                'notifications' => $notifications
            ]);
            break;
            
        case 'mark_read':
            $notification_id = intval($_POST['notification_id'] ?? 0);
            
            if (!$notification_id) {
                throw new Exception('ID thông báo không hợp lệ');
            }
            
            $stmt = $conn->prepare("
                UPDATE notifications 
                SET is_read = 1 
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$notification_id, $_SESSION['user_id']]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Đã đánh dấu đã đọc'
            ]);
            break;
            
        case 'mark_all_read':
            $stmt = $conn->prepare("
                UPDATE notifications 
                SET is_read = 1 
                WHERE user_id = ? AND is_read = 0
            ");
            $stmt->execute([$_SESSION['user_id']]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Đã đánh dấu tất cả đã đọc'
            ]);
            break;
            
        case 'count_unread':
            $stmt = $conn->prepare("
                SELECT COUNT(*) as count 
                FROM notifications 
                WHERE user_id = ? AND is_read = 0
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $result = $stmt->fetch();
            
            echo json_encode([
                'success' => true,
                'count' => $result['count']
            ]);
            break;
            
        default:
            throw new Exception('Hành động không hợp lệ');
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
