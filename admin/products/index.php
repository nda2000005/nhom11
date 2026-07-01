<?php
// Bật hiển thị lỗi (Chỉ nên dùng trên môi trường Dev, tắt khi đưa lên Production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../../config/database.php';
require_once '../../core/Database.php';

// Fix 1: Thêm die() để dừng thực thi nếu lỗi DB, tránh gây lỗi dây chuyền ở phía dưới
try {
    $totalProducts = $pdo->query("SELECT COUNT(*) FROM products WHERE is_active = 1")->fetchColumn() ?: 0;
    $totalVariants = $pdo->query("SELECT COUNT(*) FROM product_variants WHERE is_active = 1")->fetchColumn() ?: 0;
    $totalStock = $pdo->query("SELECT SUM(remaining_quantity) FROM inventory_batches")->fetchColumn() ?: 0;
    $totalValue = $pdo->query("SELECT SUM(remaining_quantity * cost_price) FROM inventory_batches")->fetchColumn() ?: 0;
} catch (PDOException $e) {
    die("Lỗi kết nối hoặc truy vấn CSDL: " . $e->getMessage());
}

$db = new Database($pdo);

$search = $_GET['search'] ?? '';
$brand_filter = $_GET['brand_id'] ?? '';

$params = [];
$where_clauses = [];

