<?php
class OrderModel {
    private $db;
    public function __construct($db) { $this->db = $db; }
    
    // Dùng cho View index & API index
    public function getAllOrders() { 
        $sql = "SELECT o.*, c.full_name FROM orders o LEFT JOIN customers c ON o.customer_id = c.id ORDER BY o.created_at DESC";
        return $this->db->query($sql)->fetchAll(); 
    }
    
    // Dùng cho View detail/print & API detail
    public function getOrderDetail($id) { 
        $sql = "SELECT o.*, c.full_name, c.phone, c.address FROM orders o LEFT JOIN customers c ON o.customer_id = c.id WHERE o.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function getOrderItems($id) { 
        $sql = "SELECT oi.*, p.name, v.variant_name, v.sku FROM order_items oi JOIN product_variants v ON oi.variant_id = v.id JOIN products p ON v.product_id = p.id WHERE oi.order_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }
    
    public function updateStatus($id, $action) {
        $status = ($action === 'ship') ? 'SHIPPING' : 'CANCELLED';
        $sql = "UPDATE orders SET current_status=? WHERE id=?";
        return $this->db->prepare($sql)->execute([$status, $id]);
    }
}
?>