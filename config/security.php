<?php
// Security configuration and helper functions

class SecurityManager {
    
    // Rate limiting
    private static $rateLimits = [
        'login' => ['attempts' => 5, 'window' => 900], // 5 attempts in 15 minutes
        'register' => ['attempts' => 3, 'window' => 3600], // 3 attempts in 1 hour
        'api' => ['attempts' => 100, 'window' => 3600], // 100 requests in 1 hour
        'password_reset' => ['attempts' => 3, 'window' => 3600] // 3 attempts in 1 hour
    ];
    
    public static function checkRateLimit($action, $identifier = null) {
        $identifier = $identifier ?: self::getClientIdentifier();
        $key = "rate_limit_{$action}_{$identifier}";
        
        $limits = self::$rateLimits[$action] ?? ['attempts' => 10, 'window' => 3600];
        $attempts = self::getStoredAttempts($key);
        $windowStart = time() - $limits['window'];
        
        // Remove old attempts
        $attempts = array_filter($attempts, function($timestamp) use ($windowStart) {
            return $timestamp > $windowStart;
        });
        
        if (count($attempts) >= $limits['attempts']) {
            return false;
        }
        
        // Record this attempt
        $attempts[] = time();
        self::storeAttempts($key, $attempts);
        
        return true;
    }
    
    public static function getClientIdentifier() {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        return hash('sha256', $ip . $userAgent);
    }
    
    private static function getStoredAttempts($key) {
        $file = sys_get_temp_dir() . "/{$key}.json";
        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true);
            return $data ?: [];
        }
        return [];
    }
    
    private static function storeAttempts($key, $attempts) {
        $file = sys_get_temp_dir() . "/{$key}.json";
        file_put_contents($file, json_encode($attempts));
    }
    
    // Input sanitization
    public static function sanitizeInput($data, $type = 'string') {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizeInput'], $data);
        }
        
        switch ($type) {
            case 'email':
                return filter_var(trim($data), FILTER_SANITIZE_EMAIL);
            case 'int':
                return filter_var($data, FILTER_SANITIZE_NUMBER_INT);
            case 'float':
                return filter_var($data, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            case 'url':
                return filter_var(trim($data), FILTER_SANITIZE_URL);
            case 'html':
                return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
            default:
                return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
        }
    }
    
    // Password validation
    public static function validatePassword($password) {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = 'Mật khẩu phải có ít nhất 8 ký tự';
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Mật khẩu phải có ít nhất 1 chữ hoa';
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Mật khẩu phải có ít nhất 1 chữ thường';
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Mật khẩu phải có ít nhất 1 số';
        }
        
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Mật khẩu phải có ít nhất 1 ký tự đặc biệt';
        }
        
        return $errors;
    }
    
    // Generate secure token
    public static function generateToken($length = 32) {
        return bin2hex(random_bytes($length));
    }
    
    // CSRF protection
    public static function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = self::generateToken();
        }
        return $_SESSION['csrf_token'];
    }
    
    public static function validateCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    // File upload security
    public static function validateFileUpload($file, $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'], $maxSize = 5242880) {
        $errors = [];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Lỗi upload file';
            return $errors;
        }
        
        if ($file['size'] > $maxSize) {
            $errors[] = 'File quá lớn (tối đa ' . round($maxSize / 1024 / 1024, 1) . 'MB)';
        }
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowedTypes)) {
            $errors[] = 'Loại file không được hỗ trợ';
        }
        
        // Check file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($extension, $allowedExtensions)) {
            $errors[] = 'Định dạng file không được hỗ trợ';
        }
        
        return $errors;
    }
    
    // SQL injection prevention
    public static function escapeString($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
    
    // XSS prevention
    public static function preventXSS($data) {
        if (is_array($data)) {
            return array_map([self::class, 'preventXSS'], $data);
        }
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
    
    // Log security events
    public static function logSecurityEvent($event, $details = []) {
        $log = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'user_id' => $_SESSION['user_id'] ?? null,
            'details' => $details
        ];
        
        $logFile = __DIR__ . '/../logs/security.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents($logFile, json_encode($log) . "\n", FILE_APPEND | LOCK_EX);
    }
    
    // Check for suspicious activity
    public static function checkSuspiciousActivity() {
        $suspicious = false;
        $reasons = [];
        
        // Check for SQL injection patterns
        $inputs = array_merge($_GET, $_POST);
        foreach ($inputs as $value) {
            if (is_string($value)) {
                if (preg_match('/(union|select|insert|update|delete|drop|create|alter)/i', $value)) {
                    $suspicious = true;
                    $reasons[] = 'SQL injection attempt detected';
                    break;
                }
            }
        }
        
        // Check for XSS patterns
        foreach ($inputs as $value) {
            if (is_string($value)) {
                if (preg_match('/<script|javascript:|on\w+\s*=/i', $value)) {
                    $suspicious = true;
                    $reasons[] = 'XSS attempt detected';
                    break;
                }
            }
        }
        
        // Check for file inclusion attempts
        foreach ($inputs as $value) {
            if (is_string($value)) {
                if (preg_match('/\.\.\/|\.\.\\\\|php:\/\/|data:/i', $value)) {
                    $suspicious = true;
                    $reasons[] = 'File inclusion attempt detected';
                    break;
                }
            }
        }
        
        if ($suspicious) {
            self::logSecurityEvent('suspicious_activity', [
                'reasons' => $reasons,
                'inputs' => $inputs
            ]);
        }
        
        return $suspicious;
    }
    
    // Session security
    public static function secureSession() {
        // Regenerate session ID periodically
        if (!isset($_SESSION['last_regeneration'])) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        } elseif (time() - $_SESSION['last_regeneration'] > 300) { // 5 minutes
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
        
        // Set secure session parameters
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);
        ini_set('session.use_strict_mode', 1);
        ini_set('session.cookie_samesite', 'Strict');
    }
    
    // Two-factor authentication
    public static function generate2FACode() {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
    
    public static function send2FACode($email, $code) {
        // Implement email sending logic here
        // For now, just log it
        self::logSecurityEvent('2fa_code_sent', ['email' => $email]);
        return true;
    }
    
    // Account lockout
    public static function checkAccountLockout($userId) {
        $key = "lockout_{$userId}";
        $lockoutData = self::getStoredAttempts($key);
        
        if (!empty($lockoutData['locked_until']) && $lockoutData['locked_until'] > time()) {
            return false; // Account is locked
        }
        
        return true; // Account is not locked
    }
    
    public static function lockAccount($userId, $duration = 3600) {
        $key = "lockout_{$userId}";
        $lockoutData = [
            'locked_until' => time() + $duration,
            'reason' => 'Too many failed login attempts'
        ];
        self::storeAttempts($key, $lockoutData);
    }
    
    // Content Security Policy
    public static function setCSPHeaders() {
        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; " .
               "style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; " .
               "img-src 'self' data: https:; " .
               "font-src 'self' https://cdnjs.cloudflare.com; " .
               "connect-src 'self'; " .
               "frame-ancestors 'none';";
        
        header("Content-Security-Policy: $csp");
        header("X-Content-Type-Options: nosniff");
        header("X-Frame-Options: DENY");
        header("X-XSS-Protection: 1; mode=block");
        header("Referrer-Policy: strict-origin-when-cross-origin");
    }
}

// Initialize security
SecurityManager::secureSession();
SecurityManager::setCSPHeaders();

// Check for suspicious activity on every request
if (SecurityManager::checkSuspiciousActivity()) {
    http_response_code(403);
    die('Access denied');
}
?>
