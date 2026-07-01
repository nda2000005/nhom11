<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nhập Kho Hàng Loạt (CSV)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 4px; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 antialiased">

<div class="flex h-screen overflow-hidden">
    <?php include(ROOT_PATH . '/admin/includes/sidebar.php');
 ?>

    <div class="flex-1 flex flex-col overflow-hidden bg-[#F9FAFB]">
        <main class="flex-1 overflow-x-hidden overflow-y-auto p-6 md:p-8">

            <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Nhập Kho Hàng Loạt</h1>
                    <p class="text-sm text-gray-500 mt-1">Hỗ trợ nhập liệu nhanh từ Excel/CSV với cấu trúc chuẩn.</p>
                </div>
                <a href="index.php" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition shadow-sm font-medium">
                    <i class="fas fa-arrow-left mr-2"></i> Quay lại danh sách
                </a>
            </div>

            <?php if ($message): ?>
                <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 p-4 mb-6 rounded shadow-sm flex items-center">
                    <i class="fas fa-check-circle text-xl mr-3"></i> <span><?= $message ?></span>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm flex items-center">
                    <i class="fas fa-exclamation-triangle text-xl mr-3"></i> <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Upload Form -->
                <div class="lg:col-span-2">
                    <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-200 h-full">
                        <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center">
                            <span class="bg-blue-600 text-white w-6 h-6 rounded-full flex items-center justify-center text-xs mr-2">1</span>
                            Tải file dữ liệu
                        </h3>

                        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded text-sm text-blue-800">
                            <p class="font-bold mb-1"><i class="fas fa-info-circle mr-1"></i> Quy định file nhập:</p>
                            <ul class="list-disc list-inside space-y-1 ml-1 text-blue-700">
                                <li>File định dạng <strong>.CSV (UTF-8)</strong></li>
                                <li>Mã SKU phải tồn tại trong hệ thống sản phẩm.</li>
                                <li><strong>ID Kho</strong> và <strong>ID Nhà cung cấp</strong> lấy từ bảng tra cứu bên phải.</li>
                            </ul>
                        </div>

                        <form method="POST" enctype="multipart/form-data" class="space-y-6">
                            <div class="w-full">
                                <label for="dropzone-file"
                                       class="flex flex-col items-center justify-center w-full h-48 border-2 border-gray-300 border-dashed rounded-xl cursor-pointer bg-gray-50 hover:bg-blue-50 hover:border-blue-400 transition duration-300 group">
                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                        <div class="w-12 h-12 bg-gray-200 text-gray-500 rounded-full flex items-center justify-center mb-3 group-hover:bg-blue-100 group-hover:text-blue-600 transition">
                                            <i class="fas fa-cloud-upload-alt text-2xl"></i>
                                        </div>
                                        <p class="mb-1 text-sm text-gray-700 font-bold group-hover:text-blue-700">Nhấn để chọn file hoặc kéo thả</p>
                                        <p class="text-xs text-gray-400">Hỗ trợ file .CSV (Max 5MB)</p>
                                    </div>
                                    <input id="dropzone-file" name="csv_file" type="file" class="hidden" accept=".csv" onchange="showFileName(this)" />
                                </label>
                            </div>

                            <div id="file-name-display" class="hidden p-3 bg-gray-100 rounded-lg text-center border border-gray-200">
                                <span class="text-gray-700 font-medium text-sm"></span>
                            </div>

                            <div class="flex flex-col sm:flex-row gap-4 pt-4 border-t border-gray-100">
                                <a href="?download_sample=1"
                                   class="flex-1 py-3 px-4 bg-white border border-gray-300 text-gray-700 rounded-lg font-bold hover:bg-gray-50 text-center transition shadow-sm">
                                    <i class="fas fa-download mr-2"></i> Tải file mẫu
                                </a>
                                <button type="submit" name="import_csv"
                                        class="flex-1 py-3 px-4 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700 text-center transition shadow-md">
                                    <i class="fas fa-box-open mr-2"></i> Tiến hành nhập kho
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Lookup Panels -->
                <div class="lg:col-span-1 space-y-6">

                    <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200">
                        <h4 class="font-bold text-gray-800 mb-4 flex items-center border-b pb-2">
                            <i class="fas fa-warehouse text-gray-400 mr-2"></i> Tra cứu ID Kho
                        </h4>
                        <div class="max-h-48 overflow-y-auto pr-2">
                            <ul class="space-y-2">
                                <?php foreach ($warehouses as $wh): ?>
                                <li class="flex justify-between items-center text-sm p-2 bg-gray-50 rounded border border-gray-100 hover:bg-blue-50 transition">
                                    <span class="text-gray-700 font-medium"><?= htmlspecialchars($wh['name']) ?></span>
                                    <span class="bg-gray-200 text-gray-700 px-2 py-0.5 rounded text-xs font-bold">ID: <?= $wh['id'] ?></span>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>

                    <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200">
                        <h4 class="font-bold text-gray-800 mb-4 flex items-center border-b pb-2">
                            <i class="fas fa-truck text-gray-400 mr-2"></i> Tra cứu ID Nhà cung cấp
                        </h4>
                        <div class="max-h-64 overflow-y-auto pr-2">
                            <ul class="space-y-2">
                                <?php foreach ($suppliers as $sup): ?>
                                <li class="flex justify-between items-center text-sm p-2 bg-gray-50 rounded border border-gray-100 hover:bg-blue-50 transition">
                                    <span class="text-gray-700 font-medium"><?= htmlspecialchars($sup['name']) ?></span>
                                    <span class="bg-gray-200 text-gray-700 px-2 py-0.5 rounded text-xs font-bold">ID: <?= $sup['id'] ?></span>
                                </li>
                                <?php endforeach; ?>
                            </ul>
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
            textSpan.innerHTML = '<i class="fas fa-file-csv text-green-500 mr-2"></i> ' + input.files[0].name;
            display.classList.remove('hidden');
            display.classList.add('block');
        }
    }
</script>
</body>
</html>