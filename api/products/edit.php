<?php
// api/products/update.php
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

$id = intval($_POST['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "ID sản phẩm không hợp lệ."]);
    exit;
}

// Lấy thông tin chung
$name = trim($_POST['name'] ?? '');
$brand_id = !empty($_POST['brand_id']) ? $_POST['brand_id'] : null;
$category_id = !empty($_POST['category_id']) ? $_POST['category_id'] : null;
$description = $_POST['description'] ?? '';
$warranty_months = intval($_POST['warranty_months'] ?? 0);

// Lấy thông tin biến thể chính
$variant_id = intval($_POST['variant_id'] ?? 0);
$sku = trim($_POST['sku'] ?? '');
$variant_name = trim($_POST['variant_name'] ?? '');
$price = $_POST['price'] ?? 0;
$compare_at_price = $_POST['compare_at_price'] ?? 0;
$weight_gram = $_POST['weight_gram'] ?? 0;

if (empty($name)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Vui lòng nhập tên sản phẩm."]);
    exit;
}

try {
    $pdo->beginTransaction();
    
    // 1. Cập nhật bảng products
    $sqlProduct = "UPDATE products SET name = ?, brand_id = ?, category_id = ?, description = ?, warranty_months = ? WHERE id = ?";
    $stmt = $pdo->prepare($sqlProduct);
    $stmt->execute([$name, $brand_id, $category_id, $description, $warranty_months, $id]);

    // 2. Cập nhật bảng product_variants (nếu tồn tại biến thể)
    if ($variant_id > 0) {
        $sqlVariant = "UPDATE product_variants SET sku = ?, variant_name = ?, selling_price = ?, compare_at_price = ?, weight_gram = ? WHERE id = ?";
        $stmtVariant = $pdo->prepare($sqlVariant);
        $stmtVariant->execute([$sku, $variant_name, $price, $compare_at_price, $weight_gram, $variant_id]);
    }

    // 3. Xử lý tải ảnh mới lên (nếu có)
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../../src/img/"; 
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        
        $fileExt = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'svg', 'webp'];

        if (!in_array($fileExt, $allowed_ext)) {
            throw new Exception("Định dạng ảnh không hợp lệ! (Chỉ chấp nhận jpg, png, svg, webp)");
        }

        $filename = time() . '_' . uniqid() . '.' . $fileExt;
        
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $filename)) {
            $dbPath = 'src/img/' . $filename;
            
            // Chuyển ảnh chính cũ thành ảnh phụ (is_main = 0)
            $pdo->prepare("UPDATE product_media SET is_main = 0 WHERE product_id = ?")->execute([$id]);
            
            // Lưu ảnh mới làm ảnh chính (is_main = 1)
            $varId = ($variant_id > 0) ? $variant_id : null;
            $stmtMedia = $pdo->prepare("INSERT INTO product_media (product_id, variant_id, file_path, is_main) VALUES (?, ?, ?, 1)");
            $stmtMedia->execute([$id, $varId, $dbPath]);
        }
    }

    $pdo->commit();
    echo json_encode(["status" => "success", "message" => "Cập nhật sản phẩm thành công!"]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Lỗi hệ thống: " . $e->getMessage()]);
}