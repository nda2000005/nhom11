<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../../config/database.php';
require_once '../../core/Database.php';

$product_id = $_GET['product_id'] ?? 0;
$db = new Database($pdo);

$product = $db->fetch("SELECT * FROM products WHERE id = ?", [$product_id]);
if (!$product) {
    echo "<script>alert('Sản phẩm không tồn tại!'); window.location.href='index.php';</script>";
    exit;
}

$attributes = $db->fetchAll("SELECT * FROM attributes ORDER BY name ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        $variantName = trim($_POST['variant_name']);
        
        if (empty($variantName)) {
            $variantName = $product['name']; 
        }

        $sqlVariant = "INSERT INTO product_variants (product_id, sku, variant_name, selling_price, compare_at_price, weight_gram, is_active) 
                       VALUES (?, ?, ?, ?, ?, ?, 1)";
        
        $stmtVariant = $pdo->prepare($sqlVariant);
        $stmtVariant->execute([
            $product_id,
            strtoupper(trim($_POST['sku'])),
            $variantName,
            $_POST['price'],
            $_POST['compare_at_price'] ?? 0, 
            $_POST['weight_gram'] ?? 0       
        ]);
        
        $variant_id = $pdo->lastInsertId();

        if (!empty($_POST['attr_ids']) && !empty($_POST['attr_values'])) {
            $attr_ids = $_POST['attr_ids'];
            $attr_vals = $_POST['attr_values'];
            
            $sqlAttr = "INSERT INTO product_attribute_values (variant_id, attribute_id, attr_value) VALUES (?, ?, ?)";
            $stmtAttr = $pdo->prepare($sqlAttr);

            for ($i = 0; $i < count($attr_ids); $i++) {
                if (!empty($attr_ids[$i]) && !empty($attr_vals[$i])) {
                    $stmtAttr->execute([
                        $variant_id, 
                        $attr_ids[$i], 
                        trim($attr_vals[$i])
                    ]);
                }
            }
        }

        if (!empty($_FILES['image']['name'])) {
            $target_dir = "../../src/img/"; 
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            
            $fileExt = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'svg', 'webp'];

            if (in_array($fileExt, $allowed_ext)) {
                $filename = time() . '_' . uniqid() . '.' . $fileExt;
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $filename)) {
                    $dbPath = 'src/img/' . $filename;
                    
                    $stmtMedia = $pdo->prepare("INSERT INTO product_media (product_id, variant_id, file_path, is_main) VALUES (?, ?, ?, 0)");
                    $stmtMedia->execute([$product_id, $variant_id, $dbPath]);
                }
            } else {
                throw new Exception("Định dạng ảnh không hợp lệ!");
            }
        }

        $pdo->commit();
        
        echo "<script>
                alert('Thêm phiên bản thành công!'); 
                window.location.href='edit.php?id=$product_id';
              </script>";
        exit;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $errorMsg = "Lỗi hệ thống: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Biến Thể - <?= htmlspecialchars($product['name']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>body { font-family: 'Segoe UI', sans-serif; }</style>
