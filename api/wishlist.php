<?php
require_once '../config/session.php';
require_once '../config/database.php';
require_once '../config/security.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để sử dụng tính năng này']);
    exit();
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    switch ($action) {
        case 'add':
            $product_id = intval($_POST['product_id'] ?? 0);
            
            if (!$product_id) {
                throw new Exception('ID sản phẩm không hợp lệ');
            }
            
            // Kiểm tra sản phẩm tồn tại
            $stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND status = 'active'");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();
            
            if (!$product) {
                throw new Exception('Sản phẩm không tồn tại');
            }
            
            // Kiểm tra đã có trong wishlist chưa
            $stmt = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$_SESSION['user_id'], $product_id]);
            
            if ($stmt->fetch()) {
                throw new Exception('Sản phẩm đã có trong danh sách yêu thích');
            }
            
            // Thêm vào wishlist
            $stmt = $conn->prepare("
                INSERT INTO wishlist (user_id, product_id, created_at) 
                VALUES (?, ?, NOW())
            ");
            $stmt->execute([$_SESSION['user_id'], $product_id]);
            
            echo json_encode(['success' => true, 'message' => 'Đã thêm vào danh sách yêu thích']);
            break;
            
        case 'remove':
            $product_id = intval($_POST['product_id'] ?? 0);
            
            if (!$product_id) {
                throw new Exception('ID sản phẩm không hợp lệ');
            }
            
            $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$_SESSION['user_id'], $product_id]);
            
            echo json_encode(['success' => true, 'message' => 'Đã xóa khỏi danh sách yêu thích']);
            break;
            
        case 'toggle':
            $product_id = intval($_POST['product_id'] ?? 0);
            
            if (!$product_id) {
                throw new Exception('ID sản phẩm không hợp lệ');
            }
            
            // Kiểm tra đã có trong wishlist chưa
            $stmt = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$_SESSION['user_id'], $product_id]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                // Xóa khỏi wishlist
                $stmt = $conn->prepare("DELETE FROM wishlist WHERE id = ?");
                $stmt->execute([$existing['id']]);
                $added = false;
            } else {
                // Thêm vào wishlist
                $stmt = $conn->prepare("
                    INSERT INTO wishlist (user_id, product_id, created_at) 
                    VALUES (?, ?, NOW())
                ");
                $stmt->execute([$_SESSION['user_id'], $product_id]);
                $added = true;
            }
            
            echo json_encode([
                'success' => true, 
                'message' => $added ? 'Đã thêm vào danh sách yêu thích' : 'Đã xóa khỏi danh sách yêu thích',
                'added' => $added
            ]);
            break;
            
        case 'get':
            $page = max(1, intval($_GET['page'] ?? 1));
            $limit = 12;
            $offset = ($page - 1) * $limit;
            
            $stmt = $conn->prepare("
                SELECT w.*, p.name, p.price, p.description, p.seller_id, u.username as seller_name
                FROM wishlist w
                LEFT JOIN products p ON w.product_id = p.id
                LEFT JOIN users u ON p.seller_id = u.id
                WHERE w.user_id = ?
                ORDER BY w.created_at DESC
                LIMIT $limit OFFSET $offset
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $wishlist_items = $stmt->fetchAll();
            
            // Get total count
            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM wishlist WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $total = $stmt->fetch()['total'];
            
            echo json_encode([
                'success' => true,
                'items' => $wishlist_items,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => ceil($total / $limit)
                ]
            ]);
            break;
            
        case 'check':
            $product_id = intval($_GET['product_id'] ?? 0);
            
            if (!$product_id) {
                throw new Exception('ID sản phẩm không hợp lệ');
            }
            
            $stmt = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$_SESSION['user_id'], $product_id]);
            $exists = $stmt->fetch() ? true : false;
            
            echo json_encode(['success' => true, 'in_wishlist' => $exists]);
            break;
            
        default:
            throw new Exception('Hành động không hợp lệ');
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
