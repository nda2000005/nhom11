<?php
// api/products/delete.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require_once '../../config/database.php';
require_once '../../core/Database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Phương thức không được hỗ trợ."]);
    exit;
}

$db = new Database($pdo);
$data = json_decode(file_get_contents("php://input"));
$id = isset($data->id) ? (int)$data->id : 0;

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "ID sản phẩm không hợp lệ."]);
    exit;
}

try {
    // 1. Kiểm tra ràng buộc đơn hàng
    $checkOrder = $db->fetch("SELECT COUNT(*) as total FROM order_items oi JOIN product_variants pv ON oi.variant_id = pv.id WHERE pv.product_id = ?", [$id]);

    if ($checkOrder['total'] > 0) {
        echo json_encode(["status" => "error", "message" => "Không thể xóa: Sản phẩm này đã có trong đơn hàng."]);
        exit;
    }

    $pdo->beginTransaction();
    
    // 2. Xóa file ảnh vật lý
    $images = $db->fetchAll("SELECT file_path FROM product_media WHERE product_id = ?", [$id]);
    foreach ($images as $img) {
        $filePath = "../../" . $img['file_path'];
        if (!empty($img['file_path']) && file_exists($filePath)) {
            unlink($filePath);
        }
    }

    // 3. Xóa các dữ liệu liên quan (Attribute, Inventory, Variants...)
    $variants = $db->fetchAll("SELECT id FROM product_variants WHERE product_id = ?", [$id]);
    $variantIds = array_column($variants, 'id');

    if (!empty($variantIds)) {
        $placeholders = implode(',', array_fill(0, count($variantIds), '?'));
        $pdo->prepare("DELETE FROM product_attribute_values WHERE variant_id IN ($placeholders)")->execute($variantIds);
        $pdo->prepare("DELETE FROM serial_numbers WHERE variant_id IN ($placeholders)")->execute($variantIds);
        $pdo->prepare("DELETE FROM inventory_batches WHERE variant_id IN ($placeholders)")->execute($variantIds);
    }

    $pdo->prepare("DELETE FROM product_media WHERE product_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM product_variants WHERE product_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);

    $pdo->commit();
    echo json_encode(["status" => "success", "message" => "Đã xóa sản phẩm và các dữ liệu liên quan thành công!"]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Lỗi hệ thống: " . $e->getMessage()]);
}