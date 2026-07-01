<?php
// api/products/add_variant.php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require_once '../../config/database.php';
require_once '../../core/Database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Phương thức không được phép"]);
    exit;
}

try {
    $pdo->beginTransaction();

    $product_id = $_POST['product_id'] ?? 0;
    $variantName = trim($_POST['variant_name'] ?? '');
    $sku = strtoupper(trim($_POST['sku'] ?? ''));
    $price = $_POST['price'] ?? 0;
    
    // 1. Insert vào bảng product_variants
    $sqlVariant = "INSERT INTO product_variants (product_id, sku, variant_name, selling_price, compare_at_price, weight_gram, is_active) 
                   VALUES (?, ?, ?, ?, ?, ?, 1)";
    $stmtVariant = $pdo->prepare($sqlVariant);
    $stmtVariant->execute([
        $product_id, $sku, $variantName, $price,
        $_POST['compare_at_price'] ?? 0,
        $_POST['weight_gram'] ?? 0
    ]);
    
    $variant_id = $pdo->lastInsertId();

    // 2. Xử lý thuộc tính (Attributes)
    if (!empty($_POST['attr_ids']) && !empty($_POST['attr_values'])) {
        $attr_ids = $_POST['attr_ids'];
        $attr_vals = $_POST['attr_values'];
        $sqlAttr = "INSERT INTO product_attribute_values (variant_id, attribute_id, attr_value) VALUES (?, ?, ?)";
        $stmtAttr = $pdo->prepare($sqlAttr);

        for ($i = 0; $i < count($attr_ids); $i++) {
            if (!empty($attr_ids[$i]) && !empty($attr_vals[$i])) {
                $stmtAttr->execute([$variant_id, $attr_ids[$i], trim($attr_vals[$i])]);
            }
        }
    }

    // 3. Xử lý Upload Ảnh
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "../../src/img/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        
        $fileExt = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $filename = time() . '_' . uniqid() . '.' . $fileExt;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $filename)) {
            $dbPath = 'src/img/' . $filename;
            $stmtMedia = $pdo->prepare("INSERT INTO product_media (product_id, variant_id, file_path, is_main) VALUES (?, ?, ?, 0)");
            $stmtMedia->execute([$product_id, $variant_id, $dbPath]);
        }
    }

    $pdo->commit();
    echo json_encode(["status" => "success", "message" => "Thêm phiên bản thành công!", "product_id" => $product_id]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Lỗi hệ thống: " . $e->getMessage()]);
}