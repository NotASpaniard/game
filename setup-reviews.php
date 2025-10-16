<?php
require_once 'config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "<h1>Setup Reviews & Ratings System</h1>";
    
    // Create reviews table
    $sql = "CREATE TABLE IF NOT EXISTS reviews (
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
    )";
    $conn->exec($sql);
    echo "<p>✅ Reviews table created</p>";
    
    // Create seller_ratings table
    $sql = "CREATE TABLE IF NOT EXISTS seller_ratings (
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
    )";
    $conn->exec($sql);
    echo "<p>✅ Seller ratings table created</p>";
    
    // Add columns to products table
    try {
        $conn->exec("ALTER TABLE products ADD COLUMN average_rating DECIMAL(3,2) DEFAULT 0.00");
        echo "<p>✅ Added average_rating to products</p>";
    } catch (Exception $e) {
        echo "<p>⚠️ average_rating column may already exist</p>";
    }
    
    try {
        $conn->exec("ALTER TABLE products ADD COLUMN review_count INT DEFAULT 0");
        echo "<p>✅ Added review_count to products</p>";
    } catch (Exception $e) {
        echo "<p>⚠️ review_count column may already exist</p>";
    }
    
    // Add columns to users table
    try {
        $conn->exec("ALTER TABLE users ADD COLUMN average_rating DECIMAL(3,2) DEFAULT 0.00");
        echo "<p>✅ Added average_rating to users</p>";
    } catch (Exception $e) {
        echo "<p>⚠️ average_rating column may already exist</p>";
    }
    
    try {
        $conn->exec("ALTER TABLE users ADD COLUMN rating_count INT DEFAULT 0");
        echo "<p>✅ Added rating_count to users</p>";
    } catch (Exception $e) {
        echo "<p>⚠️ rating_count column may already exist</p>";
    }
    
    echo "<h2>✅ Reviews & Ratings System Setup Complete!</h2>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>
