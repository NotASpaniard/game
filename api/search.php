<?php
require_once '../config/session.php';
require_once '../config/database.php';
require_once '../config/image-helper.php';

header('Content-Type: application/json');

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $query = trim($_GET['q'] ?? '');
    $game_id = intval($_GET['game_id'] ?? 0);
    $min_price = intval($_GET['min_price'] ?? 0);
    $max_price = intval($_GET['max_price'] ?? 0);
    $rarity = trim($_GET['rarity'] ?? '');
    $sort = trim($_GET['sort'] ?? 'newest');
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = 12;
    $offset = ($page - 1) * $limit;
    
    // Build WHERE conditions
    $where_conditions = ["p.status = 'active'"];
    $params = [];
    
    if (!empty($query)) {
        $where_conditions[] = "(p.name LIKE ? OR p.description LIKE ?)";
        $search_term = "%{$query}%";
        $params[] = $search_term;
        $params[] = $search_term;
    }
    
    if ($game_id > 0) {
        $where_conditions[] = "p.game_id = ?";
        $params[] = $game_id;
    }
    
    if ($min_price > 0) {
        $where_conditions[] = "p.price >= ?";
        $params[] = $min_price;
    }
    
    if ($max_price > 0) {
        $where_conditions[] = "p.price <= ?";
        $params[] = $max_price;
    }
    
    if (!empty($rarity)) {
        $where_conditions[] = "p.rarity = ?";
        $params[] = $rarity;
    }
    
    $where_clause = implode(' AND ', $where_conditions);
    
    // Build ORDER BY
    $order_by = "p.created_at DESC";
    switch ($sort) {
        case 'price_asc':
            $order_by = "p.price ASC";
            break;
        case 'price_desc':
            $order_by = "p.price DESC";
            break;
        case 'name_asc':
            $order_by = "p.name ASC";
            break;
        case 'name_desc':
            $order_by = "p.name DESC";
            break;
        case 'newest':
        default:
            $order_by = "p.created_at DESC";
            break;
    }
    
    // Get products
    $sql = "
        SELECT p.*, g.name as game_name, u.username as seller_name
        FROM products p
        LEFT JOIN games g ON p.game_id = g.id
        LEFT JOIN users u ON p.seller_id = u.id
        WHERE {$where_clause}
        ORDER BY {$order_by}
        LIMIT {$limit} OFFSET {$offset}
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
    
    // Get total count
    $count_sql = "
        SELECT COUNT(*) as total
        FROM products p
        LEFT JOIN games g ON p.game_id = g.id
        WHERE {$where_clause}
    ";
    
    $stmt = $conn->prepare($count_sql);
    $stmt->execute($params);
    $total = $stmt->fetch()['total'];
    
    // Add image URLs
    foreach ($products as &$product) {
        $product['image_url'] = getProductImage($product['id'], 'assets/images/no-image.jpg', false);
    }
    
    echo json_encode([
        'success' => true,
        'products' => $products,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => ceil($total / $limit),
            'total_items' => $total,
            'items_per_page' => $limit
        ],
        'filters' => [
            'query' => $query,
            'game_id' => $game_id,
            'min_price' => $min_price,
            'max_price' => $max_price,
            'rarity' => $rarity,
            'sort' => $sort
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Có lỗi xảy ra khi tìm kiếm: ' . $e->getMessage()
    ]);
}
?>
