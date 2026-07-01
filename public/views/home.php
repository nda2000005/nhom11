<?php
if (!isset($controller) && isset($this)) {
    $controller = $this;
}

$rootPath = dirname(__DIR__, 2);
require_once $rootPath . '/public/footer/header.php';
?>

<div class="main-container">
    <div class="section-heading">
        <h2>Sản phẩm nổi bật</h2>
        <p>Khám phá các sản phẩm công nghệ mới nhất</p>
    </div>
    
    <?php if (!empty($products) && is_array($products)): ?>
        <div class="product-grid">
            <?php foreach ($products as $item): ?>
                <div class="product-card">
                    <div class="card-image">
                        <?php 
                        $oldPrice = isset($item['old_price']) ? (float)$item['old_price'] : 0;
                        $price = isset($item['price']) ? (float)$item['price'] : 0;
                        if ($oldPrice > $price): 
                            $discountPercent = 0;
                            if (isset($controller) && method_exists($controller, 'calculateDiscount')) {
                                $discountPercent = $controller->calculateDiscount($price, $oldPrice);
                            } else {
                                $discountPercent = round((($oldPrice - $price) / $oldPrice) * 100);
                            }
                        ?>
                            <span class="badge-sale">
                                -<?php echo $discountPercent; ?>%
                            </span>
                        <?php endif; ?>
                        
                        <img src="<?php echo isset($controller) ? $controller->getProductImageUrl($item['image'] ?? '') : ''; ?>" alt="Ảnh sản phẩm">
                    </div>
                    
                    <div class="card-body">
                        <?php if (!empty($item['brand_name'])): ?>
                            <div class="brand-name"><?php echo htmlspecialchars($item['brand_name']); ?></div>
                        <?php endif; ?>
                        
                        <h3 class="product-name">
                            <a href="/mnopl/public/product-detail.php?id=<?php echo htmlspecialchars($item['product_id'] ?? ''); ?>">
                                <?php echo htmlspecialchars($item['product_name'] ?? ''); ?>
                            </a>
                        </h3>
                        
                        <div class="price-box">
                            <span class="current-price"><?php echo number_format($item['price'] ?? 0, 0, ',', '.'); ?>đ</span>
                            <?php if (!empty($item['old_price']) && $item['old_price'] > $item['price']): ?>
                                <span class="original-price"><?php echo number_format($item['old_price'], 0, ',', '.'); ?>đ</span>
                            <?php endif; ?>
                        </div>
                        
                        <a href="/mnopl/public/product-detail.php?id=<?php echo htmlspecialchars($item['product_id'] ?? ''); ?>" class="btn-detail">
                            Xem chi tiết
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="no-product">
            <p>Hiện tại không có sản phẩm nào.</p>
        </div>
    <?php endif; ?>
</div>

<?php 
require_once $rootPath . '/public/footer/footer.php';
?>