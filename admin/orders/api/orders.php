<?php
header('Content-Type: application/json');

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../../../config/database.php';
require_once '../../../core/Database.php';

$db = new Database($pdo);

$orders = $db->fetchAll("
    SELECT o.*, c.full_name 
    FROM orders o 
    JOIN customers c ON o.customer_id = c.id 
    ORDER BY o.created_at DESC
");

foreach ($orders as &$o) {
    $o['total_amount_formatted'] = number_format($o['total_amount'], 0, ',', '.') . 'đ';
    $o['created_at_formatted'] = date('d/m/Y H:i', strtotime($o['created_at']));
}

echo json_encode([
    "status" => "success",
    "data" => $orders
]);