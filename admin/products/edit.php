<?php
require_once '../../config/database.php';
require_once '../../core/Database.php';

$db = new Database($pdo);
$id = $_GET['id'] ?? 0;

$product = $db->fetch("SELECT * FROM products WHERE id = ?", [$id]);

if (!$product) {
    echo "<script>alert('Không tìm thấy sản phẩm!'); window.location.href='index.php';</script>";
    exit;
}

$variant = $db->fetch("SELECT * FROM product_variants WHERE product_id = ? LIMIT 1", [$id]);
$brands = $db->fetchAll("SELECT * FROM brands ORDER BY name");
$categories = $db->fetchAll("SELECT * FROM categories ORDER BY name");
$current_image = $db->fetch("SELECT file_path FROM product_media WHERE product_id = ? AND is_main = 1 LIMIT 1", [$id]);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa - <?= htmlspecialchars($product['name']) ?></title>
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
                        <h1 class="text-2xl font-extrabold text-slate-800">Chỉnh Sửa Sản Phẩm</h1>
                        <p class="text-slate-500 mt-1">Cập nhật thông tin cho sản phẩm</p>
                    </div>
                    <div class="flex gap-2">
                        <a href="add_variant.php?product_id=<?= $id ?>" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium transition">
                            <i class="fas fa-plus mr-2"></i> Thêm biến thể mới
                        </a>
                        <a href="index.php" class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 font-medium transition">
                            <i class="fas fa-times mr-2"></i> Trở về
                        </a>
                    </div>
                </div>

                <div id="messageBox" class="hidden p-4 mb-6 rounded-xl border flex items-center"></div>

                <div class="max-w-4xl mx-auto bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="p-8">
                        <form id="editProductForm" enctype="multipart/form-data" class="space-y-8">
                            
                            <input type="hidden" name="id" value="<?= $id ?>">
                            <input type="hidden" name="variant_id" value="<?= $variant ? $variant['id'] : 0 ?>">
                            
                            <div>
                                <h3 class="text-lg font-bold text-slate-800 mb-4 border-b pb-2">Thông tin chung</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="col-span-2">
                                        <label class="block font-bold text-slate-700 mb-2">Tên sản phẩm *</label>
                                        <input type="text" name="name" required value="<?= htmlspecialchars($product['name'] ?? '') ?>"
                                               class="w-full border border-slate-300 px-4 py-3 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                                    </div>

                                    <div>
                                        <label class="block font-bold text-slate-700 mb-2">Danh mục</label>
                                        <select name="category_id" class="w-full border border-slate-300 px-4 py-3 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none bg-white">
                                            <option value="">-- Chọn danh mục --</option>
                                            <?php foreach($categories as $cat): ?>
                                                <option value="<?= $cat['id'] ?>" <?= ($product['category_id'] == $cat['id']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block font-bold text-slate-700 mb-2">Thương hiệu</label>
                                        <select name="brand_id" class="w-full border border-slate-300 px-4 py-3 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none bg-white">
                                            <option value="">-- Chọn thương hiệu --</option>
                                            <?php foreach($brands as $brand): ?>
                                                <option value="<?= $brand['id'] ?>" <?= ($product['brand_id'] == $brand['id']) ? 'selected' : '' ?>><?= htmlspecialchars($brand['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="col-span-2">
                                        <label class="block font-bold text-slate-700 mb-2">Mô tả</label>
                                        <textarea name="description" rows="4" class="w-full border border-slate-300 px-4 py-3 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
                                    </div>

                                    <div>
                                        <label class="block font-bold text-slate-700 mb-2">Bảo hành (tháng)</label>
                                        <input type="number" name="warranty_months" value="<?= htmlspecialchars($product['warranty_months'] ?? 0) ?>" min="0" 
                                               class="w-full border border-slate-300 px-4 py-3 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                                    </div>
                                </div>
                            </div>

                            <?php if ($variant): ?>
                            <div class="mt-8 pt-8 border-t border-slate-100">
                                <h3 class="text-lg font-bold text-slate-800 mb-4 border-b pb-2">Thông tin bán hàng (Biến thể chính)</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="col-span-2">
                                        <label class="block font-bold text-slate-700 mb-2">Tên biến thể</label>
                                        <input type="text" name="variant_name" value="<?= htmlspecialchars($variant['variant_name'] ?? '') ?>"
                                               class="w-full border border-slate-300 px-4 py-3 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                                    </div>

                                    <div>
                                        <label class="block font-bold text-slate-700 mb-2">Mã SKU</label>
                                        <input type="text" name="sku" required value="<?= htmlspecialchars($variant['sku'] ?? '') ?>"
                                               class="w-full border border-slate-300 px-4 py-3 rounded-xl uppercase font-mono focus:ring-2 focus:ring-blue-500 outline-none">
                                    </div>

                                    <div>
                                        <label class="block font-bold text-slate-700 mb-2">Trọng lượng (Gram)</label>
                                        <input type="number" name="weight_gram" value="<?= htmlspecialchars($variant['weight_gram'] ?? 0) ?>" min="0" 
                                               class="w-full border border-slate-300 px-4 py-3 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                                    </div>

                                    <div>
                                        <label class="block font-bold text-slate-700 mb-2">Giá bán lẻ (VNĐ)</label>
                                        <input type="number" name="price" required min="0" value="<?= htmlspecialchars($variant['selling_price'] ?? 0) ?>"
                                               class="w-full border border-slate-300 px-4 py-3 rounded-xl font-bold text-emerald-600 focus:ring-2 focus:ring-emerald-500 outline-none">
                                    </div>

                                    <div>
                                        <label class="block font-bold text-slate-700 mb-2">Giá gốc (VNĐ)</label>
                                        <input type="number" name="compare_at_price" min="0" value="<?= htmlspecialchars($variant['compare_at_price'] ?? 0) ?>"
                                               class="w-full border border-slate-300 px-4 py-3 rounded-xl text-slate-500 focus:ring-2 focus:ring-blue-500 outline-none">
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <div class="mt-8 pt-8 border-t border-slate-100">
                                <label class="block font-bold text-slate-700 mb-2">Hình ảnh chính</label>
                                <?php if ($current_image): ?>
                                    <div class="mb-4">
                                        <img src="../../<?= htmlspecialchars($current_image['file_path']) ?>" class="h-32 w-32 object-cover rounded-xl border border-slate-200 shadow-sm" alt="Current Image">
                                        <p class="text-sm text-slate-500 mt-2">Tải lên ảnh mới bên dưới nếu muốn thay đổi.</p>
                                    </div>
                                <?php endif; ?>

                                <div class="flex items-center justify-center w-full">
                                    <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-32 border-2 border-slate-300 border-dashed rounded-xl cursor-pointer bg-slate-50 hover:bg-slate-100 transition">
                                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                            <i class="fas fa-cloud-upload-alt text-3xl text-slate-400 mb-2"></i>
                                            <p class="text-sm text-slate-500"><span class="font-bold">Nhấn để tải lên</span> hoặc kéo thả</p>
                                        </div>
                                        <input id="dropzone-file" name="image" type="file" class="hidden" accept="image/*" onchange="previewImage(this)" />
                                    </label>
                                </div>
                                <div id="image-preview" class="mt-4 hidden">
                                    <img src="" class="h-32 w-32 object-cover rounded-xl border border-slate-200 shadow-sm">
                                </div>
                            </div>

                            <div class="flex gap-4 pt-4 border-t border-slate-100">
                                <button type="submit" id="submitBtn" class="flex-1 bg-blue-600 text-white px-6 py-3.5 rounded-xl font-bold hover:bg-blue-700 shadow-lg shadow-blue-200 transition transform hover:-translate-y-0.5">
                                    <i class="fas fa-save mr-2"></i> Cập Nhật Sản Phẩm
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // JS Xem trước ảnh
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

        // JS Xử lý Submit API
        document.getElementById('editProductForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const btn = document.getElementById('submitBtn');
            const msgBox = document.getElementById('messageBox');
            const formData = new FormData(this);

            try {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Đang lưu...';

                const response = await fetch('../../api/products/edit.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                msgBox.classList.remove('hidden', 'bg-red-50', 'text-red-700', 'border-red-200', 'bg-green-50', 'text-green-700', 'border-green-200');
                
                if (response.ok && result.status === 'success') {
                    msgBox.classList.add('bg-green-50', 'text-green-700', 'border-green-200');
                    msgBox.innerHTML = `<i class="fas fa-check-circle mr-2"></i> ${result.message}`;
                    
                    // Cuộn lên top để user thấy thông báo, hoặc tự động redirect
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    setTimeout(() => window.location.href = 'index.php', 1500); 
                } else {
                    msgBox.classList.add('bg-red-50', 'text-red-700', 'border-red-200');
                    msgBox.innerHTML = `<i class="fas fa-exclamation-triangle mr-2"></i> ${result.message || 'Có lỗi xảy ra'}`;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            } catch (error) {
                console.error(error);
                alert("Lỗi kết nối máy chủ!");
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save mr-2"></i> Cập Nhật Sản Phẩm';
            }
        });
    </script>
</body>
</html>