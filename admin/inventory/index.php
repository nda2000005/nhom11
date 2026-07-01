<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
$root = dirname(__DIR__, 2);

require_once $root . '/config/database.php';
require_once __DIR__ . '/models/InventoryModel.php'; 
require_once __DIR__ . '/controllers/InventoryController.php';
$model      = new InventoryModel($pdo);
$controller = new InventoryController($model);
$controller->handleDeleteBatch();
$controller->handleEditBatch();

$data = $controller->getIndexData();
extract($data); 
include 'views/index.php';
?>
