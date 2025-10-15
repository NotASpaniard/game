<?php
require_once 'config/session.php';
require_once 'config/database.php';

echo "<h1>Test Session và Database</h1>";

// Test database connection
try {
    $db = new Database();
    $conn = $db->getConnection();
    echo "<p style='color: green;'>✅ Database connection: OK</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database connection: " . $e->getMessage() . "</p>";
}

// Test session
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>User ID in session: " . ($_SESSION['user_id'] ?? 'Not set') . "</p>";
echo "<p>Is logged in: " . (isLoggedIn() ? 'Yes' : 'No') . "</p>";

// Test getCurrentUser
$user = getCurrentUser();
if ($user) {
    echo "<p style='color: green;'>✅ getCurrentUser: OK</p>";
    echo "<p>User data: " . print_r($user, true) . "</p>";
} else {
    echo "<p style='color: orange;'>⚠️ getCurrentUser: No user found (this is normal if not logged in)</p>";
}

// Test database tables
try {
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<p>Database tables: " . implode(', ', $tables) . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error getting tables: " . $e->getMessage() . "</p>";
}

echo "<p><a href='index.php'>Go to homepage</a></p>";
echo "<p><a href='auth/dang-nhap.php'>Login page</a></p>";
?>
