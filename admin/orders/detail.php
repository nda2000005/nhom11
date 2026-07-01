<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../../config/database.php'; 
require_once '../../core/Database.php';

$db = new Database($pdo);
$id = intval($_GET['id'] ?? 0);

if ($id <= 0) die('Thiếu ID đơn hàng');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (isset($_POST['ship_order'])) {
        $db->query("UPDATE orders SET current_status='SHIPPING' WHERE id=?", [$id]);
        // Sau khi update xong, load lại trang để thấy trạng thái mới
        header("Location: detail.php?id=$id"); 
        exit;
    }

    if (isset($_POST['cancel_order'])) {
        $db->query("UPDATE orders SET current_status='CANCELLED' WHERE id=?", [$id]);
        header("Location: detail.php?id=$id");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi tiết đơn hàng</title>
    <style>
        :root {
            --bg-color: #f8f9fc; --card-bg: #ffffff; --text-main: #333;
            --text-muted: #888; --primary-blue: #2563eb; --danger-red: #dc3545;
            --pending-bg: #fff3cd; --pending-text: #856404;
        }
        body { font-family: 'Segoe UI', sans-serif; background-color: var(--bg-color); margin: 0; padding: 40px 20px; }
        .container { max-width: 1100px; margin: auto; }
        .order-header { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .order-title h1 { font-size: 24px; margin: 0; }
        .order-title span { color: var(--primary-blue); }
        .order-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
        .card { background: var(--card-bg); border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 20px; }
        .card-title { font-weight: bold; margin-bottom: 20px; display: flex; justify-content: space-between; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; font-size: 12px; color: var(--text-muted); border-bottom: 1px solid #eee; padding-bottom: 10px; }
        td { padding: 15px 0; border-bottom: 1px solid #f9f9f9; }
        .prod-name { font-weight: 600; margin: 0; }
        .price { font-weight: bold; text-align: right; }
        .serial-input { border: 1px solid #ddd; padding: 8px; border-radius: 4px; width: 180px; }
        .badge { padding: 4px 12px; border-radius: 4px; font-size: 11px; font-weight: bold; }
        .PENDING { background: var(--pending-bg); color: var(--pending-text); }
        .SHIPPING { background: #e0f2fe; color: #0369a1; }
        .total-section { margin-top: 20px; text-align: right; font-size: 18px; font-weight: bold; }
        .total-section span { color: var(--primary-blue); }
        .bottom-bar { margin-top: 30px; text-align: center; border-top: 1px solid #eee; padding-top: 20px; }
        .btn-main { background: var(--primary-blue); color: white; padding: 12px 30px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; }
        .btn-cancel { color: var(--danger-red); background: none; border: none; cursor: pointer; font-weight: bold; }
        .btn-outline { padding: 8px 16px; border: 1px solid #ddd; background: white; border-radius: 4px; text-decoration: none; color: #333; font-size: 13px; }
    </style>
</head>
<body>

<div class="container" id="app">
    <p>Đang tải dữ liệu...</p>
</div>

<script>
const id = <?= $id ?>;

fetch(`/mnopl/admin/orders/api/orders_detail.php?id=${id}`)
.then(res => res.json())
.then(res => {
    if (res.status !== "success") {
        document.getElementById("app").innerHTML = `<div class="card">${res.message}</div>`;
        return;
    }

    const order = res.data.order;
    const items = res.data.items;

    let itemsHTML = items.map(item => `
        <tr>
            <td>
                <p class="prod-name">${item.name}</p>
                <p style="font-size:12px; color:#999; margin:4px 0;">${item.variant_name} | ${item.sku}</p>
            </td>
            <td style="text-align:center"><b>${item.quantity}</b></td>
            <td><input type="text" class="serial-input" placeholder="Quét IMEI..."></td>
            <td class="price">${item.price_formatted}</td>
        </tr>
    `).join('');

    document.getElementById("app").innerHTML = `
        <a href="index.php" style="text-decoration:none; color:#666; font-size:13px;">← Quay lại danh sách</a>
        
        <div class="order-header">
            <div class="order-title">
                <h1>Đơn hàng <span>#${order.order_code}</span></h1>
                <div style="font-size:13px; color:#888; margin-top:5px;">Ngày đặt: ${order.created_at || 'N/A'}</div>
            </div>
            <div style="display:flex; gap:10px; align-items:center;">
                ${(order.current_status === 'SHIPPING' || order.current_status === 'COMPLETED') 
                    ? `<a href="print.php?id=${id}" target="_blank" class="btn-outline">🖨 In Phiếu</a>`
                    : `<button class="btn-outline" disabled style="opacity:0.5;">🖨 In Phiếu</button>`
                }
                <form method="POST">
                    <button type="submit" name="cancel_order" class="btn-cancel" 
                        onclick="return confirm('Hủy đơn hàng này?')"
                        ${order.current_status !== 'PENDING' ? 'disabled style="opacity:0.5;"' : ''}>
                        Hủy Đơn
                    </button>
                </form>
            </div>
        </div>

        <div class="order-grid">
            <div class="card">
                <div class="card-title">
                    Chi tiết & Serial Number
                    <span class="badge ${order.current_status}">${order.current_status}</span>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th style="text-align:center">SL</th>
                            <th>Serial / IMEI</th>
                            <th style="text-align:right">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>${itemsHTML}</tbody>
                </table>
                <div class="total-section">TỔNG: <span>${order.total_amount_formatted}</span></div>
            </div>

            <div class="card">
                <div class="card-title">👤 Khách hàng</div>
                <p><b>${order.full_name}</b></p>
                <p style="color:var(--primary-blue)">${order.phone}</p>
                <p style="font-size:14px;">${order.address}</p>
            </div>
        </div>

        ${order.current_status === 'PENDING' ? `
            <div class="bottom-bar">
                <form method="POST">
                    <button type="submit" name="ship_order" class="btn-main">📦 XÁC NHẬN XUẤT KHO</button>
                </form>
            </div>
        ` : ''}
    `;
})
.catch(err => {
    document.getElementById("app").innerHTML = "Lỗi tải dữ liệu";
});
</script>
</body>
</html>