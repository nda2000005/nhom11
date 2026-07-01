<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thanh toán</title>
    <?php
    $headerPath = $_SERVER['DOCUMENT_ROOT'] . '/mnopl/public/footer/header.php';
    if(file_exists($headerPath)) {
        include $headerPath;
    } else {
        echo '<link rel="stylesheet" href="assets/css/style.css">';
        echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">';
    }
    ?>
</head>
<body>

<div class="main-container" style="max-width: 1200px; margin: 20px auto; font-family: Arial, sans-serif;">

    <?php if (!empty($orderSuccess) && $orderSuccess): ?>
        <div class="success-wrapper" style="text-align: center; padding: 50px; background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
            <div class="success-icon" style="font-size: 50px; color: #28a745; margin-bottom: 15px;"><i class="fa-regular fa-circle-check"></i></div>
            <h2 style="color: #333; margin-bottom: 15px;">Đặt hàng thành công!</h2>
            <p style="font-size: 16px; color: #555; margin-bottom: 5px;"><?= $successMsg ?></p>
            <p style="font-size: 15px; color: #666;">Chúng tôi sẽ liên hệ với bạn sớm nhất để xác nhận đơn hàng.</p>
            <a href="index.php" class="btn-continue" style="display:inline-block; margin-top:20px; padding:12px 25px; background:#28a745; color:#fff; text-decoration:none; border-radius:5px; font-weight: bold;">Tiếp tục mua sắm</a>
        </div>

    <?php else: ?>
        
        <?php if (!empty($errorMsg)): ?>
            <div class="alert alert-error" style="color: #721c24; background: #f8d7da; padding: 15px; border-radius: 5px; margin-bottom: 20px; font-weight: bold; border: 1px solid #f5c6cb;">
                <i class="fa-solid fa-triangle-exclamation"></i> <?= $errorMsg ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="checkout-container" style="display: flex; gap: 30px; padding: 20px 0;">

            <div class="checkout-left" style="flex: 1.5; background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #eee; box-shadow: 0 2px 5px rgba(0,0,0,0.02);">
                <h3 class="section-title" style="margin-top: 0; margin-bottom: 20px; border-bottom: 2px solid #3b82f6; padding-bottom: 10px; color: #1e3a8a;">Thông tin giao hàng</h3>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Họ và tên <span style="color:red">*</span></label>
                    <input type="text" name="fullname" class="form-control" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;" required placeholder="Nguyễn Văn A">
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Số điện thoại <span style="color:red">*</span></label>
                    <input type="text" name="phone" class="form-control" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;" required placeholder="09xxxxxxxx">
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Email <span style="color:red">*</span></label>
                    <input type="email" name="email" class="form-control" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;" required placeholder="email@example.com">
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Địa chỉ nhận hàng <span style="color:red">*</span></label>
                    <textarea name="address" class="form-control" rows="3" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;" required placeholder="Số nhà, Đường, Phường/Xã..."></textarea>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Ghi chú (Tùy chọn)</label>
                    <textarea name="note" class="form-control" rows="2" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;" placeholder="Giao giờ hành chính..."></textarea>
                </div>
            </div>

            <div class="checkout-right" style="flex: 1; background: #fdfdfd; padding: 20px; border-radius: 8px; border: 1px solid #eee; height: fit-content; box-shadow: 0 2px 5px rgba(0,0,0,0.02);">
                <h3 class="section-title" style="margin-top: 0; margin-bottom: 20px; font-size: 18px; color: #1e3a8a; border-bottom: 1px solid #eee; padding-bottom: 10px;">Đơn hàng của bạn</h3>

                <div class="order-summary-list" style="max-height: 250px; overflow-y: auto;">
                    <?php if (!empty($summaryItems)): foreach ($summaryItems as $item): ?>
                        <div class="order-summary-item" style="display: flex; justify-content: space-between; margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 10px;">
                            <div class="item-info" style="display: flex; gap: 10px; align-items: center;">
                                <img src="<?= $item['image'] ?>" alt="Img" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #eee;">
                                <div class="item-text">
                                    <h4 style="margin: 0 0 4px 0; font-size: 14px; color: #333; font-weight: bold;"><?= $item['name'] ?></h4>
                                    <span style="font-size: 12px; color: #666;">SL: <strong><?= $item['qty'] ?></strong> x <?= $item['price_unit'] ?></span>
                                </div>
                            </div>
                            <div class="item-price" style="font-weight: bold; color: #333; align-self: center;"><?= $item['price_total'] ?></div>
                        </div>
                    <?php endforeach; else: ?>
                        <p style="color: #666; text-align: center;">Giỏ hàng trống.</p>
                    <?php endif; ?>
                </div>

                <div class="total-row" style="display: flex; justify-content: space-between; font-weight: bold; margin-top: 20px; font-size: 1.2em; border-top: 2px solid #eee; padding-top: 15px; color: #333;">
                    <span>Tổng thanh toán:</span>
                    <span style="color: #ef4444;"><?= $grandTotalFmt ?? '0đ' ?></span>
                </div>

                <div class="payment-methods" style="margin-top: 25px;">
                    <h4 style="margin-bottom: 12px; font-size: 15px; color: #475569; font-weight: bold;">Phương thức thanh toán</h4>
                    <label class="payment-option" style="display: block; margin-bottom: 10px; padding: 10px; border: 1px solid #e2e8f0; border-radius: 5px; cursor: pointer; background: #fff;">
                        <input type="radio" name="payment_method" value="COD" checked>
                        <span style="margin-left: 8px; font-size: 14px; color: #333;">Thanh toán khi nhận hàng (COD)</span>
                    </label>
                    <label class="payment-option" style="display: block; padding: 10px; border: 1px solid #e2e8f0; border-radius: 5px; cursor: pointer; background: #fff;">
                        <input type="radio" name="payment_method" value="BANK">
                        <span style="margin-left: 8px; font-size: 14px; color: #333;">Chuyển khoản ngân hàng</span>
                    </label>
                </div>

                <button type="submit" name="btn_order" class="btn-confirm" style="width: 100%; padding: 15px; background: #22c55e; color: white; border: none; border-radius: 8px; margin-top: 25px; cursor: pointer; font-weight: bold; font-size: 16px; transition: background 0.3s;">
                    XÁC NHẬN ĐẶT HÀNG
                </button>

                <a href="cart.php" class="link-back" style="display: block; text-align: center; margin-top: 15px; text-decoration: none; color: #64748b; font-size: 14px;">Quay lại giỏ hàng</a>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php 
$footerPath = $_SERVER['DOCUMENT_ROOT'] . '/mnopl/public/footer/footer.php';
if(file_exists($footerPath)) {
    include $footerPath;
}
?>

</body>
</html>