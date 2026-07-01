<?php
class ApiService {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    public function getDashboardData() {
        try {
            
            $stmt1 = $this->conn->query("SELECT SUM(total_amount) AS total FROM orders WHERE current_status = 'COMPLETED'");
            $totalRevenue = $stmt1->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

            $stmt2 = $this->conn->query("SELECT SUM(total_amount) AS total FROM orders WHERE current_status = 'PENDING'");
            $pendingRevenue = $stmt2->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

            $stmt3 = $this->conn->query("SELECT COUNT(id) AS total FROM orders WHERE current_status = 'PENDING'");
            $pendingOrdersCount = $stmt3->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
 
            $sqlLowStock = "SELECT p.name, SUM(ib.remaining_quantity) as total_qty 
                            FROM products p
                            JOIN product_variants pv ON p.id = pv.product_id
                            JOIN inventory_batches ib ON pv.id = ib.variant_id
                            GROUP BY p.id
                            HAVING total_qty <= 5
                            ORDER BY total_qty ASC 
                            LIMIT 4";
            $stmt4 = $this->conn->query($sqlLowStock);
            $lowStockList = $stmt4->fetchAll(PDO::FETCH_ASSOC);

            
            $sqlOrders = "SELECT o.id, o.order_code, c.full_name, o.total_amount, o.current_status 
                          FROM orders o
                          LEFT JOIN customers c ON o.customer_id = c.id
                          ORDER BY o.id DESC LIMIT 5";
            $recentOrders = $this->conn->query($sqlOrders)->fetchAll(PDO::FETCH_ASSOC);

            return [
                'totalRevenue' => $totalRevenue,
                'pendingRevenue' => $pendingRevenue,
                'pendingOrdersCount' => $pendingOrdersCount,
                'lowStockList' => $lowStockList,
                'recentOrders' => $recentOrders
            ];

        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }
}