<?php
require_once "inc/check.php";

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: user_list.php");
    exit;
}

$stmt = $mysqli->prepare("SELECT * FROM user WHERE id = ? AND deleted = 0 LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    header("Location: user_list.php");
    exit;
}

/* recent orders placed by this user */
$oStmt = $mysqli->prepare("
    SELECT o.id, o.order_name, o.grand_total, o.quoted_total, o.order_status_id, o.created_at, s.status_name
    FROM   orders o
    LEFT   JOIN order_status s ON s.id = o.order_status_id
    WHERE  o.user_id = ? AND o.deleted = 0
    ORDER BY o.created_at DESC
    LIMIT 10
");
$oStmt->bind_param("i", $id);
$oStmt->execute();
$orders = $oStmt->get_result()->fetch_all(MYSQLI_ASSOC);

$ordersCountStmt = $mysqli->prepare("SELECT COUNT(*) c FROM orders WHERE user_id = ? AND deleted = 0");
$ordersCountStmt->bind_param("i", $id);
$ordersCountStmt->execute();
$ordersCount = (int) $ordersCountStmt->get_result()->fetch_assoc()['c'];

$msgCountStmt = $mysqli->prepare("SELECT COUNT(*) c FROM contact_messages WHERE user_id = ? AND deleted = 0");
$msgCountStmt->bind_param("i", $id);
$msgCountStmt->execute();
$msgCount = (int) $msgCountStmt->get_result()->fetch_assoc()['c'];

$success = false;
$errors  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_save'])) {

    $name           = trim(   $_POST['name']            ?? '');
    $mobile         = trim(   $_POST['mobile']           ?? '');
    $email          = trim(   $_POST['email']            ?? '');
    $description    = trim(   $_POST['description']      ?? '');
    $mobileVerified = (int)  ($_POST['mobile_verified']  ?? 0);
    $newPassword    = trim(   $_POST['new_password']     ?? '');
    $forceLogout    = isset($_POST['force_logout']);

    /* validation */
    if ($name === '')   $errors[] = 'نام کاربر الزامی است';
    if ($mobile === '') $errors[] = 'شماره موبایل الزامی است';
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'فرمت ایمیل صحیح نیست';
    }
    if ($newPassword !== '' && strlen($newPassword) < 6) {
        $errors[] = 'رمز عبور جدید باید حداقل ۶ کاراکتر باشد';
    }

    /* make sure mobile isn't already used by another account */
    if (empty($errors)) {
        $dupStmt = $mysqli->prepare("SELECT id FROM user WHERE mobile = ? AND id != ? AND deleted = 0 LIMIT 1");
        $dupStmt->bind_param("si", $mobile, $id);
        $dupStmt->execute();
        if ($dupStmt->get_result()->fetch_assoc()) {
            $errors[] = 'این شماره موبایل قبلاً برای کاربر دیگری ثبت شده است';
        }
    }

    if (empty($errors)) {

        if ($newPassword !== '') {
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            $upd  = $mysqli->prepare("
                UPDATE user SET
                    name = ?, mobile = ?, email = ?, description = ?,
                    mobile_verified = ?, password = ?, updated_at = NOW()
                    " . ($forceLogout ? ", is_login = 0, session_token = NULL, session_expires = NULL" : "") . "
                WHERE id = ? AND deleted = 0
            ");
            $upd->bind_param("ssssisi", $name, $mobile, $email, $description, $mobileVerified, $hash, $id);
        } else {
            $upd = $mysqli->prepare("
                UPDATE user SET
                    name = ?, mobile = ?, email = ?, description = ?,
                    mobile_verified = ?, updated_at = NOW()
                    " . ($forceLogout ? ", is_login = 0, session_token = NULL, session_expires = NULL" : "") . "
                WHERE id = ? AND deleted = 0
            ");
            $upd->bind_param("sssiii", $name, $mobile, $email, $description, $mobileVerified, $id);
        }

        if ($upd->execute()) {
            header("Location: user_view.php?id={$id}&updated=1");
            exit;
        } else {
            $errors[] = 'خطا در پایگاه داده';
        }
    }

    $user = array_merge($user, [
        'name'            => $name,
        'mobile'          => $mobile,
        'email'           => $email,
        'description'     => $description,
        'mobile_verified' => $mobileVerified,
    ]);
}

$isOnline = (int) $user['is_login'];

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
    <!-- Favicon icon -->
    <link href="css/fontawesome.css" rel="stylesheet">
    <link rel="icon" type="image/png" sizes="16x16" href="images/favicon.png">
    <link href="vendor/jqvmap/css/jqvmap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="vendor/chartist/css/chartist.min.css">
    <!-- Vectormap -->
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

        .status-grid  { display:grid; grid-template-columns:repeat(2,1fr); gap:10px; }
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

        .ed-totals    { background:#f8f9fa;border-radius:10px;padding:14px;
            border:1px solid #e9ecef;font-size:.875rem; }
        .ed-total-row { display:flex;justify-content:space-between;padding:5px 0;
            border-bottom:1px dashed #e9ecef; }
        .ed-total-row:last-child{ border:none;font-weight:700;font-size:1rem;padding-top:10px; }

        .ed-save-bar  { position:sticky;bottom:0;z-index:50;background:#fff;
            border-top:2px solid #e9ecef;padding:14px 0;
            display:flex;gap:12px;align-items:center; }

        .form-check-switch { display:flex; align-items:center; gap:10px; padding:12px 14px;
            background:#fef2f2; border:1px solid #fecaca; border-radius:10px; font-size:.85rem; }
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
                <h4>ویرایش کاربر #<?= $id ?></h4>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="user_list.php">کاربران</a></li>
                    <li class="breadcrumb-item"><a href="user_view.php?id=<?= $id ?>"><?= val('name', $user) ?></a></li>
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

            <!-- ══ FORM ══ -->
            <form method="post" id="editForm">
                <input type="hidden" name="_save" value="1">

                <div class="ed-grid">

                    <div>

                        <div class="ed-card">
                            <div class="ed-card-head">
                                <i class="fa fa-shield-alt" style="background:#8b5cf6"></i>
                                <h6>وضعیت تایید موبایل</h6>
                            </div>
                            <div class="ed-card-body">
                                <div class="status-grid">
                                    <?php
                                    $verOpts = [
                                        1 => ['label' => 'تایید شده',  'icon' => 'fa-circle-check', 'col' => '#10b981'],
                                        0 => ['label' => 'تایید نشده', 'icon' => 'fa-circle-xmark',  'col' => '#f59e0b'],
                                    ];
                                    foreach ($verOpts as $vVal => $v): ?>
                                        <div class="status-opt" style="--sc:<?= $v['col'] ?>">
                                            <input type="radio"
                                                   name="mobile_verified"
                                                   id="ver<?= $vVal ?>"
                                                   value="<?= $vVal ?>"
                                                <?= ((int) $user['mobile_verified'] === $vVal) ? 'checked' : '' ?>>
                                            <label for="ver<?= $vVal ?>">
                                                <i class="fa-solid fa fas fab <?= $v['icon'] ?>"></i>
                                                <?= $v['label'] ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <div class="ed-card">
                            <div class="ed-card-head">
                                <i class="fa fa-user" style="background:#6366f1"></i>
                                <h6>اطلاعات حساب</h6>
                            </div>
                            <div class="ed-card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="ed-field">
                                            <label class="ed-label" for="name">نام و نام‌خانوادگی <span>*</span></label>
                                            <input class="ed-input" id="name" name="name"
                                                   type="text" value="<?= val('name', $user) ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="ed-field">
                                            <label class="ed-label" for="mobile">شماره موبایل <span>*</span></label>
                                            <input class="ed-input" id="mobile" name="mobile"
                                                   type="tel" dir="ltr"
                                                   value="<?= val('mobile', $user) ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="ed-field">
                                            <label class="ed-label" for="email">ایمیل</label>
                                            <input class="ed-input" id="email" name="email"
                                                   type="email" dir="ltr"
                                                   value="<?= val('email', $user) ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="ed-field">
                                            <label class="ed-label" for="new_password">رمز عبور جدید</label>
                                            <input class="ed-input" id="new_password" name="new_password"
                                                   type="password" dir="ltr" autocomplete="new-password"
                                                   placeholder="خالی بگذارید تا تغییر نکند">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="ed-field mb-0">
                                            <label class="ed-label" for="description">توضیحات</label>
                                            <textarea class="ed-input" id="description" name="description"
                                                      rows="3"><?= val('description', $user) ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if ($isOnline): ?>
                            <div class="ed-card">
                                <div class="ed-card-head">
                                    <i class="fa fa-signal" style="background:#ef4444"></i>
                                    <h6>نشست فعال</h6>
                                </div>
                                <div class="ed-card-body">
                                    <div class="form-check-switch">
                                        <input type="checkbox" name="force_logout" id="force_logout" value="1">
                                        <label for="force_logout" class="mb-0">
                                            این کاربر در حال حاضر یک نشست فعال (لاگین) دارد. با فعال کردن این گزینه،
                                            نشست او هنگام ذخیره پاک شده و باید دوباره وارد شود.
                                        </label>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($orders): ?>
                            <div class="ed-card">
                                <div class="ed-card-head">
                                    <i class="fa fa-shopping-cart" style="background:#10b981"></i>
                                    <h6>سفارش‌های این کاربر</h6>
                                    <span class="badge badge-success mr-auto"><?= $ordersCount ?> سفارش</span>
                                </div>
                                <div style="overflow-x:auto">
                                    <table class="ed-items">
                                        <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>عنوان سفارش</th>
                                            <th>وضعیت</th>
                                            <th class="text-left">مبلغ</th>
                                            <th>تاریخ</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ($orders as $o):
                                            $amt = $o['quoted_total'] !== null && $o['quoted_total'] > 0
                                                ? $o['quoted_total'] : $o['grand_total'];
                                            ?>
                                            <tr>
                                                <td class="text-muted">#<?= $o['id'] ?></td>
                                                <td><strong><?= htmlspecialchars($o['order_name'] ?: ('سفارش #' . $o['id']), ENT_QUOTES, 'UTF-8') ?></strong></td>
                                                <td><span class="badge badge-secondary"><?= htmlspecialchars($o['status_name'] ?: '-', ENT_QUOTES, 'UTF-8') ?></span></td>
                                                <td style="direction:ltr;text-align:left">
                                                    <?= $amt > 0 ? number_format((int) $amt) . ' ت' : '—' ?>
                                                </td>
                                                <td><?= jdate("Y/m/d", strtotime($o['created_at'])) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endif; ?>

                    </div>
                    <div>

                        <div class="ed-card">
                            <div class="ed-card-head">
                                <i class="fa fa-circle-info" style="background:#6366f1"></i>
                                <h6>خلاصه کاربر</h6>
                            </div>
                            <div class="ed-card-body" style="font-size:.85rem">
                                <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px dashed #f0f0f0">
                                    <span class="text-muted">شناسه کاربر</span>
                                    <strong>#<?= $id ?></strong>
                                </div>
                                <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px dashed #f0f0f0">
                                    <span class="text-muted">تاریخ عضویت</span>
                                    <span><?= jdate("Y/m/d", strtotime($user['created_at'])) ?></span>
                                </div>
                                <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px dashed #f0f0f0">
                                    <span class="text-muted">وضعیت نشست</span>
                                    <span><?= $isOnline ? '<span class="badge badge-primary">آنلاین</span>' : '<span class="badge badge-secondary">آفلاین</span>' ?></span>
                                </div>
                                <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px dashed #f0f0f0">
                                    <span class="text-muted">تعداد سفارش‌ها</span>
                                    <span><?= $ordersCount ?> سفارش</span>
                                </div>
                                <div style="display:flex;justify-content:space-between;padding:6px 0">
                                    <span class="text-muted">پیام‌های تماس</span>
                                    <span><?= $msgCount ?> پیام</span>
                                </div>
                            </div>
                        </div>

                        <div class="ed-card">
                            <div class="ed-card-body" style="display:flex;flex-direction:column;gap:10px">
                                <button type="submit" class="btn btn-primary btn-block btn-lg">
                                    <i class="fa fa-save ml-2"></i>ذخیره تغییرات
                                </button>
                                <a href="user_view.php?id=<?= $id ?>" class="btn btn-light btn-block">
                                    <i class="fa fa-eye ml-2"></i>مشاهده کاربر
                                </a>
                                <a href="user_list.php" class="btn btn-light btn-block">
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
                    <a href="user_view.php?id=<?= $id ?>" class="btn btn-light">انصراف</a>
                    <span class="text-muted small mr-auto">
                        آخرین ویرایش:
                        <?= jdate("Y/m/d H:i", strtotime($user['updated_at'] ?? $user['created_at'])) ?>
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

<!-- Chart piety plugin files -->
<script src="vendor/peity/jquery.peity.min.js"></script>

<!-- Apex Chart -->
<script src="vendor/apexchart/apexchart.js"></script>

<!-- Dashboard 1 -->
<script src="js/dashboard/dashboard-1.js"></script>
<script>
    (function () {
        'use strict';

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