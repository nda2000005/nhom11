<?php
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__, 2));
}
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/admin/inventory/controllers/InventoryController.php';

$model      = new InventoryModel($pdo);
$controller = new InventoryController($model);

$error = null;

// Xử lý POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $controller->handleAddBatch();

    if ($result['success']) {
        if (InventoryController::isAjax()) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => true, 'message' => $result['message']], JSON_UNESCAPED_UNICODE);
            exit;
        }
        header('Location: index.php?success=true');
        exit;
    } else {
        if (InventoryController::isAjax()) {
            header('Content-Type: application/json; charset=utf-8', true, 422);
            echo json_encode(['success' => false, 'message' => $result['message']], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $error = $result['message'];
    }
}

// Dữ liệu form
$form = $controller->getAddFormData();
extract($form); // $suppliers, $warehouses, $variants
include 'views/inventory-add.php';
?>
