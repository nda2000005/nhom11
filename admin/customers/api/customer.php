<?php
header('Content-Type: application/json');

require_once '../../../config/database.php';
require_once '../../../core/CustomerManager.php';

try {
    $search = $_GET['search'] ?? '';

    $customerManager = new CustomerManager($pdo);
    $customers = $customerManager->getAllCustomers($search);

    echo json_encode([
        "status" => "success",
        "data" => $customers
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}