<?php
declare(strict_types=1);
require_once "cms/myadmin/inc/config.php";

// ── Auth guard ──────────────────────────────────────────
if (empty($_SESSION['user']['id'])) {
    header('Location: entry.php?redirect=checkout.php');
    exit;
}

$cartItems  = [];
$subtotal   = 0;

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
        $cartItems[$id] = $row;
    }
}

// Redirect to cart if empty
if (empty($cartItems)) {
    header('Location: cart/');
    exit;
}

$shippingFee = $subtotal >= 500000 ? 0 : 35000;
$grandTotal  = $subtotal + $shippingFee;

// ── User info ────────────────────────────────────────────
$user = $pdo->prepare("SELECT name, mobile, email FROM user WHERE id = :id LIMIT 1");
$user->execute([':id' => $_SESSION['user']['id']]);
$user = $user->fetch(PDO::FETCH_ASSOC) ?: [];


$BANK_CARD   = '6037-9975-9999-1234';
$BANK_OWNER  = 'فروشگاه شما – امیر عسکری';
$BANK_SHEBA  = 'IR12 0000 0000 0000 0000 0000 00';

function toman(float $n): string {
    return number_format($n) . ' تومان';
}

$invoiceNo = 'INV-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 5));
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
    <title>تکمیل سفارش</title>

    <?= $global_base_address ?? '' ?>

    <link rel="stylesheet" href="assets/css/base/animate.min.css">
    <link rel="stylesheet" href="assets/css/base/flaticon.css">
    <link rel="stylesheet" href="assets/css/base/fontawesome.min.css">
    <link rel="stylesheet" href="assets/css/base/magnific-popup.min.css">
    <link rel="stylesheet" href="assets/css/base/nice-select.css">
    <link rel="stylesheet" href="assets/css/base/owl.carousel.min.css">

    <link rel="stylesheet" href="assets/fonts/font.css">

    <link rel="stylesheet" href="assets/css/base/bootstrap.rtl.css">
    <link rel="stylesheet" href="assets/css/base/base.css">
    <link rel="stylesheet" href="assets/css/layout/header.css">
    <link rel="stylesheet" href="assets/css/layout/footer.css">

    <link rel="stylesheet" href="assets/css/pages/cart.css">
    <link rel="stylesheet" href="assets/css/pages/checkout.css?v=<?= @filemtime('assets/css/pages/checkout.css') ?>">

    <script>
        (() => {
            const h = document.documentElement;
            const s = localStorage.getItem('theme');
            if (s) h.dataset.theme = s;
            else if (window.matchMedia('(prefers-color-scheme:dark)').matches) h.dataset.theme = 'dark';
            else h.dataset.theme = 'light';
        })();
    </script>
</head>
<body>

<?php include 'inc/header.php'; ?>

