<?php
require_once '../../config/database.php';
require_once '../../core/Database.php';
$db = new Database($pdo);

// Lấy dữ liệu bổ trợ cho Form
$categories = $db->fetchAll("SELECT * FROM categories ORDER BY name ASC");
$warehouses = $db->fetchAll("SELECT * FROM warehouses ORDER BY name ASC");
$suppliers = $db->fetchAll("SELECT * FROM suppliers ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm Sản Phẩm Mới</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="flex h-screen overflow-hidden">
        <?php include '../includes/sidebar.php'; ?>

        <main class="flex-1 overflow-y-auto p-8">
            <div class="max-w-5xl mx-auto">
                <div class="flex justify-between items-center mb-8">
                    <h1 class="text-2xl font-bold text-gray-800">Tạo Sản Phẩm Mới</h1>
                    <a href="index.php" class="text-gray-500 hover:text-gray-700 font-medium">Hủy bỏ</a>
                </div>

                <div id="messageBox" class="hidden p-4 mb-6 rounded-xl border"></div>

                <form id="addProductForm" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
                            <h3 class="font-bold mb-4 text-gray-700">Thông tin cơ bản</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium mb-1">Tên sản phẩm *</label>
                                    <input type="text" name="name" required class="w-full border p-2.5 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Mô tả</label>
                                    <textarea name="description" rows="4" class="w-full border p-2.5 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
                            <h3 class="font-bold mb-4 text-gray-700">Giá & Định danh</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium mb-1">Mã SKU *</label>
                                    <input type="text" name="sku" required class="w-full border p-2.5 rounded-lg uppercase">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Giá bán lẻ *</label>
                                    <input type="number" name="price" required class="w-full border p-2.5 rounded-lg">
                                </div>
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
                            <h3 class="font-bold mb-4 text-gray-700">Tồn kho ban đầu (Tùy chọn)</h3>
                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium mb-1">Số lượng</label>
                                    <input type="number" name="quantity" value="0" class="w-full border p-2.5 rounded-lg">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Kho</label>
                                    <select name="warehouse_id" class="w-full border p-2.5 rounded-lg">
                                        <option value="">-- Chọn kho --</option>
                                        <?php foreach($warehouses as $wh): ?>
                                            <option value="<?= $wh['id'] ?>"><?= $wh['name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Nhà CC</label>
                                    <select name="supplier_id" class="w-full border p-2.5 rounded-lg">
                                        <option value="">-- Chọn NCC --</option>
                                        <?php foreach($suppliers as $sp): ?>
                                            <option value="<?= $sp['id'] ?>"><?= $sp['name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
                            <h3 class="font-bold mb-4 text-gray-700">Phân loại</h3>
                            <label class="block text-sm font-medium mb-1">Danh mục</label>
                            <select name="category_id" class="w-full border p-2.5 rounded-lg">
                                <option value="">Chưa phân loại</option>
                                <?php foreach($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= $cat['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
                            <h3 class="font-bold mb-4 text-gray-700">Hình ảnh</h3>
                            <input type="file" name="image" accept="image/*" class="w-full text-sm">
                        </div>

                        <button type="submit" id="submitBtn" class="w-full bg-blue-600 text-white py-4 rounded-xl font-bold hover:bg-blue-700 shadow-lg transition">
                            Lưu Sản Phẩm
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
    document.getElementById('addProductForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const btn = document.getElementById('submitBtn');
        const msg = document.getElementById('messageBox');
        const formData = new FormData(this);

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Đang xử lý...';

        try {
            const response = await fetch('../../api/products/add.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            msg.classList.remove('hidden', 'bg-red-50', 'text-red-700', 'bg-green-50', 'text-green-700');
            
            if (response.ok) {
                msg.classList.add('bg-green-50', 'text-green-700', 'border-green-200');
                msg.innerHTML = `<i class="fas fa-check-circle mr-2"></i> ${result.message}`;
                setTimeout(() => window.location.href = 'index.php', 1500);
            } else {
                msg.classList.remove('hidden');
                msg.classList.add('bg-red-50', 'text-red-700', 'border-red-200');
                msg.innerHTML = `<i class="fas fa-exclamation-triangle mr-2"></i> ${result.message}`;
                btn.disabled = false;
                btn.innerHTML = 'Lưu Sản Phẩm';
            }
        } catch (error) {
            alert("Lỗi kết nối server!");
            btn.disabled = false;
            btn.innerHTML = 'Lưu Sản Phẩm';
        }
    });
    </script>
</body>
</html>