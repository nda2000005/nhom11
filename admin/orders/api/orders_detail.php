<?php
header('Content-Type: application/json');

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../../../config/database.php';
require_once '../../../core/Database.php';

$db = new Database($pdo);

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Thiếu ID đơn hàng"
    ]);
    exit;
}

$order = $db->fetch(
    "SELECT o.*, c.full_name, c.phone, c.address, c.email
     FROM orders o
     LEFT JOIN customers c ON o.customer_id = c.id
     WHERE o.id = ?", [$id]
);

if (!$order) {
    echo json_encode([
        "status" => "error",
        "message" => "Không tìm thấy đơn hàng"
    ]);
    exit;
}

$items = $db->fetchAll(
    "SELECT oi.*, p.name, v.variant_name, v.sku
     FROM order_items oi
     JOIN product_variants v ON oi.variant_id = v.id
     JOIN products p ON v.product_id = p.id
     WHERE oi.order_id = ?", [$id]
);

foreach ($items as &$item) {
    $item['price_formatted'] = number_format($item['price'], 0, ',', '.') . 'đ';
}

$order['total_amount_formatted'] = number_format($order['total_amount'], 0, ',', '.') . 'đ';
echo json_encode([
    "status" => "success",
    "data" => [
        "order" => $order,
        "items" => $items
    ]
]);