<?php
// Session configuration - phiên bản sạch không có warnings
if (session_status() === PHP_SESSION_NONE) {
    // Chỉ cấu hình khi chưa có session và chưa có output
    if (!headers_sent()) {
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', 0); // Đặt 1 nếu sử dụng HTTPS
        ini_set('session.use_strict_mode', 1);
        ini_set('session.cookie_samesite', 'Strict');
        
        session_start();
        
        // Regenerate session ID để tránh session fixation - chỉ khi session đã active
        if (session_status() === PHP_SESSION_ACTIVE) {
            if (!isset($_SESSION['last_regeneration'])) {
                session_regenerate_id(true);
                $_SESSION['last_regeneration'] = time();
            } elseif (time() - $_SESSION['last_regeneration'] > 300) { // 5 phút
                session_regenerate_id(true);
                $_SESSION['last_regeneration'] = time();
            }
        }
    } else {
        // Nếu headers đã được gửi, chỉ start session
        session_start();
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function getCurrentUser() {
    if (!isLoggedIn()) return null;
    
    try {
        require_once 'database.php';
        $db = new Database();
        $conn = $db->getConnection();
        
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    } catch (Exception $e) {
        error_log("Error getting current user: " . $e->getMessage());
        return null;
    }
}

function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        // Xác định đường dẫn chính xác dựa trên vị trí hiện tại
        $current_dir = dirname($_SERVER['PHP_SELF']);
        if (strpos($current_dir, '/user') !== false) {
            header('Location: ../auth/dang-nhap.php');
        } elseif (strpos($current_dir, '/admin') !== false) {
            header('Location: ../auth/dang-nhap.php');
        } elseif (strpos($current_dir, '/san-pham') !== false) {
            header('Location: ../auth/dang-nhap.php');
        } else {
            header('Location: auth/dang-nhap.php');
        }
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    $user = getCurrentUser();
    if (!$user || $user['role'] !== 'admin') {
        header('Location: index.php');
        exit();
    }
}

function requireSeller() {
    requireLogin();
    $user = getCurrentUser();
    if (!$user || !in_array($user['role'], ['admin', 'seller'])) {
        header('Location: index.php');
        exit();
    }
}

function hasPermission($permission) {
    if (!isLoggedIn()) return false;
    
    $user = getCurrentUser();
    if (!$user) return false;
    
    switch ($permission) {
        case 'admin':
            return $user['role'] === 'admin';
        case 'seller':
            return in_array($user['role'], ['admin', 'seller']);
        case 'user':
            return true;
        default:
            return false;
    }
}

function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function validateCSRF($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function generateCSRF() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
?>
