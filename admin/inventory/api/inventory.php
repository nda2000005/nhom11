<?php
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__, 3));
}
require_once ROOT_PATH . '/config/database.php';

if (strpos($_SERVER['SCRIPT_NAME'], 'suppliers.php') !== false) {
    require_once ROOT_PATH . '/admin/inventory/models/SupplierModel.php';
    require_once ROOT_PATH . '/admin/inventory/controllers/SupplierController.php';
} else {
    require_once ROOT_PATH . '/admin/inventory/models/InventoryModel.php';
if (isset($_GET['test_mode'])) {
    $model = new InventoryModel($pdo);
    
    $data = $model->getInventorySummary(); 
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'data' => $data]);
    die();
}
    require_once ROOT_PATH . '/admin/inventory/controllers/InventoryController.php';
}

// ── Helpers ─────────────────────────────────────────────────────────────────

function jsonResponse(mixed $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function jsonError(string $message, int $status = 400): never
{
    jsonResponse(['success' => false, 'message' => $message], $status);
}

function getJsonBody(): array
{
    $raw = file_get_contents('php://input');
    return !empty($raw) ? (json_decode($raw, true) ?? []) : [];
}

// ── Router ───────────────────────────────────────────────────────────────────

$method = $_SERVER['REQUEST_METHOD'];

$requestUri  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath    = '/api/inventory';
$path        = trim(substr($requestUri, strlen($basePath)), '/');
$segments    = $path !== '' ? explode('/', $path) : [];

$model      = new InventoryModel($pdo);
$controller = new InventoryController($model);

// ── Routing ──────────────────────────────────────────────────────────────────

if ($method === 'GET' && ($segments[0] ?? '') === 'stats') {
    jsonResponse([
        'success'        => true,
        'total_value'    => $model->getTotalValue(),
        'total_quantity' => $model->getTotalQuantity(),
        'low_stock'      => $model->getLowStockCount(),
    ]);
}

if ($method === 'GET' && ($segments[0] ?? '') === 'summary') {
    jsonResponse(['success' => true, 'data' => $model->getInventorySummary()]);
}

if ($method === 'GET' && ($segments[0] ?? '') === 'batches') {
    $limit = max(1, min(100, intval($_GET['limit'] ?? 10)));
    jsonResponse(['success' => true, 'data' => $model->getBatchHistory($limit)]);
}
if ($method === 'GET' && ($segments[0] ?? '') === 'quote') {
    $variantId  = intval($_GET['variant_id']  ?? 0);
    $supplierId = intval($_GET['supplier_id'] ?? 0);

    if (!$variantId || !$supplierId) {
        jsonError('Thiếu tham số variant_id hoặc supplier_id');
    }

    $result = $controller->getQuote($variantId, $supplierId);
    jsonResponse($result);
}

if ($method === 'GET' && ($segments[0] ?? '') === 'sample-csv') {
    $controller->downloadSampleCsv(); // gọi exit bên trong
}

if ($method === 'POST' && ($segments[0] ?? '') === 'batch' && !isset($segments[1])) {
    $body = getJsonBody();
    $_POST = array_merge($_POST, $body);

    $result = $controller->handleAddBatch();

    if ($result['success']) {
        jsonResponse(['success' => true, 'message' => $result['message']], 201);
    } else {
        jsonError($result['message'], 422);
    }
}

if ($method === 'PUT' && ($segments[0] ?? '') === 'batch' && isset($segments[1])) {
    $batchId = intval($segments[1]);
    $body    = getJsonBody();

    $model->updateBatch(
        $batchId,
        intval($body['warehouse_id'] ?? 0),
        intval($body['supplier_id']  ?? 0),
        floatval(str_replace(['.', ','], '', $body['cost_price'] ?? '0')),
        intval($body['quantity']     ?? 0),
        trim($body['invoice_no']     ?? ''),
        trim($body['note']           ?? '')
    );

    jsonResponse(['success' => true, 'message' => 'Cập nhật lô hàng thành công']);
}
if ($method === 'DELETE' && ($segments[0] ?? '') === 'batch' && isset($segments[1])) {
    $model->softDeleteBatch(intval($segments[1]));
    jsonResponse(['success' => true, 'message' => 'Đã xóa lô hàng']);
}

if ($method === 'POST' && ($segments[0] ?? '') === 'import-csv') {
    $result = $controller->handleImportCsv();
    $status = $result['success'] ? 200 : 422;
    jsonResponse($result, $status);
}

// ── 404 ──────────────────────────────────────────────────────────────────────
jsonError("Endpoint không tồn tại: {$method} /{$path}", 404);