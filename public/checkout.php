<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$rootPath = dirname(__DIR__);

require_once $rootPath . '/config/db.php';
require_once $rootPath . '/core/DBO.php';

$db = new DBO($pdo);

$checkCartSql = "SELECT COUNT(*) as total FROM user_cart";
$cartCheck = $db->fetch($checkCartSql);

if (!$cartCheck || $cartCheck['total'] == 0) {
    header("Location: /mnopl/index.php?controller=cart"); 
    exit();
}

$controllerPath = $rootPath . '/public/controllers/OrderController.php';

if (file_exists($controllerPath)) {
    require_once $controllerPath;
    $controller = new OrderController();
    $controller->checkout();
} else {
    die("Không tìm thấy tệp điều khiển thanh toán tại: " . $controllerPath);
}