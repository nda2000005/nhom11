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
        body { font-family: 'Inter', sans-serif; }
        .badge { padding: 4px 10px; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; }
        .bg-success { background: #d1fae5; color: #065f46; }
        .bg-pending { background: #fef3c7; color: #92400e; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased">

<div class="flex h-screen overflow-hidden">
    <?php include '../../includes/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden bg-[#F9FAFB]">
        <main class="flex-1 overflow-x-hidden overflow-y-auto p-8">
            
            <div class="mb-6">
                <h1 class="text-[20px] font-bold text-gray-900">Quản lý Đơn hàng</h1>
                <div id="statsText" class="text-gray-500 text-sm mt-1">Đang tải dữ liệu...</div>
            </div>

            <div class="bg-white border border-gray-200 rounded-sm shadow-sm overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-white border-b border-gray-100">
                        <tr class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">
                            <th class="px-6 py-4">Mã đơn</th>
                            <th class="px-6 py-4">Khách hàng</th>
                            <th class="px-6 py-4 text-right">Tổng tiền</th>
                            <th class="px-6 py-4 text-center">Trạng thái</th>
                            <th class="px-6 py-4">Ngày tạo</th>
                            <th class="px-6 py-4 text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="orderTableBody" class="divide-y divide-gray-50">
                        <tr><td colspan="6" class="px-6 py-10 text-center text-gray-500">Đang tải dữ liệu...</td></tr>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<script>
    // Logic fetch dữ liệu
    const apiPath = '/mnopl/admin/order/api/index.php';
    const orderTableBody = document.getElementById('orderTableBody');
    const statsText = document.getElementById('statsText');

    fetch(apiPath)
    .then(response => response.json())
    .then(res => {
        if(res.data && res.data.length > 0) {
            statsText.innerText = `Tổng số đơn: ${res.data.length}`;
            orderTableBody.innerHTML = res.data.map(o => `
                <tr class="hover:bg-gray-50/50 transition-colors">
                    <td class="px-6 py-4 font-medium text-gray-900">${o.order_code}</td>
                    <td class="px-6 py-4 text-gray-600">${o.full_name || 'Khách lẻ'}</td>
                    <td class="px-6 py-4 text-right font-bold text-gray-900">${new Intl.NumberFormat('vi-VN').format(o.total_amount)}đ</td>
                    <td class="px-6 py-4 text-center">
                        <span class="badge ${o.current_status === 'COMPLETED' ? 'bg-success' : 'bg-pending'}">
                            ${o.current_status}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-gray-500 text-sm">${o.created_at}</td>
                    <td class="px-6 py-4 text-right">
                        <a href="/mnopl/admin/order/View/detail.php?id=${o.id}" class="text-[12px] text-blue-600 hover:underline">Xem chi tiết</a>
                    </td>
                </tr>
            `).join('');
        } else {
            statsText.innerText = "Tổng số đơn: 0";
            orderTableBody.innerHTML = '<tr><td colspan="6" class="px-6 py-12 text-center text-gray-500">Không có đơn hàng nào.</td></tr>';
        }
    })
    .catch(err => {
        orderTableBody.innerHTML = '<tr><td colspan="6" class="px-6 py-10 text-center text-red-500">Không thể kết nối dữ liệu.</td></tr>';
    });
</script>
</body>
</html>