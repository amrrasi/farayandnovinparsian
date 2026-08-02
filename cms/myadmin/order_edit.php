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

$iStmt = $mysqli->prepare("SELECT * FROM order_items WHERE order_id = ? ORDER BY id ASC");
$iStmt->bind_param("i", $id);
$iStmt->execute();
$items = $iStmt->get_result()->fetch_all(MYSQLI_ASSOC);

$success = false;
$errors  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_save'])) {

    $newStatus   = (int)   ($_POST['order_status_id'] ?? 0);
    $quotedTotal = (float) ($_POST['quoted_total']    ?? 0);
    $adminNote   = trim(   $_POST['admin_note']       ?? '');
    $fullName    = trim(   $_POST['full_name']        ?? '');
    $phone       = trim(   $_POST['phone']            ?? '');
    $email       = trim(   $_POST['email']            ?? '');
    $address     = trim(   $_POST['address']          ?? '');
    $postalCode  = trim(   $_POST['postal_code']      ?? '');
    $orderName   = trim(   $_POST['order_name']       ?? '');


    if ($newStatus < 1 || $newStatus > 6) $errors[] = 'وضعیت نامعتبر است';
    if ($fullName === '')                  $errors[] = 'نام مشتری الزامی است';
    if ($phone === '')                     $errors[] = 'شماره موبایل الزامی است';

    $newReceiptPath = $order['receipt_path'];
    if (!empty($_FILES['receipt_file']['name'])) {
        $allowed  = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
        $finfo    = finfo_open(FILEINFO_MIME_TYPE);
        $mime     = finfo_file($finfo, $_FILES['receipt_file']['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowed, true)) {
            $errors[] = 'فرمت رسید مجاز نیست (JPG، PNG، WebP، PDF)';
        } elseif ($_FILES['receipt_file']['size'] > 5 * 1024 * 1024) {
            $errors[] = 'حجم رسید بیش از ۵ مگابایت است';
        } else {
            $uploadDir = __DIR__ . '/../assets/images/receipts/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $ext     = strtolower(pathinfo($_FILES['receipt_file']['name'], PATHINFO_EXTENSION));
            $fname   = 'receipt_' . $id . '_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['receipt_file']['tmp_name'], $uploadDir . $fname)) {
                $newReceiptPath = 'receipts/' . $fname;
            } else {
                $errors[] = 'خطا در ذخیره فایل رسید';
            }
        }
    }

    if (empty($errors)) {
        $upd = $mysqli->prepare("
            UPDATE orders SET
                order_status_id = ?,
                quoted_total    = ?,
                admin_note      = ?,
                full_name       = ?,
                phone           = ?,
                email           = ?,
                address         = ?,
                postal_code     = ?,
                order_name      = ?,
                receipt_path    = ?,
                visited         = 1,
                updated_at      = NOW()
            WHERE id = ? AND deleted = 0
        ");
        $upd->bind_param(
            "idssssssssi",
            $newStatus, $quotedTotal, $adminNote,
            $fullName, $phone, $email, $address, $postalCode, $orderName,
            $newReceiptPath, $id
        );

        if ($upd->execute()) {
            header("Location: order_view.php?id={$id}&updated=1");
            exit;
        } else {
            $errors[] = 'خطا در پایگاه داده';
        }
    }

    $order = array_merge($order, [
        'order_status_id' => $newStatus,
        'quoted_total'    => $quotedTotal,
        'admin_note'      => $adminNote,
        'full_name'       => $fullName,
        'phone'           => $phone,
        'email'           => $email,
        'address'         => $address,
        'postal_code'     => $postalCode,
        'order_name'      => $orderName,
    ]);
}

$hasRcpt  = !empty($order['receipt_path']);
$hasQuote = (float) $order['quoted_total'] > 0;
$sid      = (int) $order['order_status_id'];

