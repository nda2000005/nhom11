<?php
require_once __DIR__ . '/../models/SupplierModel.php';
class SupplierController
{
    private SupplierModel $model;

    public function __construct(SupplierModel $model)
    {
        $this->model = $model;
    }
    public function handleCreate(): array
    {
        try {
            $this->model->create($this->collectPostData());
            return ['success' => true, 'redirect' => 'suppliers.php?msg=added'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Lỗi thêm mới: ' . $e->getMessage()];
        }
    }
    public function handleUpdate(): array
    {
        try {
            $this->model->update(intval($_POST['id']), $this->collectPostData());
            return ['success' => true, 'redirect' => 'suppliers.php?msg=updated'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Lỗi cập nhật: ' . $e->getMessage()];
        }
    }
    public function handleDelete(int $id): array
    {
        try {
            if ($this->model->hasInventoryHistory($id)) {
                return ['success' => false, 'message' => 'Không thể xóa nhà cung cấp này vì đã có lịch sử nhập hàng.'];
            }
            $this->model->delete($id);
            return ['success' => true, 'redirect' => 'suppliers.php?msg=deleted'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Lỗi xóa dữ liệu: ' . $e->getMessage()];
        }
    }

    public function getListData(): array
    {
        $message = '';
        if (isset($_GET['msg'])) {
            $msgs = [
                'added'   => 'Thêm nhà cung cấp thành công!',
                'updated' => 'Cập nhật thông tin thành công!',
                'deleted' => 'Đã xóa nhà cung cấp.',
            ];
            $message = $msgs[$_GET['msg']] ?? '';
        }

        return [
            'stats'     => $this->model->getStats(),
            'suppliers' => $this->model->getAll(),
            'message'   => $message,
        ];
    }

    private function collectPostData(): array
    {
        return [
            'name'         => trim($_POST['name']         ?? ''),
            'tax_code'     => trim($_POST['tax_code']     ?? ''),
            'contact_name' => trim($_POST['contact_name'] ?? ''),
            'phone'        => trim($_POST['phone']        ?? ''),
            'email'        => trim($_POST['email']        ?? ''),
            'address'      => trim($_POST['address']      ?? ''),
            'website'      => trim($_POST['website']      ?? ''),
            'status'       => $_POST['status']            ?? 'ACTIVE',
            'note'         => trim($_POST['note']         ?? ''),
        ];
    }
}