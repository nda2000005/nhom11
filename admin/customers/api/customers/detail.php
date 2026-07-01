<?php
// 1. Ép kiểu nội dung trả về là JSON
header('Content-Type: application/json');

// 2. Nạp file
$basePath = dirname(__DIR__, 4); // Nếu file ở 4 cấp so với root
require_once $basePath . '/admin/customers/models/CustomerManager.php';
require_once $basePath . '/config/database.php';
require_once $basePath . '/admin/customers/controller/CustomerController.php';

// 3. Khởi tạo
$manager = new CustomerManager($pdo);
$controller = new CustomerController($manager);

// 4. Lấy ID
$id = intval($_GET['id'] ?? 0);

// Quan trọng: Hãy đảm bảo trong class CustomerController, 
// hàm detail() của bạn sẽ echo json_encode(...) và kết thúc bằng exit;
$controller->detail($id);