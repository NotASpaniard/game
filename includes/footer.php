    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Game Trading Platform</h3>
                    <p>Nền tảng giao dịch game uy tín và an toàn. Kết nối người chơi, tạo cộng đồng gaming lớn mạnh.</p>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h4>Dịch vụ</h4>
                    <ul>
                        <li><a href="<?php echo $base_url ?? ''; ?>san-pham/index.php">Mua sản phẩm</a></li>
                        <li><a href="<?php echo $base_url ?? ''; ?>user/them-san-pham.php">Bán sản phẩm</a></li>
                        <li><a href="<?php echo $base_url ?? ''; ?>tim-kiem.php">Tìm kiếm</a></li>
                        <li><a href="<?php echo $base_url ?? ''; ?>user/gio-hang.php">Giỏ hàng</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Hỗ trợ</h4>
                    <ul>
                        <li><a href="<?php echo $base_url ?? ''; ?>huong-dan.php">Hướng dẫn</a></li>
                        <li><a href="<?php echo $base_url ?? ''; ?>tro-giup.php">Trợ giúp</a></li>
                        <li><a href="<?php echo $base_url ?? ''; ?>lien-he.php">Liên hệ</a></li>
                        <li><a href="<?php echo $base_url ?? ''; ?>bao-mat.php">Bảo mật</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Pháp lý</h4>
                    <ul>
                        <li><a href="<?php echo $base_url ?? ''; ?>dieu-khoan.php">Điều khoản</a></li>
                        <li><a href="<?php echo $base_url ?? ''; ?>bao-mat.php">Chính sách bảo mật</a></li>
                        <li><a href="<?php echo $base_url ?? ''; ?>lien-he.php">Liên hệ</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Liên hệ</h4>
                    <div class="contact-info">
                        <p><i class="fas fa-envelope"></i> support@gametrading.com</p>
                        <p><i class="fas fa-phone"></i> 1900-1234</p>
                        <p><i class="fas fa-map-marker-alt"></i> Hà Nội, Việt Nam</p>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <div class="footer-bottom-content">
                    <p>&copy; 2024 Game Trading Platform. Tất cả quyền được bảo lưu.</p>
                    <div class="footer-links">
                        <a href="<?php echo $base_url ?? ''; ?>dieu-khoan.php">Điều khoản</a>
                        <a href="<?php echo $base_url ?? ''; ?>bao-mat.php">Bảo mật</a>
                        <a href="<?php echo $base_url ?? ''; ?>lien-he.php">Liên hệ</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <style>
        .footer {
            background: #2c3e50;
            color: white;
            margin-top: 50px;
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            padding: 40px 0;
        }
        
        .footer-section h3 {
            color: #3498db;
            margin-bottom: 15px;
            font-size: 1.3em;
        }
        
        .footer-section h4 {
            color: #ecf0f1;
            margin-bottom: 15px;
            font-size: 1.1em;
        }
        
        .footer-section p {
            color: #bdc3c7;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        
        .footer-section ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .footer-section ul li {
            margin-bottom: 8px;
        }
        
        .footer-section ul li a {
            color: #bdc3c7;
            text-decoration: none;
            transition: color 0.2s;
        }
        
        .footer-section ul li a:hover {
            color: #3498db;
        }
        
        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 15px;
        }
        
        .social-link {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: #34495e;
            color: white;
            border-radius: 50%;
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .social-link:hover {
            background: #3498db;
            transform: translateY(-2px);
        }
        
        .contact-info p {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }
        
        .contact-info i {
            color: #3498db;
            width: 20px;
        }
        
        .footer-bottom {
            border-top: 1px solid #34495e;
            padding: 20px 0;
        }
        
        .footer-bottom-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .footer-bottom p {
            color: #bdc3c7;
            margin: 0;
        }
        
        .footer-links {
            display: flex;
            gap: 20px;
        }
        
        .footer-links a {
            color: #bdc3c7;
            text-decoration: none;
            transition: color 0.2s;
        }
        
        .footer-links a:hover {
            color: #3498db;
        }
        
        @media (max-width: 768px) {
            .footer-content {
                grid-template-columns: 1fr;
                gap: 20px;
                padding: 30px 0;
            }
            
            .footer-bottom-content {
                flex-direction: column;
                text-align: center;
            }
            
            .footer-links {
                justify-content: center;
            }
        }
    </style>
</body>
</html>
