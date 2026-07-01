<?php
/**
 * File: index.php
 * Vị trí: C:/xampp/htdocs/mnopl/admin/customers/api/customers/index.php
 * * Sử dụng dirname(__DIR__, 4) để tự động tìm về thư mục gốc 'mnopl/'
 * mà không cần phải đếm dấu ../ thủ công.
 */

// 1. Xác định đường dẫn gốc
$basePath = dirname(__DIR__, 4);

// 2. Nạp các file cần thiết (Đảm bảo các file này tồn tại ở các đường dẫn bên dưới)
require_once $basePath . '/config/database.php';
// Sửa thành:
require_once $basePath . '/admin/customers/models/CustomerManager.php';
require_once $basePath . '/admin/customers/controller/CustomerController.php';

// 3. Kiểm tra xem kết nối database ($pdo) đã sẵn sàng chưa (Tùy chọn nhưng tốt cho việc debug)
if (!isset($pdo)) {
    die("LỖI: Biến \$pdo chưa được khởi tạo. Hãy kiểm tra file /config/database.php");
}

// 4. Khởi tạo và xử lý request
try {
    $manager = new CustomerManager($pdo);
    $controller = new CustomerController($manager);
    
    // Lấy từ khóa tìm kiếm (nếu có)
    $keyword = $_GET['search'] ?? '';
    
    // Gọi hàm index
    $controller->index($keyword);
} catch (Exception $e) {
    echo "Đã xảy ra lỗi: " . $e->getMessage();
}
?>