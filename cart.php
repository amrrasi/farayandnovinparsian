<?php
declare(strict_types=1);
require_once "cms/myadmin/inc/config.php";

// ── Pull cart items from session ───────────────────────
$cartItems   = [];
$subtotal    = 0;
$totalQty    = 0;

if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {

    $ids = array_map('intval', array_keys($_SESSION['cart']));
    $in  = implode(',', $ids);

    $rows = $pdo->query("
        SELECT id, name, price, thumbnail
        FROM   product
        WHERE  id IN ($in)
          AND  active  = 1
          AND  deleted = 0
    ")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $row) {
        $id  = (int) $row['id'];
        $qty = (int) ($_SESSION['cart'][$id]['qty'] ?? 1);
        $row['qty']   = $qty;
        $row['total'] = $qty * (float) $row['price'];
        $subtotal    += $row['total'];
        $totalQty    += $qty;
        $cartItems[$id] = $row;
    }
}

$isEmpty = empty($cartItems);

// Simple shipping: free over 500,000 Toman
$shippingFee  = (!$isEmpty && $subtotal >= 500000) ? 0 : 35000;
$grandTotal   = $subtotal + $shippingFee;

// Format helper
function toman(float $n): string {
    return number_format($n) . ' تومان';
}
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
    <title>سبد خرید</title>
    <?= $global_base_address ?? '' ?>


    <link rel="stylesheet" href="assets/css/base/fontawesome.min.css">
    <link rel="stylesheet" href="assets/fonts/font.css">
    <link rel="stylesheet" href="assets/css/base/base.css">
    <link rel="stylesheet" href="assets/css/layout/header.css">
    <link rel="stylesheet" href="assets/css/layout/footer.css">
    <link rel="stylesheet" href="assets/css/pages/cart.css?v=<?= @filemtime('assets/css/pages/cart.css') ?>">

    <script>
        (() => {
            const h = document.documentElement;
            const s = localStorage.getItem('theme');
            if (s) { h.dataset.theme = s; }
            else if (window.matchMedia('(prefers-color-scheme:dark)').matches) { h.dataset.theme = 'dark'; }
            else { h.dataset.theme = 'light'; }
        })();
    </script>
</head>
<body>

<?php include 'inc/header.php'; ?>

