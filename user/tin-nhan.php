<?php
require_once '../config/session.php';
require_once '../config/database.php';

requireLogin();

try {
    $db = new Database();
    $conn = $db->getConnection();
    
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
    
} catch (Exception $e) {
    error_log("Error loading conversations: " . $e->getMessage());
    $conversations = [];
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tin nhắn - Game Trading Platform</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1>Tin nhắn</h1>
                <div class="header-actions">
                    <button id="newMessage" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tin nhắn mới
                    </button>
                </div>
            </div>
            
            <div class="messages-container">
                <?php if (empty($conversations)): ?>
                <div class="no-messages">
                    <i class="fas fa-comments"></i>
                    <h3>Chưa có tin nhắn nào</h3>
                    <p>Bắt đầu trò chuyện với người dùng khác để trao đổi về sản phẩm.</p>
                </div>
                <?php else: ?>
                <div class="conversations-list">
                    <?php foreach ($conversations as $conversation): ?>
                    <div class="conversation-item" data-user-id="<?php echo $conversation['other_user_id']; ?>">
                        <div class="conversation-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        
                        <div class="conversation-content">
                            <div class="conversation-header">
                                <h4><?php echo htmlspecialchars($conversation['other_username']); ?></h4>
                                <span class="conversation-time">
                                    <?php echo date('d/m H:i', strtotime($conversation['last_message_time'])); ?>
                                </span>
                            </div>
                            
                            <div class="conversation-preview">
                                <?php echo htmlspecialchars($conversation['last_message']); ?>
                            </div>
                        </div>
                        
                        <?php if ($conversation['unread_count'] > 0): ?>
                        <div class="unread-badge">
                            <?php echo $conversation['unread_count']; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <!-- New Message Modal -->
    <div id="newMessageModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Tin nhắn mới</h3>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="newMessageForm">
                    <div class="form-group">
                        <label for="recipient">Gửi đến:</label>
                        <input type="text" id="recipient" name="recipient" placeholder="Nhập tên người dùng" required>
                    </div>
                    <div class="form-group">
                        <label for="message">Nội dung:</label>
                        <textarea id="message" name="message" rows="4" placeholder="Nhập nội dung tin nhắn..." required></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary modal-close">Hủy</button>
                        <button type="submit" class="btn btn-primary">Gửi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Chat Modal -->
    <div id="chatModal" class="modal">
        <div class="modal-content chat-modal">
            <div class="modal-header">
                <h3 id="chatTitle">Trò chuyện</h3>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div id="chatMessages" class="chat-messages"></div>
                <div class="chat-input">
                    <form id="chatForm">
                        <input type="hidden" id="chatUserId" name="user_id">
                        <div class="input-group">
                            <input type="text" id="chatMessage" name="message" placeholder="Nhập tin nhắn..." required>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="../assets/js/notifications.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        // New message modal
        document.getElementById('newMessage').addEventListener('click', function() {
            document.getElementById('newMessageModal').style.display = 'flex';
        });
        
        // Close modals
        document.querySelectorAll('.modal-close').forEach(button => {
            button.addEventListener('click', function() {
                this.closest('.modal').style.display = 'none';
            });
        });
        
        // New message form
        document.getElementById('newMessageForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'send');
            
            fetch('../api/messages.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    NotificationManager.show('Gửi tin nhắn thành công!', 'success');
                    document.getElementById('newMessageModal').style.display = 'none';
                    this.reset();
                    location.reload();
                } else {
                    NotificationManager.show(data.message, 'error');
                }
            });
        });
        
        // Open chat
        document.querySelectorAll('.conversation-item').forEach(item => {
            item.addEventListener('click', function() {
                const userId = this.dataset.userId;
                const username = this.querySelector('h4').textContent;
                
                document.getElementById('chatTitle').textContent = username;
                document.getElementById('chatUserId').value = userId;
                document.getElementById('chatModal').style.display = 'flex';
                
                loadChatMessages(userId);
            });
        });
        
        // Load chat messages
        function loadChatMessages(userId) {
            fetch(`../api/messages.php?action=get&user_id=${userId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayMessages(data.messages);
                }
            });
        }
        
        // Display messages
        function displayMessages(messages) {
            const container = document.getElementById('chatMessages');
            container.innerHTML = '';
            
            messages.forEach(message => {
                const messageDiv = document.createElement('div');
                messageDiv.className = `message ${message.sender_id == <?php echo $_SESSION['user_id']; ?> ? 'sent' : 'received'}`;
                
                messageDiv.innerHTML = `
                    <div class="message-content">${message.message}</div>
                    <div class="message-time">${message.created_at}</div>
                `;
                
                container.appendChild(messageDiv);
            });
            
            container.scrollTop = container.scrollHeight;
        }
        
        // Send message
        document.getElementById('chatForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'send');
            
            fetch('../api/messages.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.reset();
                    loadChatMessages(document.getElementById('chatUserId').value);
                } else {
                    NotificationManager.show(data.message, 'error');
                }
            });
        });
        
        // Auto refresh messages every 5 seconds
        setInterval(() => {
            const chatModal = document.getElementById('chatModal');
            if (chatModal.style.display === 'flex') {
                const userId = document.getElementById('chatUserId').value;
                if (userId) {
                    loadChatMessages(userId);
                }
            }
        }, 5000);
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
        
        .messages-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .no-messages {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .no-messages i {
            font-size: 3em;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .conversations-list {
            max-height: 600px;
            overflow-y: auto;
        }
        
        .conversation-item {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            transition: background 0.2s;
            position: relative;
        }
        
        .conversation-item:last-child {
            border-bottom: none;
        }
        
        .conversation-item:hover {
            background: #f5f5f5;
        }
        
        .conversation-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: #666;
            font-size: 1.2em;
        }
        
        .conversation-content {
            flex: 1;
        }
        
        .conversation-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
        }
        
        .conversation-header h4 {
            margin: 0;
            color: #333;
            font-size: 1.1em;
        }
        
        .conversation-time {
            font-size: 0.85em;
            color: #999;
        }
        
        .conversation-preview {
            color: #666;
            font-size: 0.9em;
            line-height: 1.4;
        }
        
        .unread-badge {
            position: absolute;
            top: 15px;
            right: 20px;
            background: #007bff;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8em;
            font-weight: bold;
        }
        
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background: white;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
            max-height: 80vh;
            overflow: hidden;
        }
        
        .chat-modal {
            max-width: 600px;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .modal-header h3 {
            margin: 0;
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 1.5em;
            cursor: pointer;
            color: #666;
        }
        
        .modal-body {
            padding: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        
        /* Chat styles */
        .chat-messages {
            height: 300px;
            overflow-y: auto;
            border: 1px solid #eee;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
        }
        
        .message {
            margin-bottom: 15px;
        }
        
        .message.sent {
            text-align: right;
        }
        
        .message.received {
            text-align: left;
        }
        
        .message-content {
            display: inline-block;
            max-width: 70%;
            padding: 10px 15px;
            border-radius: 15px;
            background: #f1f1f1;
            color: #333;
        }
        
        .message.sent .message-content {
            background: #007bff;
            color: white;
        }
        
        .message-time {
            font-size: 0.8em;
            color: #999;
            margin-top: 5px;
        }
        
        .chat-input {
            border-top: 1px solid #eee;
            padding-top: 15px;
        }
        
        .input-group {
            display: flex;
            gap: 10px;
        }
        
        .input-group input {
            flex: 1;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        @media (max-width: 768px) {
            .modal-content {
                width: 95%;
                margin: 10px;
            }
            
            .conversation-item {
                padding: 12px 15px;
            }
            
            .conversation-avatar {
                width: 40px;
                height: 40px;
                margin-right: 12px;
            }
        }
    </style>
</body>
</html>
