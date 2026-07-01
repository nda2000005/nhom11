<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

$rootPath = dirname(__DIR__);

$controllerName = isset($_GET['c']) ? ucfirst($_GET['c']) . 'Controller' : 'HomeController';
$actionName     = isset($_GET['a']) ? $_GET['a'] : 'index';

$controllerFile = $rootPath . '/public/controllers/' . $controllerName . '.php';

if (file_exists($controllerFile)) {
    require_once $controllerFile;
    if (class_exists($controllerName)) {
        $controllerObj = new $controllerName();
        if (method_exists($controllerObj, $actionName)) {
            $controllerObj->$actionName();
        } else {
            die("Không tìm thấy hành động (action) <strong>{$actionName}</strong> trong controller <strong>{$controllerName}</strong>.");
        }
    } else {
        die("Không tìm thấy lớp (class) <strong>{$controllerName}</strong>.");
    }
} else {
    die("Không tìm thấy tệp điều khiển (controller) tại: " . htmlspecialchars($controllerFile));
}