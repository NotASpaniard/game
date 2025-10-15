<?php
require_once '../config/session.php';
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để sử dụng giỏ hàng']);
    exit();
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    switch ($action) {
        case 'add':
            $product_id = intval($_POST['product_id'] ?? 0);
            $quantity = intval($_POST['quantity'] ?? 1);
            
            if (!$product_id) {
                throw new Exception('ID sản phẩm không hợp lệ');
            }
            
            // Kiểm tra sản phẩm tồn tại và có sẵn
            $stmt = $conn->prepare("
                SELECT p.*, u.username as seller_name 
                FROM products p 
                LEFT JOIN users u ON p.seller_id = u.id
                WHERE p.id = ? AND p.status = 'active' AND p.stock_quantity > 0
            ");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();
            
            if (!$product) {
                throw new Exception('Sản phẩm không tồn tại hoặc đã hết hàng');
            }
            
            // Kiểm tra không mua sản phẩm của chính mình
            if ($product['seller_id'] == $_SESSION['user_id']) {
                throw new Exception('Không thể mua sản phẩm của chính mình');
            }
            
            // Kiểm tra đã có trong giỏ hàng chưa
            $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$_SESSION['user_id'], $product_id]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                // Cập nhật số lượng
                $new_quantity = $existing['quantity'] + $quantity;
                $stmt = $conn->prepare("UPDATE cart SET quantity = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$new_quantity, $existing['id']]);
            } else {
                // Thêm mới vào giỏ hàng
                $stmt = $conn->prepare("
                    INSERT INTO cart (user_id, product_id, quantity, created_at, updated_at) 
                    VALUES (?, ?, ?, NOW(), NOW())
                ");
                $stmt->execute([$_SESSION['user_id'], $product_id, $quantity]);
            }
            
            echo json_encode([
                'success' => true, 
                'message' => 'Đã thêm vào giỏ hàng',
                'product' => [
                    'id' => $product['id'],
                    'name' => $product['name'],
                    'price' => $product['price'],
                    'seller_name' => $product['seller_name']
                ]
            ]);
            break;
            
        case 'remove':
            $product_id = intval($_POST['product_id'] ?? 0);
            
            if (!$product_id) {
                throw new Exception('ID sản phẩm không hợp lệ');
            }
            
            $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$_SESSION['user_id'], $product_id]);
            
            echo json_encode(['success' => true, 'message' => 'Đã xóa khỏi giỏ hàng']);
            break;
            
        case 'update':
            $product_id = intval($_POST['product_id'] ?? 0);
            $quantity = intval($_POST['quantity'] ?? 1);
            
            if (!$product_id || $quantity < 1) {
                throw new Exception('Dữ liệu không hợp lệ');
            }
            
            if ($quantity == 0) {
                // Xóa khỏi giỏ hàng
                $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
                $stmt->execute([$_SESSION['user_id'], $product_id]);
            } else {
                // Cập nhật số lượng
                $stmt = $conn->prepare("UPDATE cart SET quantity = ?, updated_at = NOW() WHERE user_id = ? AND product_id = ?");
                $stmt->execute([$quantity, $_SESSION['user_id'], $product_id]);
            }
            
            echo json_encode(['success' => true, 'message' => 'Đã cập nhật giỏ hàng']);
            break;
            
        case 'clear':
            $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            
            echo json_encode(['success' => true, 'message' => 'Đã xóa tất cả sản phẩm khỏi giỏ hàng']);
            break;
            
        case 'get':
            $stmt = $conn->prepare("
                SELECT c.*, p.name, p.price, p.image_url, u.username as seller_name
                FROM cart c
                LEFT JOIN products p ON c.product_id = p.id
                LEFT JOIN users u ON p.seller_id = u.id
                WHERE c.user_id = ?
                ORDER BY c.created_at DESC
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $cart_items = $stmt->fetchAll();
            
            $total = 0;
            foreach ($cart_items as &$item) {
                $item['total_price'] = $item['price'] * $item['quantity'];
                $total += $item['total_price'];
            }
            
            echo json_encode([
                'success' => true,
                'items' => $cart_items,
                'total' => $total,
                'count' => count($cart_items)
            ]);
            break;
            
        case 'count':
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM cart WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $result = $stmt->fetch();
            
            echo json_encode(['success' => true, 'count' => $result['count']]);
            break;
            
        default:
            throw new Exception('Hành động không hợp lệ');
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
