<?php
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__, 3));
}
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/admin/inventory/controllers/InventoryController.php';

$model      = new InventoryModel($pdo);
$controller = new InventoryController($model);

$error = null;

// Xử lý POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $controller->handleAddBatch();

    if ($result['success']) {
        if (InventoryController::isAjax()) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => true, 'message' => $result['message']], JSON_UNESCAPED_UNICODE);
            exit;
        }
        header('Location: index.php?success=true');
        exit;
    } else {
        if (InventoryController::isAjax()) {
            header('Content-Type: application/json; charset=utf-8', true, 422);
            echo json_encode(['success' => false, 'message' => $result['message']], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $error = $result['message'];
    }
}

// Dữ liệu form
$form = $controller->getAddFormData();
extract($form); // $suppliers, $warehouses, $variants
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo Phiếu Nhập Kho</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50 text-gray-800 antialiased">

<div class="flex h-screen overflow-hidden">
    <?php include(ROOT_PATH . '/admin/includes/sidebar.php'); ?>

    <div class="flex-1 flex flex-col overflow-hidden bg-[#F9FAFB]">
        <main class="flex-1 overflow-x-hidden overflow-y-auto p-6 md:p-8">

            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Tạo Phiếu Nhập Kho</h1>
                    <p class="text-sm text-gray-500 mt-1">Nhập hàng thủ công vào kho và ghi nhận Serial/IMEI.</p>
                </div>
                <a href="index.php" class="text-gray-500 hover:text-gray-700 font-medium flex items-center transition">
                    <i class="fas fa-times mr-2"></i> Hủy bỏ
                </a>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="importForm" class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <div class="lg:col-span-2 space-y-6">

                    <!-- Thông tin hàng hóa -->
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                        <h3 class="text-sm font-bold text-gray-800 uppercase mb-4 border-b pb-2">1. Thông tin hàng hóa</h3>

                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Sản phẩm <span class="text-red-500">*</span></label>
                            <select name="variant_id" id="variant_id" onchange="fetchQuote()" required
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 outline-none bg-gray-50">
                                <option value="">-- Chọn sản phẩm --</option>
                                <?php foreach ($variants as $v): ?>
                                    <option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['full_name']) ?> (SKU: <?= $v['sku'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Nhà cung cấp <span class="text-red-500">*</span></label>
                                <select name="supplier_id" id="supplier_id" onchange="fetchQuote()" required
                                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 outline-none">
                                    <option value="">-- Chọn nhà cung cấp --</option>
                                    <?php foreach ($suppliers as $s): ?>
                                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Kho nhập <span class="text-red-500">*</span></label>
                                <select name="warehouse_id" required
                                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 outline-none">
                                    <option value="">-- Chọn kho --</option>
                                    <?php foreach ($warehouses as $w): ?>
                                        <option value="<?= $w['id'] ?>"><?= htmlspecialchars($w['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Thông báo báo giá -->
                        <div id="quoteBox" class="hidden items-center gap-2 bg-green-50 border border-green-200 rounded-lg px-4 py-2 mb-4 text-sm text-green-700">
                            <i class="fas fa-tag"></i>
                            <span>Báo giá từ NCC: <strong id="quotePriceDisplay"></strong></span>
                        </div>
                    </div>

                    <!-- Số lượng & Giá -->
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                        <h3 class="text-sm font-bold text-gray-800 uppercase mb-4 border-b pb-2">2. Số lượng & Giá</h3>
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Số lượng <span class="text-red-500">*</span></label>
                                <input type="number" name="quantity" id="qty" required min="1"
                                       oninput="calculateTotal(); checkSerialMatch()"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 outline-none text-lg font-bold text-center">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Đơn giá nhập (VNĐ) <span class="text-red-500">*</span></label>
                                <input type="text" name="cost_price" id="price" required
                                       oninput="calculateTotal()" placeholder="0"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 outline-none text-lg font-bold text-right text-gray-800">
                            </div>
                        </div>
                        <div class="bg-blue-50 rounded-lg p-4 flex justify-between items-center border border-blue-100">
                            <span class="text-sm font-semibold text-blue-800 uppercase">Tổng thành tiền dự kiến</span>
                            <span id="total" class="text-2xl font-bold text-blue-700">0 <small class="text-sm font-normal text-blue-600">VNĐ</small></span>
                        </div>
                    </div>

                    <!-- Thông tin bổ sung -->
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                        <h3 class="text-sm font-bold text-gray-800 uppercase mb-4 border-b pb-2">3. Thông tin bổ sung</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Số hóa đơn / PO</label>
                                <input type="text" name="invoice_no" placeholder="VD: INV-2023-001"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Ghi chú</label>
                                <input type="text" name="note" placeholder="Nhập ghi chú..."
                                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 outline-none">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Serial Panel -->
                <div class="lg:col-span-1">
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 h-full flex flex-col sticky top-6">
                        <h3 class="text-sm font-bold text-gray-800 uppercase mb-4 flex items-center justify-between">
                            <span><i class="fas fa-barcode mr-2"></i> Serial / IMEI</span>
                            <span id="serialCountBadge" class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded text-xs">0 / 0</span>
                        </h3>
                        <div class="flex-1 flex flex-col">
                            <p class="text-xs text-gray-500 mb-2">Quét mã vạch hoặc nhập từng dòng.</p>
                            <textarea name="serials" id="serialInput" oninput="checkSerialMatch()"
                                      placeholder="Mỗi mã một dòng..."
                                      class="flex-1 w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 outline-none font-mono text-sm resize-none bg-gray-50 min-h-[300px]"></textarea>
                        </div>
                        <div class="mt-6 pt-4 border-t border-gray-100">
                            <button type="submit" name="add_batch"
                                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg shadow-lg shadow-blue-200 transition flex justify-center items-center">
                                <i class="fas fa-check-circle mr-2"></i> Hoàn tất nhập kho
                            </button>
                        </div>
                    </div>
                </div>

            </form>
        </main>
    </div>
</div>

<script>
    // Lấy báo giá từ API endpoint riêng
    function fetchQuote() {
        const variantId  = document.getElementById('variant_id').value;
        const supplierId = document.getElementById('supplier_id').value;
        const quoteBox   = document.getElementById('quoteBox');

        if (!variantId || !supplierId) { quoteBox.classList.add('hidden'); return; }

        // Gọi API endpoint độc lập
        fetch(`/api/inventory/quote?variant_id=${variantId}&supplier_id=${supplierId}`)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const price = parseInt(data.price);
                    document.getElementById('price').value = price.toLocaleString('vi-VN');
                    document.getElementById('quotePriceDisplay').textContent = price.toLocaleString('vi-VN') + ' đ';
                    quoteBox.classList.remove('hidden');
                    quoteBox.classList.add('flex');
                    calculateTotal();
                } else {
                    quoteBox.classList.add('hidden');
                    quoteBox.classList.remove('flex');
                    document.getElementById('price').value = '';
                    calculateTotal();
                }
            })
            .catch(() => quoteBox.classList.add('hidden'));
    }

    function calculateTotal() {
        const qty   = parseInt(document.getElementById('qty').value) || 0;
        const price = parseInt(document.getElementById('price').value.replace(/\D/g, '')) || 0;
        document.getElementById('total').innerHTML =
            (qty * price).toLocaleString('vi-VN') + ' <small class="text-sm font-normal text-blue-600">VNĐ</small>';
    }

    function checkSerialMatch() {
        const qty   = parseInt(document.getElementById('qty').value) || 0;
        const lines = document.getElementById('serialInput').value.split('\n').filter(l => l.trim().length > 0);
        const count = lines.length;
        const badge = document.getElementById('serialCountBadge');
        badge.textContent = `${count} / ${qty}`;

        if (count === qty && qty > 0) {
            badge.className = 'bg-green-100 text-green-700 px-2 py-0.5 rounded text-xs font-bold';
            badge.innerHTML = '<i class="fas fa-check mr-1"></i> Đủ';
        } else if (count > qty) {
            badge.className = 'bg-red-100 text-red-700 px-2 py-0.5 rounded text-xs font-bold';
            badge.innerHTML = `<i class="fas fa-exclamation-circle mr-1"></i> Dư ${count - qty}`;
        } else {
            badge.className = 'bg-gray-100 text-gray-600 px-2 py-0.5 rounded text-xs';
        }
    }

    // Auto-format giá khi nhập tay
    document.getElementById('price').addEventListener('input', function () {
        const val = this.value.replace(/\D/g, '');
        this.value = val ? parseInt(val).toLocaleString('vi-VN') : '';
        calculateTotal();
    });

    calculateTotal();
</script>
</body>
</html>