<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { margin: 20px 0; padding: 20px; border: 1px solid #ccc; }
        .error { color: red; }
        .success { color: green; }
        .product-image { max-width: 200px; border: 1px solid #ddd; margin: 10px; }
    </style>
</head>
<body>
    <h1>Debug Test - Kiểm tra toàn bộ hệ thống</h1>
    
    <?php
    // Test 1: Kiểm tra cơ bản
    echo "<div class='test-section'>";
    echo "<h2>1. Test Cơ Bản</h2>";
    
    try {
        require_once 'config/session.php';
        echo "<p class='success'>✅ Session loaded</p>";
    } catch (Exception $e) {
        echo "<p class='error'>❌ Session error: " . $e->getMessage() . "</p>";
    }
    
    try {
        require_once 'config/database.php';
        $db = new Database();
        $conn = $db->getConnection();
        echo "<p class='success'>✅ Database connected</p>";
        
        // Test query
        $stmt = $conn->query("SELECT COUNT(*) as count FROM products");
        $result = $stmt->fetch();
        echo "<p class='success'>✅ Database query works - " . $result['count'] . " products</p>";
    } catch (Exception $e) {
        echo "<p class='error'>❌ Database error: " . $e->getMessage() . "</p>";
    }
    echo "</div>";
    
    // Test 2: Test ảnh
    echo "<div class='test-section'>";
    echo "<h2>2. Test Ảnh Sản Phẩm</h2>";
    
    try {
        require_once 'config/image-helper.php';
        
        // Test với sản phẩm có ảnh
        $image_url = getProductImage(11);
        echo "<p>Product 11 image: " . htmlspecialchars($image_url) . "</p>";
        echo "<p>File exists: " . (file_exists($image_url) ? "✅ Yes" : "❌ No") . "</p>";
        
        if (file_exists($image_url)) {
            echo "<img src='" . htmlspecialchars($image_url) . "' class='product-image' alt='Product 11'>";
        }
        
        // Test với sản phẩm không có ảnh
        $image_url = getProductImage(5);
        echo "<p>Product 5 image: " . htmlspecialchars($image_url) . "</p>";
        echo "<p>File exists: " . (file_exists($image_url) ? "✅ Yes" : "❌ No") . "</p>";
        
        if (file_exists($image_url)) {
            echo "<img src='" . htmlspecialchars($image_url) . "' class='product-image' alt='Default'>";
        }
        
    } catch (Exception $e) {
        echo "<p class='error'>❌ Image error: " . $e->getMessage() . "</p>";
    }
    echo "</div>";
    
    // Test 3: Test session
    echo "<div class='test-section'>";
    echo "<h2>3. Test Session</h2>";
    echo "<p>Session ID: " . session_id() . "</p>";
    echo "<p>User ID: " . ($_SESSION['user_id'] ?? 'Not set') . "</p>";
    echo "<p>Username: " . ($_SESSION['username'] ?? 'Not set') . "</p>";
    echo "<p>Is logged in: " . (isLoggedIn() ? "✅ Yes" : "❌ No") . "</p>";
    echo "</div>";
    
    // Test 4: Test API
    echo "<div class='test-section'>";
    echo "<h2>4. Test API</h2>";
    echo "<p>Cart API exists: " . (file_exists('api/cart.php') ? "✅ Yes" : "❌ No") . "</p>";
    echo "<p>Products API exists: " . (file_exists('api/products.php') ? "✅ Yes" : "❌ No") . "</p>";
    echo "</div>";
    ?>
    
    <div class="test-section">
        <h2>5. Test JavaScript</h2>
        <button onclick="testJS()">Test JavaScript</button>
        <div id="js-result"></div>
    </div>
    
    <div class="test-section">
        <h2>6. Test Add to Cart</h2>
        <button onclick="testAddToCart()">Test Add to Cart</button>
        <div id="cart-result"></div>
    </div>
    
    <script>
    function testJS() {
        document.getElementById('js-result').innerHTML = '<p class="success">✅ JavaScript hoạt động</p>';
    }
    
    async function testAddToCart() {
        try {
            const response = await fetch('api/cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=add&product_id=11&quantity=1'
            });
            
            const data = await response.json();
            document.getElementById('cart-result').innerHTML = 
                '<p class="' + (data.success ? 'success' : 'error') + '">' + 
                (data.success ? '✅' : '❌') + ' ' + data.message + '</p>';
        } catch (error) {
            document.getElementById('cart-result').innerHTML = 
                '<p class="error">❌ Error: ' + error.message + '</p>';
        }
    }
    </script>
</body>
</html>
