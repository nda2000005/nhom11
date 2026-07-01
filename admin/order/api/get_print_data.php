<?php
// Bật hiển thị lỗi để dễ kiểm tra
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

// Định nghĩa thư mục gốc (mnopl)
$basePath = dirname(__DIR__, 3);

// --- NHÚNG FILE ---
// Đường dẫn này phải khớp chính xác với cây thư mục của bạn
require_once $basePath . '/config/database.php';
require_once $basePath . '/admin/order/models/order.model.php'; // Đã sửa đường dẫn

try {
    // Lưu ý: Đảm bảo class trong file order.model.php tên là OrderModel
    // Nếu class trong file đó tên khác, bạn cần đổi lại cho khớp
    $model = new OrderModel($pdo); 
    
    // Lấy ID từ URL
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($id <= 0) {
        throw new Exception("ID đơn hàng không hợp lệ");
    }

    // Lấy dữ liệu
    $order = $model->getOrderDetail($id);
    $items = $model->getOrderItems($id);

    if (!$order) {
        throw new Exception("Không tìm thấy đơn hàng");
    }

    // Trả về JSON
    echo json_encode([
        'status' => 'success',
        'order' => $order,
        'items' => $items
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>