<main class="checkout-page mt-5">
    <div class="container-site">

        <!-- ══ LEFT — Form ══ -->
        <div class="checkout-left">

            <!-- Step indicator -->
            <div class="checkout-steps">
                <div class="step-item done">
                    <div class="step-circle"><i class="fas fa-check"></i></div>
                    <span class="step-label">سبد خرید</span>
                </div>
                <div class="step-item active">
                    <div class="step-circle">۲</div>
                    <span class="step-label">اطلاعات سفارش</span>
                </div>
                <div class="step-item">
                    <div class="step-circle">۳</div>
                    <span class="step-label">تأیید و پرداخت</span>
                </div>

            </div>
            <hr>
            <div>
                پس از تکمیل فرم ما با پیش‌فاکتور را دریافت کنید و در سریع‌ترین زمان ممکن کارشناسان ما جهت اتمام خرید با ما تماس خواهند گرفت
            </div>

            <!-- ── Receiver info ── -->
            <div class="checkout-card reveal">
                <div class="checkout-card-header">
                    <i class="fas fa-user-circle"></i>
                    <h2>اطلاعات گیرنده</h2>
                </div>
                <div class="checkout-card-body">
                    <div class="form-grid">

                        <div class="form-group">
                            <label class="form-label" for="fullName">نام و نام خانوادگی *</label>
                            <input type="text" id="fullName" name="full_name" class="form-control"
                                   value="<?= htmlspecialchars($user['name'] ?? '') ?>"
                                   placeholder="نام کامل خود را وارد کنید" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="phone">شماره تماس *</label>
                            <input type="tel" id="phone" name="phone" class="form-control"
                                   value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                                   placeholder="09xxxxxxxxx" required>
                        </div>

                        <div class="form-group full">
                            <label class="form-label" for="email">ایمیل (اختیاری)</label>
                            <input type="email" id="email" name="email" class="form-control"
                                   value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                                   placeholder="email@example.com">
                        </div>

                        <div class="form-group full">
                            <label class="form-label" for="address">آدرس تحویل *</label>
                            <textarea id="address" name="address" class="form-control"
                                      placeholder="استان، شهر، خیابان، کوچه، پلاک" required><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="postalCode">کد پستی</label>
                            <input type="text" id="postalCode" name="postal_code" class="form-control"
                                   placeholder="۱۰ رقم" maxlength="10">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="orderNote">توضیحات سفارش</label>
                            <input type="text" id="orderNote" name="order_note" class="form-control"
                                   placeholder="مثلاً رنگ یا سایز دلخواه">
                        </div>

                    </div>
                </div>
            </div>

            <!-- ── Payment info ── -->
            <div class="checkout-card reveal">
                <div class="checkout-card-header">
                    <i class="fas fa-credit-card"></i>
                    <h2>اطلاعات پرداخت</h2>
                </div>
                <div class="checkout-card-body">

                    <div class="payment-info-card">

                        <div class="payment-info-title">
                            <i class="fas fa-info-circle"></i>
                            نحوه پرداخت — کارت به کارت
                        </div>

                        <!-- Bank card visual -->
                        <div class="bank-card">
                            <div class="bank-card-logo">شماره کارت جهت واریز</div>
                            <div class="bank-card-number" id="bankCardNumber"><?= $BANK_CARD ?></div>
                            <div class="bank-card-owner"><?= $BANK_OWNER ?></div>
                            <button class="copy-btn" id="copyCardBtn" type="button">
                                <i class="fas fa-copy"></i>
                                کپی شماره کارت
                            </button>
                        </div>

                        <!-- Steps -->
                        <div class="payment-steps">
                            <div class="payment-step">
                                <span class="payment-step-num">۱</span>
                                مبلغ <strong id="payStepAmount"><?= toman($grandTotal) ?></strong>
                                را به شماره کارت فوق واریز کنید.
                            </div>
                            <div class="payment-step">
                                <span class="payment-step-num">۲</span>
                                تصویر رسید واریز را در قسمت زیر بارگذاری کنید.
                            </div>
                            <div class="payment-step">
                                <span class="payment-step-num">۳</span>
                                پس از ثبت سفارش، سفارش شما در پنل کاربری قابل پیگیری خواهد بود.
                            </div>
                        </div>

                    </div>

                </div>
            </div>

            <!-- ── Receipt upload ── -->
            <div class="checkout-card reveal">
                <div class="checkout-card-header">
                    <i class="fas fa-file-image"></i>
                    <h2>بارگذاری رسید پرداخت</h2>
                </div>
                <div class="checkout-card-body">

                    <div class="upload-zone" id="uploadZone">
                        <input type="file" id="receiptFile" accept="image/*,application/pdf" style="user-select:auto;-webkit-user-select:auto;">
                        <i class="fas fa-cloud-arrow-up"></i>
                        <p>تصویر رسید واریز را اینجا بکشید یا کلیک کنید</p>
                        <span>فرمت‌های مجاز: JPG, PNG, PDF — حداکثر ۵ مگابایت</span>
                    </div>

                    <div class="upload-preview" id="uploadPreview">
                        <i class="fas fa-file-check"></i>
                        <span id="uploadFileName"></span>
                        <button class="remove-file" id="removeFile" type="button">
                            <i class="fas fa-xmark"></i>
                        </button>
                    </div>

                </div>
            </div>

            <!-- ── Pre-invoice actions ── -->
            <div class="checkout-card reveal">
                <div class="checkout-card-header">
                    <i class="fas fa-file-invoice"></i>
                    <h2>پیش‌فاکتور</h2>
                </div>
                <div class="checkout-card-body">
                    <p style="font-size:.88rem;color:var(--clr-text-muted);margin-bottom:16px;line-height:1.9">
                        می‌توانید پیش از ثبت نهایی، پیش‌فاکتور سفارش خود را مشاهده، چاپ یا دریافت کنید.
                    </p>
                    <div style="display:flex;gap:12px;flex-wrap:wrap">
                        <button class="btn btn-outline" id="btnShowInvoice" type="button">
                            <i class="fas fa-eye"></i>
                            مشاهده پیش‌فاکتور
                        </button>
                    </div>
                </div>
            </div>

        </div>


        <div class="checkout-summary">

            <div class="checkout-summary-card">

                <div class="checkout-summary-header">
                    <h3>خلاصه سفارش</h3>
                </div>

                <div class="checkout-summary-items">
                    <?php foreach ($cartItems as $item): ?>
                        <div class="summary-item">
                            <?php if (!empty($item['thumbnail'])): ?>
                                <img src="cms/<?= htmlspecialchars($item['thumbnail']) ?>"
                                     class="summary-item-thumb"
                                     alt="<?= htmlspecialchars($item['name']) ?>"
                                     loading="lazy">
                            <?php else: ?>
                                <div class="summary-item-thumb" style="display:flex;align-items:center;justify-content:center">
                                    <i class="fas fa-image" style="color:var(--clr-text-faint);font-size:.8rem"></i>
                                </div>
                            <?php endif; ?>
                            <div class="summary-item-info">
                                <div class="summary-item-name"><?= htmlspecialchars($item['name']) ?></div>
                                <div class="summary-item-qty">×<?= $item['qty'] ?></div>
                            </div>
                            <div class="summary-item-price"><?= toman($item['total']) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="checkout-totals">
                    <div class="total-row">
                        <span class="lbl">جمع محصولات</span>
                        <span class="val"><?= toman($subtotal) ?></span>
                    </div>
                    <div class="total-row">
                        <span class="lbl">هزینه ارسال</span>
                        <span class="val">
                            <?= $shippingFee === 0
                                ? '<span style="color:var(--clr-success)">رایگان</span>'
                                : toman($shippingFee) ?>
                        </span>
                    </div>
                    <div class="total-row grand">
                        <span class="lbl">مجموع</span>
                        <span class="val"><?= toman($grandTotal) ?></span>
                    </div>
                </div>

            </div>

            <!-- Submit -->
            <button class="btn-submit-order" id="btnSubmitOrder" type="button">
                <i class="fas fa-circle-check"></i>
                ثبت نهایی سفارش
            </button>

            <a href="cart/" class="btn-continue" style="display:flex;align-items:center;justify-content:center;gap:8px;text-decoration:none">
                <i class="fas fa-arrow-right"></i>
                بازگشت به سبد خرید
            </a>

        </div>

    </div>
