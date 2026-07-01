<?php
class ProductController {
    public function detail() {
        $rootPath = dirname(__DIR__, 2); 
        
        try {
            if (file_exists($rootPath . '/config/db.php')) require_once $rootPath . '/config/db.php';
            if (file_exists($rootPath . '/core/DBO.php')) require_once $rootPath . '/core/DBO.php';
            if (file_exists($rootPath . '/core/ProductManager.php')) require_once $rootPath . '/core/ProductManager.php';
        } catch (Exception $e) {
            die("Lỗi hệ thống: " . $e->getMessage());
        }

        if (!function_exists('getDetailImgUrl')) {
            function getDetailImgUrl($path) {
                if (empty($path)) return 'assets/img/no-image.png';
                if (strpos($path, 'src/') === 0 || strpos($path, 'uploads/') === 0) {
                    return '../' . $path;
                }
                return $path;
            }
        }

        $productId    = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $reqVariantId = isset($_GET['variant_id']) ? (int)$_GET['variant_id'] : 0;

        $product         = null;
        $selectedVariant = null;

        if (isset($pdo) && $productId > 0) {
            $db = new DBO($pdo);
            $manager = new ProductManager($db);
            $product = $manager->getProductDetail($productId);
        }

        if (!$product) {
            die("Sản phẩm không tồn tại hoặc đã bị xóa.");
        }

        $selectedVariant = $product['variants'][0] ?? null;

        if ($reqVariantId > 0 && !empty($product['variants'])) {
            foreach ($product['variants'] as $v) {
                if ($v['id'] == $reqVariantId) {
                    $selectedVariant = $v;
                    break;
                }
            }
        }

        $displayPrice = $selectedVariant ? $selectedVariant['selling_price'] : 0;
        $displayStock = $selectedVariant ? $selectedVariant['stock'] : 0;
        $brandName    = htmlspecialchars($product['brand_name'] ?? 'TechStore');
        $warranty     = $product['warranty_months'] ?? 12;

        $displayName = htmlspecialchars($product['name']);
        if ($selectedVariant && !empty($selectedVariant['variant_name'])) {
            $displayName .= ' - ' . htmlspecialchars($selectedVariant['variant_name']);
        }

        $mainImgPath = $product['media'][0]['file_path'] ?? '';

        if ($selectedVariant) {
            foreach($product['media'] as $m) {
                if (isset($m['variant_id']) && $m['variant_id'] == $selectedVariant['id']) {
                    $mainImgPath = $m['file_path'];
                    break;
                }
            }
        }
        $displayImage = getDetailImgUrl($mainImgPath);

$viewPath = $rootPath . '/public/views/detail.php';        
        if (file_exists($viewPath)) {
            require_once $viewPath;
        } else {
            die("Không tìm thấy tệp giao diện chi tiết sản phẩm tại: " . $viewPath);
        }
    }
}