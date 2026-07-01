<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Kho & Nguồn Hàng</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 4px; }
        .modal { transition: opacity 0.25s ease; }
        body.modal-active { overflow-x: hidden; overflow-y: hidden !important; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 antialiased">

<div class="flex h-screen overflow-hidden">

    <?php 
include(__DIR__ . '/../../includes/sidebar.php'); ?>

    <div class="flex-1 flex flex-col overflow-hidden bg-[#F9FAFB]">
        <main class="flex-1 overflow-x-hidden overflow-y-auto p-6 md:p-8">

            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Quản Lý Kho</h1>
                    <p class="text-sm text-gray-500 mt-1">Theo dõi tồn kho, giá trị hàng hóa và lịch sử nhập hàng.</p>
                </div>
                <div class="flex gap-3">
                    <a href="suppliers.php" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg font-medium hover:bg-gray-50 transition shadow-sm flex items-center">
                        <i class="fas fa-truck-field mr-2 text-indigo-600"></i> Quản lý NCC
                    </a>
                    <a href="inventory_insert.php" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg font-medium hover:bg-gray-50 transition shadow-sm flex items-center">
                        <i class="fas fa-file-csv mr-2 text-green-600"></i> Nhập Excel
                    </a>
                    <a href="inventory_add.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg font-bold hover:bg-blue-700 transition shadow-md flex items-center">
                        <i class="fas fa-plus mr-2"></i> Nhập kho mới
                    </a>
                </div>
            </div>

            <!-- Stat Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                    <h4 class="text-xs font-bold text-gray-400 uppercase mb-2">Tổng giá trị tồn kho</h4>
                    <div class="text-2xl font-bold text-gray-900">
                        <?= number_format($total_value, 0, ',', '.') ?> <span class="text-sm text-gray-500 font-normal">đ</span>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                    <h4 class="text-xs font-bold text-gray-400 uppercase mb-2">Tổng sản phẩm</h4>
                    <div class="text-2xl font-bold text-gray-900">
                        <?= number_format($total_quantity, 0, ',', '.') ?> <span class="text-sm text-gray-500 font-normal">sp</span>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 relative overflow-hidden">
                    <?php if ($low_stock > 0): ?>
                        <div class="absolute top-0 right-0 w-16 h-16 bg-red-100 rounded-bl-full -mr-8 -mt-8 z-0"></div>
                    <?php endif; ?>
                    <h4 class="text-xs font-bold text-gray-400 uppercase mb-2 relative z-10">Sắp hết hàng (&lt; 5)</h4>
                    <div class="text-2xl font-bold <?= $low_stock > 0 ? 'text-red-600' : 'text-gray-900' ?> relative z-10">
                        <?= $low_stock ?> <span class="text-sm text-gray-500 font-normal">mã</span>
                    </div>
                </div>
            </div>

            <!-- Tồn kho theo sản phẩm -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden mb-8">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="font-bold text-gray-800">Tồn kho theo sản phẩm</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs font-bold text-gray-500 uppercase">
                            <tr>
                                <th class="px-6 py-3 text-left">Sản phẩm</th>
                                <th class="px-6 py-3 text-left">SKU</th>
                                <th class="px-6 py-3 text-right">Tồn kho</th>
                                <th class="px-6 py-3 text-right">Giá trị</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php foreach ($inventory_summary as $item): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 font-medium text-gray-800">
                                    <?= htmlspecialchars($item['name']) ?>
                                    <span class="text-gray-400 text-xs ml-1"><?= htmlspecialchars($item['variant_name']) ?></span>
                                </td>
                                <td class="px-6 py-4 text-gray-500 font-mono text-xs"><?= htmlspecialchars($item['sku']) ?></td>
                                <td class="px-6 py-4 text-right font-bold
                                    <?= $item['stock'] <= 5 ? 'text-red-600' : 'text-gray-800' ?>">
                                    <?= number_format($item['stock'], 0, ',', '.') ?>
                                </td>
                                <td class="px-6 py-4 text-right text-gray-600">
                                    <?= number_format($item['value'], 0, ',', '.') ?> đ
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Lịch sử nhập kho -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="font-bold text-gray-800">Lịch sử nhập kho (10 gần nhất)</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs font-bold text-gray-500 uppercase">
                            <tr>
                                <th class="px-6 py-3 text-left">Mã lô</th>
                                <th class="px-6 py-3 text-left">Sản phẩm</th>
                                <th class="px-6 py-3 text-left">NCC / Kho</th>
                                <th class="px-6 py-3 text-right">SL nhập</th>
                                <th class="px-6 py-3 text-right">Còn lại</th>
                                <th class="px-6 py-3 text-right">Đơn giá</th>
                                <th class="px-6 py-3 text-center">Serial</th>
                                <th class="px-6 py-3 text-left">Ngày nhập</th>
                                <th class="px-6 py-3 text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php foreach ($batch_history as $row): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 font-mono text-xs text-gray-500"><?= htmlspecialchars($row['batch_code']) ?></td>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-800"><?= htmlspecialchars($row['name'] ?? '-') ?></div>
                                    <div class="text-xs text-gray-400 font-mono"><?= htmlspecialchars($row['sku'] ?? '') ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-xs text-gray-600"><?= htmlspecialchars($row['supplier_name'] ?? '-') ?></div>
                                    <div class="text-xs text-gray-400"><?= htmlspecialchars($row['warehouse_name'] ?? '-') ?></div>
                                </td>
                                <td class="px-6 py-4 text-right font-medium"><?= number_format($row['import_quantity'], 0, ',', '.') ?></td>
                                <td class="px-6 py-4 text-right <?= $row['remaining_quantity'] == 0 ? 'text-red-500' : 'text-green-600 font-bold' ?>">
                                    <?= number_format($row['remaining_quantity'], 0, ',', '.') ?>
                                </td>
                                <td class="px-6 py-4 text-right"><?= number_format($row['cost_price'], 0, ',', '.') ?> đ</td>
                                <td class="px-6 py-4 text-center">
                                    <?php $sc = $serial_counts[$row['id']] ?? 0; ?>
                                    <?php if ($sc > 0): ?>
                                        <span class="bg-blue-100 text-blue-700 text-xs px-2 py-0.5 rounded-full font-bold"><?= $sc ?></span>
                                    <?php else: ?>
                                        <span class="text-gray-300 text-xs">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-xs text-gray-400">
                                    <?= date('d/m/Y', strtotime($row['created_at'])) ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end space-x-2">
                                        <button onclick="openEditModal(
                                            <?= $row['id'] ?>,
                                            '<?= addslashes($row['name'] ?? '') ?>',
                                            <?= $row['supplier_id'] ?>,
                                            <?= $row['warehouse_id'] ?>,
                                            <?= $row['import_quantity'] ?>,
                                            '<?= number_format($row['cost_price'], 0, ',', '.') ?>',
                                            '<?= addslashes($row['invoice_no'] ?? '') ?>',
                                            '<?= addslashes($row['note'] ?? '') ?>'
                                        )" class="text-gray-400 hover:text-blue-600 transition p-1" title="Sửa">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="?delete_batch=<?= $row['id'] ?>"
                                           onclick="return confirm('Bạn chắc chắn muốn xóa lô hàng này? Tồn kho sẽ về 0.')"
                                           class="text-gray-400 hover:text-red-600 transition p-1" title="Xóa">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal opacity-0 pointer-events-none fixed w-full h-full top-0 left-0 flex items-center justify-center z-50">
    <div class="modal-overlay absolute w-full h-full bg-gray-900 opacity-50"></div>
    <div class="modal-container bg-white w-11/12 md:max-w-lg mx-auto rounded-xl shadow-2xl z-50 overflow-y-auto">
        <div class="modal-header flex justify-between items-center px-6 py-4 border-b border-gray-100">
            <p class="font-bold text-gray-800 text-lg flex items-center">
                <i class="fas fa-pen-to-square text-blue-600 mr-2"></i> Sửa phiếu nhập
            </p>
            <div class="cursor-pointer" onclick="closeEditModal()">
                <i class="fas fa-times text-gray-500 hover:text-gray-800"></i>
            </div>
        </div>
        <form method="POST" action="">
            <div class="modal-content px-6 py-6 text-left">
                <input type="hidden" name="batch_id" id="edit_batch_id">
                <input type="hidden" name="edit_batch" value="1">

                <div class="mb-4">
                    <label class="block text-gray-700 text-xs font-bold mb-2 uppercase">Sản phẩm (Read-only)</label>
                    <input type="text" id="edit_product_name" readonly class="w-full bg-gray-100 text-gray-500 border border-gray-200 rounded px-3 py-2 outline-none">
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 text-xs font-bold mb-2 uppercase">Nhà cung cấp</label>
                        <select name="supplier_id" id="edit_supplier" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-blue-500">
                            <?php foreach ($suppliers as $sup): ?>
                                <option value="<?= $sup['id'] ?>"><?= htmlspecialchars($sup['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700 text-xs font-bold mb-2 uppercase">Kho nhập</label>
                        <select name="warehouse_id" id="edit_warehouse" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-blue-500">
                            <?php foreach ($warehouses as $wh): ?>
                                <option value="<?= $wh['id'] ?>"><?= htmlspecialchars($wh['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="bg-blue-50 p-4 rounded-lg mb-4 border border-blue-100">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-blue-800 text-xs font-bold mb-2">Số lượng</label>
                            <input type="number" name="quantity" id="edit_qty" oninput="calculateEditTotal()"
                                   class="w-full border border-blue-200 rounded px-3 py-2 focus:outline-none focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-blue-800 text-xs font-bold mb-2">Giá nhập</label>
                            <input type="text" name="cost_price" id="edit_price" oninput="calculateEditTotal()"
                                   class="w-full border border-blue-200 rounded px-3 py-2 focus:outline-none focus:border-blue-500">
                        </div>
                    </div>
                    <div class="mt-3 text-center border-t border-blue-200 pt-2">
                        <span class="text-xs text-blue-600 font-bold uppercase">Tổng thành tiền:</span>
                        <div id="edit_total" class="text-lg font-bold text-blue-800">0đ</div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-2">
                    <div>
                        <label class="block text-gray-700 text-xs font-bold mb-2 uppercase">Số hóa đơn</label>
                        <input type="text" name="invoice_no" id="edit_invoice_no"
                               class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-gray-700 text-xs font-bold mb-2 uppercase">Ghi chú</label>
                        <input type="text" name="note" id="edit_note"
                               class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-blue-500">
                    </div>
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 text-right rounded-b-xl flex gap-3">
                <button type="button" onclick="closeEditModal()" class="flex-1 bg-white border border-gray-300 text-gray-700 font-bold py-2 px-4 rounded hover:bg-gray-50">Hủy bỏ</button>
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow-md">Lưu thay đổi</button>
            </div>
        </form>
    </div>
</div>

<script>
    const editModal = document.getElementById('editModal');
    const body = document.querySelector('body');

    function openEditModal(id, name, supplier, warehouse, qty, price, invoice, note) {
        document.getElementById('edit_batch_id').value  = id;
        document.getElementById('edit_product_name').value = name;
        document.getElementById('edit_supplier').value  = supplier;
        document.getElementById('edit_warehouse').value = warehouse;
        document.getElementById('edit_qty').value       = qty;
        document.getElementById('edit_price').value     = price;
        document.getElementById('edit_invoice_no').value = invoice;
        document.getElementById('edit_note').value      = note;
        calculateEditTotal();
        toggleModal();
    }

    function closeEditModal() { toggleModal(); }

    function toggleModal() {
        editModal.classList.toggle('opacity-0');
        editModal.classList.toggle('pointer-events-none');
        body.classList.toggle('modal-active');
    }

    function calculateEditTotal() {
        const qty = parseInt(document.getElementById('edit_qty').value) || 0;
        const price = parseInt(document.getElementById('edit_price').value.replace(/\./g, '').replace(/,/g, '')) || 0;
        document.getElementById('edit_total').innerText =
            new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(qty * price);
    }

    editModal.querySelector('.modal-overlay').addEventListener('click', toggleModal);
</script>
</body>
</html>