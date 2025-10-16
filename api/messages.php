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
        case 'send':
            $recipient = trim($_POST['recipient'] ?? '');
            $message = trim($_POST['message'] ?? '');
            $user_id = intval($_POST['user_id'] ?? 0);
            
            if (empty($message)) {
                throw new Exception('Nội dung tin nhắn không được để trống');
            }
            
            // Nếu có user_id (chat trực tiếp)
            if ($user_id) {
                $receiver_id = $user_id;
            } else {
                // Tìm user theo username
                if (empty($recipient)) {
                    throw new Exception('Vui lòng nhập tên người dùng');
                }
                
                $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
                $stmt->execute([$recipient]);
                $user = $stmt->fetch();
                
                if (!$user) {
                    throw new Exception('Không tìm thấy người dùng');
                }
                
                $receiver_id = $user['id'];
            }
            
            // Không được gửi tin nhắn cho chính mình
            if ($receiver_id == $_SESSION['user_id']) {
                throw new Exception('Không thể gửi tin nhắn cho chính mình');
            }
            
            // Gửi tin nhắn
            $stmt = $conn->prepare("
                INSERT INTO messages (sender_id, receiver_id, message) 
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$_SESSION['user_id'], $receiver_id, $message]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Gửi tin nhắn thành công'
            ]);
            break;
            
        case 'get':
            $user_id = intval($_GET['user_id'] ?? 0);
            
            if (!$user_id) {
                throw new Exception('ID người dùng không hợp lệ');
            }
            
            // Lấy tin nhắn giữa 2 người
            $stmt = $conn->prepare("
                SELECT m.*, 
                       sender.username as sender_username,
                       receiver.username as receiver_username
                FROM messages m
                LEFT JOIN users sender ON m.sender_id = sender.id
                LEFT JOIN users receiver ON m.receiver_id = receiver.id
                WHERE (m.sender_id = ? AND m.receiver_id = ?) 
                   OR (m.sender_id = ? AND m.receiver_id = ?)
                ORDER BY m.created_at ASC
            ");
            $stmt->execute([$_SESSION['user_id'], $user_id, $user_id, $_SESSION['user_id']]);
            $messages = $stmt->fetchAll();
            
            // Đánh dấu tin nhắn đã đọc
            $stmt = $conn->prepare("
                UPDATE messages 
                SET is_read = 1 
                WHERE sender_id = ? AND receiver_id = ? AND is_read = 0
            ");
            $stmt->execute([$user_id, $_SESSION['user_id']]);
            
            echo json_encode([
                'success' => true,
                'messages' => $messages
            ]);
            break;
            
        case 'get_conversations':
            // Lấy danh sách cuộc trò chuyện
            $stmt = $conn->prepare("
                SELECT DISTINCT 
                    CASE 
                        WHEN sender_id = ? THEN receiver_id 
                        ELSE sender_id 
                    END as other_user_id,
                    u.username as other_username,
                    u.full_name as other_full_name,
                    m.message as last_message,
                    m.created_at as last_message_time,
                    COUNT(CASE WHEN receiver_id = ? AND is_read = 0 THEN 1 END) as unread_count
                FROM messages m
                LEFT JOIN users u ON (
                    CASE 
                        WHEN m.sender_id = ? THEN m.receiver_id 
                        ELSE m.sender_id 
                    END
                ) = u.id
                WHERE m.sender_id = ? OR m.receiver_id = ?
                GROUP BY other_user_id, u.username, u.full_name, m.message, m.created_at
                ORDER BY last_message_time DESC
            ");
            $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']]);
            $conversations = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true,
                'conversations' => $conversations
            ]);
            break;
            
        case 'mark_read':
            $user_id = intval($_POST['user_id'] ?? 0);
            
            if (!$user_id) {
                throw new Exception('ID người dùng không hợp lệ');
            }
            
            $stmt = $conn->prepare("
                UPDATE messages 
                SET is_read = 1 
                WHERE sender_id = ? AND receiver_id = ? AND is_read = 0
            ");
            $stmt->execute([$user_id, $_SESSION['user_id']]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Đã đánh dấu đã đọc'
            ]);
            break;
            
        case 'get_unread_count':
            $stmt = $conn->prepare("
                SELECT COUNT(*) as count 
                FROM messages 
                WHERE receiver_id = ? AND is_read = 0
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
