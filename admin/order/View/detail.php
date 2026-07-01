<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi tiết đơn hàng</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background-color: #f7f9fc; color: #333; margin: 0; padding: 20px; }
        .container { max-width: 1100px; margin: auto; }
        
        /* Header Section */
        .header-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
        .back-link { color: #666; text-decoration: none; font-size: 14px; margin-bottom: 5px; display: block; }
        h1 { margin: 0; font-size: 24px; color: #1a1a1a; }
        .order-date { color: #888; font-size: 14px; margin-top: 5px; }
        .header-actions { display: flex; gap: 10px; }
        
        /* Buttons */
        .btn-header { padding: 8px 16px; border: 1px solid #ddd; background: white; cursor: pointer; border-radius: 4px; font-size: 13px; }
        .btn-cancel { color: #dc3545; border-color: #dc3545; }
        .btn-main { width: 100%; padding: 16px; background: #2563eb; color: white; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; margin-top: 20px; }
        
        /* Nút > Style (Đồng bộ) */
        .btn-arrow { display: inline-block; width: 32px; height: 32px; line-height: 32px; text-align: center; border: 1px solid #ddd; border-radius: 6px; background-color: white; color: #333; text-decoration: none; transition: all 0.2s; font-weight: bold; }
        .btn-arrow:hover { background-color: #007bff; color: white; border-color: #007bff; transform: scale(1.05); }

        /* Main Layout */
        .main-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }

        /* Table */
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { text-align: left; padding: 12px 5px; border-bottom: 1px solid #eee; color: #666; font-size: 12px; text-transform: uppercase; }
        td { padding: 15px 5px; border-bottom: 1px solid #eee; vertical-align: middle; }
        input[type="text"] { width: 90%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        .total-section { text-align: right; margin-top: 20px; font-size: 18px; }
        
        /* Badge & Info */
        .status-badge { padding: 4px 12px; border-radius: 12px; font-size: 11px; background: #fff3cd; color: #856404; font-weight: bold; }
        .info-label { font-size: 11px; color: #888; display: block; margin-bottom: 4px; text-transform: uppercase; }
        .info-value { font-weight: 600; margin-bottom: 15px; display: block; }
    </style>
</head>
<body>

<div class="container">
    <div class="header-top">
        <div>
            <a href="index.php" class="back-link">← Quay lại danh sách</a>
            <h1 id="orderTitle">Đơn hàng #...</h1>
            <div class="order-date" id="orderDate">Ngày đặt: ...</div>
        </div>
        <div class="header-actions" id="headerActions"></div>
    </div>

    <div class="main-grid">
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <strong>Chi tiết & Serial Number</strong>
                <span class="status-badge" id="statusBadge">PENDING</span>
            </div>
            <table>
                <thead><tr><th>Sản phẩm</th><th>SL</th><th>Mua Serial / IMEI</th><th>Thành tiền</th></tr></thead>
                <tbody id="itemsBody"></tbody>
            </table>
            <div class="total-section">
                <strong>TỔNG THANH TOÁN:</strong> <span id="totalAmount" style="color: #2563eb; font-weight: bold;">0đ</span>
            </div>
            <div id="mainActionContainer"></div>
            <p style="text-align:center; color:#888; font-size: 12px; margin-top:15px;">* Vui lòng quét đủ số lượng IMEI để xuất kho chính xác.</p>
        </div>

        <div class="card">
            <strong>👤 Thông tin khách hàng</strong>
            <div style="margin-top:20px;">
                <span class="info-label">Họ tên</span><span class="info-value" id="cusName">-</span>
                <span class="info-label">Điện thoại</span><span class="info-value" id="cusPhone">-</span>
                <span class="info-label">Địa chỉ</span><span class="info-value" id="cusAddr">-</span>
                <span class="info-label">Email</span><span class="info-value" id="cusEmail">-</span>
            </div>
        </div>
    </div>
</div>

<script>
    const orderId = new URLSearchParams(window.location.search).get('id');

    // 1. Fetch dữ liệu
    fetch('../api/detail.php?id=' + orderId)
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success') {
            const o = data.order;
            
            // Cập nhật thông tin text
            document.getElementById('orderTitle').innerText = 'Đơn hàng #' + o.order_code;
            document.getElementById('orderDate').innerText = 'Ngày đặt: ' + o.created_at;
            document.getElementById('statusBadge').innerText = o.current_status;
            document.getElementById('cusName').innerText = o.full_name || 'Khách lẻ';
            document.getElementById('cusPhone').innerText = o.phone || 'Chưa cập nhật';
            document.getElementById('cusAddr').innerText = o.address || 'Chưa cập nhật';
            document.getElementById('cusEmail').innerText = o.email || 'Chưa cập nhật';
            document.getElementById('totalAmount').innerText = new Intl.NumberFormat('vi-VN').format(o.total_amount) + 'đ';
            
            // Cập nhật danh sách sản phẩm
            document.getElementById('itemsBody').innerHTML = data.items.map((item, i) => `
                <tr>
                    <td>${item.name}</td>
                    <td>${item.quantity}</td>
                    <td><input type="text" id="imei_${i}" placeholder="Quét IMEI..."></td>
                    <td>${new Intl.NumberFormat('vi-VN').format(item.price * item.quantity)}đ</td>
                </tr>
            `).join('');

            // 2. Tạo nút bấm bằng JavaScript (đảm bảo bấm được)
            const headerActions = document.getElementById('headerActions');
            const mainAction = document.getElementById('mainActionContainer');

            headerActions.innerHTML = '';
            mainAction.innerHTML = '';

            if(o.current_status === 'PENDING') {
                const btnCancel = document.createElement('button');
                btnCancel.className = 'btn-header btn-cancel';
                btnCancel.innerText = 'Hủy Đơn';
                btnCancel.onclick = () => alert('Tính năng hủy đang phát triển!');
                headerActions.appendChild(btnCancel);

                const btnConfirm = document.createElement('button');
                btnConfirm.className = 'btn-main';
                btnConfirm.innerText = 'XÁC NHẬN XUẤT KHO';
                btnConfirm.onclick = () => {
                    alert('Đang xác nhận đơn hàng #' + o.order_code);
                };
                mainAction.appendChild(btnConfirm);
            } else {
                const btnPrint = document.createElement('button');
                btnPrint.className = 'btn-header';
                btnPrint.innerText = 'In Phiếu';
                btnPrint.onclick = () => window.open('print.php?id=' + orderId, '_blank');
                headerActions.appendChild(btnPrint);
            }
        }
    })
    .catch(err => console.error("Lỗi tải dữ liệu:", err));
</script>
</body>
</html>