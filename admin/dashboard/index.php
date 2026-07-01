<?php
// 1. Cấu hình hằng số (Đảm bảo đúng đường dẫn tới thư mục gốc của project)
define('ROOT_PATH', 'C:/xampp/htdocs/mnopl/');

// 2. Kết nối Database
// File này sẽ tạo ra biến $pdo (dựa trên file config của bạn)
if (file_exists(ROOT_PATH . 'config/db.php')) {
    require_once ROOT_PATH . 'config/db.php'; 
} else {
    die("<b style='color:red;'>Lỗi:</b> Không tìm thấy file cấu hình database.");
}

// 3. Nạp file Controller
// Đảm bảo đường dẫn này đúng với cấu trúc thư mục của bạn
require_once ROOT_PATH . 'app/controllers/DashboardController.php';

// 4. Khởi tạo kết nối và Chạy
// Kiểm tra biến kết nối (File config của bạn dùng $pdo, file cũ dùng $conn)
$db = $pdo ?? $conn ?? null;

if (!$db) {
    die("<b style='color:red;'>Lỗi:</b> Không tìm thấy biến kết nối Database (\$pdo hoặc \$conn). Hãy kiểm tra lại file db.php.");
}

// Khởi chạy Controller
$controller = new DashboardController($db); 
$controller->index();
?>