<?php
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__, 3));
}
require_once ROOT_PATH . '/config/database.php';
if (strpos($_SERVER['SCRIPT_NAME'], 'suppliers.php') !== false) {
    require_once ROOT_PATH . '/admin/inventory/models/SupplierModel.php';
if (isset($_GET['test_mode'])) {
    $model = new SupplierModel($pdo);
    
    $data = $model->getAll(); 
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'data' => $data]);
    die();
}
    require_once ROOT_PATH . '/admin/inventory/controllers/SupplierController.php';
    $model      = new SupplierModel($pdo);
    $controller = new SupplierController($model);
if (strpos($_SERVER['SCRIPT_NAME'], 'suppliers.php') !== false) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $body = getJsonBody();
        $_POST = parseSupplierPayload($body);
        $result = $controller->handleCreate(); // Lúc này $controller đã tồn tại, sẽ hết lỗi
        jsonResponse($result, $result['success'] ? 201 : 422);
    }
}
} else {
    require_once ROOT_PATH . '/admin/inventory/models/InventoryModel.php';
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
function parseSupplierPayload(array $body): array
{
    $required = ['name', 'phone'];
    foreach ($required as $field) {
        if (empty($body[$field])) {
            jsonError("Thiếu trường bắt buộc: {$field}");
        }
    }

    $allowedStatus = ['ACTIVE', 'INACTIVE'];
    $status = strtoupper($body['status'] ?? 'ACTIVE');
    if (!in_array($status, $allowedStatus, true)) {
        jsonError("Giá trị status không hợp lệ. Chấp nhận: ACTIVE | INACTIVE");
    }

    return [
        'name'         => trim($body['name']),
        'tax_code'     => trim($body['tax_code']     ?? ''),
        'contact_name' => trim($body['contact_name'] ?? ''),
        'phone'        => trim($body['phone']),
        'email'        => trim($body['email']        ?? ''),
        'address'      => trim($body['address']      ?? ''),
        'website'      => trim($body['website']      ?? ''),
        'status'       => $status,
        'note'         => trim($body['note']         ?? ''),
    ];
}

// ── Router ───────────────────────────────────────────────────────────────────

$method     = $_SERVER['REQUEST_METHOD'];
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath   = '/api/suppliers';
$path       = trim(substr($requestUri, strlen($basePath)), '/');
$segments   = $path !== '' ? explode('/', $path) : [];

$model      = new SupplierModel($pdo);
$controller = new SupplierController($model);

// ── Routing ──────────────────────────────────────────────────────────────────

if ($method === 'GET' && ($segments[0] ?? '') === 'stats') {
    jsonResponse(['success' => true, 'data' => $model->getStats()]);
}
if ($method === 'GET' && empty($segments[0])) {
    jsonResponse(['success' => true, 'data' => $model->getAll()]);
}
if ($method === 'GET' && isset($segments[0]) && is_numeric($segments[0])) {
    $id  = intval($segments[0]);
    $row = $model->findById($id);

    if (!$row) {
        jsonError("Không tìm thấy nhà cung cấp ID {$id}", 404);
    }

    jsonResponse(['success' => true, 'data' => $row]);
}
if ($method === 'POST' && empty($segments[0])) {
    $body    = getJsonBody();
    $payload = parseSupplierPayload($body);
    $_POST = $payload;
    $result = $controller->handleCreate();

    if ($result['success']) {
        $newId  = $model->getAll()[0]['id'] ?? null; 
        jsonResponse(['success' => true, 'message' => 'Thêm nhà cung cấp thành công', 'id' => $newId], 201);
    } else {
        jsonError($result['message'], 422);
    }
}
if ($method === 'PUT' && isset($segments[0]) && is_numeric($segments[0])) {
    $id  = intval($segments[0]);
    $row = $model->findById($id);

    if (!$row) {
        jsonError("Không tìm thấy nhà cung cấp ID {$id}", 404);
    }

    $body    = getJsonBody();
    $payload = parseSupplierPayload($body);

    try {
        $model->update($id, $payload);
        jsonResponse(['success' => true, 'message' => 'Cập nhật thành công']);
    } catch (PDOException $e) {
        jsonError('Lỗi cập nhật: ' . $e->getMessage(), 500);
    }
}
if ($method === 'DELETE' && isset($segments[0]) && is_numeric($segments[0])) {
    $id  = intval($segments[0]);
    $row = $model->findById($id);

    if (!$row) {
        jsonError("Không tìm thấy nhà cung cấp ID {$id}", 404);
    }

    if ($model->hasInventoryHistory($id)) {
        jsonError('Không thể xóa nhà cung cấp này vì đã có lịch sử nhập hàng.', 409);
    }

    try {
        $model->delete($id);
        jsonResponse(['success' => true, 'message' => 'Đã xóa nhà cung cấp']);
    } catch (PDOException $e) {
        jsonError('Lỗi xóa: ' . $e->getMessage(), 500);
    }
}

// ── 404 ──────────────────────────────────────────────────────────────────────
jsonError("Endpoint không tồn tại: {$method} /{$path}", 404);