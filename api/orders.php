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
        case 'create':
            $cart_items = $_POST['cart_items'] ?? [];
            $shipping_address = trim($_POST['shipping_address'] ?? '');
            $shipping_phone = trim($_POST['shipping_phone'] ?? '');
            $payment_method = $_POST['payment_method'] ?? 'cod';
            $notes = trim($_POST['notes'] ?? '');
            
            if (empty($cart_items) || !is_array($cart_items)) {
                throw new Exception('Giỏ hàng trống');
            }
            
            if (empty($shipping_address) || empty($shipping_phone)) {
                throw new Exception('Vui lòng nhập đầy đủ thông tin giao hàng');
            }
            
            // Validate cart items
            $product_ids = array_column($cart_items, 'product_id');
            $placeholders = str_repeat('?,', count($product_ids) - 1) . '?';
            
            $stmt = $conn->prepare("
                SELECT p.*, c.quantity as cart_quantity, u.username as seller_name
                FROM products p
                LEFT JOIN cart c ON p.id = c.product_id
                LEFT JOIN users u ON p.seller_id = u.id
                WHERE p.id IN ($placeholders) AND c.user_id = ? AND p.status = 'active'
            ");
            $stmt->execute(array_merge($product_ids, [$_SESSION['user_id']]));
            $products = $stmt->fetchAll();
            
            if (count($products) != count($cart_items)) {
                throw new Exception('Một số sản phẩm không còn khả dụng');
            }
            
            // Group by seller
            $orders_by_seller = [];
            $total_amount = 0;
            
            foreach ($products as $product) {
                $seller_id = $product['seller_id'];
                if (!isset($orders_by_seller[$seller_id])) {
                    $orders_by_seller[$seller_id] = [
                        'seller_id' => $seller_id,
                        'seller_name' => $product['seller_name'],
                        'items' => [],
                        'subtotal' => 0
                    ];
                }
                
                $item_total = $product['price'] * $product['cart_quantity'];
                $orders_by_seller[$seller_id]['items'][] = [
                    'product_id' => $product['id'],
                    'product_name' => $product['name'],
                    'product_price' => $product['price'],
                    'quantity' => $product['cart_quantity'],
                    'total_price' => $item_total
                ];
                $orders_by_seller[$seller_id]['subtotal'] += $item_total;
                $total_amount += $item_total;
            }
            
            // Create orders for each seller
            $order_numbers = [];
            $conn->beginTransaction();
            
            try {
                foreach ($orders_by_seller as $seller_id => $order_data) {
                    // Generate order number
                    $order_number = 'ORD' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
                    
                    // Create order
                    $stmt = $conn->prepare("
                        INSERT INTO orders (buyer_id, seller_id, order_number, status, payment_method, 
                                          total_amount, shipping_address, shipping_phone, notes, created_at) 
                        VALUES (?, ?, ?, 'pending', ?, ?, ?, ?, ?, NOW())
                    ");
                    $stmt->execute([
                        $_SESSION['user_id'], $seller_id, $order_number, $payment_method,
                        $order_data['subtotal'], $shipping_address, $shipping_phone, $notes
                    ]);
                    
                    $order_id = $conn->lastInsertId();
                    $order_numbers[] = $order_number;
                    
                    // Create order items
                    foreach ($order_data['items'] as $item) {
                        $stmt = $conn->prepare("
                            INSERT INTO order_items (order_id, product_id, product_name, product_price, 
                                                  quantity, total_price, created_at) 
                            VALUES (?, ?, ?, ?, ?, ?, NOW())
                        ");
                        $stmt->execute([
                            $order_id, $item['product_id'], $item['product_name'], 
                            $item['product_price'], $item['quantity'], $item['total_price']
                        ]);
                    }
                }
                
                // Clear cart
                $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                
                $conn->commit();
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Đặt hàng thành công',
                    'order_numbers' => $order_numbers,
                    'total_amount' => $total_amount
                ]);
                
            } catch (Exception $e) {
                $conn->rollBack();
                throw $e;
            }
            break;
            
        case 'get':
            $order_id = intval($_GET['order_id'] ?? 0);
            
            if ($order_id) {
                // Get single order
                $stmt = $conn->prepare("
                    SELECT o.*, u.username as seller_name, u.phone as seller_phone
                    FROM orders o
                    LEFT JOIN users u ON o.seller_id = u.id
                    WHERE o.id = ? AND o.buyer_id = ?
                ");
                $stmt->execute([$order_id, $_SESSION['user_id']]);
                $order = $stmt->fetch();
                
                if (!$order) {
                    throw new Exception('Đơn hàng không tồn tại');
                }
                
                // Get order items
                $stmt = $conn->prepare("
                    SELECT oi.*, p.image_url
                    FROM order_items oi
                    LEFT JOIN products p ON oi.product_id = p.id
                    WHERE oi.order_id = ?
                ");
                $stmt->execute([$order_id]);
                $order['items'] = $stmt->fetchAll();
                
                echo json_encode(['success' => true, 'order' => $order]);
            } else {
                // Get all orders
                $page = max(1, intval($_GET['page'] ?? 1));
                $limit = 10;
                $offset = ($page - 1) * $limit;
                
                $stmt = $conn->prepare("
                    SELECT o.*, u.username as seller_name
                    FROM orders o
                    LEFT JOIN users u ON o.seller_id = u.id
                    WHERE o.buyer_id = ?
                    ORDER BY o.created_at DESC
                    LIMIT $limit OFFSET $offset
                ");
                $stmt->execute([$_SESSION['user_id']]);
                $orders = $stmt->fetchAll();
                
                // Get total count
                $stmt = $conn->prepare("SELECT COUNT(*) as total FROM orders WHERE buyer_id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $total = $stmt->fetch()['total'];
                
                echo json_encode([
                    'success' => true,
                    'orders' => $orders,
                    'pagination' => [
                        'page' => $page,
                        'limit' => $limit,
                        'total' => $total,
                        'pages' => ceil($total / $limit)
                    ]
                ]);
            }
            break;
            
        case 'update_status':
            $order_id = intval($_POST['order_id'] ?? 0);
            $status = $_POST['status'] ?? '';
            
            if (!$order_id || !$status) {
                throw new Exception('Dữ liệu không hợp lệ');
            }
            
            // Check if user can update this order
            $stmt = $conn->prepare("
                SELECT * FROM orders 
                WHERE id = ? AND (buyer_id = ? OR seller_id = ?)
            ");
            $stmt->execute([$order_id, $_SESSION['user_id'], $_SESSION['user_id']]);
            $order = $stmt->fetch();
            
            if (!$order) {
                throw new Exception('Không có quyền cập nhật đơn hàng này');
            }
            
            // Update order status
            $stmt = $conn->prepare("
                UPDATE orders 
                SET status = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$status, $order_id]);
            
            // Log activity
            $stmt = $conn->prepare("
                INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent) 
                VALUES (?, 'order_update', 'Updated order status to $status', ?, ?)
            ");
            $stmt->execute([
                $_SESSION['user_id'],
                $_SERVER['REMOTE_ADDR'] ?? '',
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Đã cập nhật trạng thái đơn hàng']);
            break;
            
        case 'cancel':
            $order_id = intval($_POST['order_id'] ?? 0);
            
            if (!$order_id) {
                throw new Exception('ID đơn hàng không hợp lệ');
            }
            
            // Check if user can cancel this order
            $stmt = $conn->prepare("
                SELECT * FROM orders 
                WHERE id = ? AND buyer_id = ? AND status IN ('pending', 'confirmed')
            ");
            $stmt->execute([$order_id, $_SESSION['user_id']]);
            $order = $stmt->fetch();
            
            if (!$order) {
                throw new Exception('Không thể hủy đơn hàng này');
            }
            
            // Update order status
            $stmt = $conn->prepare("
                UPDATE orders 
                SET status = 'cancelled', updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$order_id]);
            
            echo json_encode(['success' => true, 'message' => 'Đã hủy đơn hàng']);
            break;
            
        default:
            throw new Exception('Hành động không hợp lệ');
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
