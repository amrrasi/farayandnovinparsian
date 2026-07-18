<?php
require_once "inc/check.php";

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: order_list.php");
    exit;
}

$stmt = $mysqli->prepare("
    SELECT o.*, s.status_name
    FROM   orders o
    LEFT   JOIN order_status s ON s.id = o.order_status_id
    WHERE  o.id = ? AND o.deleted = 0
    LIMIT  1
");
$stmt->bind_param("i", $id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header("Location: order_list.php");
    exit;
}

if ((int) $order['visited'] === 0) {
    $mysqli->query("UPDATE orders SET visited=1 WHERE id={$id}");
}

$iStmt = $mysqli->prepare("
    SELECT * FROM order_items WHERE order_id = ? ORDER BY id ASC
");
$iStmt->bind_param("i", $id);
$iStmt->execute();
$items = $iStmt->get_result()->fetch_all(MYSQLI_ASSOC);

$statusMap = [
    1 => ['cls' => 'warning',   'icon' => 'fa-clock',         'label' => 'در انتظار قیمت‌دهی'],
    2 => ['cls' => 'info',      'icon' => 'fa-credit-card',   'label' => 'در انتظار پرداخت'],
    3 => ['cls' => 'primary',   'icon' => 'fa-magnifying-glass','label' => 'در حال بررسی'],
    4 => ['cls' => 'success',   'icon' => 'fa-circle-check',  'label' => 'تأیید شده'],
    5 => ['cls' => 'secondary', 'icon' => 'fa-truck',         'label' => 'ارسال شده'],
    6 => ['cls' => 'danger',    'icon' => 'fa-box-archive',   'label' => 'آرشیو / لغو شده'],
];
$sid    = (int) $order['order_status_id'];
$sInfo  = $statusMap[$sid] ?? ['cls' => 'dark', 'icon' => 'fa-circle', 'label' => 'نامشخص'];

$hasQuote = (float) $order['quoted_total'] > 0;
$hasRcpt  = !empty($order['receipt_path']);

function fmt(float $n): string {
    return number_format((int)$n) . ' تومان';
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>سفارش #<?= $id ?> — <?= setting('name') ?></title>
    <link rel="icon" type="image/png" href="images/favicon.jpg">
    <link href="vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/fontawesome.min.css">

    <link href="css/style.css" rel="stylesheet">
    <style>
        /* ── layout ── */
        .ov-grid       { display: grid; grid-template-columns: 1fr 340px; gap: 24px; align-items: start; }
        @media(max-width:992px){ .ov-grid { grid-template-columns: 1fr; } }

        /* ── section card ── */
        .ov-card       { border-radius: 14px; border: 1px solid #e9ecef; background:#fff; margin-bottom: 20px; overflow: hidden; }
        .ov-card-head  { display:flex; align-items:center; gap:10px; padding:16px 20px;
            background:#f8f9fa; border-bottom:1px solid #e9ecef; }
        .ov-card-head i{ width:34px;height:34px;border-radius:10px;display:flex;align-items:center;
            justify-content:center;color:#fff;font-size:.9rem;flex-shrink:0; }
        .ov-card-head h6{ margin:0;font-weight:700;font-size:.92rem; }
        .ov-card-body  { padding:20px; }

        /* ── info rows ── */
        .ov-info-row   { display:flex; justify-content:space-between; align-items:baseline;
            padding:9px 0; border-bottom:1px dashed #f0f0f0; font-size:.875rem; }
        .ov-info-row:last-child{ border-bottom:none; }
        .ov-info-label { color:#8a8fa3; flex-shrink:0; margin-left:10px; }
        .ov-info-value { font-weight:600; text-align:left; direction:ltr; }
        .ov-info-value.rtl-val{ direction:rtl; text-align:right; }

        /* ── status timeline ── */
        .ov-timeline   { display:flex; flex-direction:column; gap:0; }
        .ov-tl-item    { display:flex; gap:14px; position:relative; padding-bottom:20px; }
        .ov-tl-item:last-child{ padding-bottom:0; }
        .ov-tl-dot     { width:32px;height:32px;border-radius:50%;display:flex;align-items:center;
            justify-content:center;font-size:.8rem;flex-shrink:0;z-index:1; }
        .ov-tl-line    { position:absolute; right:15px; top:32px; bottom:0;
            width:2px; background:#e9ecef; }
        .ov-tl-item:last-child .ov-tl-line{ display:none; }
        .ov-tl-content { padding-top:4px; }
        .ov-tl-content strong{ display:block; font-size:.875rem; }
        .ov-tl-content small { color:#8a8fa3; font-size:.78rem; }

        /* ── items table ── */
        .ov-items      { width:100%; border-collapse:collapse; font-size:.875rem; }
        .ov-items th   { background:#f8f9fa; padding:10px 12px; font-weight:700;
            font-size:.78rem; color:#6b7280; border-bottom:2px solid #e9ecef; }
        .ov-items td   { padding:11px 12px; border-bottom:1px solid #f3f4f6; vertical-align:middle; }
        .ov-items tbody tr:last-child td{ border-bottom:none; }
        .ov-items tbody tr:hover td    { background:#fafafa; }
        .ov-items .col-price{ direction:ltr; text-align:left; font-weight:600; }

        /* ── totals ── */
        .ov-totals     { margin-top:16px; padding:14px 16px; border-radius:10px;
            background:#f8f9fa; border:1px solid #e9ecef; }
        .ov-total-row  { display:flex;justify-content:space-between;font-size:.875rem;padding:5px 0; }
        .ov-total-row.final{ font-size:1rem;font-weight:700;color:#10b981;padding-top:10px;
            border-top:2px solid #e9ecef;margin-top:6px; }
        .ov-total-row.final .ov-total-val{ font-size:1.1rem; }

        /* ── receipt preview ── */
        .ov-receipt-img { width:100%;border-radius:10px;border:1px solid #e9ecef;
            cursor:zoom-in;transition:.2s; }
        .ov-receipt-img:hover{ transform:scale(1.02);box-shadow:0 6px 20px rgba(0,0,0,.12); }

        /* ── status badge pill ── */
        .ov-status-pill{ display:inline-flex;align-items:center;gap:8px;
            padding:8px 16px;border-radius:50px;font-weight:700;font-size:.875rem; }

        /* ── note box ── */
        .ov-note-box   { background:#fffbeb;border:1px solid #fde68a;border-radius:10px;
            padding:14px 16px;font-size:.875rem;line-height:1.7;color:#78350f; }
        .ov-admin-note { background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af; }

        /* ── action bar ── */
        .ov-action-bar { display:flex;gap:10px;flex-wrap:wrap;margin-bottom:24px; }

        /* print */
        @media print {
            #main-wrapper > nav,
            #main-wrapper > .nav-header,
            .ov-action-bar,
            .deznav { display:none!important; }
            .content-body { margin:0!important; padding:0!important; }
        }
    </style>
</head>
<body>

<div id="preloader">
    <div class="sk-three-bounce">
        <div class="sk-child sk-bounce1"></div>
        <div class="sk-child sk-bounce2"></div>
        <div class="sk-child sk-bounce3"></div>
    </div>
</div>

<div id="main-wrapper">

    <?php require_once "inc/header.php"; ?>
    <?php require_once "inc/aside.php"; ?>

    <div class="content-body">
        <div class="container-fluid">

            <div class="page-titles">
                <h4>سفارش #<?= $id ?></h4>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="order_list.php">سفارشات</a>
                    </li>
                    <li class="breadcrumb-item active">مشاهده سفارش</li>
                </ol>
            </div>

            <div class="ov-action-bar">
                <a href="order_list.php" class="btn btn-light">
                    <i class="fa fa-arrow-right ml-1"></i>بازگشت
                </a>
                <a href="order_edit.php?id=<?= $id ?>" class="btn btn-primary">
                    <i class="fa fa-edit ml-1"></i>ویرایش سفارش
                </a>
                <?php if ($hasRcpt): ?>
                    <a href="../../<?= htmlspecialchars($order['receipt_path'], ENT_QUOTES, 'UTF-8') ?>"
                       target="_blank" class="btn btn-success">
                        <i class="fa fa-file-image ml-1"></i>دانلود رسید
                    </a>
                <?php endif; ?>
                <button onclick="window.print()" class="btn btn-light">
                    <i class="fa fa-print ml-1"></i>چاپ
                </button>
                <a href="order_list.php?delete=<?= $id ?>"
                   onclick="return confirm('سفارش #<?= $id ?> حذف شود؟')"
                   class="btn btn-danger mr-auto">
                    <i class="fa fa-trash ml-1"></i>حذف
                </a>
            </div>

            <div class="ov-grid">

                <div>

                    <div class="ov-card">
                        <div class="ov-card-head">
                            <i class="fa fa-list" style="background:#0ea5e9"></i>
                            <h6>اقلام سفارش</h6>
                            <span class="badge badge-info mr-auto"><?= count($items) ?> قلم</span>
                        </div>
                        <div class="ov-card-body" style="padding:0">
                            <?php if ($items): ?>
                                <table class="ov-items">
                                    <thead>
                                    <tr>
                                        <th width="40">#</th>
                                        <th>محصول</th>
                                        <th width="80" class="text-center">تعداد</th>
                                        <th width="130" class="text-left">قیمت واحد</th>
                                        <th width="130" class="text-left">جمع</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($items as $i => $item):
                                        $unitPrice = (float) $item['price'];
                                        $qty       = (int)   $item['qty'];
                                        $lineTotal = $unitPrice * $qty;
                                        ?>
                                        <tr>
                                            <td class="text-muted"><?= $i + 1 ?></td>
                                            <td>
                                                <strong>
                                                    <?= htmlspecialchars($item['product_name'] ?? 'محصول حذف‌شده', ENT_QUOTES, 'UTF-8') ?>
                                                </strong>
                                                <?php if (!empty($item['sku'])): ?>
                                                    <br><small class="text-muted">SKU: <?= htmlspecialchars($item['sku'], ENT_QUOTES, 'UTF-8') ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">× <?= $qty ?></td>
                                            <td class="col-price">
                                                <?= $unitPrice > 0 ? number_format((int)$unitPrice) : '—' ?>
                                            </td>
                                            <td class="col-price">
                                                <?php if ($lineTotal > 0): ?>
                                                    <span class="text-success"><?= number_format((int)$lineTotal) ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">—</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>

                                <div class="ov-card-body pt-0">
                                    <div class="ov-totals">
                                        <?php if ((float) $order['grand_total'] > 0): ?>
                                            <div class="ov-total-row">
                                                <span class="ov-info-label">جمع اولیه (مشتری)</span>
                                                <span class="ov-total-val"><?= fmt((float)$order['grand_total']) ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($hasQuote): ?>
                                            <div class="ov-total-row final">
                                                <span>مبلغ نهایی (کارشناس)</span>
                                                <span class="ov-total-val"><?= fmt((float)$order['quoted_total']) ?></span>
                                            </div>
                                        <?php else: ?>
                                            <div class="ov-total-row">
                                                <span class="ov-info-label">مبلغ نهایی</span>
                                                <span class="badge badge-warning">در انتظار قیمت‌گذاری</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                            <?php else: ?>
                                <div class="text-center py-4 text-muted">
                                    <i class="fa fa-inbox fa-2x d-block mb-2"></i>
                                    ریز اقلام ثبت نشده
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="ov-card">
                        <div class="ov-card-head">
                            <i class="flaticon-381-list" style="background:#6366f1"></i>
                            <h6>اطلاعات سفارش</h6>
                            <span class="mr-auto">
                                <span class="ov-status-pill badge-<?= $sInfo['cls'] ?>"
                                      style="background: #3c910f)">
                                    <i class="fa-solid <?= $sInfo['icon'] ?>"></i>
                                    <?= htmlspecialchars($sInfo['label'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </span>
                        </div>
                        <div class="ov-card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="ov-info-row">
                                        <span class="ov-info-label">شماره سفارش</span>
                                        <span class="ov-info-value">#<?= $id ?></span>
                                    </div>
                                    <div class="ov-info-row">
                                        <span class="ov-info-label">عنوان سفارش</span>
                                        <span class="ov-info-value rtl-val">
                                            <?= htmlspecialchars($order['order_name'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </div>
                                    <div class="ov-info-row">
                                        <span class="ov-info-label">تاریخ ثبت</span>
                                        <span class="ov-info-value">
                                            <?= jdate("Y/m/d H:i", strtotime($order['created_at'])) ?>
                                        </span>
                                    </div>
                                    <?php if (!empty($order['updated_at']) && $order['updated_at'] !== $order['created_at']): ?>
                                        <div class="ov-info-row">
                                            <span class="ov-info-label">آخرین ویرایش</span>
                                            <span class="ov-info-value">
                                            <?= jdate("Y/m/d H:i", strtotime($order['updated_at'])) ?>
                                        </span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <div class="ov-info-row">
                                        <span class="ov-info-label">نام مشتری</span>
                                        <span class="ov-info-value rtl-val">
                                            <?= htmlspecialchars($order['full_name'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </div>
                                    <div class="ov-info-row">
                                        <span class="ov-info-label">موبایل</span>
                                        <span class="ov-info-value">
                                            <?= htmlspecialchars($order['phone'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </div>
                                    <?php if (!empty($order['email'])): ?>
                                        <div class="ov-info-row">
                                            <span class="ov-info-label">ایمیل</span>
                                            <span class="ov-info-value">
                                            <?= htmlspecialchars($order['email'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($order['address'])): ?>
                                        <div class="ov-info-row">
                                            <span class="ov-info-label">آدرس</span>
                                            <span class="ov-info-value rtl-val">
                                            <?= htmlspecialchars($order['address'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($order['postal_code'])): ?>
                                        <div class="ov-info-row">
                                            <span class="ov-info-label">کدپستی</span>
                                            <span class="ov-info-value">
                                            <?= htmlspecialchars($order['postal_code'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($order['note']) || !empty($order['admin_note'])): ?>
                        <div class="ov-card">
                            <div class="ov-card-head">
                                <i class="fa fa-note-sticky" style="background:#f59e0b"></i>
                                <h6>یادداشت‌ها</h6>
                            </div>
                            <div class="ov-card-body">
                                <?php if (!empty($order['note'])): ?>
                                    <p class="small text-muted mb-2">یادداشت مشتری:</p>
                                    <div class="ov-note-box mb-3">
                                        <?= nl2br(htmlspecialchars($order['note'], ENT_QUOTES, 'UTF-8')) ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($order['admin_note'])): ?>
                                    <p class="small text-muted mb-2">یادداشت کارشناس:</p>
                                    <div class="ov-note-box ov-admin-note">
                                        <?= nl2br(htmlspecialchars($order['admin_note'], ENT_QUOTES, 'UTF-8')) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>

                <div>

                    <div class="ov-card">
                        <div class="ov-card-head">
                            <i class="fa fa-list-check" style="background:#8b5cf6"></i>
                            <h6>مراحل سفارش</h6>
                        </div>
                        <div class="ov-card-body">
                            <div class="ov-timeline">
                                <?php
                                $steps = [
                                    1 => ['label' => 'ثبت سفارش',          'icon' => 'fa-cart-plus',      'col' => '#6366f1'],
                                    2 => ['label' => 'قیمت‌دهی شد',        'icon' => 'fa-tags',           'col' => '#0ea5e9'],
                                    3 => ['label' => 'در انتظار پرداخت',   'icon' => 'fa-credit-card',    'col' => '#f59e0b'],
                                    4 => ['label' => 'رسید بررسی شد',      'icon' => 'fa-magnifying-glass','col' => '#8b5cf6'],
                                    5 => ['label' => 'تأیید و آماده ارسال','icon' => 'fa-box-check',      'col' => '#10b981'],
                                    6 => ['label' => 'ارسال شد',           'icon' => 'fa-truck',          'col' => '#10b981'],
                                ];
                                $showUpTo = min($sid + 1, 6);
                                foreach ($steps as $step => $s):
                                    if ($step > $showUpTo) break;
                                    $done    = ($step <= $sid);
                                    $current = ($step === $sid);
                                    ?>
                                    <div class="ov-tl-item">
                                        <div class="ov-tl-line"></div>
                                        <div class="ov-tl-dot"
                                             style="background:<?= $done ? $s['col'] : '#e9ecef' ?>;
                                                 color:<?= $done ? '#fff' : '#9ca3af' ?>">
                                            <i class="fas fa fa-solid <?= $s['icon'] ?>" style="font-size:.75rem"></i>
                                        </div>
                                        <div class="ov-tl-content">
                                            <strong style="color:<?= $current ? $s['col'] : ($done ? 'inherit' : '#9ca3af') ?>">
                                                <?= $s['label'] ?>
                                                <?php if ($current): ?>
                                                    <span class="badge badge-<?= $sInfo['cls'] ?> mr-1" style="font-size:.65rem">فعلی</span>
                                                <?php endif; ?>
                                            </strong>
                                            <?php if ($current): ?>
                                                <small><?= jdate("Y/m/d", strtotime($order['updated_at'] ?? $order['created_at'])) ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="ov-card">
                        <div class="ov-card-head">
                            <i class="fa fa-file-image" style="background:<?= $hasRcpt ? '#10b981' : '#9ca3af' ?>"></i>
                            <h6>رسید پرداخت</h6>
                        </div>
                        <div class="ov-card-body">
                            <?php if ($hasRcpt):
                                $rcptUrl  = '../../' . $order['receipt_path'];
                                $rcptExt  = strtolower(pathinfo($order['receipt_path'], PATHINFO_EXTENSION));
                                $isPdf    = ($rcptExt === 'pdf');
                                ?>
                                <?php if ($isPdf): ?>
                                <div class="text-center py-3">
                                    <i class="fa fa-file-pdf fa-3x text-danger mb-2 d-block"></i>
                                    <a href="<?= htmlspecialchars($rcptUrl, ENT_QUOTES, 'UTF-8') ?>"
                                       target="_blank" class="btn btn-outline-danger btn-sm">
                                        <i class="fa fa-eye ml-1"></i>مشاهده PDF
                                    </a>
                                </div>
                            <?php else: ?>
                                <a href="<?= htmlspecialchars($rcptUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank">
                                    <img src="<?= htmlspecialchars($rcptUrl, ENT_QUOTES, 'UTF-8') ?>"
                                         class="ov-receipt-img" alt="رسید پرداخت">
                                </a>
                                <a href="<?= htmlspecialchars($rcptUrl, ENT_QUOTES, 'UTF-8') ?>"
                                   target="_blank"
                                   class="btn btn-outline-success btn-block btn-sm mt-3">
                                    <i class="fa fa-download ml-1"></i>دانلود / نمایش کامل
                                </a>
                            <?php endif; ?>
                            <?php else: ?>
                                <div class="text-center py-4 text-muted">
                                    <i class="fa fa-file-circle-xmark fa-2x d-block mb-2" style="color:#d1d5db"></i>
                                    رسیدی بارگذاری نشده
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="ov-card">
                        <div class="ov-card-head">
                            <i class="fa fa-bolt" style="background:#f59e0b"></i>
                            <h6>عملیات سریع</h6>
                        </div>
                        <div class="ov-card-body" style="display:flex;flex-direction:column;gap:10px">
                            <a href="order_edit.php?id=<?= $id ?>" class="btn btn-primary btn-block">
                                <i class="fa fa-edit ml-2"></i>ویرایش و تغییر وضعیت
                            </a>
                            <a href="order_list.php" class="btn btn-light btn-block">
                                <i class="fa fa-list ml-2"></i>بازگشت به لیست
                            </a>
                            <button onclick="window.print()" class="btn btn-light btn-block">
                                <i class="fa fa-print ml-2"></i>چاپ این صفحه
                            </button>
                            <hr class="my-1">
                            <a href="order_list.php?delete=<?= $id ?>"
                               onclick="return confirm('سفارش #<?= $id ?> حذف شود؟')"
                               class="btn btn-outline-danger btn-block">
                                <i class="fa fa-trash ml-2"></i>حذف سفارش
                            </a>
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </div>

    <?php require_once "inc/footer.php"; ?>

</div>

<script src="vendor/global/global.min.js"></script>
<script src="vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
<script src="js/custom.min.js"></script>
</body>
</html>