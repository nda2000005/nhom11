<?php
require_once __DIR__ . '/../models/InventoryModel.php';
class InventoryController
{
    private InventoryModel $model;

    public function __construct(InventoryModel $model)
    {
        $this->model = $model;
    }
    public function handleEditBatch(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['edit_batch'])) return;

        $this->model->updateBatch(
            intval($_POST['batch_id']),
            intval($_POST['warehouse_id']),
            intval($_POST['supplier_id']),
            floatval(str_replace('.', '', $_POST['cost_price'])),
            intval($_POST['quantity']),
            trim($_POST['invoice_no']),
            trim($_POST['note'])
        );

        header('Location: index.php');
        exit;
    }
    public function handleDeleteBatch(): void
    {
        if (!isset($_GET['delete_batch'])) return;

        $this->model->softDeleteBatch(intval($_GET['delete_batch']));
        header('Location: index.php');
        exit;
    }
    public function getIndexData(): array
    {
        return [
            'total_value'       => $this->model->getTotalValue(),
            'total_quantity'    => $this->model->getTotalQuantity(),
            'low_stock'         => $this->model->getLowStockCount(),
            'inventory_summary' => $this->model->getInventorySummary(),
            'batch_history'     => $this->model->getBatchHistory(10),
            'serial_counts'     => $this->model->getSerialCounts(),
            'suppliers'         => $this->model->getAllSuppliers(),
            'warehouses'        => $this->model->getAllWarehouses(),
        ];
    }
    public function handleAddBatch(): array
    {
        $input = $this->resolveInput();

        $variantId   = intval($input['variant_id']   ?? 0);
        $warehouseId = intval($input['warehouse_id'] ?? 0);
        $supplierId  = intval($input['supplier_id']  ?? 0);
        $qty         = intval($input['quantity']     ?? 0);
        $cost        = floatval(str_replace(['.', ','], '', $input['cost_price'] ?? '0'));
        $invoiceNo   = trim($input['invoice_no'] ?? '');
        $note        = trim($input['note']       ?? '');
        $serials     = $input['serials']         ?? '';

        // Validation
        if ($qty <= 0)   return ['success' => false, 'message' => 'Số lượng phải lớn hơn 0'];
        if ($cost <= 0)  return ['success' => false, 'message' => 'Vui lòng nhập đơn giá nhập'];

        try {
            $this->model->beginTransaction();

            $batchId = $this->model->createBatch(
                $variantId, $warehouseId, $supplierId,
                $qty, $cost, $invoiceNo, $note
            );

            if (!empty($serials)) {
                $lines = is_array($serials)
                    ? $serials
                    : array_filter(array_map('trim', explode("\n", $serials)));
                $this->model->saveSerials($variantId, $batchId, $lines);
            }

            $this->model->commit();
            return ['success' => true, 'message' => 'Nhập kho thành công!'];

        } catch (Exception $e) {
            $this->model->rollBack();
            return ['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()];
        }
    }
    public function getAddFormData(): array
    {
        return [
            'suppliers'  => $this->model->getAllSuppliers(),
            'warehouses' => $this->model->getAllWarehouses(),
            'variants'   => $this->model->getActiveVariants(),
        ];
    }
    public function getQuote(int $variantId, int $supplierId): array
    {
        $row = $this->model->getQuote($variantId, $supplierId);
        if ($row) {
            return ['success' => true, 'price' => $row['quoted_price'], 'note' => $row['note']];
        }
        return ['success' => false, 'price' => 0, 'note' => ''];
    }
    public function downloadSampleCsv(): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="mau_nhap_kho.csv"');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['SKU (Ma san pham)', 'ID Kho', 'ID Nha Cung Cap', 'So Luong', 'Gia Nhap (Don gia)', 'So Hoa Don', 'Ghi Chu']);
        fputcsv($out, ['AT-001', '1', '1', '100', '150000', 'INV-2023-001', 'Nhập hàng đầu mùa']);
        fputcsv($out, ['QJ-002', '1', '2', '50',  '250000', 'INV-2023-002', 'Hàng khuyến mãi']);
        fclose($out);
        exit;
    }
    public function handleImportCsv(): array
    {
        if (empty($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== 0) {
            return ['success' => false, 'message' => 'Vui lòng chọn file cần nhập.', 'count' => 0];
        }

        $ext = strtolower(pathinfo($_FILES['csv_file']['name'], PATHINFO_EXTENSION));
        if ($ext !== 'csv') {
            return ['success' => false, 'message' => 'Vui lòng chỉ chọn file định dạng .CSV', 'count' => 0];
        }

        $handle = fopen($_FILES['csv_file']['tmp_name'], 'r');
        fgetcsv($handle); 

        try {
            $this->model->beginTransaction();
            $count = 0;

            while (($data = fgetcsv($handle)) !== false) {
                $sku         = trim($data[0] ?? '');
                $warehouseId = intval($data[1] ?? 0);
                $supplierId  = intval($data[2] ?? 0);
                $qty         = intval($data[3] ?? 0);
                $cost        = floatval(str_replace(['.', ','], '', $data[4] ?? '0'));
                $invoiceNo   = trim($data[5] ?? '');
                $note        = trim($data[6] ?? '');

                if (empty($sku) || $qty <= 0) continue;

                $variantId = $this->model->findVariantBySku($sku);
                if ($variantId === null) continue;

                $this->model->createBatch($variantId, $warehouseId, $supplierId, $qty, $cost, $invoiceNo, $note);
                $count++;
            }

            $this->model->commit();
            fclose($handle);
            return ['success' => true, 'message' => "Đã nhập thành công <strong>{$count}</strong> lô hàng vào kho!", 'count' => $count];

        } catch (Exception $e) {
            $this->model->rollBack();
            return ['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage(), 'count' => 0];
        }
    }
    public function getInsertFormData(): array
    {
        return [
            'warehouses' => $this->model->getAllWarehouses(),
            'suppliers'  => $this->model->getAllSuppliers(),
        ];
    }
    private function resolveInput(): array
    {
        $json = file_get_contents('php://input');
        if (!empty($json)) {
            $decoded = json_decode($json, true);
            if (is_array($decoded)) return $decoded;
        }
        return $_POST;
    }
    public static function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}