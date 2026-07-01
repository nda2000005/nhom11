<?php
// Bật header JSON ngay từ đầu
header('Content-Type: application/json; charset=utf-8');

try {
    // 1. Đường dẫn tới cấu hình DB (lùi 3 cấp để ra thư mục root/config)
    $dbPath = __DIR__ . '/../../../config/database.php';
    if (!file_exists($dbPath)) throw new Exception("Không tìm thấy file Database tại: " . $dbPath);
    require_once $dbPath;

    // 2. Đường dẫn tới Model và Controller
    $modelPath = __DIR__ . '/../models/order.model.php';
    $ctrlPath = __DIR__ . '/../controller/OrderController.php';

    if (!file_exists($modelPath)) throw new Exception("Không tìm thấy Model tại: " . $modelPath);
    if (!file_exists($ctrlPath)) throw new Exception("Không tìm thấy Controller tại: " . $ctrlPath);

    require_once $modelPath;
    require_once $ctrlPath;

    // 3. Khởi tạo (Lưu ý: dùng $pdo nếu file config của bạn tạo biến $pdo)
    if (!isset($pdo)) throw new Exception("Biến \$pdo không tồn tại trong file database.php");
    
    $manager = new OrderModel($pdo); 
    $controller = new OrderController($manager);

    // 4. Gọi hàm index
    $controller->index();

} catch (Exception $e) {
    // Nếu có lỗi, in ra thông báo lỗi dưới dạng JSON
    echo json_encode([
        "status" => "error", 
        "message" => $e->getMessage()
    ]);
}
?>