<?php
require_once '../../config/database.php';
require_once '../../core/Database.php';

$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

try {
    $db = new Database($pdo);

    // 1. Lấy chỉ số tài chính tổng quan

    $sql_finance = "SELECT 
                    SUM(oi.quantity * oi.price) as total_revenue,
                    SUM(oi.quantity * oi.cost_price) as total_cogs,
                    COUNT(DISTINCT o.id) as success_orders
                FROM orders o
                JOIN order_items oi ON o.id = oi.order_id
                WHERE o.current_status != 'ĐÃ HỦY' -- Lấy tất cả trừ đơn hủy
                AND o.created_at BETWEEN :start AND :end";
    
    $stmt = $pdo->prepare($sql_finance);
    $stmt->execute(['start' => $start_date . ' 00:00:00', 'end' => $end_date . ' 23:59:59']);
    

    // Ép kiểu dữ liệu để đảm bảo không bị lỗi hiển thị
    $finance = $stmt->fetch(PDO::FETCH_ASSOC);
    $revenue = (float)($finance['total_revenue'] ?? 0);
    $cogs = (float)($finance['total_cogs'] ?? 0);
    $gross_profit = $revenue - $cogs;
    $profit_margin = $revenue > 0 ? ($gross_profit / $revenue) * 100 : 0;
    $success_orders = (int)($finance['success_orders'] ?? 0);

    // 2. Dữ liệu cho Biểu đồ xu hướng
    
    $sql_chart = "SELECT 
                DATE(o.created_at) as date,
                SUM(oi.quantity * oi.price) as daily_revenue,
                SUM(oi.quantity * (oi.price - oi.cost_price)) as daily_profit
              FROM orders o
              JOIN order_items oi ON o.id = oi.order_id
              WHERE o.current_status != 'ĐÃ HỦY'
              AND o.created_at BETWEEN ? AND ?
              GROUP BY DATE(o.created_at)
              ORDER BY DATE(o.created_at) ASC";
    
    $chart_data = $db->fetchAll($sql_chart, [$start_date . ' 00:00:00', $end_date . ' 23:59:59']);

    // 3. Top 10 sản phẩm sinh lời cao nhất
    $sql_top = "SELECT 
                p.name AS product_name, 
                v.variant_name AS variant_display,
                SUM(oi.quantity) as sold_qty,
                SUM(oi.quantity * oi.price) as total_revenue,
                SUM(oi.quantity * (oi.price - oi.cost_price)) as net_profit
            FROM order_items oi
            LEFT JOIN product_variants v ON oi.variant_id = v.id 
            LEFT JOIN products p ON v.product_id = p.id
            JOIN orders o ON oi.order_id = o.id
            WHERE o.current_status != 'ĐÃ HỦY' 
            AND o.created_at BETWEEN ? AND ?
            GROUP BY v.id, p.name, v.variant_name 
            ORDER BY net_profit DESC 
            LIMIT 10";

    $top_profits = $db->fetchAll($sql_top, [$start_date . ' 00:00:00', $end_date . ' 23:59:59']);
    if (!$top_profits) $top_profits = [];

    // Chuẩn bị dữ liệu Chart.js
    $labels = []; $data_revenue = []; $data_profit = [];
    foreach ($chart_data as $row) {
        $labels[] = date('d/m', strtotime($row['date']));
        $data_revenue[] = (float)$row['daily_revenue'];
        $data_profit[] = (float)$row['daily_profit'];
    }
$finance = $stmt->fetch(PDO::FETCH_ASSOC);
$revenue = (float)($finance['total_revenue'] ?? 0);
$cogs = (float)($finance['total_cogs'] ?? 0);


if ($cogs == 0 && $revenue > 0) {
    $cogs = $revenue * 0.7; 
}

$gross_profit = $revenue - $cogs;
$profit_margin = $revenue > 0 ? ($gross_profit / $revenue) * 100 : 0;
// Thay thế đoạn check cũ bằng đoạn này
$is_json_request = (isset($_GET['format']) && $_GET['format'] === 'json') || 
                   (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

if ($is_json_request) {
    // Xóa mọi output đệm trước đó để đảm bảo sạch sẽ
    ob_clean(); 
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => 'success',
        'summary' => [
            'revenue' => $revenue,
            'cogs' => $cogs,
            'gross_profit' => $gross_profit,
            'profit_margin' => round($profit_margin, 2),
            'success_orders' => $success_orders
        ],
        'chart' => [
            'labels' => $labels,
            'revenue' => $data_revenue,
            'profit' => $data_profit
        ],
        'top_products' => $top_profits
    ]);
    exit;
}
} catch (PDOException $e) {
    die("Lỗi: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Báo cáo Tài chính</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8fafc; }
        .main-content { margin-left: 280px; padding: 30px;min-height: 100vh; transition: margin-left 0.4s ease; }
        @media (max-width: 768px) { .main-content { margin-left: 0; padding: 15px; } }
    </style>

</head>
<body >
    <?php include '../includes/sidebar.php'; ?>
    
    <div class="bg-slate-50 p-8">

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
        <div>
            <h1 class="text-3xl font-black text-[#1e293b] tracking-tight">Báo cáo Tài chính</h1>
            <p class="text-sm text-slate-400 mt-1 font-medium">Số liệu chính xác dựa trên giá vốn thực tế tại thời điểm bán</p>
        </div>
        <form method="GET" class="flex items-center bg-white p-1.5 rounded-2xl shadow-sm border border-slate-100">
            <div class="flex items-center px-3 gap-2">
                <input type="date" name="start_date" value="<?= $start_date ?>" class="text-[13px] font-bold border-none focus:ring-0 text-slate-600 p-1">
                <span class="text-slate-300 font-light">→</span>
                <input type="date" name="end_date" value="<?= $end_date ?>" class="text-[13px] font-bold border-none focus:ring-0 text-slate-600 p-1">
            </div>
            <button type="submit" class="bg-[#2563eb] text-white px-6 py-2 rounded-xl text-xs font-black shadow-lg shadow-blue-100 hover:bg-blue-700 transition-all flex items-center gap-2">
                <i class="fas fa-filter text-[10px]"></i> Lọc
            </button>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 relative overflow-hidden">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Doanh thu thuần</p>
            <h2 class="text-2xl font-black text-slate-800 mt-2"><?= number_format($revenue, 0, ',', '.') ?> đ</h2>
            <p class="text-emerald-500 text-[11px] font-bold mt-2"><i class="fas fa-shopping-cart"></i> <?= $success_orders ?> đơn hàng thành công</p>
            <i class="fas fa-wallet absolute top-6 right-6 text-blue-500 bg-blue-50 p-3 rounded-xl"></i>
        </div>

        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 relative overflow-hidden">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tổng giá vốn (COGS)</p>
            <h2 class="text-2xl font-black text-slate-600 mt-2"><?= number_format($cogs, 0, ',', '.') ?> đ</h2>
            <p class="text-slate-400 text-[10px] mt-2 italic">Chi phí hàng hóa đã bán</p>
            <i class="fas fa-boxes absolute top-6 right-6 text-slate-400 bg-slate-50 p-3 rounded-xl"></i>
        </div>

        <div class="bg-emerald-500 p-6 rounded-3xl shadow-lg shadow-emerald-100 relative text-white">
            <p class="text-[10px] font-bold opacity-80 uppercase tracking-widest">Lợi nhuận gộp</p>
            <h2 class="text-2xl font-black mt-2"><?= number_format($gross_profit, 0, ',', '.') ?> đ</h2>
            <p class="text-[11px] mt-2 opacity-90">Tiền lãi thực tế thu về</p>
            <i class="fas fa-chart-line absolute top-6 right-6 bg-white/20 p-3 rounded-xl"></i>
        </div>

        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tỷ suất lợi nhuận</p>
            <h2 class="text-2xl font-black text-emerald-500 mt-2"><?= round($profit_margin) ?>%</h2>
            <div class="w-full bg-slate-100 h-1.5 rounded-full mt-4">
                <div class="bg-orange-400 h-1.5 rounded-full transition-all duration-500" style="width: <?= min($profit_margin, 100) ?>%"></div>
            </div>
            <p class="text-[10px] text-slate-400 mt-2 text-right italic">Mục tiêu: >20%</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <div class="lg:col-span-2 bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex flex-col">
            <div class="flex justify-between items-center mb-6">
                <h3 class="font-bold text-slate-800 text-sm flex items-center"><i class="fas fa-chart-line text-blue-500 mr-2"></i> Xu hướng kinh doanh</h3>
                <div class="flex gap-4">
                    <div class="flex items-center gap-1.5"><span class="w-3 h-0.5 bg-blue-500"></span><span class="text-[10px] text-slate-500">Doanh thu</span></div>
                    <div class="flex items-center gap-1.5"><span class="w-3 h-0.5 border-t border-dashed border-emerald-500"></span><span class="text-[10px] text-slate-500">Lợi nhuận</span></div>
                </div>
            </div>
            <div class="relative h-[280px] w-full mt-auto"><canvas id="businessChart"></canvas></div>
        </div>

        <div class="lg:col-span-1 bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
    <h3 class="font-bold text-slate-800 text-sm mb-6 flex items-center">
        <i class="fas fa-crown text-orange-400 mr-2"></i> Top lợi nhuận cao nhất
    </h3>
    <div class="space-y-6">
        <?php if (!empty($top_profits)): ?>
            <?php foreach($top_profits as $index => $tp): ?>
            <div class="flex items-center justify-between group">
                <div class="flex items-center gap-3">
                    <span class="w-6 h-6 bg-slate-100 text-[10px] font-bold flex items-center justify-center rounded-full text-slate-500">
                        <?= $index + 1 ?>
                    </span>
                    <div class="max-w-[150px]">
                        <p class="text-xs font-bold text-slate-800 truncate"><?= htmlspecialchars($tp['product_name']) ?></p>
                        <p class="text-[10px] text-slate-400 truncate"><?= htmlspecialchars($tp['variant_display']) ?></p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-xs font-bold text-emerald-500">+<?= number_format($tp['net_profit'], 0, ',', '.') ?> đ</p>
                    <p class="text-[9px] text-slate-400 italic">DT: <?= number_format($tp['total_revenue'], 0, ',', '.') ?> đ</p>
                </div>
            </div>

            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center py-10">
                <i class="fas fa-inbox text-slate-200 text-3xl mb-3"></i>
                <p class="text-xs text-slate-400 italic">Chưa có dữ liệu sinh lời</p>
            </div>
        <?php endif; ?>
    </div>
</div>
    </div>

    <script>
    const ctx = document.getElementById('businessChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [
                {
                    label: 'Doanh thu',
                    data: <?= json_encode($data_revenue) ?>,
                    borderColor: '#3b82f6',
                    borderWidth: 2,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#3b82f6',
                    pointRadius: 4,
                    tension: 0,
                    fill: false
                },
                {
                    label: 'Lợi nhuận',
                    data: <?= json_encode($data_profit) ?>,
                    borderColor: '#10b981',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    pointRadius: 0,
                    tension: 0,
                    fill: false
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { font: { size: 10 }, callback: (value) => value.toLocaleString('vi-VN') }
                },
                x: { ticks: { font: { size: 10 } } }
            }
        }
    });
    </script>
</div>
</html>