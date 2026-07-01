<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý khách hàng (CRM)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { 
            font-family: 'Inter', sans-serif; 
            letter-spacing: -0.01em; 
            -webkit-text-size-adjust: 100%; 
            text-size-adjust: 100%; 
        }
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
            <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                <div>
                    <h1 class="text-[20px] font-bold text-gray-900">Quản lý khách hàng (CRM)</h1>
                    <div id="statsText" class="text-gray-500 text-sm mt-1">Đang tải dữ liệu...</div>
                </div>
                <div class="w-full md:w-auto relative">
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <i class="fas fa-search text-gray-400"></i>
                        </span>
                        <input type="text" id="searchInput" placeholder="Tìm tên, SĐT, Email..." class="w-full md:w-80 pl-10 pr-10 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm">
                        <button id="clearSearch" aria-label="Xóa tìm kiếm" title="Xóa" class="hidden absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times-circle" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-sm shadow-sm overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-white border-b border-gray-100">
                        <tr class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">
                            <th class="px-6 py-4">Khách hàng</th>
                            <th class="px-6 py-4">Liên hệ</th>
                            <th class="px-6 py-4 text-center">Số đơn thành công</th>
                            <th class="px-6 py-4 text-right">Tổng chi tiêu</th>
                            <th class="px-6 py-4">Mua gần nhất</th>
                            <th class="px-6 py-4 text-right">Hành động</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody" class="divide-y divide-gray-50">
                        </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<script>
const tableBody = document.getElementById("tableBody");
const statsText = document.getElementById("statsText");
const searchInput = document.getElementById("searchInput");
const clearSearch = document.getElementById("clearSearch");

function renderRow(c) {
    const firstLetter = c.full_name ? c.full_name.charAt(0).toUpperCase() : '?';
    return `
        <tr class="hover:bg-gray-50/50 transition-colors">
            <td class="px-6 py-4 flex items-center">
                <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-xs mr-3">${firstLetter}</div>
                <div class="font-medium text-gray-900">${c.full_name}</div>
            </td>
            <td class="px-6 py-4 text-gray-600">
                <div class="flex items-center gap-2"><i class="fas fa-phone text-xs text-gray-400"></i> ${c.phone || '-'}</div>
                <div class="flex items-center gap-2 mt-1"><i class="fas fa-envelope text-xs text-gray-400"></i> ${c.email || '-'}</div>
            </td>
            <td class="px-6 py-4 text-center"><span class="bg-gray-100 text-gray-700 py-1 px-2 rounded text-xs font-bold">${c.success_orders || 0}</span></td>
            <td class="px-6 py-4 text-right font-bold text-emerald-600">${c.total_spent_formatted || '0đ'}</td>
            <td class="px-6 py-4 text-gray-500 text-sm">${c.last_order_date || '-'}</td>
            <td class="px-6 py-4 text-right"><a href="detail.php?id=${c.id}" class="text-[12px] text-blue-600 hover:text-blue-800 font-medium hover:underline">Xem chi tiết <i class="fas fa-arrow-right ml-1"></i></a></td>
        </tr>
    `;
}

function loadCustomers(search = "") {
    clearSearch.classList.toggle('hidden', search === "");
    
    // Đường dẫn chuẩn: File API nằm ở 'api/customers/index.php' 
    // so với vị trí hiện tại của file index.php
    fetch(`api/customers/index.php?search=${encodeURIComponent(search)}`)
    .then(res => {
        if (!res.ok) throw new Error("Server phản hồi lỗi: " + res.status);
        return res.json();
    })
    .then(res => {
        if (res.status !== "success") {
            tableBody.innerHTML = `<tr><td colspan="6" class="px-6 py-10 text-center text-red-500">Lỗi: ${res.message || 'Không có dữ liệu'}</td></tr>`;
            return;
        }
        const data = res.data || [];
        statsText.innerHTML = search ? `Kết quả tìm kiếm cho: "${search}" (${data.length})` : `Tổng số khách: ${data.length}`;
        tableBody.innerHTML = data.length > 0 ? data.map(renderRow).join('') : `<tr><td colspan="6" class="px-6 py-12 text-center text-gray-500">Không tìm thấy khách hàng nào.</td></tr>`;
    })
    .catch(err => {
        console.error("Lỗi fetch:", err);
        tableBody.innerHTML = `<tr><td colspan="6" class="px-6 py-10 text-center text-red-500">Không thể kết nối API. Mở F12 > Console để xem lỗi.</td></tr>`;
    });
}

searchInput.addEventListener("keyup", () => loadCustomers(searchInput.value));
clearSearch.addEventListener("click", () => { searchInput.value = ""; loadCustomers(""); });
loadCustomers(); // Chạy ngay khi tải trang
</script>
</body>
</html>