$statusColors = [
    1 => '#f59e0b', 2 => '#0ea5e9', 3 => '#6366f1',
    4 => '#10b981', 5 => '#8b5cf6', 6 => '#ef4444',
];

function val(string $key, array $arr): string {
    return htmlspecialchars($arr[$key] ?? '', ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= setting('name') ?></title>

    <link href="css/fontawesome.css" rel="stylesheet">
    <link rel="icon" type="image/png" sizes="16x16" href="images/favicon.png">
    <link href="vendor/jqvmap/css/jqvmap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="vendor/chartist/css/chartist.min.css">

    <link href="vendor/jqvmap/css/jqvmap.min.css" rel="stylesheet">
    <link href="vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="vendor/owl-carousel/owl.carousel.css" rel="stylesheet">

    <style>
        .ed-grid      { display:grid; grid-template-columns:1fr 320px; gap:24px; align-items:start; }
        @media(max-width:992px){ .ed-grid { grid-template-columns:1fr; } }

        .ed-card      { border-radius:14px; border:1px solid #e9ecef; background:#fff;
            margin-bottom:20px; overflow:hidden; }
        .ed-card-head { display:flex; align-items:center; gap:10px; padding:14px 20px;
            background:#f8f9fa; border-bottom:1px solid #e9ecef; }
        .ed-card-head i{ width:32px;height:32px;border-radius:9px;display:flex;align-items:center;
            justify-content:center;color:#fff;font-size:.85rem;flex-shrink:0; }
        .ed-card-head h6{ margin:0;font-weight:700;font-size:.9rem; }
        .ed-card-body { padding:22px; }

        .ed-field     { margin-bottom:18px; }
        .ed-label     { display:block; font-size:.8rem; font-weight:600;
            color:#374151; margin-bottom:6px; }
        .ed-label span{ color:#ef4444; margin-right:2px; }
        .ed-input     { width:100%; padding:10px 14px; border:1px solid #d1d5db;
            border-radius:9px; font-size:.875rem; font-family:inherit;
            transition:border-color .2s, box-shadow .2s; background:#fff; }
        .ed-input:focus{ outline:none; border-color:#6366f1;
            box-shadow:0 0 0 3px rgba(99,102,241,.12); }
        textarea.ed-input{ resize:vertical; min-height:80px; }

        .status-grid  { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; }
        .status-opt   { position:relative; }
        .status-opt input[type=radio]{ position:absolute; opacity:0; width:0; height:0; }
        .status-opt label{
            display:flex; flex-direction:column; align-items:center; justify-content:center;
            gap:6px; padding:12px 8px; border-radius:10px; border:2px solid #e9ecef;
            cursor:pointer; font-size:.78rem; font-weight:600; text-align:center;
            transition:.2s; background:#fafafa; color:#6b7280;
        }
        .status-opt label i{ font-size:1.1rem; }
        .status-opt input:checked + label{
            border-color:var(--sc);
            background:color-mix(in srgb, var(--sc) 10%, white);
            color:var(--sc);
            box-shadow:0 0 0 3px color-mix(in srgb, var(--sc) 15%, transparent);
        }
        .status-opt label:hover{ border-color:var(--sc); color:var(--sc); }

        .ed-items     { width:100%; border-collapse:collapse; font-size:.85rem; }
        .ed-items th  { background:#f8f9fa; padding:9px 12px; font-weight:700;
            font-size:.75rem; color:#6b7280; border-bottom:2px solid #e9ecef; }
        .ed-items td  { padding:10px 12px; border-bottom:1px solid #f3f4f6; vertical-align:middle; }
        .ed-items tbody tr:last-child td{ border-bottom:none; }

        .ed-drop-zone { border:2px dashed #d1d5db; border-radius:12px; padding:28px 20px;
            text-align:center; cursor:pointer; transition:.25s; position:relative; }
        .ed-drop-zone:hover,
        .ed-drop-zone.drag-over{ border-color:#6366f1; background:rgba(99,102,241,.04); }
        .ed-drop-zone input{ position:absolute;inset:0;opacity:0;cursor:pointer; }
        .ed-drop-icon { width:52px;height:52px;border-radius:50%;background:#ede9fe;
            display:flex;align-items:center;justify-content:center;
            margin:0 auto 10px;color:#6366f1;font-size:1.3rem; }
        .ed-drop-preview{ margin-top:14px;padding:12px 14px;border-radius:10px;
            background:#ecfdf5;border:1px solid #a7f3d0;
            display:flex;align-items:center;gap:10px; }
        .ed-drop-preview i{ color:#10b981;font-size:1.2rem; }
        .ed-drop-preview span{ font-size:.85rem;font-weight:600; }

        .ed-rcpt-thumb{ width:100%;border-radius:10px;border:1px solid #e9ecef;
            max-height:200px;object-fit:contain; }

        .ed-totals    { background:#f8f9fa;border-radius:10px;padding:14px;
            border:1px solid #e9ecef;font-size:.875rem; }
        .ed-total-row { display:flex;justify-content:space-between;padding:5px 0;
            border-bottom:1px dashed #e9ecef; }
        .ed-total-row:last-child{ border:none;font-weight:700;font-size:1rem;padding-top:10px; }

        .ed-save-bar  { position:sticky;bottom:0;z-index:50;background:#fff;
            border-top:2px solid #e9ecef;padding:14px 0;
            display:flex;gap:12px;align-items:center; }
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
                <h4>ویرایش سفارش #<?= $id ?></h4>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="order_list.php">سفارشات</a></li>
                    <li class="breadcrumb-item"><a href="order_view.php?id=<?= $id ?>">سفارش #<?= $id ?></a></li>
                    <li class="breadcrumb-item active">ویرایش</li>
                </ol>
            </div>

            <?php if ($errors): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <ul class="mb-0">
                        <?php foreach ($errors as $e): ?>
                            <li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            <?php endif; ?>


            <form method="post" enctype="multipart/form-data" id="editForm">
                <input type="hidden" name="_save" value="1">

                <div class="ed-grid">

                    <div>

                        <div class="ed-card">
                            <div class="ed-card-head">
                                <i class="fa fa-list" style="background:#8b5cf6"></i>
                                <h6>وضعیت سفارش</h6>
                            </div>
                            <div class="ed-card-body">
                                <div class="status-grid">
                                    <?php
                                    $statuses = [
                                        1 => ['label' => 'در انتظار قیمت',   'icon' => 'fa-clock',          'col' => '#f59e0b'],
                                        2 => ['label' => 'در انتظار پرداخت', 'icon' => 'fa-credit-card',    'col' => '#0ea5e9'],
                                        3 => ['label' => 'در حال بررسی',     'icon' => 'fa-magnifying-glass','col' => '#6366f1'],
                                        4 => ['label' => 'تأیید شده',        'icon' => 'fa-circle-check',   'col' => '#10b981'],
                                        5 => ['label' => 'ارسال شده',        'icon' => 'fa-truck',          'col' => '#8b5cf6'],
                                        6 => ['label' => 'آرشیو / لغو',      'icon' => 'fa-box-archive',    'col' => '#ef4444'],
                                    ];
                                    foreach ($statuses as $sVal => $s): ?>
                                        <div class="status-opt" style="--sc:<?= $s['col'] ?>">
                                            <input type="radio"
                                                   name="order_status_id"
                                                   id="st<?= $sVal ?>"
                                                   value="<?= $sVal ?>"
                                                <?= ($sid === $sVal) ? 'checked' : '' ?>>
                                            <label for="st<?= $sVal ?>">
                                                <i class="fa-solid fa fas fab <?= $s['icon'] ?>"></i>
                                                <?= $s['label'] ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <div class="ed-card">
                            <div class="ed-card-head">
                                <i class="fa fa-user" style="background:#6366f1"></i>
                                <h6>اطلاعات مشتری</h6>
                            </div>
                            <div class="ed-card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="ed-field">
                                            <label class="ed-label" for="full_name">نام و نام‌خانوادگی <span>*</span></label>
                                            <input class="ed-input" id="full_name" name="full_name"
                                                   type="text" value="<?= val('full_name', $order) ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="ed-field">
                                            <label class="ed-label" for="phone">شماره موبایل <span>*</span></label>
                                            <input class="ed-input" id="phone" name="phone"
                                                   type="tel" dir="ltr"
                                                   value="<?= val('phone', $order) ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="ed-field">
                                            <label class="ed-label" for="email">ایمیل</label>
                                            <input class="ed-input" id="email" name="email"
                                                   type="email" dir="ltr"
                                                   value="<?= val('email', $order) ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="ed-field">
                                            <label class="ed-label" for="postal_code">کدپستی</label>
                                            <input class="ed-input" id="postal_code" name="postal_code"
                                                   type="text" dir="ltr"
                                                   value="<?= val('postal_code', $order) ?>">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="ed-field">
                                            <label class="ed-label" for="address">آدرس</label>
                                            <textarea class="ed-input" id="address" name="address"
                                                      rows="2"><?= val('address', $order) ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="ed-card">
                            <div class="ed-card-head">
                                <i class="fa fa-calculator" style="background:#0ea5e9"></i>
                                <h6>جزئیات سفارش</h6>
                            </div>
                            <div class="ed-card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="ed-field">
                                            <label class="ed-label" for="order_name">عنوان / توضیح سفارش</label>
                                            <input class="ed-input" id="order_name" name="order_name"
                                                   type="text" value="<?= val('order_name', $order) ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="ed-field">
                                            <label class="ed-label" for="quoted_total">
                                                مبلغ نهایی (تومان)
                                            </label>
                                            <input class="ed-input" id="quoted_total" name="quoted_total"
                                                   type="number" min="0" dir="ltr"
                                                   value="<?= (float)$order['quoted_total'] > 0 ? (int)$order['quoted_total'] : '' ?>"
                                                   placeholder="اعلام نشده">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="ed-field mb-0">
                                            <label class="ed-label" for="admin_note">
                                                یادداشت کارشناس
                                                <small class="font-weight-normal text-muted mr-1">(برای مشتری نمایش داده می‌شود)</small>
                                            </label>
                                            <textarea class="ed-input" id="admin_note" name="admin_note"
                                                      rows="3"><?= val('admin_note', $order) ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if ($items): ?>
                            <div class="ed-card">
                                <div class="ed-card-head">
                                    <i class="fa fa-list" style="background:#10b981"></i>
                                    <h6>اقلام سفارش</h6>
                                    <span class="badge badge-success mr-auto"><?= count($items) ?> قلم</span>
                                </div>
                                <div style="overflow-x:auto">
                                    <table class="ed-items">
                                        <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>محصول</th>
                                            <th class="text-center">تعداد</th>
                                            <th class="text-left">قیمت واحد</th>
                                            <th class="text-left">جمع</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        $grandCalc = 0;
                                        foreach ($items as $i => $item):
                                            $up  = (float) $item['price'];
                                            $qty = (int)   $item['qty'];
                                            $lt  = $up * $qty;
                                            $grandCalc += $lt;
                                            ?>
                                            <tr>
                                                <td class="text-muted"><?= $i+1 ?></td>
                                                <td><strong><?= htmlspecialchars($item['product_name'] ?? '—', ENT_QUOTES, 'UTF-8') ?></strong></td>
                                                <td class="text-center">× <?= $qty ?></td>
                                                <td style="direction:ltr;text-align:left">
                                                    <?= $up > 0 ? number_format((int)$up) : '—' ?>
                                                </td>
                                                <td style="direction:ltr;text-align:left;color:#10b981;font-weight:600">
                                                    <?= $lt > 0 ? number_format((int)$lt) : '—' ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="ed-card-body pt-2">
                                    <div class="ed-totals">
                                        <?php if ($grandCalc > 0): ?>
                                            <div class="ed-total-row">
                                                <span class="text-muted">جمع کالاها</span>
                                                <span><?= number_format((int)$grandCalc) ?> تومان</span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($hasQuote): ?>
                                            <div class="ed-total-row">
                                                <span class="text-success font-weight-bold">مبلغ نهایی اعلام‌شده</span>
                                                <span class="text-success"><?= number_format((int)$order['quoted_total']) ?> تومان</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                    </div>
                    <div>

                        <div class="ed-card">
                            <div class="ed-card-head">
                                <i class="fa fa-file-image" style="background:<?= $hasRcpt ? '#10b981' : '#9ca3af' ?>"></i>
                                <h6>رسید پرداخت</h6>
                            </div>
                            <div class="ed-card-body">

                                <?php if ($hasRcpt):
                                    $rcptUrl = '../assets/images/' . $order['receipt_path'];
                                    $rcptExt = strtolower(pathinfo($order['receipt_path'], PATHINFO_EXTENSION));
                                    ?>
                                    <p class="small text-muted mb-2">رسید فعلی:</p>
                                    <?php if ($rcptExt === 'pdf'): ?>
                                    <div class="text-center p-3" style="background:#fef2f2;border-radius:10px;border:1px solid #fecaca">
                                        <i class="fa fa-file-pdf fa-2x text-danger d-block mb-2"></i>
                                        <a href="<?= htmlspecialchars($rcptUrl, ENT_QUOTES, 'UTF-8') ?>"
                                           target="_blank" class="btn btn-sm btn-outline-danger">
                                            <i class="fa fa-eye ml-1"></i>مشاهده PDF
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <a href="<?= htmlspecialchars($rcptUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank">
                                        <img src="<?= htmlspecialchars($rcptUrl, ENT_QUOTES, 'UTF-8') ?>"
                                             class="ed-rcpt-thumb" alt="رسید فعلی">
                                    </a>
                                <?php endif; ?>
                                    <hr>
                                    <p class="small text-muted mb-2">جایگزینی رسید (اختیاری):</p>
                                <?php else: ?>
                                    <div class="text-center py-2 text-muted mb-3">
                                        <i class="fa fa-file-circle-xmark fa-2x d-block mb-1" style="color:#d1d5db"></i>
                                        <small>رسیدی بارگذاری نشده</small>
                                    </div>
                                <?php endif; ?>

                                <div class="ed-drop-zone" id="dropZone">
                                    <input type="file" name="receipt_file" id="receiptFile"
                                           accept="image/jpeg,image/png,image/webp,application/pdf">
                                    <div class="ed-drop-icon">
                                        <i class="fa fa-cloud-arrow-up"></i>
                                    </div>
                                    <p class="mb-1 font-weight-600" style="font-size:.875rem">
                                        فایل را اینجا بکشید یا کلیک کنید
                                    </p>
                                    <small class="text-muted">JPG، PNG، WebP یا PDF — حداکثر ۵ مگابایت</small>
                                </div>

                                <div class="ed-drop-preview d-none" id="dropPreview">
                                    <i class="fa fa-file-check"></i>
                                    <span id="dropFileName"></span>
                                </div>

                            </div>
                        </div>

                        <div class="ed-card">
                            <div class="ed-card-head">
                                <i class="fa fa-circle-info" style="background:#6366f1"></i>
                                <h6>خلاصه سفارش</h6>
                            </div>
                            <div class="ed-card-body" style="font-size:.85rem">
                                <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px dashed #f0f0f0">
                                    <span class="text-muted">شماره سفارش</span>
                                    <strong>#<?= $id ?></strong>
                                </div>
                                <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px dashed #f0f0f0">
                                    <span class="text-muted">تاریخ ثبت</span>
                                    <span><?= jdate("Y/m/d", strtotime($order['created_at'])) ?></span>
                                </div>
                                <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px dashed #f0f0f0">
                                    <span class="text-muted">تعداد اقلام</span>
                                    <span><?= count($items) ?> قلم</span>
                                </div>
                                <?php if (!empty($order['note'])): ?>
                                    <div style="padding:10px 0">
                                        <span class="text-muted d-block mb-1" style="font-size:.78rem">یادداشت مشتری:</span>
                                        <div style="background:#fffbeb;border-radius:8px;padding:10px;
                                                border:1px solid #fde68a;font-size:.82rem;color:#78350f;line-height:1.6">
                                            <?= nl2br(htmlspecialchars($order['note'], ENT_QUOTES, 'UTF-8')) ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="ed-card">
                            <div class="ed-card-body" style="display:flex;flex-direction:column;gap:10px">
                                <button type="submit" class="btn btn-primary btn-block btn-lg">
                                    <i class="fa fa-save ml-2"></i>ذخیره تغییرات
                                </button>
                                <a href="order_view.php?id=<?= $id ?>" class="btn btn-light btn-block">
                                    <i class="fa fa-eye ml-2"></i>مشاهده سفارش
                                </a>
                                <a href="order_list.php" class="btn btn-light btn-block">
                                    <i class="fa fa-list ml-2"></i>بازگشت به لیست
                                </a>
                            </div>
                        </div>

                    </div>

                </div>

                <div class="ed-save-bar">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save ml-1"></i>ذخیره تغییرات
                    </button>
                    <a href="order_view.php?id=<?= $id ?>" class="btn btn-light">انصراف</a>
                    <span class="text-muted small mr-auto">
                        آخرین ویرایش:
                        <?= jdate("Y/m/d H:i", strtotime($order['updated_at'] ?? $order['created_at'])) ?>
                    </span>
                </div>

            </form>

        </div>
    </div>

    <?php require_once "inc/footer.php"; ?>

</div>

<script src="vendor/global/global.min.js"></script>
<script src="vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
<script src="vendor/chart.js/Chart.bundle.min.js"></script>
<script src="js/custom.min.js"></script>
<script src="js/deznav-init.js"></script>
<script src="vendor/owl-carousel/owl.carousel.js"></script>


<script src="vendor/peity/jquery.peity.min.js"></script>


<script src="vendor/apexchart/apexchart.js"></script>


<script src="js/dashboard/dashboard-1.js"></script>
<script>
    (function () {
        'use strict';

        const zone     = document.getElementById('dropZone');
        const fileIn   = document.getElementById('receiptFile');
        const preview  = document.getElementById('dropPreview');
        const fileName = document.getElementById('dropFileName');

        zone.addEventListener('dragover', e => {
            e.preventDefault();
            zone.classList.add('drag-over');
        });
        zone.addEventListener('dragleave', e => {
            if (!zone.contains(e.relatedTarget)) zone.classList.remove('drag-over');
        });
        zone.addEventListener('drop', e => {
            e.preventDefault();
            zone.classList.remove('drag-over');
            setFile(e.dataTransfer.files[0]);
        });
        fileIn.addEventListener('change', () => setFile(fileIn.files[0]));

        function setFile(file) {
            if (!file) return;
            if (file.size > 5 * 1024 * 1024) {
                alert('حجم فایل بیش از ۵ مگابایت است');
                fileIn.value = '';
                return;
            }
            fileName.textContent = file.name;
            preview.classList.remove('d-none');
            zone.style.borderColor = '#10b981';
        }

        let formDirty = false;
        document.getElementById('editForm').addEventListener('change', () => { formDirty = true; });
        document.getElementById('editForm').addEventListener('submit',  () => { formDirty = false; });
        window.addEventListener('beforeunload', e => {
            if (formDirty) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

    })();
</script>

</body>
</html>