<?php
require_once '../config/session.php';
require_once '../config/database.php';
require_once '../config/security.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để thực hiện chức năng này.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$db = new Database();
$conn = $db->getConnection();

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';
$product_id = filter_var($data['product_id'] ?? 0, FILTER_SANITIZE_NUMBER_INT);

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID sản phẩm không hợp lệ.']);
    exit();
}

switch ($action) {
    case 'delete':
        try {
            // Kiểm tra quyền sở hữu
            $stmt = $conn->prepare("SELECT seller_id FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$product) {
                echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại.']);
                exit();
            }
            
            if ($product['seller_id'] != $user_id) {
                echo json_encode(['success' => false, 'message' => 'Bạn không có quyền xóa sản phẩm này.']);
                exit();
            }
            
            // Xóa sản phẩm
            $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            
            echo json_encode(['success' => true, 'message' => 'Đã xóa sản phẩm thành công.']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi khi xóa sản phẩm: ' . $e->getMessage()]);
        }
        break;

    case 'update_status':
        $status = sanitize_input($data['status'] ?? '');
        $allowed_statuses = ['active', 'inactive', 'sold'];
        
        if (!in_array($status, $allowed_statuses)) {
            echo json_encode(['success' => false, 'message' => 'Trạng thái không hợp lệ.']);
            exit();
        }
        
        try {
            // Kiểm tra quyền sở hữu
            $stmt = $conn->prepare("SELECT seller_id FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$product) {
                echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại.']);
                exit();
            }
            
            if ($product['seller_id'] != $user_id) {
                echo json_encode(['success' => false, 'message' => 'Bạn không có quyền cập nhật sản phẩm này.']);
                exit();
            }
            
            // Cập nhật trạng thái
            $stmt = $conn->prepare("UPDATE products SET status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$status, $product_id]);
            
            echo json_encode(['success' => true, 'message' => 'Đã cập nhật trạng thái sản phẩm.']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi khi cập nhật sản phẩm: ' . $e->getMessage()]);
        }
        break;

    case 'get_details':
        try {
            $stmt = $conn->prepare("
                SELECT p.*, g.name as game_name, u.username as seller_name
                FROM products p
                LEFT JOIN games g ON p.game_id = g.id
                LEFT JOIN users u ON p.seller_id = u.id
                WHERE p.id = ?
            ");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$product) {
                echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại.']);
                exit();
            }
            
            echo json_encode(['success' => true, 'product' => $product]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi khi lấy thông tin sản phẩm: ' . $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Hành động không hợp lệ.']);
        break;
}
?>
