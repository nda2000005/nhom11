<?php
class OrderController {
    private $manager;

    public function __construct($manager) {
        $this->manager = $manager;
    }

    // API lấy danh sách
    public function index() {
        $data = $this->manager->getAllOrders();
        $this->jsonResponse(['status' => 'success', 'data' => $data]);
    }

    // API lấy chi tiết đơn hàng
    public function detail($id) {
        if ($id <= 0) return $this->jsonResponse(['status' => 'error', 'message' => 'Thiếu ID']);
        
        $order = $this->manager->getOrderDetail($id);
        if (!$order) return $this->jsonResponse(['status' => 'error', 'message' => 'Không tìm thấy đơn hàng']);

        $items = $this->manager->getOrderItems($id);

        // Format tiền
        $order['total_amount_formatted'] = number_format((float)$order['total_amount'], 0, ',', '.') . 'đ';

        $this->jsonResponse([
            'status' => 'success',
            'data' => [
                'order' => $order,
                'items' => $items
            ]
        ]);
    }

    private function jsonResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}