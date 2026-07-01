<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Tránh lỗi cảnh báo biến chưa được định nghĩa khi controller nạp view
$displayName  = $displayName ?? 'Chi tiết sản phẩm';
$displayImage = $displayImage ?? 'assets/img/no-image.png';
$brandName    = $brandName ?? 'TechStore';
$displayPrice = $displayPrice ?? 0;
$displayStock = $displayStock ?? 0;
$warranty     = $warranty ?? 12;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($displayName) ?> - TechStore</title>
    <?php
    if(file_exists('footer/header.php')) {
        include 'footer/header.php';
    } else {
        echo '<link rel="stylesheet" href="assets/css/style.css">';
    }
    ?>
</head>
<body>

<div class="main-container">

    <div class="product-top-section">
        <div class="gallery-box">
            <div class="main-image">
                <img src="<?= htmlspecialchars($displayImage) ?>" alt="<?= htmlspecialchars($displayName) ?>">
            </div>

            <div class="thumb-list">
                <?php if (!empty($product['media'])): foreach ($product['media'] as $img): ?>
                    <div class="thumb-item">
                        <img src="<?= function_exists('getDetailImgUrl') ? getDetailImgUrl($img['file_path']) : htmlspecialchars($img['file_path']) ?>" alt="Thumbnail">
                    </div>
                <?php endforeach; endif; ?>
            </div>
        </div>

        <div class="info-box">
            <div class="brand-label"><?= htmlspecialchars($brandName) ?></div>
            <h1 class="product-title"><?= htmlspecialchars($displayName) ?></h1>

            <div class="price-section">
                <span class="current-price">
                    <?= number_format($displayPrice, 0, ',', '.') ?>đ
                </span>
                <span class="vat-note">(Đã bao gồm VAT)</span>
            </div>

            <div class="variants-section">
                <p class="section-label">Chọn phiên bản:</p>
                <div class="variant-grid">
                    <?php if (!empty($product['variants'])): foreach ($product['variants'] as $v):
                        $isActive = ($selectedVariant && $v['id'] == $selectedVariant['id']) ? 'active' : '';
                        $url = "?id=" . ($product['id'] ?? $product['product_id'] ?? '') . "&variant_id=" . $v['id'];
                        ?>
                        <a href="<?= $url ?>" class="variant-btn <?= $isActive ?>">
                            <strong><?= htmlspecialchars($v['variant_name']) ?></strong>
                            <span><?= number_format($v['selling_price'], 0, ',', '.') ?>đ</span>
                        </a>
                    <?php endforeach; endif; ?>
                </div>
            </div>

            <div class="action-section">
                <div class="stock-status">
                    <?php if ($displayStock > 0): ?>
                        <i class="fa-solid fa-check-circle" style="color: green;"></i> Còn hàng (<?= $displayStock ?>)
                    <?php else: ?>
                        <i class="fa-solid fa-circle-xmark" style="color: red;"></i> Hết hàng
                    <?php endif; ?>
                </div>

                <form action="api/cart-action.php" method="POST">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="variant_id" value="<?= (int)($selectedVariant['id'] ?? 0) ?>">
                    <input type="hidden" name="qty" value="1"> 
                    
                    <button type="submit" class="btn-buy-now" <?= $displayStock <= 0 ? 'disabled style="background:#ccc; cursor:not-allowed;"' : '' ?>>
                        <i class="fa-solid fa-cart-plus"></i> THÊM VÀO GIỎ HÀNG
                        <span>Giao tận nơi hoặc nhận tại cửa hàng</span>
                    </button>
                </form>
            </div>

            <ul class="policy-list">
                <li><i class="fa-solid fa-shield-halved"></i> Bảo hành chính hãng <?= htmlspecialchars($warranty) ?> tháng</li>
                <li><i class="fa-solid fa-rotate-left"></i> 1 đổi 1 trong 30 ngày nếu lỗi</li>
            </ul>
        </div>
    </div>

    <div class="product-bottom-section">
        <div class="description-box">
            <h2 class="box-title">Đặc điểm nổi bật</h2>
            <div class="ck-content">
                <?= html_entity_decode($product['description'] ?? '') ?>
            </div>
        </div>

        <div class="specs-box">
            <h2 class="box-title">Thông số kỹ thuật</h2>
            <table class="specs-table">
                <?php if (!empty($product['attributes'])): foreach ($product['attributes'] as $attr): ?>
                    <tr>
                        <td class="attr-name"><?= htmlspecialchars($attr['attr_name']) ?></td>
                        <td class="attr-val"><?= htmlspecialchars($attr['attr_value']) ?></td>
                    </tr>
                <?php endforeach; endif; ?>
            </table>
        </div>
    </div>

</div>

<?php if(file_exists('footer/footer.php')) include 'footer/footer.php'; ?>

</body>
</html>