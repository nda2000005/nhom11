<?php
// models/CustomerManager.php
class CustomerManager {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAllCustomers($keyword = '') {
        $sql = "SELECT c.*, COUNT(o.id) AS success_orders, COALESCE(SUM(o.total_amount),0) AS total_spent, MAX(o.created_at) AS last_order_date
                FROM customers c
                LEFT JOIN orders o ON c.id = o.customer_id AND o.current_status = 'COMPLETED'";
        $params = [];
        if (!empty($keyword)) {
            $sql .= " WHERE (c.full_name LIKE ? OR c.phone LIKE ? OR c.email LIKE ?) ";
            $term = "%$keyword%";
            $params = [$term, $term, $term];
        }
        $sql .= " GROUP BY c.id ORDER BY c.created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($customers as &$c) {
            $c['total_spent_formatted'] = number_format($c['total_spent'], 0, ',', '.') . 'đ';
            $c['last_order_date'] = $c['last_order_date'] ? date('d/m/Y', strtotime($c['last_order_date'])) : '-';
        }
        return $customers;
    }

    public function getCustomerById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM customers WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllOrdersByCustomer($customerId) {
        $stmt = $this->pdo->prepare("SELECT id, order_code, total_amount, current_status, created_at FROM orders WHERE customer_id = ? ORDER BY created_at DESC");
        $stmt->execute([$customerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}