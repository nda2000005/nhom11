<?php
class InventoryModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }
    public function getTotalValue(): float
    {
        return (float) $this->pdo
            ->query("SELECT COALESCE(SUM(remaining_quantity * cost_price), 0) FROM inventory_batches")
            ->fetchColumn();
    }

    public function getTotalQuantity(): int
    {
        return (int) $this->pdo
            ->query("SELECT COALESCE(SUM(remaining_quantity), 0) FROM inventory_batches")
            ->fetchColumn();
    }

    public function getLowStockCount(int $threshold = 5): int
    {
        return (int) $this->pdo->query("
            SELECT COUNT(*) FROM (
                SELECT variant_id
                FROM inventory_batches
                GROUP BY variant_id
                HAVING SUM(remaining_quantity) <= {$threshold}
            ) AS sub
        ")->fetchColumn();
    }
    public function getInventorySummary(): array
    {
        return $this->pdo->query("
            SELECT p.name, pv.variant_name, pv.sku,
                   SUM(ib.remaining_quantity)              AS stock,
                   SUM(ib.remaining_quantity * ib.cost_price) AS value
            FROM inventory_batches ib
            JOIN product_variants pv ON ib.variant_id = pv.id
            JOIN products         p  ON pv.product_id  = p.id
            WHERE ib.remaining_quantity > 0
            GROUP BY ib.variant_id
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBatchHistory(int $limit = 10): array
    {
        $stmt = $this->pdo->prepare("
            SELECT ib.*, p.name, pv.sku,
                   s.name AS supplier_name,
                   w.name AS warehouse_name
            FROM inventory_batches ib
            LEFT JOIN product_variants pv ON ib.variant_id   = pv.id
            LEFT JOIN products         p  ON pv.product_id   = p.id
            LEFT JOIN suppliers        s  ON ib.supplier_id  = s.id
            LEFT JOIN warehouses       w  ON ib.warehouse_id = w.id
            ORDER BY ib.created_at DESC
            LIMIT :lim
        ");
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSerialCounts(): array
    {
        $rows = $this->pdo
            ->query("SELECT batch_id, COUNT(*) AS cnt FROM serial_numbers GROUP BY batch_id")
            ->fetchAll(PDO::FETCH_ASSOC);

        $map = [];
        foreach ($rows as $row) {
            $map[$row['batch_id']] = (int) $row['cnt'];
        }
        return $map;
    }

    public function getAllWarehouses(): array
    {
        return $this->pdo->query("SELECT id, name FROM warehouses ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllSuppliers(): array
    {
        return $this->pdo->query("SELECT id, name FROM suppliers ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getActiveVariants(): array
    {
        return $this->pdo->query("
            SELECT pv.id,
                   CONCAT(p.name, ' - ', pv.variant_name) AS full_name,
                   pv.sku
            FROM product_variants pv
            JOIN products p ON pv.product_id = p.id
            WHERE pv.is_active = 1
            ORDER BY p.name
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getQuote(int $variantId, int $supplierId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT quoted_price, note
            FROM supplier_price_quotes
            WHERE variant_id = ? AND supplier_id = ?
            ORDER BY created_at DESC
            LIMIT 1
        ");
        $stmt->execute([$variantId, $supplierId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
    public function createBatch(
        int    $variantId,
        int    $warehouseId,
        int    $supplierId,
        int    $qty,
        float  $cost,
        string $invoiceNo,
        string $note
    ): int {
        $batchCode = 'GRN-' . date('ymd') . '-' . strtoupper(substr(uniqid(), -4));

        $stmt = $this->pdo->prepare("
            INSERT INTO inventory_batches
                (variant_id, warehouse_id, supplier_id, batch_code,
                 invoice_no, cost_price, import_quantity, remaining_quantity, note, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $variantId, $warehouseId, $supplierId, $batchCode,
            $invoiceNo, $cost, $qty, $qty, $note,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function saveSerials(int $variantId, int $batchId, array $serials): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO serial_numbers (variant_id, batch_id, serial_code, status)
            VALUES (?, ?, ?, 'AVAILABLE')
        ");
        foreach ($serials as $serial) {
            $serial = trim($serial);
            if ($serial !== '') {
                $stmt->execute([$variantId, $batchId, $serial]);
            }
        }
    }
    public function findVariantBySku(string $sku): ?int
    {
        $stmt = $this->pdo->prepare("SELECT id FROM product_variants WHERE sku = ?");
        $stmt->execute([$sku]);
        $id = $stmt->fetchColumn();
        return $id !== false ? (int) $id : null;
    }
    public function updateBatch(
        int    $batchId,
        int    $warehouseId,
        int    $supplierId,
        float  $cost,
        int    $qty,
        string $invoiceNo,
        string $note
    ): void {
        $stmt = $this->pdo->prepare("
            UPDATE inventory_batches
            SET warehouse_id      = ?,
                supplier_id       = ?,
                cost_price        = ?,
                import_quantity   = ?,
                remaining_quantity= ?,
                invoice_no        = ?,
                note              = ?
            WHERE id = ?
        ");
        $stmt->execute([$warehouseId, $supplierId, $cost, $qty, $qty, $invoiceNo, $note, $batchId]);
    }

    public function softDeleteBatch(int $batchId): void
    {
        $stmt = $this->pdo->prepare("UPDATE inventory_batches SET remaining_quantity = 0 WHERE id = ?");
        $stmt->execute([$batchId]);
    }
    public function beginTransaction(): void   { $this->pdo->beginTransaction(); }
    public function commit(): void             { $this->pdo->commit(); }
    public function rollBack(): void           { if ($this->pdo->inTransaction()) $this->pdo->rollBack(); }
}