<main class="cart-page">
    <div class="container-site">

        <!-- ══ LEFT — Item list ══ -->
        <div class="cart-left">

            <div class="cart-heading">
                <h1>سبد خرید</h1>
                <?php if (!$isEmpty): ?>
                    <span class="cart-badge"><?= $totalQty ?></span>
                <?php endif; ?>
            </div>

            <?php if ($isEmpty): ?>

                <div class="cart-empty reveal">
                    <i class="fas fa-cart-xmark"></i>
                    <p>سبد خرید شما خالی است</p>
                    <a href="products.php" class="btn btn-primary">
                        <i class="fas fa-bag-shopping"></i>
                        مشاهده محصولات
                    </a>
                </div>

            <?php else: ?>

                <div class="cart-items" id="cartItems">

                    <?php foreach ($cartItems as $item): ?>
                        <div class="cart-item reveal"
                             data-id="<?= $item['id'] ?>"
                             data-price="<?= $item['price'] ?>">

                            <!-- Thumbnail -->
                            <?php if (!empty($item['thumbnail'])): ?>
                                <img src="<?= htmlspecialchars($item['thumbnail']) ?>"
                                     alt="<?= htmlspecialchars($item['name']) ?>"
                                     class="cart-item-thumb"
                                     loading="lazy">
                            <?php else: ?>
                                <div class="cart-item-thumb-placeholder">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>

                            <!-- Info -->
                            <div class="cart-item-info">
                                <div class="cart-item-name"><?= htmlspecialchars($item['name']) ?></div>
                                <div class="cart-item-unit-price">
                                    قیمت واحد: <?= toman((float)$item['price']) ?>
                                </div>

                                <!-- Qty control -->
                                <div class="cart-item-qty">
                                    <button class="qty-btn btn-minus"
                                            data-id="<?= $item['id'] ?>"
                                            title="کاهش تعداد">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <span class="qty-value" id="qty-<?= $item['id'] ?>"><?= $item['qty'] ?></span>
                                    <button class="qty-btn btn-plus"
                                            data-id="<?= $item['id'] ?>"
                                            title="افزایش تعداد">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Price + remove -->
                            <div class="cart-item-actions">
                                <div class="cart-item-price" id="price-<?= $item['id'] ?>">
                                    <?= toman($item['total']) ?>
                                </div>
                                <button class="cart-item-remove btn-remove"
                                        data-id="<?= $item['id'] ?>"
                                        title="حذف از سبد">
                                    <i class="fas fa-trash-can"></i>
                                </button>
                            </div>

                        </div>
                    <?php endforeach; ?>

                </div>

                <!-- Clear all -->
                <div class="cart-clear-row">
                    <button class="btn-clear-cart" id="btnClearCart">
                        <i class="fas fa-broom"></i>
                        پاک کردن سبد
                    </button>
                </div>

            <?php endif; ?>
        </div>

        <!-- ══ RIGHT — Summary ══ -->
        <div class="cart-summary" id="cartSummary" <?= $isEmpty ? 'style="display:none"' : '' ?>>

            <div class="cart-summary-header">
                <h2>خلاصه سفارش</h2>
            </div>

            <div class="cart-summary-body">

                <div class="summary-row">
                    <span class="label">جمع محصولات</span>
                    <span class="value" id="summarySubtotal"><?= toman($subtotal) ?></span>
                </div>

                <div class="summary-row">
                    <span class="label">هزینه ارسال</span>
                    <span class="value" id="summaryShipping">
                        <?= $shippingFee === 0 ? '<span style="color:var(--clr-success)">رایگان</span>' : toman($shippingFee) ?>
                    </span>
                </div>

                <?php if ($shippingFee > 0): ?>
                    <div class="shipping-note">
                        <i class="fas fa-truck-fast"></i>
                        خرید بالای ۵۰۰٬۰۰۰ تومان ارسال رایگان
                    </div>
                <?php endif; ?>

                <div class="summary-row total">
                    <span class="label">مجموع قابل پرداخت</span>
                    <span class="value" id="summaryTotal"><?= toman($grandTotal) ?></span>
                </div>

            </div>

            <div class="cart-summary-footer">
                <a href="checkout.php" class="btn-checkout" id="btnCheckout">
                    <i class="fas fa-lock"></i>
                    ادامه و پرداخت
                </a>
                <a href="products.php" class="btn-continue">
                    <i class="fas fa-arrow-right"></i>
                    ادامه خرید
                </a>
            </div>

        </div><!-- /cart-summary -->

    </div><!-- /container-site -->
</main>

<!-- Toast -->
<div class="cart-toast" id="cartToast">
    <i class="fas fa-circle-check"></i>
    <span id="cartToastMsg"></span>
</div>

<?php include 'inc/footer.php'; ?>

<!-- ── Embedded cart state for JS ── -->
<script>
window.CART_DATA = <?= json_encode([
    'items'        => array_values($cartItems),
    'subtotal'     => $subtotal,
    'shippingFee'  => $shippingFee,
    'grandTotal'   => $grandTotal,
    'csrfToken'    => $_SESSION['csrf_token'] ?? '',
    'updateUrl'    => 'api/updateCart.php',
    'removeUrl'    => 'api/removeFromCart.php',
    'clearUrl'     => 'api/clearCart.php',
    'shippingThreshold' => 500000,
    'shippingCost'      => 35000,
], JSON_UNESCAPED_UNICODE) ?>;
</script>

<script src="assets/js/main.js"></script>
<script src="assets/js/pages/cart.js?v=<?= @filemtime('assets/js/pages/cart.js') ?>"></script>

</body>
</html>
