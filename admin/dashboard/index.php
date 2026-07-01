<?php

define('ROOT_PATH', 'C:/xampp/htdocs/mnopl/');

if (file_exists(ROOT_PATH . 'config/db.php')) {
    require_once ROOT_PATH . 'config/db.php'; 
} else {
    die("<b style='color:red;'>Lỗi:</b> Không tìm thấy file cấu hình database.");
}

require_once ROOT_PATH . 'app/controllers/DashboardController.php';

$db = $pdo ?? $conn ?? null;

if (!$db) {
    die("<b style='color:red;'>Lỗi:</b> Không tìm thấy biến kết nối Database (\$pdo hoặc \$conn). Hãy kiểm tra lại file db.php.");
}

$controller = new DashboardController($db); 
$controller->index();
?>