if ($search) {
    $where_clauses[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($brand_filter) {
    $where_clauses[] = "p.brand_id = ?";
    $params[] = $brand_filter;
}

$where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

// Fix 2: Bổ sung b.name vào GROUP BY để tránh lỗi MySQL (ONLY_FULL_GROUP_BY)
$sql = "SELECT p.*, b.name as brand_name,
        COUNT(v.id) as variant_count,
        MIN(v.selling_price) as min_price,
        MAX(v.selling_price) as max_price,
        (SELECT file_path FROM product_media WHERE product_id = p.id AND is_main = 1 LIMIT 1) as image
        FROM products p
        LEFT JOIN brands b ON p.brand_id = b.id
        LEFT JOIN product_variants v ON p.id = v.product_id
        $where_sql
        GROUP BY p.id, b.name
        ORDER BY p.id DESC";

$products = $db->fetchAll($sql, $params);
$brands = $db->fetchAll("SELECT * FROM brands ORDER BY name");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý sản phẩm</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f8fafc; }
        .main-content {
            margin-left: 280px; 
            padding: 30px;
            min-height: 100vh;
            transition: margin-left 0.4s ease;
        }
        @media (max-width: 768px) {
            .main-content { margin-left: 0; padding: 15px; }
        }
    </style>
</head>
<body>

    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        
        <div class="flex flex-col md:flex-row justify-between items-end mb-6 gap-4">
            <div>
                <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">Danh sách Sản phẩm</h2>
                <div class="flex gap-4 mt-2 text-sm text-slate-500">
                    <span><i class="fas fa-box"></i> <?= $totalProducts ?> SP</span>
                    <span><i class="fas fa-layer-group"></i> <?= $totalVariants ?> Biến thể</span>
                    <span><i class="fas fa-warehouse"></i> Tồn: <?= number_format($totalStock) ?></span>
                </div>
            </div>
            
            <div class="flex gap-2">
                <a href="import_csv.php" class="bg-white text-emerald-600 border border-emerald-200 px-4 py-2 rounded-xl font-bold hover:bg-emerald-50 transition shadow-sm flex items-center decoration-0">
                    <i class="fas fa-file-csv mr-2"></i> Nhập Excel
                </a>
                <a href="add.php" class="bg-blue-600 text-white px-5 py-2 rounded-xl font-bold hover:bg-blue-700 shadow-lg shadow-blue-200 transition transform hover:-translate-y-0.5 flex items-center decoration-0">
                    <i class="fas fa-plus mr-2"></i> Thêm mới
                </a>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 mb-6">
            <form method="GET" class="flex flex-col md:flex-row gap-4">
                <div class="flex-1 relative">
                    <i class="fas fa-search absolute left-4 top-3.5 text-slate-400"></i>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                        class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition" 
                        placeholder="Tìm kiếm tên sản phẩm, mô tả...">
                </div>
                
                <div class="w-full md:w-48">
                    <select name="brand_id" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:border-blue-500 bg-white">
                        <option value="">-- Thương hiệu --</option>
                        <?php foreach($brands as $brand): ?>
                            <option value="<?= $brand['id'] ?>" <?= $brand_filter == $brand['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($brand['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="bg-slate-800 text-white px-6 py-3 rounded-xl font-bold hover:bg-slate-700 transition">
                    Lọc
                </button>
                
                <?php if($search || $brand_filter): ?>
                    <a href="index.php" class="bg-gray-100 text-gray-600 px-4 py-3 rounded-xl font-bold hover:bg-gray-200 transition flex items-center justify-center">
                        <i class="fas fa-times"></i>
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-bold border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4 w-20">Hình ảnh</th>
                            <th class="px-6 py-4">Thông tin sản phẩm</th>
                            <th class="px-6 py-4 text-center">Phân khúc giá</th>
                            <th class="px-6 py-4 text-center">Trạng thái</th>
                            <th class="px-6 py-4 text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        <?php foreach($products as $p): ?>
                        <tr class="hover:bg-slate-50 transition duration-150 group">
                            <td class="px-6 py-4">
                                <div class="w-16 h-16 rounded-lg border border-slate-200 overflow-hidden bg-white p-1">
                                    <img class="w-full h-full object-contain"
                                        src="../../<?= htmlspecialchars($p['image'] ?? 'public/assets/imgs/no-image.png') ?>" 
                                        alt="Img">
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <a href="edit.php?id=<?= $p['id'] ?>" class="font-bold text-slate-800 text-base hover:text-blue-600 transition mb-1 decoration-0">
                                        <?= htmlspecialchars($p['name']) ?>
                                    </a>
                                    <div class="flex items-center gap-2">
                                        <span class="bg-slate-100 text-slate-600 text-xs px-2 py-0.5 rounded font-medium border border-slate-200">
                                            <?= htmlspecialchars($p['brand_name'] ?? 'No Brand') ?>
                                        </span>
                                        <span class="text-slate-400 text-xs">ID: #<?= $p['id'] ?></span>
                                    </div>
                                    
                                    <div class="mt-2">
                                        <span class="inline-flex items-center gap-1 text-xs text-indigo-600 font-medium bg-indigo-50 px-2 py-1 rounded-full border border-indigo-100">
                                            <i class="fas fa-layer-group"></i> <?= $p['variant_count'] ?> phiên bản
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-center">
                                <?php if ($p['variant_count'] > 0 && !is_null($p['min_price'])): ?>
                                    <div class="font-mono text-slate-700 font-bold">
                                        <?php if ($p['min_price'] == $p['max_price']): ?>
                                            <?= number_format($p['min_price'], 0, ',', '.') ?>đ
                                        <?php else: ?>
                                            <?= number_format($p['min_price'], 0, ',', '.') ?> - <?= number_format($p['max_price'], 0, ',', '.') ?>đ
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-slate-400 text-xs italic">Chưa có giá</span>
                                <?php endif; ?>
                            </td>

                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold 
                                    <?= $p['is_active'] ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' ?>">
                                    <span class="w-1.5 h-1.5 rounded-full mr-1.5 <?= $p['is_active'] ? 'bg-emerald-500' : 'bg-red-500' ?>"></span>
                                    <?= $p['is_active'] ? 'Đang bán' : 'Ngừng bán' ?>
                                </span>
                            </td>

                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2 opacity-100 md:opacity-60 group-hover:opacity-100 transition-opacity">
                                    <a href="add_variant.php?product_id=<?= $p['id'] ?>" 
                                    class="w-9 h-9 flex items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white border border-indigo-100 transition tooltip"
                                    title="Thêm biến thể">
                                    <i class="fas fa-plus-circle"></i>
                                    </a>

                                    <a href="edit.php?id=<?= $p['id'] ?>" 
                                    class="w-9 h-9 flex items-center justify-center rounded-lg bg-slate-50 text-slate-600 hover:bg-blue-600 hover:text-white border border-slate-200 transition"
                                    title="Chỉnh sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    <a href="delete.php?id=<?= $p['id'] ?>" 
                                    onclick="return confirm('CẢNH BÁO: Xóa sản phẩm này sẽ xóa toàn bộ biến thể và tồn kho liên quan! Bạn chắc chắn chứ?')" 
                                    class="w-9 h-9 flex items-center justify-center rounded-lg bg-red-50 text-red-500 hover:bg-red-600 hover:text-white border border-red-100 transition"
                                    title="Xóa">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <?php if(empty($products)): ?>
                    <div class="p-12 text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-100 mb-4">
                            <i class="fas fa-box-open text-3xl text-slate-400"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-700">Không tìm thấy sản phẩm</h3>
                        <a href="add.php" class="inline-block mt-4 text-blue-600 font-bold hover:underline">Thêm sản phẩm ngay</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div> 
</body>
</html>