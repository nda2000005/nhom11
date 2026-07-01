<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Quản lý TechStore</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8fafc; font-family: 'Inter', system-ui, sans-serif; }
        .main-content { margin-left: 280px; padding: 30px; }
        @media (max-width: 768px) { .main-content { margin-left: 0; } }
    </style>
</head>
<body>

    <?php 
    $sidebar_path = $_SERVER['DOCUMENT_ROOT'] . '/mnopl/admin/includes/sidebar.php';
    if (file_exists($sidebar_path)) {
        include $sidebar_path;
    }
    ?>

    <div class="main-content">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-8 gap-4">
            <div>
                <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">Tổng quan Kinh doanh</h2>
                <p class="text-slate-500 mt-1">Hệ thống báo cáo tài chính hiển thị theo thời gian thực.</p>
            </div>
            <div class="flex gap-3">
                <a href="../products/add.php" class="bg-white text-slate-700 border border-slate-200 px-4 py-2 rounded-xl font-bold hover:bg-slate-50 transition shadow-sm">
                    + Thêm SP
                </a>
                <a href="../products/import_csv.php" class="bg-blue-600 text-white px-5 py-2 rounded-xl font-bold hover:bg-blue-700 shadow-lg shadow-blue-200 transition flex items-center">
                    <i class="fas fa-download mr-2"></i> Nhập kho
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-2xl shadow-sm border-l-4 border-emerald-500">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Doanh thu thực tế</p>
                <p class="text-2xl font-black text-slate-800 mt-1"><?= number_format($totalRevenue ?? 0, 0, ',', '.') ?>đ</p>
                <div class="mt-2 flex items-center text-emerald-600 text-[10px] font-bold">
                    <span class="w-2 h-2 bg-emerald-500 rounded-full mr-2"></span> Đã hoàn thành
                </div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border-l-4 border-orange-400">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Doanh thu chờ thu</p>
                <p class="text-2xl font-black text-slate-800 mt-1"><?= number_format($pendingRevenue ?? 0, 0, ',', '.') ?>đ</p>
                <div class="mt-2 flex items-center text-orange-500 text-[10px] font-bold">
                    <span class="w-2 h-2 bg-orange-400 rounded-full mr-2"></span> Đang xử lý
                </div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border-l-4 border-blue-500">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Vốn tồn kho</p>
                <p class="text-2xl font-black text-slate-800 mt-1"><?= number_format($inventoryValue ?? 0, 0, ',', '.') ?>đ</p>
                <div class="mt-2 text-blue-600 text-xs font-bold flex items-center">
                    <i class="fas fa-wallet mr-2"></i> Tài sản hiện có
                </div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border-l-4 border-purple-500">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Đơn cần xử lý</p>
                <p class="text-2xl font-black text-slate-800 mt-1"><?= (int)($pendingOrdersCount ?? 0) ?> đơn</p>
                <span class="mt-2 inline-block text-blue-600 text-xs font-bold uppercase">Xử lý hệ thống</span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                    <h3 class="font-bold text-slate-800 flex items-center">
                        <i class="fas fa-shopping-bag text-blue-500 mr-2"></i> Giao dịch gần đây
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 text-slate-400 text-[10px] uppercase font-bold">
                            <tr>
                                <th class="px-6 py-4">Mã đơn</th>
                                <th class="px-6 py-4">Khách hàng</th>
                                <th class="px-6 py-4">Tổng tiền</th>
                                <th class="px-6 py-4">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($recentOrders)): ?>
                                <?php foreach ($recentOrders as $o): ?>
                                <tr class="hover:bg-gray-50/50 border-b border-slate-50 transition-colors">
                                    <td class="px-6 py-4 text-[13px] font-bold text-blue-600">#<?= htmlspecialchars($o['order_code'] ?? $o['id']) ?></td>
                                    <td class="px-6 py-4 text-[13px] text-gray-600"><?= htmlspecialchars($o['full_name'] ?? 'Khách lẻ') ?></td>
                                    <td class="px-6 py-4 text-[13px] font-bold text-gray-900"><?= number_format($o['total_amount'], 0, ',', '.') ?>đ</td>
                                    <td class="px-6 py-4"><?= getStatusBadge($o['current_status']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center py-6 text-sm text-slate-400">Không có giao dịch</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex flex-col gap-6">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-red-100">
                    <h3 class="font-bold text-red-600 text-sm mb-4 flex items-center">
                        <i class="fas fa-exclamation-triangle mr-2"></i> 
                        Sắp hết hàng (<?= count($low_stock_list ?? []) ?>)
                    </h3>
                    <div class="space-y-3">
                        <?php if (!empty($low_stock_list)): ?>
                            <?php foreach($low_stock_list as $lp): ?>
                            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-100">
                                <div class="flex flex-col max-w-[150px]">
                                    <span class="text-xs font-bold text-slate-700 truncate" title="<?= htmlspecialchars($lp['name']) ?>"><?= htmlspecialchars($lp['name']) ?></span>
                                    <span class="text-[10px] text-orange-500 font-bold">Còn: <?= (int)$lp['total_qty'] ?></span>
                                </div>
                                <a href="../products/import_csv.php" class="bg-white text-red-600 border border-red-200 px-3 py-1 rounded-lg text-[10px] font-black hover:bg-red-600 hover:text-white transition">NHẬP</a>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-[11px] text-slate-500 text-center py-4">Kho hàng đang ổn định</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                    <h3 class="font-bold text-slate-800 text-sm mb-4 flex items-center">
                        <i class="fas fa-bolt text-amber-500 mr-2"></i> TRUY CẬP NHANH
                    </h3>
                    <div class="grid grid-cols-2 gap-3">
                        <a href="../products/index.php" class="flex flex-col items-center justify-center p-4 bg-blue-50/50 hover:bg-blue-50 rounded-xl border border-blue-100/50 transition text-center group">
                            <i class="fas fa-box text-blue-500 text-lg mb-2"></i>
                            <span class="text-xs font-bold text-slate-700">Sản phẩm</span>
                        </a>
                        <a href="../customers/index.php" class="flex flex-col items-center justify-center p-4 bg-emerald-50/50 hover:bg-emerald-50 rounded-xl border border-emerald-100/50 transition text-center group">
                            <i class="fas fa-users text-emerald-500 text-lg mb-2"></i>
                            <span class="text-xs font-bold text-slate-700">Khách hàng</span>
                        </a>
                        <a href="../order/View/index.php" class="flex flex-col items-center justify-center p-4 bg-purple-50/50 hover:bg-purple-50 rounded-xl border border-purple-100/50 transition text-center group">
                            <i class="fas fa-file-invoice-dollar text-purple-500 text-lg mb-2"></i>
                            <span class="text-xs font-bold text-slate-700">Đơn hàng</span>
                        </a>
                        <a href="../report/index.php" class="flex flex-col items-center justify-center p-4 bg-orange-50/50 hover:bg-orange-50 rounded-xl border border-orange-100/50 transition text-center group">
                            <i class="fas fa-chart-pie text-orange-500 text-lg mb-2"></i>
                            <span class="text-xs font-bold text-slate-700">Báo cáo</span>
                        </a>
                    </div>
                </div>
            </div> 
        </div> 
    </div> 
</body>
</html>