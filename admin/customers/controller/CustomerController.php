<?php
// controllers/CustomerController.php
class CustomerController {
    private $manager;

    public function __construct($manager) {
        $this->manager = $manager;
    }

    public function index($search) {
        $data = $this->manager->getAllCustomers($search);
        $this->jsonResponse(['status' => 'success', 'data' => $data]);
    }

    public function detail($id) {
    if ($id <= 0) return $this->jsonResponse(['status' => 'error', 'message' => 'Thiếu ID']);
    
    $customer = $this->manager->getCustomerById($id);
    if (!$customer) return $this->jsonResponse(['status' => 'error', 'message' => 'Không tìm thấy khách hàng']);

    $orders = $this->manager->getAllOrdersByCustomer($id);
    
    // Tính toán số tiền
    $totalSpent = 0; 
    $successOrders = 0;
    foreach ($orders as $o) {
        // Chỉ cộng vào nếu đơn hàng ở trạng thái hoàn thành
        if (isset($o['current_status']) && $o['current_status'] === 'COMPLETED') {
            $totalSpent += (float)$o['total_amount']; 
            $successOrders++;
        }
    }

    // BỔ SUNG: Định dạng số tiền có dấu chấm và chữ "đ"
    $totalSpentFormatted = number_format($totalSpent, 0, ',', '.') . 'đ';

    $this->jsonResponse([
        'status' => 'success',
        'data' => [
            'customer' => $customer,
            'orders' => $orders,
            'summary' => [
                'total_spent_formatted' => $totalSpentFormatted, // <-- Đây là cái quan trọng nhất để hiển thị
                'success_orders' => $successOrders, 
                'total_orders' => count($orders)
            ]
        ]
    ]);
}
      

    private function jsonResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}