<?php

class ProductManager
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }
    public function getAllProducts($limit = 20)
    {

        $limit = intval($limit);

        $sql = "
            SELECT 
                p.id, 
                p.name, 
                b.name as brand_name,
                -- Lấy ảnh chính
                (SELECT file_path FROM product_media WHERE product_id = p.id AND is_main = 1 LIMIT 1) as image,
                -- Lấy giá thấp nhất
                (SELECT MIN(selling_price) FROM product_variants WHERE product_id = p.id AND is_active = 1) as price,
                -- Lấy giá cũ cao nhất
                (SELECT MAX(compare_at_price) FROM product_variants WHERE product_id = p.id AND is_active = 1) as old_price
            FROM products p
            LEFT JOIN brands b ON p.brand_id = b.id
            WHERE p.is_active = 1
            ORDER BY p.created_at DESC
            LIMIT $limit"; // Nối thẳng biến limit vào

        return $this->db->fetchAll($sql);
    }


    public function getProductDetail($id)
    {

        $id = intval($id);


        $sqlProduct = "SELECT p.*, b.name as brand_name 
                       FROM products p 
                       LEFT JOIN brands b ON p.brand_id = b.id 
                       WHERE p.id = $id AND p.is_active = 1";


        $product = $this->db->fetch($sqlProduct);

        if (!$product) return null;


        $sqlVariants = "SELECT v.*, 
                        (SELECT COALESCE(SUM(remaining_quantity), 0) 
                         FROM inventory_batches WHERE variant_id = v.id) as stock
                        FROM product_variants v 
                        WHERE v.product_id = $id AND v.is_active = 1
                        ORDER BY v.selling_price ASC";


        $product['variants'] = $this->db->fetchAll($sqlVariants);


        $sqlMedia = "SELECT * FROM product_media WHERE product_id = $id ORDER BY is_main DESC, id ASC";
        $product['media'] = $this->db->fetchAll($sqlMedia);


        $sqlAttrs = "SELECT DISTINCT a.name as attr_name, pav.attr_value 
                     FROM product_attribute_values pav
                     JOIN attributes a ON pav.attribute_id = a.id
                     JOIN product_variants pv ON pav.variant_id = pv.id
                     WHERE pv.product_id = $id
                     ORDER BY a.id ASC";
        $product['attributes'] = $this->db->fetchAll($sqlAttrs);

        return $product;
    }


    public function getAllVariants($limit = 20)
    {
        $limit = intval($limit);

        $sql = "
            SELECT 
                v.id as variant_id,
                v.variant_name,
                v.selling_price as price,
                v.compare_at_price as old_price,
                v.sku,
                p.id as product_id,
                p.name as product_name,
                b.name as brand_name,
                (SELECT file_path FROM product_media WHERE product_id = p.id AND is_main = 1 LIMIT 1) as image
            FROM product_variants v
            JOIN products p ON v.product_id = p.id
            LEFT JOIN brands b ON p.brand_id = b.id
            WHERE v.is_active = 1 AND p.is_active = 1
            ORDER BY p.created_at DESC, v.id ASC
            LIMIT $limit";

        return $this->db->fetchAll($sql);
    }
}

?>