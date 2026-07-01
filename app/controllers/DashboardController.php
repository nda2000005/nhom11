<?php
// File: C:/xampp/htdocs/mnopl/app/controllers/DashboardController.php

require_once 'C:/xampp/htdocs/mnopl/app/models/DashboardModel.php';

class DashboardController {
    private $model;

    public function __construct($dbConnection) {
        // Nếu biến truyền vào bị rỗng, dừng hệ thống và báo ngay lập tức
        if (!$dbConnection) {
            die("<h1 style='color:red;padding:20px;font-family:sans-serif;'>Lỗi: Biến kết nối Database truyền vào Controller bị NULL. Hãy kiểm tra lại file admin/dashboard/index.php xem đã truyền đúng biến \$conn hoặc \$pdo chưa!</h1>");
        }
        
        // Cấu hình ép PDO phải lộ lỗi SQL ra ngoài nếu có
        if ($dbConnection instanceof PDO) {
            $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        $this->model = new DashboardModel($dbConnection);
    }

    public function index() {
        // Chạy trực tiếp không bọc try-catch ẩn lỗi để ép dữ liệu phải lên hoặc hiện lỗi thật
        $totalRevenue       = $this->model->getTotalRevenue();
        $pendingRevenue     = $this->model->getPendingRevenue();
        $inventoryValue     = $this->model->getInventoryValue();
        $pendingOrdersCount = $this->model->getPendingOrdersCount();
        $recentOrders       = $this->model->getRecentOrders(5);
        $low_stock_list     = $this->model->getLowStockProducts(5, 4); 

        // Helper hiển thị trạng thái đơn hàng dạng badge màu sắc
        if (!function_exists('getStatusBadge')) {
            function getStatusBadge($status) {
                $base = "px-2.5 py-0.5 text-[10px] font-bold rounded uppercase tracking-tight border ";
                switch ($status) {
                    case 'PENDING': return "<span class='{$base} bg-orange-50 text-orange-500 border-orange-100'>CHỜ XỬ LÝ</span>";
                    case 'SHIPPING': return "<span class='{$base} bg-blue-50 text-blue-500 border-blue-100'>ĐANG GIAO</span>";
                    case 'COMPLETED': return "<span class='{$base} bg-emerald-50 text-emerald-600 border-emerald-100'>HOÀN THÀNH</span>";
                    case 'CANCELLED': return "<span class='{$base} bg-red-50 text-red-600 border-red-100'>ĐÃ HỦY</span>";
                    default: return "<span class='{$base} bg-gray-50 text-gray-500 border-gray-100'>$status</span>";
                }
            }
        }

        // Gọi file View để hiển thị giao diện HTML
        require_once 'C:/xampp/htdocs/mnopl/app/views/dashboard.php';
    }
}