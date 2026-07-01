<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$basePath = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/') . '/mnopl';

$controllerPath = $basePath . '/public/controllers/CartController.php';

if (file_exists($controllerPath)) {
    require_once $controllerPath;
    $controller = new CartController();
    $controller->viewCart();
} else {
    die("Không tìm thấy tệp điều khiển giỏ hàng tại: " . $controllerPath);
}