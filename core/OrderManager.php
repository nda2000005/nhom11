<?php

class OrderManager
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function createOrder($userId, $data, $cart)
    {

        $this->checkStock($cart);


        $customerId = $this->handleCustomer($data);


        $orderCode = 'ORD-' . strtoupper(uniqid());
        $totalAmount = 0;
        foreach ($cart as $item) $totalAmount += ($item['price'] * $item['qty']);

        $this->db->insert('orders', [
            'order_code' => $orderCode,
            'customer_id' => $customerId,
            'total_amount' => $totalAmount,
            'shipping_fee' => 0,
            'current_status' => 'PENDING',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        $orderId = $this->db->getLastId();


        $this->db->insert('payments', [
            'order_id' => $orderId,
            'payment_method' => $data['payment_method'],
            'amount' => $totalAmount,
            'status' => 'PENDING'
        ]);


        foreach ($cart as $item) {
            $this->processItems($orderId, $item);
        }

        return $orderCode;
    }


    private function checkStock($cart)
    {
        foreach ($cart as $item) {
            $sql = "SELECT SUM(remaining_quantity) as total FROM inventory_batches WHERE variant_id = " . $item['id'];
            $res = $this->db->fetch($sql);

            if (!$res || $res['total'] < $item['qty']) {
                throw new \core\Exception("Sản phẩm '{$item['name']}' không đủ hàng (Còn: " . (int)$res['total'] . ")");
            }
        }
    }

    private function handleCustomer($data)
    {

        $sql = "SELECT id FROM customers WHERE phone = '" . $data['phone'] . "'";
        $row = $this->db->fetch($sql);

        if ($row) return $row['id'];

        // Chưa có thì tạo mới
        $this->db->insert('customers', [
            'full_name' => $data['fullname'],
            'phone' => $data['phone'],
            'email' => $data['email'],
            'address' => $data['address'],
            'created_at' => date('Y-m-d H:i:s')
        ]);
        return $this->db->getLastId();
    }

    private function processItems($orderId, $item)
    {
        $qtyNeeded = $item['qty'];


        $sql = "SELECT * FROM inventory_batches WHERE variant_id = " . $item['id'] . " AND remaining_quantity > 0 ORDER BY created_at ASC";
        $batches = $this->db->fetchAll($sql);

        foreach ($batches as $batch) {
            if ($qtyNeeded <= 0) break;

            $deduct = min($qtyNeeded, $batch['remaining_quantity']);


            $this->db->update('inventory_batches',
                ['remaining_quantity' => ($batch['remaining_quantity'] - $deduct)],
                "id = " . $batch['id']
            );


            $this->db->insert('order_items', [
                'order_id' => $orderId,
                'variant_id' => $item['id'],
                'quantity' => $deduct,
                'price' => $item['price'],
                'cost_price' => $batch['cost_price']
            ]);

            $qtyNeeded -= $deduct;
        }
    }
}

?>