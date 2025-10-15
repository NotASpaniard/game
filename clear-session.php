<?php
session_start();
session_unset();
session_destroy();

echo "<h2>Session đã được xóa</h2>";
echo "<p>Bạn có thể truy cập các trang sau:</p>";
echo "<ul>";
echo "<li><a href='auth/dang-nhap.php'>Đăng nhập</a></li>";
echo "<li><a href='auth/dang-ky.php'>Đăng ký</a></li>";
echo "<li><a href='index.php'>Trang chủ</a></li>";
echo "<li><a href='setup.php'>Setup Database</a></li>";
echo "<li><a href='demo-san-pham.php'>Demo sản phẩm</a></li>";
echo "</ul>";
?>
