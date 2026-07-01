<?php
if (!function_exists('getCheckoutImgUrl')) {
    function getCheckoutImgUrl($path) {
        if (empty($path)) return 'assets/img/no-image.png';
        if (strpos($path, 'src/') === 0 || strpos($path, 'uploads/') === 0) {
            return '../' . $path;
        }
        return $path;
    }
}

if (!function_exists('formatMoney')) {
    function formatMoney($amount) {
        return number_format((float)$amount, 0, ',', '.') . 'đ';
    }
}

$rootPathAdmin = dirname(__DIR__, 2);
require_once $rootPathAdmin . '/config/db.php';

class OrderController {
    public function checkout() {
        global $pdo;

        $rootPath = dirname(__DIR__, 2);

        try {
            require_once $rootPath . '/core/DBO.php';
            require_once $rootPath . '/core/OrderManager.php';
        } catch (Exception $e) {
            die("Lỗi hệ thống nạp lõi: " . $e->getMessage());
        }

        $errorMsg     = "";
        $successMsg   = "";
        $orderSuccess = false;
        $grandTotal   = 0;
        $summaryItems = [];

        $userId = (int)($_SESSION['user_id'] ?? 0);
        
        if (!isset($pdo)) {
            die("Lỗi: Không thể kết nối cơ sở dữ liệu (biến \$pdo chưa được nạp).");
        }

        $db = new DBO($pdo);

        $sqlCart = "SELECT c.qty, v.selling_price as price, v.variant_name, p.name as product_name, 
                           m.file_path as image, c.variant_id, p.id as product_id
                    FROM user_cart c
                    JOIN product_variants v ON c.variant_id = v.id
                    JOIN products p ON v.product_id = p.id
                    LEFT JOIN (
                        SELECT variant_id, MIN(file_path) as file_path 
                        FROM product_media 
                        GROUP BY variant_id
                    ) m ON v.id = m.variant_id";
                    
        $cartFromDb = $db->fetchAll($sqlCart);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn_order'])) {
            if (empty($cartFromDb)) {
                $errorMsg = "Giỏ hàng trống, không thể đặt hàng.";
            } else {
                $customerData = [
                    'fullname'       => trim($_POST['fullname'] ?? ''),
                    'email'          => trim($_POST['email'] ?? ''),
                    'phone'          => trim($_POST['phone'] ?? ''),
                    'address'        => trim($_POST['address'] ?? ''),
                    'note'           => trim($_POST['note'] ?? ''),
                    'payment_method' => $_POST['payment_method'] ?? 'COD'
                ];

                try {
                    $orderManager = new OrderManager($db);
                    
                    $cartForOrder = [];
                    foreach ($cartFromDb as $item) {
                        $cartForOrder[] = [
                            'id'    => $item['variant_id'],
                            'qty'   => (int)$item['qty'],
                            'price' => (float)$item['price'],
                            'name'  => $item['product_name'] . (!empty($item['variant_name']) ? ' - ' . $item['variant_name'] : '')
                        ];
                    }
                    
                    $orderCode = $orderManager->createOrder($userId, $customerData, $cartForOrder);
                    
                    $db->delete("user_cart", "1=1");

                    $orderSuccess = true;
                    $successMsg   = "Đơn hàng được tạo thành công! Mã đơn hàng: #$orderCode";
                } catch (Exception $e) {
                    $errorMsg = "Lỗi đặt hàng: " . $e->getMessage();
                } catch (\Throwable $e) {
                    $errorMsg = "Lỗi hệ thống đặt hàng: " . $e->getMessage();
                }
            }
        }

        //  hiển thị ra giao diện thanh toán
        if (!empty($cartFromDb)) {
            foreach ($cartFromDb as $item) {
                $price      = (float)($item['price'] ?? 0);
                $qty        = (int)($item['qty'] ?? 0);
                $itemTotal  = $price * $qty;
                $grandTotal += $itemTotal;

                $summaryItems[] = [
                    'name'        => htmlspecialchars($item['product_name'] . ($item['variant_name'] ? ' - ' . $item['variant_name'] : '')),
                    'image'       => getCheckoutImgUrl($item['image'] ?? ''),
                    'qty'         => $qty,
                    'price_unit'  => formatMoney($price),
                    'price_total' => formatMoney($itemTotal)
                ];
            }
        }
        $grandTotalFmt = formatMoney($grandTotal);

        $viewPath = $rootPath . '/public/views/checkout.php';
        if (file_exists($viewPath)) {
            require_once $viewPath;
        } else {
            die("Không tìm thấy tệp giao diện checkout tại: " . $viewPath);
        }
    }
}