<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Phiếu Xuất Kho</title>
    <style>
        body { font-family: 'Times New Roman', serif; padding: 40px; color: #000; }
        
        /* Phần điều khiển không in */
        .no-print { margin-bottom: 20px; display: flex; gap: 10px; }
        .print-btn { padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer; border-radius: 5px; }
        .back-btn { padding: 10px 20px; background: #6c757d; color: white; border: none; cursor: pointer; border-radius: 5px; text-decoration: none; }
        
        /* Cấu trúc phiếu */
        .container { width: 100%; max-width: 800px; margin: auto; }
        h1 { text-align: center; text-transform: uppercase; margin-bottom: 20px; }
        .info { margin-bottom: 20px; }
        
        /* Bảng */
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: center; }
        th { background-color: #f2f2f2; }
        
        /* Chữ ký */
        .signatures { display: flex; justify-content: space-between; margin-top: 50px; }
        .sig-box { width: 30%; text-align: center; }
        .sig-box p { margin-top: 60px; font-style: italic; }

        /* Không in các nút bấm */
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

<div class="no-print">
    <button class="back-btn" onclick="window.location.href='index.php'"> &larr; Quay lại</button>
    <button class="print-btn" onclick="window.print()">In Phiếu</button>
</div>

<div id="order-content" class="container">
    <h3>Đang tải dữ liệu đơn hàng...</h3>
</div>

<script>
    const urlParams = new URLSearchParams(window.location.search);
    const id = urlParams.get('id');

    // Gọi API của bạn
    fetch('../api/get_print_data.php?id=' + id)
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('order-content');
            if (data.status === 'success') {
                const order = data.order;
                const items = data.items;
                const today = new Date().toLocaleDateString('vi-VN');

                let html = `
                    <h1>PHIẾU XUẤT KHO</h1>
                    <div class="info">
                        <p><strong>Mã đơn hàng:</strong> ${order.order_code}</p>
                        <p><strong>Khách hàng:</strong> ${order.full_name}</p>
                        <p><strong>Điện thoại:</strong> ${order.phone}</p>
                        <p><strong>Địa chỉ:</strong> ${order.address}</p>
                        <p><strong>Ngày in:</strong> ${today}</p>
                    </div>
                    <table>
                        <tr>
                            <th>STT</th>
                            <th>Sản phẩm</th>
                            <th>Phiên bản / SKU</th>
                            <th>Số lượng</th>
                            <th>Đơn giá</th>
                            <th>Thành tiền</th>
                        </tr>`;
                
                items.forEach((item, index) => {
                    const price = parseFloat(item.price);
                    const total = price * item.quantity;
                    html += `<tr>
                        <td>${index + 1}</td>
                        <td style="text-align: left;">${item.name}</td>
                        <td>${item.variant_name || ''} | ${item.sku || ''}</td>
                        <td>${item.quantity}</td>
                        <td>${price.toLocaleString()}đ</td>
                        <td>${total.toLocaleString()}đ</td>
                    </tr>`;
                });

                html += `
                    <tr>
                        <td colspan="5" style="text-align: right; font-weight: bold;">TỔNG THANH TOÁN:</td>
                        <td style="font-weight: bold;">${parseFloat(order.total_amount).toLocaleString()}đ</td>
                    </tr>
                    </table>

                    <div class="signatures">
                        <div class="sig-box"><strong>Người xuất kho</strong><br>(Ký, ghi rõ họ tên)</div>
                        <div class="sig-box"><strong>Người giao hàng</strong><br>(Ký, ghi rõ họ tên)</div>
                        <div class="sig-box"><strong>Khách hàng</strong><br>(Ký, ghi rõ họ tên)</div>
                    </div>
                `;
                container.innerHTML = html;
            } else {
                container.innerHTML = "<h2 style='color:red'>Lỗi: " + data.message + "</h2>";
            }
        })
        .catch(err => {
            document.getElementById('order-content').innerHTML = "Có lỗi xảy ra khi kết nối tới API.";
        });
</script>

</body>
</html>