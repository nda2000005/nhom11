<?php
// 1. Cấu hình & Database
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../../config/database.php';
require_once '../../core/Database.php';

$db = new Database($pdo);
$message = '';
$error = '';
$imported_count = 0;


try {
    $warehouses = $pdo->query("SELECT * FROM warehouses ORDER BY name")->fetchAll();
    $suppliers = $pdo->query("SELECT * FROM suppliers ORDER BY name")->fetchAll();
} catch (Exception $e) { $warehouses = []; $suppliers = []; }


if (isset($_GET['download_sample'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="mau_nhap_san_pham.csv"');
    $output = fopen('php://output', 'w');
    
    fputcsv($output, ['Ten San Pham', 'SKU', 'Thuong Hieu', 'ID Danh Muc', 'Gia Ban', 'Gia Goc (So sanh)', 'So Luong', 'Bao Hanh (Thang)', 'Mo Ta']);
    
    fputcsv($output, ['Áo Thun Basic', 'AT-001', 'Uniqlo', '1', '150000', '180000', '50', '12', 'Chất liệu 100% Cotton']);
    fputcsv($output, ['Quần Jean Slim', 'QJ-002', 'Levi', '2', '550000', '0', '20', '6', 'Form ôm vừa vặn']);
    fclose($output);
    exit;
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['import'])) {
    if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != '') {
        
        $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
        $default_warehouse_id = $_POST['warehouse_id'] ?? null;
        $default_supplier_id = $_POST['supplier_id'] ?? null;

        if (strtolower($ext) == 'csv') {
            $file = $_FILES['file']['tmp_name'];
            $handle = fopen($file, "r");
            fgetcsv($handle); 

            try {
                $pdo->beginTransaction();

                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    
                    $name = trim($data[0] ?? '');
                    $sku = strtoupper(trim($data[1] ?? ''));
                    $brand_name = trim($data[2] ?? '');
                    $category_id = intval($data[3] ?? 1);
                    $price = floatval($data[4] ?? 0);
                    $compare_price = floatval($data[5] ?? 0);
                    $qty = intval($data[6] ?? 0);
                    $warranty = intval($data[7] ?? 0);
                    $desc = trim($data[8] ?? '');

                    if (empty($name) || empty($sku)) continue;

                    
                    $checkSku = $pdo->prepare("SELECT COUNT(*) FROM product_variants WHERE sku = ?");
                    $checkSku->execute([$sku]);
                    if ($checkSku->fetchColumn() > 0) continue; 

                   
                    $brand_id = null;
                    if (!empty($brand_name)) {
                        $stmtBrand = $pdo->prepare("SELECT id FROM brands WHERE name = ?");
                        $stmtBrand->execute([$brand_name]);
                        $brand = $stmtBrand->fetch();
                        if ($brand) $brand_id = $brand['id'];
                        else {
                            $pdo->prepare("INSERT INTO brands (name) VALUES (?)")->execute([$brand_name]);
                            $brand_id = $pdo->lastInsertId();
                        }
                    }

                    
                    $stmtProd = $pdo->prepare("INSERT INTO products (name, brand_id, category_id, description, warranty_months, is_active, created_at) VALUES (?, ?, ?, ?, ?, 1, NOW())");
                    $stmtProd->execute([$name, $brand_id, $category_id, $desc, $warranty]);
                    $product_id = $pdo->lastInsertId();

                    
                    $stmtVar = $pdo->prepare("INSERT INTO product_variants (product_id, sku, variant_name, selling_price, compare_at_price, is_active) VALUES (?, ?, ?, ?, ?, 1)");
                    $stmtVar->execute([$product_id, $sku, 'Tiêu chuẩn', $price, $compare_price]);
                    $variant_id = $pdo->lastInsertId();

                    
                    if ($qty > 0 && $default_warehouse_id && $default_supplier_id) {
                        $batch_code = 'IMP-' . date('ymd') . '-' . rand(1000, 9999);
                        $cost_price = $price * 0.7; 
                        
                        $stmtBatch = $pdo->prepare("INSERT INTO inventory_batches (variant_id, warehouse_id, supplier_id, batch_code, invoice_no, cost_price, import_quantity, remaining_quantity, note, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                        $stmtBatch->execute([$variant_id, $default_warehouse_id, $default_supplier_id, $batch_code, 'CSV-IMPORT', $cost_price, $qty, $qty, 'Nhập từ file CSV']);
                    }

                    $imported_count++;
                }
                
                $pdo->commit();
                fclose($handle);
                $message = "Đã nhập thành công <strong>$imported_count</strong> sản phẩm!";

            } catch (Exception $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $error = "Lỗi dòng " . ($imported_count + 1) . ": " . $e->getMessage();
            }
        } else {
            $error = "Chỉ chấp nhận file .CSV";
        }
    } else {
        $error = "Vui lòng chọn file.";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nhập sản phẩm từ Excel (CSV)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 4px; }
    </style>
</head>
<body class="bg-gray-100 font-sans antialiased text-gray-800">

    <div class="flex h-screen overflow-hidden">
        
        <?php include '../includes/sidebar.php'; ?>

        <div class="flex-1 flex flex-col overflow-hidden bg-gray-50">
            
            <main class="flex-1 overflow-x-hidden overflow-y-auto p-6">
                
                <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">Nhập Sản Phẩm (CSV)</h1>
                        <p class="text-gray-500 text-sm mt-1">Hỗ trợ nhập hàng loạt sản phẩm và tồn kho ban đầu.</p>
                    </div>
                    <a href="index.php" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition shadow-sm font-medium">
                        <i class="fas fa-arrow-left mr-2"></i> Quay lại danh sách
                    </a>
                </div>

                <?php if($message): ?>
                    <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 p-4 mb-6 rounded shadow-sm flex items-center animate-pulse">
                        <i class="fas fa-check-circle text-xl mr-3"></i> 
                        <span><?= $message ?></span>
                    </div>
                <?php endif; ?>

                <?php if($error): ?>
                    <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm flex items-center">
                        <i class="fas fa-exclamation-triangle text-xl mr-3"></i> 
                        <span><?= $error ?></span>
                    </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <div class="lg:col-span-2">
                        <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-100 h-full">
                            <h3 class="text-lg font-bold text-gray-800 mb-6 border-b pb-2 flex items-center">
                                <i class="fas fa-file-import text-blue-600 mr-2"></i> Thiết lập & Tải file
                            </h3>
                            
                            <form method="POST" enctype="multipart/form-data" class="space-y-6">
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Kho nhập mặc định <span class="text-red-500">*</span></label>
                                        <select name="warehouse_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:border-blue-500">
                                            <option value="">-- Chọn Kho --</option>
                                            <?php foreach($warehouses as $w): ?>
                                                <option value="<?= $w['id'] ?>"><?= htmlspecialchars($w['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nhà cung cấp mặc định <span class="text-red-500">*</span></label>
                                        <select name="supplier_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:border-blue-500">
                                            <option value="">-- Chọn NCC --</option>
                                            <?php foreach($suppliers as $s): ?>
                                                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500 italic">Hai thông tin trên dùng để tạo lô hàng tồn kho cho các sản phẩm trong file CSV.</p>

                                <div class="w-full">
                                    <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-48 border-2 border-blue-300 border-dashed rounded-xl cursor-pointer bg-blue-50 hover:bg-blue-100 transition duration-300 ease-in-out group">
                                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                            <div class="w-12 h-12 bg-blue-100 text-blue-500 rounded-full flex items-center justify-center mb-3 group-hover:scale-110 transition">
                                                <i class="fas fa-cloud-upload-alt text-2xl"></i>
                                            </div>
                                            <p class="mb-1 text-sm text-gray-600 font-bold">Nhấn để chọn file CSV</p>
                                            <p class="text-xs text-gray-400">hoặc kéo thả vào đây</p>
                                        </div>
                                        <input id="dropzone-file" name="file" type="file" class="hidden" accept=".csv" required onchange="showFileName(this)" />
                                    </label>
                                </div>

                                <div id="file-name-display" class="hidden p-3 bg-gray-100 rounded-lg text-center border border-gray-200">
                                    <span class="text-gray-700 font-medium text-sm"></span>
                                </div>

                                <button type="submit" name="import" class="w-full bg-blue-600 text-white py-3 rounded-lg font-bold hover:bg-blue-700 shadow-md transition transform hover:-translate-y-0.5">
                                    <i class="fas fa-check-circle mr-2"></i> Tiến hành Import
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="lg:col-span-1">
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 h-full">
                            <h3 class="font-bold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-lightbulb text-yellow-500 mr-2"></i> Cấu trúc file CSV
                            </h3>
                            
                            <p class="text-sm text-gray-600 mb-4">File CSV cần tuân thủ đúng thứ tự cột sau:</p>

                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 mb-6">
                                <ol class="list-decimal list-inside text-sm text-gray-700 space-y-2">
                                    <li><span class="font-semibold text-blue-600">Tên sản phẩm</span> (Bắt buộc)</li>
                                    <li><span class="font-semibold text-blue-600">SKU</span> (Mã duy nhất)</li>
                                    <li><span class="font-semibold">Thương hiệu</span></li>
                                    <li><span class="font-semibold">ID Danh mục</span> (Số nguyên)</li>
                                    <li><span class="font-semibold">Giá bán</span> (VNĐ)</li>
                                    <li><span class="font-semibold">Giá gốc</span> (So sánh)</li>
                                    <li><span class="font-semibold text-green-600">Số lượng</span></li>
                                    <li><span class="font-semibold">Bảo hành</span> (Tháng)</li>
                                    <li><span class="font-semibold">Mô tả</span></li>
                                </ol>
                            </div>

                            <div class="pt-4 border-t border-gray-100">
                                <a href="?download_sample=1" class="block w-full text-center py-2 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-bold transition border border-gray-300">
                                    <i class="fas fa-download mr-2"></i> Tải file mẫu chuẩn
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        function showFileName(input) {
            const display = document.getElementById('file-name-display');
            const textSpan = display.querySelector('span');
            
            if (input.files && input.files[0]) {
                textSpan.innerHTML = '<i class="fas fa-file-csv text-green-500 mr-2"></i> Đã chọn: ' + input.files[0].name;
                display.classList.remove('hidden');
                display.classList.add('block');
            }
        }
    </script>
</body>
</html>