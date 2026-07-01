<?php
class CartModel {
    private $db;
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->db = new DBO($pdo);
    }

    
    public function getCartItems() {
       
        $sql = "SELECT c.qty, v.selling_price as price, v.variant_name, p.name as product_name, 
                       p.id as product_id, m.file_path as image, c.variant_id
                FROM user_cart c
                JOIN product_variants v ON c.variant_id = v.id
                JOIN products p ON v.product_id = p.id
                LEFT JOIN (
                    SELECT variant_id, MIN(file_path) as file_path 
                    FROM product_media 
                    GROUP BY variant_id
                ) m ON v.id = m.variant_id";

        return $this->db->fetchAll($sql);
    }
}