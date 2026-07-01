<?php
if (!isset($_GET['id'])) {
    die("Thiếu ID");
}

$id = (int)$_GET['id'];

// URL API (chỉnh lại domain nếu cần)
$apiUrl = "http://localhost/mnopl/api/products/delete.php";

// dữ liệu gửi đi
$data = json_encode(["id" => $id]);

// dùng cURL gọi API
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    die("Lỗi gọi API: " . curl_error($ch));
}

curl_close($ch);

$result = json_decode($response, true);

// xử lý kết quả
if ($result['status'] === 'success') {
    header("Location: index.php?message=deleted");
} else {
    echo "Lỗi: " . $result['message'];
}