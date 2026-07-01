<?php
require_once '../../config/database.php';
require_once '../../core/Database.php';

$db = new Database($pdo);

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) die('Thiếu ID đơn hàng');

$order = $db->fetch(
    "SELECT o.*, c.full_name, c.phone, c.address
     FROM orders o
     LEFT JOIN customers c ON o.customer_id = c.id
     WHERE o.id = ?", [$id]
);

if (!$order) die('Không tìm thấy đơn hàng');

if ($order['current_status'] === 'SHIPPING') {
    $title = "PHIẾU XUẤT KHO";
} elseif ($order['current_status'] === 'COMPLETED') {
    $title = "PHIẾU HOÀN TẤT ĐƠN HÀNG";
} else {
    die("Đơn hàng chưa đủ điều kiện in phiếu");
}

$items = $db->fetchAll(
    "SELECT oi.quantity, oi.price, p.name, v.variant_name, v.sku
     FROM order_items oi
     JOIN product_variants v ON oi.variant_id = v.id
     JOIN products p ON v.product_id = p.id
     WHERE oi.order_id = ?", [$id]
);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title><?= $title ?></title>

<style>
    body {
        font-family: Arial, sans-serif;
        padding: 30px;
        font-size: 14px;
    }

    h2 {
        text-align: center;
        margin-bottom: 30px;
    }

    p {
        margin: 5px 0;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    th, td {
        border: 1px solid #333;
        padding: 8px;
        text-align: left;
    }

    th {
        background: #f0f0f0;
    }

    .total {
        text-align: right;
        font-weight: bold;
        margin-top: 15px;
        font-size: 16px;
    }

    .sign {
        margin-top: 50px;
        display: flex;
        justify-content: space-between;
        text-align: center;
    }

    .btn-back {
        text-decoration: none;
        color: #2563eb;
        font-weight: bold;
        display: inline-block;
        margin-bottom: 15px;
    }

    .btn-print {
        margin-top: 30px;
        padding: 10px 20px;
        font-size: 14px;
        cursor: pointer;
    }

    @media print {
        .btn-back, .btn-print {
            display: none;
        }
    }
</style>
</head>

<body>

<a href="detail.php?id=<?= $order['id'] ?>" class="btn-back">
    ← Quay lại chi tiết đơn hàng
</a>

<h2><?= $title ?></h2>

<p><b>Mã đơn hàng:</b> <?= htmlspecialchars($order['order_code']) ?></p>
<p><b>Khách hàng:</b> <?= htmlspecialchars($order['full_name']) ?></p>
<p><b>Điện thoại:</b> <?= htmlspecialchars($order['phone']) ?></p>
<p><b>Địa chỉ:</b> <?= htmlspecialchars($order['address']) ?></p>
<p><b>Ngày in:</b> <?= date('d/m/Y H:i') ?></p>

<table>
    <thead>
        <tr>
            <th>STT</th>
            <th>Sản phẩm</th>
            <th>Phiên bản / SKU</th>
            <th>Số lượng</th>
            <th>Đơn giá</th>
            <th>Thành tiền</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $i = 1;
        $total = 0;
        foreach ($items as $item):
            $lineTotal = $item['quantity'] * $item['price'];
            $total += $lineTotal;
        ?>
        <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($item['name']) ?></td>
            <td><?= htmlspecialchars($item['variant_name']) ?> | <?= htmlspecialchars($item['sku']) ?></td>
            <td><?= $item['quantity'] ?></td>
            <td><?= number_format($item['price'], 0, ',', '.') ?>đ</td>
            <td><?= number_format($lineTotal, 0, ',', '.') ?>đ</td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="total">
    TỔNG THANH TOÁN: <?= number_format($total, 0, ',', '.') ?>đ
</div>

<div class="sign">
    <div>
        <b>Người xuất kho</b><br><br>
        (Ký, ghi rõ họ tên)
    </div>
    <div>
        <b>Người giao hàng</b><br><br>
        (Ký, ghi rõ họ tên)
    </div>
    <div>
        <b>Khách hàng</b><br><br>
        (Ký, ghi rõ họ tên)
    </div>
</div>

<button class="btn-print" onclick="window.print()">🖨 In phiếu</button>

</body>
</html>
