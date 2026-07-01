<?php
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__, 2));
}
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/admin/inventory/controllers/InventoryController.php';
$model      = new InventoryModel($pdo);
$controller = new InventoryController($model);

$message = '';
$error   = '';

// Tải file mẫu
if (isset($_GET['download_sample'])) {
    $controller->downloadSampleCsv(); // hàm này gọi exit bên trong
}

// Xử lý import
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import_csv'])) {
    $result = $controller->handleImportCsv();
    if ($result['success']) {
        $message = $result['message'];
    } else {
        $error = $result['message'];
    }
}

// Dữ liệu lookup
$form = $controller->getInsertFormData();
extract($form); // $warehouses, $suppliers
include 'views/inventory-insert.php';
?>
