<?php
class HomeController {
    public function index() {
        $rootPath = dirname(__DIR__, 2); 
        
        try {
            if (file_exists($rootPath . '/config/db.php')) require_once $rootPath . '/config/db.php';
            if (file_exists($rootPath . '/core/DBO.php')) require_once $rootPath . '/core/DBO.php';
            if (file_exists($rootPath . '/core/ProductManager.php')) require_once $rootPath . '/core/ProductManager.php';
        } catch (Exception $e) {
            die("Lỗi hệ thống: " . $e->getMessage());
        }

        $products = [];
        $errorMessage = "";
        
        if (isset($pdo)) {
            try {
                $db = new DBO($pdo);
                $pm = new ProductManager($db);
                $products = $pm->getAllVariants(20);
            } catch (Exception $e) {
                $errorMessage = "Lỗi lấy dữ liệu: " . $e->getMessage();
            }
        }

        $controller = $this;

        require_once $rootPath . '/public/views/home.php'; 
    }

    public function getProductImageUrl($path) {
        if (empty($path)) return 'assets/img/no-image.png';
        if (strpos($path, 'src/') === 0 || strpos($path, 'uploads/') === 0) {
            return '../' . $path;
        }
        return $path;
    }

    public function calculateDiscount($price, $oldPrice) {
        if ($oldPrice > 0 && $oldPrice > $price) {
            return round((($oldPrice - $price) / $oldPrice) * 100);
        }
        return 0;
    }
}