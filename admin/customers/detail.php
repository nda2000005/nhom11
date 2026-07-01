<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết khách hàng</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .btn-arrow {
            display: inline-block;
            width: 32px;
            height: 32px;
            line-height: 32px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 6px;
            background-color: white;
            color: #333;
            text-decoration: none;
            transition: all 0.2s;
            font-weight: bold;
        }
        .btn-arrow:hover {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
            transform: scale(1.05);
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">

<div class="flex h-screen overflow-hidden">
    <?php include '../includes/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <main class="flex-1 overflow-y-auto p-8">
            <div id="app" class="max-w-6xl mx-auto">
                <div id="content">
                    <div class="flex justify-between items-center mb-6">
                        <a href="index.php" class="text-sm text-gray-500 hover:text-blue-600"><i class="fas fa-arrow-left mr-2"></i> Quay lại danh sách</a>
                        <div id="customerIdDisplay" class="text-xs text-gray-400 font-mono">ID: ...</div>
                    </div>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div class="col-span-1 space-y-6">
                            <div class="bg-white p-8 rounded-lg border border-gray-100 shadow-sm text-center">
                                <div id="avatar" class="h-24 w-24 mx-auto rounded-full bg-blue-50 flex items-center justify-center text-3xl font-bold text-blue-600 mb-4 uppercase">?</div>
                                <h1 id="customerName" class="text-xl font-bold">...</h1>
                                <span class="inline-block px-3 py-1 bg-yellow-50 text-yellow-700 text-xs font-bold rounded-full mt-2">VIP PLATINUM</span>
                                
                                <div class="mt-8 text-left space-y-4 text-sm text-gray-600" id="infoPanel"></div>
                            </div>
                            
                            <div class="bg-white p-6 rounded-lg border border-gray-100 shadow-sm">
                                <div class="text-xs font-bold text-gray-400 uppercase mb-2">Giá trị trọn đời (LTV)</div>
                                <div id="totalSpent" class="text-2xl font-bold text-blue-600 border-b-2 border-blue-500 pb-2 mb-4">0đ</div>
                                <div class="flex justify-between">
                                    <div class="text-center"><div id="successOrders" class="font-bold text-lg">0</div><div class="text-[10px] text-gray-400 uppercase">Thành công</div></div>
                                    <div class="text-center"><div id="totalOrders" class="font-bold text-lg">0</div><div class="text-[10px] text-gray-400 uppercase">Tổng đơn</div></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-span-1 lg:col-span-2">
                            <div class="bg-white rounded-lg border border-gray-100 shadow-sm overflow-hidden">
                                <div class="px-6 py-4 border-b border-gray-100 font-bold text-gray-700 flex items-center">
                                    <i class="fas fa-list mr-2 text-blue-500"></i> Lịch sử giao dịch
                                </div>
                                <table class="w-full text-left">
                                    <thead class="bg-gray-50 text-gray-400 text-[11px] uppercase tracking-wider">
                                        <tr>
                                            <th class="px-6 py-3">Mã đơn</th>
                                            <th class="px-6 py-3">Ngày mua</th>
                                            <th class="px-6 py-3">Trạng thái</th>
                                            <th class="px-6 py-3 text-right">Giá trị</th>
                                            <th class="px-6 py-3"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="orderTableBody" class="divide-y divide-gray-50"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
const id = <?= intval($_GET['id'] ?? 0) ?>;
const orderTableBody = document.getElementById("orderTableBody");

// Hàm định dạng tiền tệ cho bảng (tương tự như cách bạn muốn)
function formatMoney(amount) {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
}

function renderOrderRow(o) {
    // Ép kiểu số để định dạng tiền tệ chuẩn
    const price = parseFloat(o.total_amount) || 0;
    return `
        <tr class="hover:bg-gray-50 transition-colors text-sm">
            <td class="px-6 py-4 font-medium text-blue-600">${o.order_code}</td>
            <td class="px-6 py-4 text-gray-500">${o.created_at}</td>
            <td class="px-6 py-4">
                <span class="px-2 py-1 bg-green-50 text-green-700 text-[10px] font-bold uppercase rounded">${o.current_status}</span>
            </td>
            <td class="px-6 py-4 text-right font-bold text-gray-900">${formatMoney(price)}</td>
            <td class="px-6 py-4 text-right">
                <a href="/mnopl/admin/order/View/detail.php?id=${o.id}" class="btn-arrow">></a>
            </td>
        </tr>
    `;
}

function loadDetail() {
    fetch(`/mnopl/admin/customers/api/customers/detail.php?id=${id}`)
    .then(res => res.json())
    .then(res => {
        if (res.status !== "success") {
            document.getElementById("app").innerHTML = `<div class="text-red-500 p-8 text-center">Lỗi: ${res.message}</div>`;
            return;
        }
        
        const { customer, orders, summary } = res.data;

        // Cập nhật thông tin khách hàng
        document.getElementById("customerName").innerText = customer.full_name;
        document.getElementById("customerIdDisplay").innerText = "ID: " + customer.id;
        document.getElementById("avatar").innerText = customer.full_name.charAt(0);
        
        // Cập nhật thông tin chi tiết
        document.getElementById("infoPanel").innerHTML = `
            <div class="flex items-center"><i class="fas fa-phone mr-3 text-gray-400"></i> ${customer.phone || '-'}</div>
            <div class="flex items-center"><i class="fas fa-envelope mr-3 text-gray-400"></i> ${customer.email || '-'}</div>
            <div class="flex items-center"><i class="fas fa-map-marker-alt mr-3 text-gray-400"></i> ${customer.address || '-'}</div>
            <div class="flex items-center"><i class="fas fa-clock mr-3 text-gray-400"></i> ${customer.created_at || '-'}</div>
        `;

        // Stats - Giữ nguyên như cũ
        document.getElementById("totalSpent").innerText = summary.total_spent_formatted || '0đ';
        document.getElementById("successOrders").innerText = summary.success_orders || 0;
        document.getElementById("totalOrders").innerText = summary.total_orders || 0;

        // Table
        orderTableBody.innerHTML = orders.length > 0 
            ? orders.map(renderOrderRow).join('') 
            : '<tr><td colspan="5" class="px-6 py-10 text-center text-gray-400">Không có đơn hàng.</td></tr>';
    })
    .catch(err => {
        document.getElementById("app").innerHTML = `<div class="text-red-500 p-8 text-center">Lỗi kết nối API.</div>`;
    });
}

loadDetail();
</script>
</body>
</html>