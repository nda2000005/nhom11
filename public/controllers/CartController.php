<?php
$basePath = dirname(__DIR__, 2);
require_once $basePath . '/public/models/CartModel.php';

class CartController {
    public function viewCart() {
        $rootPath = dirname(__DIR__, 2);
        
        // Nạp kết nối CSDL và lõi DBO
        require_once $rootPath . '/config/db.php';
        require_once $rootPath . '/core/DBO.php';

        if (!function_exists('getCartImageUrl')) {
            function getCartImageUrl($path) {
                if (empty($path)) {
                    return 'assets/img/no-image.png';
                }
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

        $db = new DBO($pdo);
        $cartModel = new CartModel($pdo);
        $dbItems = $cartModel->getCartItems();
        
        $renderItems = [];
        $totalOrder  = 0;

        if (!empty($dbItems) && is_array($dbItems)) {
            foreach ($dbItems as $item) {
                $qty      = (int)$item['qty'];
                $price    = (float)$item['price'];
                $subtotal = $price * $qty;
                $totalOrder += $subtotal;
                $vId      = $item['variant_id'];

                $renderItems[] = [
                    'id'           => $vId, 
                    'name'         => htmlspecialchars($item['product_name'] . (!empty($item['variant_name']) ? ' - ' . $item['variant_name'] : '')),
                    'image'        => getCartImageUrl($item['image'] ?? ''),
                    'price_fmt'    => formatMoney($price),
                    'qty'          => $qty,
                    'subtotal_fmt' => formatMoney($subtotal),
                    'link_detail'  => "product-detail.php?id={$item['product_id']}&variant_id={$vId}",
                    'link_dec'     => "api/cart-action.php?action=update&variant_id={$vId}&qty=" . ($qty - 1),
                    'link_inc'     => "api/cart-action.php?action=update&variant_id={$vId}&qty=" . ($qty + 1),
                    'link_del'     => "api/cart-action.php?action=remove&variant_id={$vId}"
                ];
            }
        }
        $totalOrderFmt = formatMoney($totalOrder);

        // Nạp tệp giao diện (View)
        $viewPath = $rootPath . '/public/views/cart.php';
        
        if (file_exists($viewPath)) {
            require_once $viewPath;
        } else {
            die("Không tìm thấy tệp giao diện giỏ hàng tại: " . $viewPath);
        }
    }
}