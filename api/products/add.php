<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require_once '../../config/database.php';
require_once '../../core/Database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method Not Allowed"]);
    exit;
}

// Lấy dữ liệu
$name = trim($_POST['name'] ?? '');
$sku = strtoupper(trim($_POST['sku'] ?? ''));
$price = $_POST['price'] ?? 0;
$category_id = $_POST['category_id'] ?? null;
$description = $_POST['description'] ?? '';

// Dữ liệu kho hàng ban đầu
$quantity = intval($_POST['quantity'] ?? 0);
$warehouse_id = !empty($_POST['warehouse_id']) ? $_POST['warehouse_id'] : null;
$supplier_id = !empty($_POST['supplier_id']) ? $_POST['supplier_id'] : null;

// Validation cơ bản
if (empty($name) || empty($sku) || empty($price)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Vui lòng nhập Tên, SKU và Giá bán."]);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Insert vào bảng products
    $stmtProd = $pdo->prepare("INSERT INTO products (name, category_id, description, is_active) VALUES (?, ?, ?, 1)");
    $stmtProd->execute([$name, $category_id, $description]);
    $product_id = $pdo->lastInsertId();

    // 2. Tạo biến thể mặc định (Default Variant)
    $stmtVariant = $pdo->prepare("INSERT INTO product_variants (product_id, sku, variant_name, selling_price, is_active) VALUES (?, ?, ?, ?, 1)");
    $stmtVariant->execute([$product_id, $sku, 'Mặc định', $price]);
    $variant_id = $pdo->lastInsertId();

    // 3. Xử lý Upload Ảnh
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $uploadDir = '../../src/img/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $fileExt = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $fileName = time() . '_' . uniqid() . '.' . $fileExt;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $fileName)) {
            $dbPath = 'src/img/' . $fileName;
            $stmtMedia = $pdo->prepare("INSERT INTO product_media (product_id, variant_id, file_path, is_main) VALUES (?, ?, ?, 1)");
            $stmtMedia->execute([$product_id, $variant_id, $dbPath]);
        }
    }

    // 4. Cập nhật kho hàng nếu có nhập số lượng ban đầu
    if ($quantity > 0 && $warehouse_id && $supplier_id) {
       $stmtInv = $pdo->prepare("
    INSERT INTO inventory_batches (variant_id, remaining_quantity, cost_price) 
    VALUES (?, ?, ?)
    ");
    $stmtInv->execute([$variant_id, $quantity, 0]);
    }

    $pdo->commit();
    http_response_code(201);
    echo json_encode([
        "status" => "success", 
        "message" => "Thêm sản phẩm thành công!",
        "product_id" => $product_id 
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Lỗi hệ thống: " . $e->getMessage()]);
}