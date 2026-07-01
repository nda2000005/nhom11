<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Đơn hàng</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; letter-spacing: -0.01em; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 4px; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased">

    <div class="flex h-screen overflow-hidden">
        
        <?php include '../includes/sidebar.php'; ?>

        <div class="flex-1 flex flex-col overflow-hidden bg-[#F9FAFB]">
            
            <main class="flex-1 overflow-x-hidden overflow-y-auto p-8">
                
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-[20px] font-bold text-gray-900">Quản lý Đơn hàng</h1>
                    <div class="text-gray-700 font-semibold text-sm">
                        Tổng số khách: <span id="totalCount">0</span>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-sm shadow-sm overflow-hidden">
                    <table class="w-full text-left">
                        <thead class="bg-white border-b border-gray-100">
                            <tr class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">
                                <th class="px-6 py-4">Mã đơn</th>
                                <th class="px-6 py-4">Khách hàng</th>
                                <th class="px-6 py-4">Tổng tiền</th>
                                <th class="px-6 py-4">Trạng thái</th>
                                <th class="px-6 py-4">Ngày tạo</th>
                                <th class="px-6 py-4"></th>
                            </tr>
                        </thead>

                        <tbody id="orderTable" class="divide-y divide-gray-50">
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-gray-500 text-sm">
                                    <i class="fas fa-spinner fa-spin mr-2"></i> Đang tải dữ liệu...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </main>
        </div>
    </div>

<script>
function getStatusBadge(status) {
    const st = status.toUpperCase();
    const base = "px-2.5 py-0.5 text-[10px] font-bold rounded uppercase tracking-tight border ";
    
    switch (st) {
        case 'PENDING': 
            return `<span class="${base} bg-orange-50 text-orange-500 border-orange-100">PENDING</span>`;
        case 'SHIPPING': 
            return `<span class="${base} bg-blue-50 text-blue-500 border-blue-100">SHIPPING</span>`;
        case 'COMPLETED': 
            return `<span class="${base} bg-emerald-50 text-emerald-600 border-emerald-100">COMPLETED</span>`;
        case 'CANCELLED': 
            return `<span class="${base} bg-red-50 text-red-600 border-red-100">CANCELLED</span>`;
        default: 
            return `<span class="${base} bg-gray-50 text-gray-500 border-gray-100">${st}</span>`;
    }
}

fetch('api/orders.php')
.then(res => res.json())
.then(data => {
    const orders = data.data || [];
    const table = document.getElementById("orderTable");
    document.getElementById("totalCount").innerText = orders.length;

    if (orders.length === 0) {
        table.innerHTML = `<tr><td colspan="6" class="px-6 py-10 text-center text-gray-500 text-sm">Chưa có đơn hàng nào.</td></tr>`;
        return;
    }

    let html = "";
    orders.forEach(o => {
        const amount = o.total_amount_formatted || (new Intl.NumberFormat('vi-VN').format(o.total_amount) + 'đ');
        const date = o.created_at_formatted || o.created_at;

        html += `
            <tr class="hover:bg-gray-50/50 transition-colors">
                <td class="px-6 py-4 text-[13px] font-bold text-blue-600 tracking-tight">
                    <a href="detail.php?id=${o.id}">#${o.order_code}</a>
                </td>
                <td class="px-6 py-4 text-[13px] text-gray-600">${o.full_name}</td>
                <td class="px-6 py-4 text-[13px] font-bold text-gray-900">${amount}</td>
                <td class="px-6 py-4">
                    ${getStatusBadge(o.current_status)}
                </td>
                <td class="px-6 py-4 text-[12px] text-gray-400">${date}</td>
                <td class="px-6 py-4 text-right">
                    <a href="detail.php?id=${o.id}" 
                       class="text-[12px] text-gray-400 hover:text-blue-600 font-medium border border-gray-200 px-3 py-1 rounded hover:bg-white transition">
                        Chi tiết
                    </a>
                </td>
            </tr>
        `;
    });

    table.innerHTML = html;
})
.catch(err => {
    console.error("Lỗi khi tải dữ liệu: ", err);
    document.getElementById("orderTable").innerHTML = `<tr><td colspan="6" class="px-6 py-10 text-center text-red-500 text-sm">Lỗi kết nối máy chủ.</td></tr>`;
});
</script>
</body>
</html>