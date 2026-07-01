<?php
class SupplierModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }
    public function getStats(): array
    {
        return $this->pdo->query("
            SELECT
                COUNT(*)                                              AS total,
                SUM(CASE WHEN status = 'ACTIVE'   THEN 1 ELSE 0 END) AS active,
                SUM(CASE WHEN status = 'INACTIVE' THEN 1 ELSE 0 END) AS inactive
            FROM suppliers
        ")->fetch(PDO::FETCH_ASSOC);
    }
    public function getAll(): array
    {
        return $this->pdo->query("
            SELECT s.*,
                   (SELECT COUNT(*) FROM inventory_batches ib WHERE ib.supplier_id = s.id) AS batch_count
            FROM suppliers s
            ORDER BY s.id DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM suppliers WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO suppliers
                (name, tax_code, contact_name, phone, email, address, website, status, note, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $data['name'],
            $data['tax_code'],
            $data['contact_name'],
            $data['phone'],
            $data['email'],
            $data['address'],
            $data['website'],
            $data['status'],
            $data['note'],
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE suppliers
            SET name         = ?,
                tax_code     = ?,
                contact_name = ?,
                phone        = ?,
                email        = ?,
                address      = ?,
                website      = ?,
                status       = ?,
                note         = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $data['name'],
            $data['tax_code'],
            $data['contact_name'],
            $data['phone'],
            $data['email'],
            $data['address'],
            $data['website'],
            $data['status'],
            $data['note'],
            $id,
        ]);
    }
    public function hasInventoryHistory(int $id): bool
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM inventory_batches WHERE supplier_id = ?");
        $stmt->execute([$id]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM suppliers WHERE id = ?");
        $stmt->execute([$id]);
    }
}