<?php
$headerPath = $_SERVER['DOCUMENT_ROOT'] . '/mnopl/public/footer/header.php';
if (file_exists($headerPath)) {
    include $headerPath;
} else {
    echo '<!DOCTYPE html><html lang="vi"><head><meta charset="UTF-8"><link rel="stylesheet" href="/mnopl/public/assets/css/style.css"></head><body>';
}
?>

<div class="main-container" style="max-width: 1200px; margin: 0 auto; font-family: Arial, sans-serif;">
    <h1 class="page-title" style="text-align: center; margin: 30px 0; color: #333;">Giỏ hàng của bạn</h1>

    <?php if (empty($renderItems)): ?>
        <div class="empty-cart" style="text-align: center; padding: 60px; background: #f9f9f9; border-radius: 8px; margin-bottom: 50px;">
            <i class="fa-solid fa-basket-shopping" style="font-size: 60px; color: #ccc; margin-bottom: 15px;"></i>
            <p style="margin: 10px 0; color: #666; font-size: 16px;">Giỏ hàng của bạn đang trống</p>
            <a href="index.php" class="btn-back" style="display: inline-block; text-decoration: none; background: #007bff; color: white; padding: 12px 25px; border-radius: 5px; margin-top: 15px; font-weight: bold;">Tiếp tục mua sắm</a>
        </div>
    <?php else: ?>
        <div class="cart-layout" style="display: flex; gap: 30px; padding: 0 20px; margin-bottom: 50px;">
            <div class="cart-items" style="flex: 2;">
                <div class="cart-header-row" style="display: grid; grid-template-columns: 2.5fr 1fr 1.2fr 1fr 0.5fr; font-weight: bold; border-bottom: 2px solid #ddd; padding-bottom: 10px; margin-bottom: 10px; color: #555;">
                    <span>Sản phẩm</span>
                    <span>Đơn giá</span>
                    <span style="text-align: center;">Số lượng</span>
                    <span>Thành tiền</span>
                    <span></span>
                </div>

                <?php foreach ($renderItems as $item): ?>
                    <div class="cart-item" style="display: grid; grid-template-columns: 2.5fr 1fr 1.2fr 1fr 0.5fr; align-items: center; padding: 15px 0; border-bottom: 1px solid #eee;">
                        <div class="ci-info" style="display: flex; align-items: center; gap: 15px;">
                            <img src="<?= $item['image'] ?>" alt="Ảnh SP" style="width: 80px; height: 80px; object-fit: cover; border-radius: 5px; border: 1px solid #eee;">
                            <div class="ci-name">
                                <a href="<?= $item['link_detail'] ?>" style="text-decoration: none; color: #333; font-weight: bold; font-size: 14px; line-height: 1.4;">
                                    <?= $item['name'] ?>
                                </a>
                            </div>
                        </div>
                        <div class="ci-price" style="color: #d9534f; font-weight: bold;"><?= $item['price_fmt'] ?></div>
                        <div class="ci-qty" style="display: flex; align-items: center; justify-content: center;">
                            <a href="<?= $item['link_dec'] ?>" class="btn-qty" style="text-decoration: none; padding: 5px 12px; background: #f0f0f0; color: #333; border: 1px solid #ccc; border-radius: 3px 0 0 3px; font-weight: bold;">-</a>
                            <input type="text" value="<?= $item['qty'] ?>" readonly style="width: 40px; text-align: center; border: 1px solid #ccc; border-left: none; border-right: none; margin: 0; height: 32px; box-sizing: border-box; font-weight: bold;">
                            <a href="<?= $item['link_inc'] ?>" class="btn-qty" style="text-decoration: none; padding: 5px 12px; background: #f0f0f0; color: #333; border: 1px solid #ccc; border-radius: 0 3px 3px 0; font-weight: bold;">+</a>
                        </div>
                        <div class="ci-subtotal" style="font-weight: bold; color: #333;"><?= $item['subtotal_fmt'] ?></div>
                        <div class="ci-remove" style="text-align: center;">
                            <a href="<?= $item['link_del'] ?>" onclick="return confirm('Bạn chắc chắn muốn xóa sản phẩm này?');" style="color: #ff4d4f;" title="Xóa sản phẩm">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="cart-actions-row" style="text-align: right; padding-top: 20px;">
                    <a href="api/cart-action.php?action=clear" class="btn-clear-cart" onclick="return confirm('Xóa toàn bộ giỏ hàng?');" style="color: #777; font-size: 14px; text-decoration: none;">
                        <i class="fa-solid fa-broom"></i> Xóa hết giỏ hàng
                    </a>
                </div>
            </div>

            <div class="cart-summary" style="flex: 0.9; background: #fff; padding: 25px; border-radius: 8px; height: fit-content; border: 1px solid #cbd5e1; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 18px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px;">Cộng giỏ hàng</h3>
                <div class="summary-row" style="display: flex; justify-content: space-between; margin-bottom: 15px; color: #475569;">
                    <span>Tạm tính:</span>
                    <span style="font-weight: bold;"><?= $totalOrderFmt ?></span>
                </div>
                <div class="summary-total" style="font-weight: bold; font-size: 1.3em; border-top: 1px solid #cbd5e1; padding-top: 15px; margin-top: 15px; display: flex; justify-content: space-between;">
                    <span>Tổng cộng:</span>
                    <span style="color: #ef4444;"><?= $totalOrderFmt ?></span>
                </div>
                <a href="checkout.php" class="btn-checkout" style="display: block; text-align: center; background: #22c55e; color: white; padding: 16px; border-radius: 6px; text-decoration: none; margin-top: 30px; font-weight: bold; font-size: 15px; letter-spacing: 0.5px; transition: background 0.3s;">TIẾN HÀNH ĐẶT HÀNG</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php 
$footerPath = $_SERVER['DOCUMENT_ROOT'] . '/mnopl/public/footer/footer.php';
if (file_exists($footerPath)) {
    include $footerPath;
} else {
    echo '</body></html>';
}
?>