</main>

<!-- ══════════════════════════════════════
     PRE-INVOICE MODAL
════════════════════════════════════════ -->
<div class="invoice-modal-backdrop" id="invoiceModal">
    <div class="invoice-modal">

        <!-- Printable paper -->
        <div class="invoice-paper" id="invoicePaper">

            <!-- Top: brand + meta -->
            <div class="invoice-top">
                <div class="invoice-brand">
                    <div class="invoice-brand-name">Logo</div>
                    <div class="invoice-brand-sub">fanapit.com</div>
                </div>
                <div class="invoice-meta">
                    <span>شماره پیش‌فاکتور</span>
                    <strong><?= $invoiceNo ?></strong>
                    <span style="margin-top:6px">تاریخ</span>
                    <strong id="invoiceDate"></strong>
                </div>
            </div>

            <!-- Customer -->
            <div class="invoice-section-title">مشخصات مشتری</div>
            <div class="invoice-customer">
                <div class="invoice-field">
                    <label>نام و نام خانوادگی</label>
                    <span id="inv-name">—</span>
                </div>
                <div class="invoice-field">
                    <label>شماره تماس</label>
                    <span id="inv-phone">—</span>
                </div>
                <div class="invoice-field" style="grid-column:1/-1">
                    <label>آدرس تحویل</label>
                    <span id="inv-address">—</span>
                </div>
            </div>

            <!-- Items table -->
            <div class="invoice-section-title">اقلام سفارش</div>
            <table class="invoice-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>نام محصول</th>
                        <th>قیمت واحد</th>
                        <th>تعداد</th>
                        <th>جمع</th>
                    </tr>
                </thead>
                <tbody id="invoiceTableBody">
                    <?php $rowNum = 1; foreach ($cartItems as $item): ?>
                        <tr>
                            <td><?= $rowNum++ ?></td>
                            <td><?= htmlspecialchars($item['name']) ?></td>
                            <td><?= toman((float)$item['price']) ?></td>
                            <td><?= $item['qty'] ?></td>
                            <td><?= toman($item['total']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Totals -->
            <div class="invoice-totals">
                <div class="invoice-total-row">
                    <span class="lbl">جمع محصولات</span>
                    <span class="val"><?= toman($subtotal) ?></span>
                </div>
                <div class="invoice-total-row">
                    <span class="lbl">هزینه ارسال</span>
                    <span class="val"><?= $shippingFee === 0 ? 'رایگان' : toman($shippingFee) ?></span>
                </div>
                <div class="invoice-total-row grand">
                    <span class="lbl">مجموع قابل پرداخت</span>
                    <span class="val"><?= toman($grandTotal) ?></span>
                </div>
            </div>

            <!-- Payment note -->
            <div class="invoice-payment-note">
                <p>لطفاً مبلغ فوق را به شماره کارت زیر واریز کرده و تصویر رسید را در پنل کاربری بارگذاری کنید:</p>
                <div class="card-num"><?= $BANK_CARD ?></div>
                <p style="margin-top:6px;font-size:.75rem"><?= $BANK_OWNER ?></p>
            </div>

            <div class="invoice-footer-note">
                این پیش‌فاکتور تأیید نهایی سفارش نیست. پس از بررسی رسید پرداخت، سفارش شما تأیید خواهد شد.
            </div>

        </div>

        <!-- Action bar -->
        <div class="invoice-actions">
            <button class="btn btn-print" id="btnPrint" type="button">
                <i class="fas fa-print"></i>
                چاپ
            </button>
            <button class="btn btn-download" id="btnDownload" type="button">
                <i class="fas fa-download"></i>
                دریافت PDF
            </button>
            <button class="btn-close-modal" id="btnCloseModal" type="button" title="بستن">
                <i class="fas fa-xmark"></i>
            </button>
        </div>

    </div>
</div>

<!-- Toast -->
<div class="cart-toast" id="cartToast">
    <i class="fas fa-circle-check"></i>
    <span id="cartToastMsg"></span>
</div>

<script>
window.CHECKOUT_DATA = <?= json_encode([
    'grandTotal'  => $grandTotal,
    'invoiceNo'   => $invoiceNo,
    'csrfToken'   => $_SESSION['csrf_token'] ?? '',
    'submitUrl'   => 'ajax/cart/submitOrder.php',
    'bankCard'    => $BANK_CARD,
], JSON_UNESCAPED_UNICODE) ?>;
</script>


<script src="assets/js/jquery.nice-select.min.js"></script>
<script src="assets/js/owl.carousel.min.js"></script>
<script src="assets/js/bootstrap.js"></script>
<script src="assets/js/bootstrap.bundle.js"></script>
<script src="assets/js/jquery.js"></script>
<script src="assets/js/main.js"></script>
<script src="assets/js/pages/checkout.js?v=<?= @filemtime('assets/js/pages/checkout.js') ?>"></script>

</body>
</html>
