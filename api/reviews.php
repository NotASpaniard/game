<?php
require_once '../config/session.php';
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để đánh giá']);
    exit();
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    switch ($action) {
        case 'add':
            $product_id = intval($_POST['product_id'] ?? 0);
            $rating = intval($_POST['rating'] ?? 0);
            $comment = trim($_POST['comment'] ?? '');
            
            if (!$product_id || $rating < 1 || $rating > 5) {
                throw new Exception('Dữ liệu không hợp lệ');
            }
            
            // Kiểm tra sản phẩm tồn tại
            $stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND status = 'active'");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();
            
            if (!$product) {
                throw new Exception('Sản phẩm không tồn tại');
            }
            
            // Kiểm tra đã đánh giá chưa
            $stmt = $conn->prepare("SELECT id FROM reviews WHERE product_id = ? AND user_id = ?");
            $stmt->execute([$product_id, $_SESSION['user_id']]);
            
            if ($stmt->fetch()) {
                throw new Exception('Bạn đã đánh giá sản phẩm này rồi');
            }
            
            // Thêm review
            $stmt = $conn->prepare("
                INSERT INTO reviews (product_id, user_id, rating, comment, status) 
                VALUES (?, ?, ?, ?, 'approved')
            ");
            $stmt->execute([$product_id, $_SESSION['user_id'], $rating, $comment]);
            
            // Cập nhật average rating của sản phẩm
            $this->updateProductRating($conn, $product_id);
            
            echo json_encode([
                'success' => true,
                'message' => 'Đánh giá thành công'
            ]);
            break;
            
        case 'get':
            $product_id = intval($_GET['product_id'] ?? 0);
            
            if (!$product_id) {
                throw new Exception('ID sản phẩm không hợp lệ');
            }
            
            $stmt = $conn->prepare("
                SELECT r.*, u.username, u.full_name
                FROM reviews r
                LEFT JOIN users u ON r.user_id = u.id
                WHERE r.product_id = ? AND r.status = 'approved'
                ORDER BY r.created_at DESC
            ");
            $stmt->execute([$product_id]);
            $reviews = $stmt->fetchAll();
            
            // Tính average rating
            $stmt = $conn->prepare("
                SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews
                FROM reviews 
                WHERE product_id = ? AND status = 'approved'
            ");
            $stmt->execute([$product_id]);
            $stats = $stmt->fetch();
            
            echo json_encode([
                'success' => true,
                'reviews' => $reviews,
                'stats' => [
                    'average_rating' => round($stats['avg_rating'] ?? 0, 1),
                    'total_reviews' => $stats['total_reviews']
                ]
            ]);
            break;
            
        case 'update':
            $review_id = intval($_POST['review_id'] ?? 0);
            $rating = intval($_POST['rating'] ?? 0);
            $comment = trim($_POST['comment'] ?? '');
            
            if (!$review_id || $rating < 1 || $rating > 5) {
                throw new Exception('Dữ liệu không hợp lệ');
            }
            
            // Kiểm tra quyền sở hữu
            $stmt = $conn->prepare("SELECT product_id FROM reviews WHERE id = ? AND user_id = ?");
            $stmt->execute([$review_id, $_SESSION['user_id']]);
            $review = $stmt->fetch();
            
            if (!$review) {
                throw new Exception('Không có quyền chỉnh sửa đánh giá này');
            }
            
            // Cập nhật review
            $stmt = $conn->prepare("
                UPDATE reviews 
                SET rating = ?, comment = ?, updated_at = NOW() 
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$rating, $comment, $review_id, $_SESSION['user_id']]);
            
            // Cập nhật average rating
            $this->updateProductRating($conn, $review['product_id']);
            
            echo json_encode([
                'success' => true,
                'message' => 'Cập nhật đánh giá thành công'
            ]);
            break;
            
        case 'delete':
            $review_id = intval($_POST['review_id'] ?? 0);
            
            if (!$review_id) {
                throw new Exception('ID đánh giá không hợp lệ');
            }
            
            // Kiểm tra quyền sở hữu
            $stmt = $conn->prepare("SELECT product_id FROM reviews WHERE id = ? AND user_id = ?");
            $stmt->execute([$review_id, $_SESSION['user_id']]);
            $review = $stmt->fetch();
            
            if (!$review) {
                throw new Exception('Không có quyền xóa đánh giá này');
            }
            
            // Xóa review
            $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ? AND user_id = ?");
            $stmt->execute([$review_id, $_SESSION['user_id']]);
            
            // Cập nhật average rating
            $this->updateProductRating($conn, $review['product_id']);
            
            echo json_encode([
                'success' => true,
                'message' => 'Xóa đánh giá thành công'
            ]);
            break;
            
        default:
            throw new Exception('Hành động không hợp lệ');
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function updateProductRating($conn, $product_id) {
    // Tính average rating mới
    $stmt = $conn->prepare("
        SELECT AVG(rating) as avg_rating, COUNT(*) as review_count
        FROM reviews 
        WHERE product_id = ? AND status = 'approved'
    ");
    $stmt->execute([$product_id]);
    $stats = $stmt->fetch();
    
    // Cập nhật products table
    $stmt = $conn->prepare("
        UPDATE products 
        SET average_rating = ?, review_count = ? 
        WHERE id = ?
    ");
    $stmt->execute([
        round($stats['avg_rating'] ?? 0, 2),
        $stats['review_count'],
        $product_id
    ]);
}
?>
