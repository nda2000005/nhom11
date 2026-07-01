<?php

$filename = "mau_nhap_san_pham.csv";


header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);


$output = fopen('php://output', 'w');


fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));


fputcsv($output, array(
    'Tên sản phẩm (Bắt buộc)', 
    'Mã SKU (Duy nhất)', 
    'Thương hiệu', 
    'Danh mục (ID)', 
    'Giá bán', 
    'Giá niêm yết (Giá cũ)', 
    'Mô tả ngắn'
));


fputcsv($output, array('iPhone 15 Pro Max', 'IP15PM-256', 'Apple', '1', '30000000', '32000000', 'Hàng chính hãng VN/A'));
fputcsv($output, array('Samsung S24 Ultra', 'S24U-512', 'Samsung', '1', '28000000', '30000000', 'AI Phone đỉnh cao'));

fclose($output);
exit;
?>