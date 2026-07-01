<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$rootPath = dirname(__DIR__);

require_once $rootPath . '/public/controllers/ProductController.php';

$controller = new ProductController();
$controller->detail();