</head>
<body class="bg-gray-50 text-gray-800">
    <div class="flex h-screen overflow-hidden">
        <?php include '../includes/sidebar.php'; ?>

        <div class="flex-1 flex flex-col overflow-hidden">
            <main class="flex-1 overflow-x-hidden overflow-y-auto p-6 md:p-8">
                
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h1 class="text-2xl font-extrabold text-slate-800">Thêm Biến Thể Mới</h1>
                        <p class="text-slate-500 mt-1">Sản phẩm gốc: <span class="text-blue-600 font-bold"><?= htmlspecialchars($product['name']) ?></span></p>
                    </div>
                    <a href="index.php?id=<?= $product_id ?>" class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 font-medium transition">
                        <i class="fas fa-arrow-left mr-2"></i> Quay lại
                    </a>
                </div>

                <?php if (isset($errorMsg)): ?>
                    <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm flex items-center">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <span><?= htmlspecialchars($errorMsg) ?></span>
                    </div>
                <?php endif; ?>

                <div class="max-w-4xl mx-auto bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="p-8">
                        <form method="POST" enctype="multipart/form-data" class="space-y-8">
                            
                            <div class="bg-indigo-50/50 p-6 rounded-xl border border-indigo-100">
                                <div class="flex justify-between items-center mb-4">
                                    <div>
                                        <h3 class="font-bold text-indigo-900 text-lg flex items-center">
                                            <i class="fas fa-sliders-h mr-2"></i> Thuộc tính phiên bản
                                        </h3>
                                        <p class="text-sm text-indigo-600/80 mt-1">Chọn đặc điểm (Màu, RAM...) để tạo sự khác biệt.</p>
                                    </div>
                                    <button type="button" onclick="addAttributeRow()" class="px-3 py-1.5 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 font-medium transition shadow-sm">
                                        <i class="fas fa-plus mr-1"></i> Thêm dòng
                                    </button>
                                </div>
                                
                                <div id="attributes-container" class="space-y-3">
                                    <div class="flex gap-3 attr-row items-start">
                                        <div class="w-1/3">
                                            <select name="attr_ids[]" class="w-full border border-slate-300 p-2.5 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none bg-white font-medium text-slate-700" required onchange="generateName()">
                                                <option value="">-- Chọn thuộc tính --</option>
                                                <?php foreach($attributes as $attr): ?>
                                                    <option value="<?= $attr['id'] ?>"><?= htmlspecialchars($attr['name']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="w-2/3 flex gap-2">
                                            <input type="text" name="attr_values[]" class="w-full border border-slate-300 p-2.5 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" 
                                                   placeholder="Nhập giá trị (VD: Xanh, 256GB...)" required oninput="generateName()">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="col-span-2">
                                    <label class="block font-bold text-slate-700 mb-2">Tên phiên bản (Tự động)</label>
                                    <div class="relative">
                                        <input type="text" name="variant_name" id="auto_variant_name" readonly 
                                               class="w-full border border-slate-300 px-4 py-3 rounded-xl bg-slate-50 text-slate-500 font-bold focus:outline-none cursor-not-allowed"
                                               placeholder="Sẽ hiển thị VD: Đen / 256GB">
                                        <i class="fas fa-magic absolute right-4 top-3.5 text-slate-400"></i>
                                    </div>
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-700 mb-2">Mã SKU <span class="text-red-500">*</span></label>
                                    <input type="text" name="sku" id="sku_input" required 
                                           class="w-full border border-slate-300 px-4 py-3 rounded-xl uppercase font-mono tracking-wide focus:ring-2 focus:ring-blue-500 outline-none" 
                                           placeholder="VD: IP15-BLK-256">
                                    <p class="text-xs text-slate-500 mt-1">Mã duy nhất để quản lý kho.</p>
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-700 mb-2">Trọng lượng (Gram)</label>
                                    <input type="number" name="weight_gram" value="0" min="0" 
                                           class="w-full border border-slate-300 px-4 py-3 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-700 mb-2">Giá bán lẻ (VNĐ) <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <input type="number" name="price" required min="0"
                                               class="w-full border border-slate-300 px-4 py-3 rounded-xl font-bold text-emerald-600 focus:ring-2 focus:ring-emerald-500 outline-none pl-10" 
                                               placeholder="0">
                                        <span class="absolute left-4 top-3.5 text-slate-400">₫</span>
                                    </div>
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-700 mb-2">Giá gốc (So sánh)</label>
                                    <div class="relative">
                                        <input type="number" name="compare_at_price" min="0"
                                               class="w-full border border-slate-300 px-4 py-3 rounded-xl text-slate-500 focus:ring-2 focus:ring-indigo-500 outline-none pl-10" 
                                               placeholder="0">
                                        <span class="absolute left-4 top-3.5 text-slate-400">₫</span>
                                    </div>
                                </div>
                            </div>

                            <div class="border-t border-slate-100 pt-6">
                                <label class="block font-bold text-slate-700 mb-2">Hình ảnh riêng (Tùy chọn)</label>
                                <div class="flex items-center justify-center w-full">
                                    <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-32 border-2 border-slate-300 border-dashed rounded-xl cursor-pointer bg-slate-50 hover:bg-slate-100 transition">
                                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                            <i class="fas fa-cloud-upload-alt text-3xl text-slate-400 mb-2"></i>
                                            <p class="text-sm text-slate-500"><span class="font-bold">Nhấn để tải lên</span> hoặc kéo thả</p>
                                            <p class="text-xs text-slate-400">PNG, JPG, WEBP (Max. 2MB)</p>
                                        </div>
                                        <input id="dropzone-file" name="image" type="file" class="hidden" accept="image/*" onchange="previewImage(this)" />
                                    </label>
                                </div>
                                <div id="image-preview" class="mt-4 hidden">
                                    <img src="" class="h-20 w-20 object-cover rounded-lg border border-slate-200 shadow-sm">
                                </div>
                            </div>

                            <div class="flex gap-4 pt-4">
                                <button type="submit" class="flex-1 bg-blue-600 text-white px-6 py-3.5 rounded-xl font-bold hover:bg-blue-700 shadow-lg shadow-blue-200 transition transform hover:-translate-y-0.5">
                                    <i class="fas fa-save mr-2"></i> Lưu Biến Thể
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        const availableAttributes = <?= json_encode($attributes) ?>;
        const productNameBase = "<?= addslashes($product['name']) ?>";

        function addAttributeRow() {
            const container = document.getElementById('attributes-container');
            const div = document.createElement('div');
            div.className = 'flex gap-3 attr-row items-start mt-3';
            
            let options = '<option value="">-- Chọn --</option>';
            availableAttributes.forEach(attr => {
                options += `<option value="${attr.id}">${attr.name}</option>`;
            });

            div.innerHTML = `
                <div class="w-1/3">
                    <select name="attr_ids[]" class="w-full border border-slate-300 p-2.5 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none bg-white font-medium text-slate-700" required onchange="generateName()">
                        ${options}
                    </select>
                </div>
                <div class="w-2/3 flex gap-2">
                    <input type="text" name="attr_values[]" class="w-full border border-slate-300 p-2.5 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" 
                           placeholder="Giá trị..." required oninput="generateName()">
                    <button type="button" onclick="this.closest('.attr-row').remove(); generateName()" 
                            class="px-3 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            `;
            container.appendChild(div);
        }

        function generateName() {
            const rows = document.querySelectorAll('.attr-row');
            let nameParts = [];
            let skuParts = [];
            
            let baseSku = productNameBase.match(/\b(\w)/g);
            if(baseSku) baseSku = baseSku.join('').toUpperCase();
            else baseSku = "VAR";
            
            skuParts.push(baseSku);

            rows.forEach(row => {
                const input = row.querySelector('input');
                const attrVal = input.value.trim();

                if (attrVal) {
                    nameParts.push(attrVal);
                    skuParts.push(attrVal.toUpperCase().replace(/[^A-Z0-9]/g, '').substring(0, 4));
                }
            });

            const finalName = nameParts.join(' / ');
            document.getElementById('auto_variant_name').value = finalName;
            
            const skuInput = document.getElementById('sku_input');
            if (finalName) {
                 skuInput.value = skuParts.join('-');
            }
        }

        function previewImage(input) {
            const preview = document.getElementById('image-preview');
            const img = preview.querySelector('img');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    img.src = e.target.result;
                    preview.classList.remove('hidden');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>
