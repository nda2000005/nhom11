<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) session_start();

$rootPath = dirname(dirname(__DIR__)); 

try {
    require_once $rootPath . '/config/db.php';
    require_once $rootPath . '/core/DBO.php';
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Lỗi kết nối hệ thống']);
    exit();
}

$db = new DBO($pdo);

$inputJSON = json_decode(file_get_contents('php://input'), true);
$action = $inputJSON['action'] ?? $_REQUEST['action'] ?? '';
$variantId = (int)($inputJSON['variant_id'] ?? $_REQUEST['variant_id'] ?? 0);
$qty = (int)($inputJSON['qty'] ?? $inputJSON['quantity'] ?? $_REQUEST['qty'] ?? $_REQUEST['quantity'] ?? 1);

if ($variantId > 0) {
    switch ($action) {
        case 'add':
            $sqlAdd = "INSERT INTO user_cart (variant_id, qty) 
                       VALUES (:v_id, :qty) 
                       ON DUPLICATE KEY UPDATE qty = qty + :qty_add";
            $stmtAdd = $pdo->prepare($sqlAdd);
            $stmtAdd->execute([
                'v_id'    => $variantId,
                'qty'     => $qty,
                'qty_add' => $qty
            ]);
            break;

        case 'update':
            if ($qty <= 0) {
                $pdo->prepare("DELETE FROM user_cart WHERE variant_id = ?")->execute([$variantId]);
            } else {
                $pdo->prepare("UPDATE user_cart SET qty = ? WHERE variant_id = ?")->execute([$qty, $variantId]);
            }
            break;

        case 'remove':
            $pdo->prepare("DELETE FROM user_cart WHERE variant_id = ?")->execute([$variantId]);
            break;
    }
}

if ($action === 'clear') {
    $pdo->exec("DELETE FROM user_cart");
}

// 3. Trả về kết quả
$isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest');
$isJsonRequest = (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

if (isset($inputJSON['action']) || $isAjax || $isJsonRequest) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => 'success', 
        'message' => 'Thành Công',
        'data' => [
            'action' => $action,
            'variant_id' => $variantId,
            'qty_processed' => $qty
        ]
    ]);
    exit();
} else {
    if ($action === 'add') {
        header("Location: ../cart.php");
    } else {
        $referer = $_SERVER['HTTP_REFERER'] ?? '../cart.php';
        header("Location: $referer");
    }
    exit();
}