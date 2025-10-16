<?php
require_once '../config/session.php';
require_once '../config/database.php';
require_once '../config/image-helper.php';

$product_id = intval($_GET['id'] ?? 0);

if (!$product_id) {
    header('Location: index.php');
    exit();
}

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Lấy thông tin sản phẩm
    $stmt = $conn->prepare("
        SELECT p.*, g.name as game_name, u.username as seller_name
        FROM products p
        LEFT JOIN games g ON p.game_id = g.id
        LEFT JOIN users u ON p.seller_id = u.id
        WHERE p.id = ? AND p.status = 'active'
    ");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        header('Location: index.php');
        exit();
    }
    
    // Lấy reviews
    $stmt = $conn->prepare("
        SELECT r.*, u.username, u.full_name
        FROM reviews r
        LEFT JOIN users u ON r.user_id = u.id
        WHERE r.product_id = ? AND r.status = 'approved'
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$product_id]);
    $reviews = $stmt->fetchAll();
    
    // Tính stats
    $stmt = $conn->prepare("
        SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews
        FROM reviews 
        WHERE product_id = ? AND status = 'approved'
    ");
    $stmt->execute([$product_id]);
    $stats = $stmt->fetch();
    
} catch (Exception $e) {
    error_log("Error loading reviews: " . $e->getMessage());
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đánh giá sản phẩm - <?php echo htmlspecialchars($product['name']); ?></title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/products.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main class="main-content">
        <div class="container">
            <!-- Breadcrumb -->
            <nav class="breadcrumb">
                <a href="../index.php">Trang chủ</a>
                <span>/</span>
                <a href="index.php">Sản phẩm</a>
                <span>/</span>
                <a href="chi-tiet.php?id=<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?></a>
                <span>/</span>
                <span>Đánh giá</span>
            </nav>
            
            <!-- Product Info -->
            <div class="product-header">
                <div class="product-image">
                    <img src="<?php echo getProductImage($product['id'], 'assets/images/no-image.jpg', true); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>">
                </div>
                <div class="product-info">
                    <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                    <p class="game-name"><?php echo htmlspecialchars($product['game_name']); ?></p>
                    <p class="price"><?php echo number_format($product['price']); ?>đ</p>
                    <div class="rating-summary">
                        <div class="stars">
                            <?php
                            $avg_rating = round($stats['avg_rating'] ?? 0, 1);
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $avg_rating) {
                                    echo '<i class="fas fa-star"></i>';
                                } elseif ($i - 0.5 <= $avg_rating) {
                                    echo '<i class="fas fa-star-half-alt"></i>';
                                } else {
                                    echo '<i class="far fa-star"></i>';
                                }
                            }
                            ?>
                        </div>
                        <span class="rating-text">
                            <?php echo $avg_rating; ?>/5 
                            (<?php echo $stats['total_reviews']; ?> đánh giá)
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Add Review Form -->
            <?php if (isLoggedIn()): ?>
            <div class="add-review-section">
                <h2>Viết đánh giá</h2>
                <form id="reviewForm" class="review-form">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    
                    <div class="form-group">
                        <label>Đánh giá của bạn:</label>
                        <div class="star-rating">
                            <input type="radio" name="rating" value="5" id="star5">
                            <label for="star5"><i class="fas fa-star"></i></label>
                            <input type="radio" name="rating" value="4" id="star4">
                            <label for="star4"><i class="fas fa-star"></i></label>
                            <input type="radio" name="rating" value="3" id="star3">
                            <label for="star3"><i class="fas fa-star"></i></label>
                            <input type="radio" name="rating" value="2" id="star2">
                            <label for="star2"><i class="fas fa-star"></i></label>
                            <input type="radio" name="rating" value="1" id="star1">
                            <label for="star1"><i class="fas fa-star"></i></label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="comment">Nhận xét:</label>
                        <textarea name="comment" id="comment" rows="4" placeholder="Chia sẻ trải nghiệm của bạn về sản phẩm này..."></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Gửi đánh giá</button>
                </form>
            </div>
            <?php else: ?>
            <div class="login-prompt">
                <p>Vui lòng <a href="../auth/dang-nhap.php">đăng nhập</a> để viết đánh giá</p>
            </div>
            <?php endif; ?>
            
            <!-- Reviews List -->
            <div class="reviews-section">
                <h2>Đánh giá từ khách hàng</h2>
                
                <?php if (empty($reviews)): ?>
                <div class="no-reviews">
                    <p>Chưa có đánh giá nào cho sản phẩm này.</p>
                </div>
                <?php else: ?>
                <div class="reviews-list">
                    <?php foreach ($reviews as $review): ?>
                    <div class="review-item">
                        <div class="review-header">
                            <div class="reviewer-info">
                                <strong><?php echo htmlspecialchars($review['username']); ?></strong>
                                <div class="review-rating">
                                    <?php
                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($i <= $review['rating']) {
                                            echo '<i class="fas fa-star"></i>';
                                        } else {
                                            echo '<i class="far fa-star"></i>';
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="review-date">
                                <?php echo date('d/m/Y', strtotime($review['created_at'])); ?>
                            </div>
                        </div>
                        
                        <?php if (!empty($review['comment'])): ?>
                        <div class="review-comment">
                            <?php echo nl2br(htmlspecialchars($review['comment'])); ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (isLoggedIn() && $review['user_id'] == $_SESSION['user_id']): ?>
                        <div class="review-actions">
                            <button class="btn-edit-review" data-review-id="<?php echo $review['id']; ?>">
                                <i class="fas fa-edit"></i> Sửa
                            </button>
                            <button class="btn-delete-review" data-review-id="<?php echo $review['id']; ?>">
                                <i class="fas fa-trash"></i> Xóa
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="../assets/js/notifications.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        // Review form handling
        document.getElementById('reviewForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'add');
            
            fetch('../api/reviews.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    NotificationManager.show('Đánh giá thành công!', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    NotificationManager.show(data.message, 'error');
                }
            })
            .catch(error => {
                NotificationManager.show('Có lỗi xảy ra', 'error');
            });
        });
        
        // Star rating interaction
        document.querySelectorAll('.star-rating input').forEach(input => {
            input.addEventListener('change', function() {
                const rating = this.value;
                const labels = document.querySelectorAll('.star-rating label');
                labels.forEach((label, index) => {
                    if (index < rating) {
                        label.classList.add('active');
                    } else {
                        label.classList.remove('active');
                    }
                });
            });
        });
        
        // Delete review
        document.querySelectorAll('.btn-delete-review').forEach(button => {
            button.addEventListener('click', function() {
                if (confirm('Bạn có chắc muốn xóa đánh giá này?')) {
                    const reviewId = this.dataset.reviewId;
                    const formData = new FormData();
                    formData.append('action', 'delete');
                    formData.append('review_id', reviewId);
                    
                    fetch('../api/reviews.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            NotificationManager.show('Xóa đánh giá thành công!', 'success');
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            NotificationManager.show(data.message, 'error');
                        }
                    });
                }
            });
        });
    </script>
    
    <style>
        .product-header {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .product-header .product-image {
            width: 120px;
            height: 120px;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .product-header .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .product-info h1 {
            margin: 0 0 10px 0;
            color: #333;
        }
        
        .game-name {
            color: #666;
            margin: 5px 0;
        }
        
        .price {
            font-size: 1.2em;
            font-weight: bold;
            color: #e74c3c;
            margin: 10px 0;
        }
        
        .rating-summary {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .stars {
            color: #ffc107;
        }
        
        .rating-text {
            color: #666;
        }
        
        .add-review-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            gap: 5px;
        }
        
        .star-rating input {
            display: none;
        }
        
        .star-rating label {
            cursor: pointer;
            color: #ddd;
            transition: color 0.2s;
        }
        
        .star-rating label:hover,
        .star-rating label.active {
            color: #ffc107;
        }
        
        .review-form textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            resize: vertical;
        }
        
        .reviews-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .review-item {
            border-bottom: 1px solid #eee;
            padding: 15px 0;
        }
        
        .review-item:last-child {
            border-bottom: none;
        }
        
        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .reviewer-info strong {
            color: #333;
        }
        
        .review-rating {
            color: #ffc107;
            margin-top: 5px;
        }
        
        .review-date {
            color: #666;
            font-size: 0.9em;
        }
        
        .review-comment {
            color: #555;
            line-height: 1.5;
            margin: 10px 0;
        }
        
        .review-actions {
            margin-top: 10px;
        }
        
        .review-actions button {
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            margin-right: 10px;
            padding: 5px 10px;
            border-radius: 3px;
            transition: background 0.2s;
        }
        
        .review-actions button:hover {
            background: #f5f5f5;
        }
        
        .no-reviews {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .login-prompt {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .login-prompt a {
            color: #007bff;
            text-decoration: none;
        }
        
        .login-prompt a:hover {
            text-decoration: underline;
        }
    </style>
</body>
</html>
