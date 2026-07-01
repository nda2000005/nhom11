<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
define('ROOT_PATH', dirname(__DIR__, 3)); 
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/admin/inventory/controllers/SupplierController.php';

$model      = new SupplierModel($pdo);
$controller = new SupplierController($model);
$suppliers = $model->getAll();

$error = '';

// --- Xử lý DELETE (trước khi xuất HTML) ---
if (isset($_GET['delete'])) {
    $result = $controller->handleDelete(intval($_GET['delete']));
    if ($result['success']) {
        header('Location: ' . $result['redirect']);
        exit;
    }
    $error = $result['message'];
}

// --- Xử lý POST (CREATE / UPDATE) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $result = $_POST['action'] === 'edit'
        ? $controller->handleUpdate()
        : $controller->handleCreate();

    if ($result['success']) {
        header('Location: ' . $result['redirect']);
        exit;
    }
    $error = $result['message'];
}

// --- Lấy dữ liệu View ---
$data = $controller->getListData();
extract($data); // $stats, $suppliers, $message
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Nhà Cung Cấp</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .modal { transition: opacity 0.25s ease; }
        body.modal-active { overflow-x: hidden; overflow-y: hidden !important; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 antialiased">

<div class="flex h-screen overflow-hidden">
    <?php // Thay vì: include '../includes/sidebar.php';
// Hãy dùng:
include(ROOT_PATH . '/admin/includes/sidebar.php'); ?>

    <div class="flex-1 flex flex-col overflow-hidden bg-[#F9FAFB]">
        <main class="flex-1 overflow-x-hidden overflow-y-auto p-6 md:p-8">

            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Nhà Cung Cấp</h1>
                    <p class="text-sm text-gray-500 mt-1">Quản lý danh sách đối tác cung ứng hàng hóa.</p>
                </div>
                <div class="flex gap-3">
                    <a href="index.php" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg font-medium hover:bg-gray-50 transition shadow-sm flex items-center">
                        <i class="fas fa-arrow-left mr-2"></i> Quay lại Kho
                    </a>
                    <button onclick="openModal('add')" class="bg-blue-600 text-white px-4 py-2 rounded-lg font-bold hover:bg-blue-700 transition shadow-md flex items-center">
                        <i class="fas fa-plus mr-2"></i> Thêm NCC Mới
                    </button>
                </div>
            </div>

            <!-- Notifications -->
            <?php if ($message): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm">
                    <p class="font-bold"><i class="fas fa-check-circle mr-2"></i>Thành công</p>
                    <p><?= htmlspecialchars($message) ?></p>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm">
                    <p class="font-bold"><i class="fas fa-exclamation-triangle mr-2"></i>Lỗi</p>
                    <p><?= htmlspecialchars($error) ?></p>
                </div>
            <?php endif; ?>

            <!-- Stat Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                    <h4 class="text-xs font-bold text-gray-400 uppercase mb-2">Tổng Nhà Cung Cấp</h4>
                    <div class="text-2xl font-bold text-gray-900"><?= $stats['total'] ?></div>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                    <h4 class="text-xs font-bold text-gray-400 uppercase mb-2">Đang Hợp Tác (Active)</h4>
                    <div class="text-2xl font-bold text-green-600"><?= $stats['active'] ?></div>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                    <h4 class="text-xs font-bold text-gray-400 uppercase mb-2">Ngừng Hợp Tác</h4>
                    <div class="text-2xl font-bold text-gray-400"><?= $stats['inactive'] ?></div>
                </div>
            </div>

            <!-- Supplier Table -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                    <h3 class="font-bold text-gray-800">Danh sách nhà cung cấp</h3>
                    <input id="searchInput" onkeyup="searchTable()" type="text" placeholder="Tìm kiếm..."
                           class="border border-gray-300 rounded-lg px-4 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none w-56">
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm" id="supplierTable">
                        <thead class="bg-gray-50 text-xs font-bold text-gray-500 uppercase">
                            <tr>
                                <th class="px-6 py-3 text-left">Tên nhà cung cấp</th>
                                <th class="px-6 py-3 text-left">Liên hệ</th>
                                <th class="px-6 py-3 text-left">Mã số thuế</th>
                                <th class="px-6 py-3 text-center">Đơn nhập</th>
                                <th class="px-6 py-3 text-center">Trạng thái</th>
                                <th class="px-6 py-3 text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php foreach ($suppliers as $s): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-gray-800"><?= htmlspecialchars($s['name']) ?></div>
                                    <div class="text-xs text-blue-500"><?= htmlspecialchars($s['website'] ?? '') ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-gray-700"><?= htmlspecialchars($s['contact_name'] ?? '') ?></div>
                                    <div class="text-xs text-gray-400"><?= htmlspecialchars($s['phone'] ?? '') ?></div>
                                </td>
                                <td class="px-6 py-4 text-gray-500 font-mono text-xs"><?= htmlspecialchars($s['tax_code'] ?? '-') ?></td>
                                <td class="px-6 py-4 text-center">
                                    <span class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded-full text-xs font-bold"><?= $s['batch_count'] ?></span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <?php if ($s['status'] === 'ACTIVE'): ?>
                                        <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full font-bold">Hoạt động</span>
                                    <?php else: ?>
                                        <span class="bg-gray-100 text-gray-500 text-xs px-2 py-1 rounded-full font-bold">Ngừng</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <button onclick='openModal("edit", <?= json_encode($s, JSON_HEX_APOS | JSON_UNESCAPED_UNICODE) ?>)'
                                            class="text-blue-400 hover:text-blue-600 p-2" title="Sửa">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="suppliers.php?delete=<?= $s['id'] ?>"
                                       onclick="return confirm('Bạn chắc chắn muốn xóa?')"
                                       class="text-red-400 hover:text-red-600 p-2" title="Xóa">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
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

<!-- Supplier Modal -->
<div id="supplierModal" class="modal opacity-0 pointer-events-none fixed w-full h-full top-0 left-0 flex items-center justify-center z-50">
    <div class="modal-overlay absolute w-full h-full bg-gray-900 opacity-50"></div>
    <div class="modal-container bg-white w-11/12 md:max-w-2xl mx-auto rounded-xl shadow-2xl z-50 overflow-y-auto max-h-[90vh]">
        <div class="modal-header flex justify-between items-center px-6 py-4 border-b border-gray-100 bg-gray-50 rounded-t-xl">
            <p class="font-bold text-gray-800 text-lg flex items-center" id="modalTitle">
                <i class="fas fa-truck-field text-blue-600 mr-2"></i> Thêm Nhà Cung Cấp
            </p>
            <div class="cursor-pointer" onclick="toggleModal()">
                <i class="fas fa-times text-gray-500 hover:text-gray-800 text-lg"></i>
            </div>
        </div>

        <form method="POST" action="suppliers.php">
            <div class="modal-content px-6 py-6 text-left">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="id" id="supplierId">

                <h4 class="text-xs font-bold text-gray-400 uppercase mb-3 border-b pb-1">1. Thông tin doanh nghiệp</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div class="md:col-span-2">
                        <label class="block text-gray-700 text-sm font-bold mb-1">Tên Nhà Cung Cấp <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="name" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-1">Mã Số Thuế</label>
                        <input type="text" name="tax_code" id="tax_code" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-1">Website</label>
                        <input type="text" name="website" id="website" placeholder="https://" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none">
                    </div>
                </div>

                <h4 class="text-xs font-bold text-gray-400 uppercase mb-3 border-b pb-1 mt-6">2. Thông tin liên hệ</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-1">Người liên hệ</label>
                        <input type="text" name="contact_name" id="contact_name" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-1">Điện thoại <span class="text-red-500">*</span></label>
                        <input type="text" name="phone" id="phone" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-1">Email</label>
                        <input type="email" name="email" id="email" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none">
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-gray-700 text-sm font-bold mb-1">Địa chỉ kho/Văn phòng</label>
                        <input type="text" name="address" id="address" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none">
                    </div>
                </div>

                <h4 class="text-xs font-bold text-gray-400 uppercase mb-3 border-b pb-1 mt-6">3. Cài đặt khác</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-1">Trạng thái</label>
                        <select name="status" id="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none">
                            <option value="ACTIVE">Đang hoạt động</option>
                            <option value="INACTIVE">Ngừng hợp tác</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-1">Ghi chú nội bộ</label>
                        <input type="text" name="note" id="note" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none">
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 text-right rounded-b-xl border-t border-gray-100 flex gap-3 justify-end">
                <button type="button" onclick="toggleModal()" class="bg-white border border-gray-300 text-gray-700 font-bold py-2 px-6 rounded-lg hover:bg-gray-50 transition">Hủy</button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg shadow-md transition">
                    <i class="fas fa-save mr-2"></i> Lưu Dữ Liệu
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const modal = document.getElementById('supplierModal');
    const body  = document.querySelector('body');

    function resetForm() {
        document.getElementById('formAction').value = 'add';
        document.getElementById('supplierId').value = '';
        document.getElementById('modalTitle').innerHTML = '<i class="fas fa-truck-field text-blue-600 mr-2"></i> Thêm Nhà Cung Cấp';
        ['name', 'tax_code', 'website', 'contact_name', 'phone', 'email', 'address', 'note'].forEach(id => {
            document.getElementById(id).value = '';
        });
        document.getElementById('status').value = 'ACTIVE';
    }

    function openModal(type, data = null) {
        resetForm();
        if (type === 'edit' && data) {
            document.getElementById('formAction').value = 'edit';
            document.getElementById('supplierId').value = data.id;
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-pen-to-square text-blue-600 mr-2"></i> Cập Nhật Nhà Cung Cấp';

            ['name', 'tax_code', 'website', 'contact_name', 'phone', 'email', 'address', 'note'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.value = data[id] ?? '';
            });
            document.getElementById('status').value = data.status ?? 'ACTIVE';
        }
        toggleModal();
    }

    function toggleModal() {
        modal.classList.toggle('opacity-0');
        modal.classList.toggle('pointer-events-none');
        body.classList.toggle('modal-active');
    }

    modal.querySelector('.modal-overlay').addEventListener('click', toggleModal);

    function searchTable() {
        const filter = document.getElementById('searchInput').value.toLowerCase();
        const rows   = document.querySelectorAll('#supplierTable tbody tr');
        rows.forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
        });
    }
</script>
</body>
</html>