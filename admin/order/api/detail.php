<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../models/order.model.php';

$id = $_GET['id'] ?? 0;
if (!$id) {
    echo json_encode(["status" => "error", "message" => "Thiếu ID"]);
    exit;
}

$manager = new OrderModel($pdo);
$order = $manager->getOrderDetail($id);
$items = $manager->getOrderItems($id);

echo json_encode([
    "status" => "success",
    "order" => $order,
    "items" => $items
]);
?>