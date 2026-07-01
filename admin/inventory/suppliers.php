<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
define('ROOT_PATH', dirname(__DIR__, 2)); 
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/admin/inventory/controllers/SupplierController.php';

$model      = new SupplierModel($pdo);
$controller = new SupplierController($model);
$suppliers = $model->getAll();

$error = '';

// --- Xử lý DELETE (trước khi xuất HTML) ---
if (isset($_GET['delete'])) {
    $result = $controller->handleDelete(intval($_GET['delete']));
    if ($result['success']) {
        header('Location: ' . $result['redirect']);
        exit;
    }
    $error = $result['message'];
}

// --- Xử lý POST (CREATE / UPDATE) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $result = $_POST['action'] === 'edit'
        ? $controller->handleUpdate()
        : $controller->handleCreate();

    if ($result['success']) {
        header('Location: ' . $result['redirect']);
        exit;
    }
    $error = $result['message'];
}

// --- Lấy dữ liệu View ---
$data = $controller->getListData();
extract($data); // $stats, $suppliers, $message
include 'views/supplier_list.php';
?>
