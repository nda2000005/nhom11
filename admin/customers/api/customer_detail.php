<?php
header('Content-Type: application/json');

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../../../config/database.php';

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Thiếu ID khách hàng"
    ]);
    exit;
}

// lấy khách hàng
$stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->execute([$id]);
$customer = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$customer) {
    echo json_encode([
        "status" => "error",
        "message" => "Không tìm thấy khách hàng"
    ]);
    exit;
}

// lấy đơn hàng
$stmt = $pdo->prepare("
    SELECT id, order_code, total_amount, current_status, created_at 
    FROM orders 
    WHERE customer_id = ? 
    ORDER BY created_at DESC
");
$stmt->execute([$id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// tính toán
$totalSpent = 0;
$successOrders = 0;

foreach ($orders as $o) {
    if ($o['current_status'] === 'COMPLETED') {
        $totalSpent += $o['total_amount'];
        $successOrders++;
    }
}

echo json_encode([
    "status" => "success",
    "data" => [
        "customer" => $customer,
        "orders" => $orders,
        "summary" => [
            "total_spent" => $totalSpent,
            "success_orders" => $successOrders,
            "total_orders" => count($orders)
        ]
    ]
]);