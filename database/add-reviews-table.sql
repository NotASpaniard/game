-- Thêm bảng reviews và ratings
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating TINYINT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_review (product_id, user_id)
);

-- Thêm bảng seller ratings
CREATE TABLE IF NOT EXISTS seller_ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    buyer_id INT NOT NULL,
    rating TINYINT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    order_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    UNIQUE KEY unique_seller_rating (seller_id, buyer_id, order_id)
);

-- Thêm cột average_rating vào bảng products
ALTER TABLE products ADD COLUMN average_rating DECIMAL(3,2) DEFAULT 0.00;
ALTER TABLE products ADD COLUMN review_count INT DEFAULT 0;

-- Thêm cột average_rating vào bảng users
ALTER TABLE users ADD COLUMN average_rating DECIMAL(3,2) DEFAULT 0.00;
ALTER TABLE users ADD COLUMN rating_count INT DEFAULT 0;
