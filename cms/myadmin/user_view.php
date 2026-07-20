<?php
require_once "inc/check.php";

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) { header("Location: user_list.php"); exit; }

/* ── user row ── */
$stmt = $mysqli->prepare("SELECT * FROM user WHERE id = ? AND deleted = 0 LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
if (!$user) { header("Location: user_list.php"); exit; }

/* ── orders ── */
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

$ordersCountStmt = $mysqli->prepare("SELECT COUNT(*) c, COALESCE(SUM(COALESCE(quoted_total, grand_total)),0) t FROM orders WHERE user_id = ? AND deleted = 0");
$ordersCountStmt->bind_param("i", $id);
$ordersCountStmt->execute();
$oAgg = $ordersCountStmt->get_result()->fetch_assoc();
$ordersCount = (int) $oAgg['c'];
$ordersTotal = (float) $oAgg['t'];

/* paid orders total (status 4 or 5) */
$paidStmt = $mysqli->prepare("SELECT COALESCE(SUM(COALESCE(quoted_total, grand_total)),0) t FROM orders WHERE user_id = ? AND deleted = 0 AND order_status_id IN (4,5)");
$paidStmt->bind_param("i", $id);
$paidStmt->execute();
$paidTotal = (float) $paidStmt->get_result()->fetch_assoc()['t'];

/* messages */
$msgStmt = $mysqli->prepare("SELECT COUNT(*) c FROM contact_messages WHERE user_id = ? AND deleted = 0");
$msgStmt->bind_param("i", $id);
$msgStmt->execute();
$msgCount = (int) $msgStmt->get_result()->fetch_assoc()['c'];

/* unread messages */
$unreadStmt = $mysqli->prepare("SELECT COUNT(*) c FROM contact_messages WHERE user_id = ? AND deleted = 0 AND seen = 0");
$unreadStmt->bind_param("i", $id);
$unreadStmt->execute();
$unreadCount = (int) $unreadStmt->get_result()->fetch_assoc()['c'];

/* ── helpers ── */
$isOnline     = (int) $user['is_login'];
$isVerified   = (int) $user['mobile_verified'];
$initials     = mb_strtoupper(mb_substr(trim($user['name']) ?: '?', 0, 2, 'UTF-8'), 'UTF-8');

/* avatar gradient based on id */
$gradients = [
    ['#6366f1','#8b5cf6'], ['#0ea5e9','#6366f1'], ['#10b981','#0ea5e9'],
    ['#f59e0b','#ef4444'], ['#8b5cf6','#ec4899'], ['#ef4444','#f59e0b'],
];
$grad = $gradients[$id % count($gradients)];

$statusMap = [
    1 => ['cls'=>'warning',  'label'=>'در انتظار قیمت‌دهی', 'icon'=>'fa-clock'],
    2 => ['cls'=>'info',     'label'=>'در انتظار پرداخت',    'icon'=>'fa-credit-card'],
    3 => ['cls'=>'primary',  'label'=>'در حال بررسی',        'icon'=>'fa-magnifying-glass'],
    4 => ['cls'=>'success',  'label'=>'تأیید شده',           'icon'=>'fa-circle-check'],
    5 => ['cls'=>'secondary','label'=>'ارسال شده',           'icon'=>'fa-truck'],
    6 => ['cls'=>'danger',   'label'=>'لغو شده',             'icon'=>'fa-box-archive'],
];
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= setting('name') ?> — پروفایل کاربر</title>
    <link href="css/fontawesome.css" rel="stylesheet">
    <link rel="icon" type="image/png" sizes="16x16" href="images/favicon.png">
    <link href="vendor/jqvmap/css/jqvmap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="vendor/chartist/css/chartist.min.css">
    <link href="vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="vendor/owl-carousel/owl.carousel.css" rel="stylesheet">

    <style>
        /* ════════════════════════════════════════
           HERO BANNER
        ════════════════════════════════════════ */
        .uv-hero {
            position: relative;
            border-radius: 18px;
            overflow: hidden;
            margin-bottom: 28px;
            background: linear-gradient(135deg, <?= $grad[0] ?> 0%, <?= $grad[1] ?> 100%);
            padding: 36px 32px 90px;
        }
        .uv-hero::before {
            content: '';
            position: absolute; inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='80' height='80' viewBox='0 0 80 80' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M50 50c0-5.523 4.477-10 10-10s10 4.477 10 10-4.477 10-10 10c0 5.523-4.477 10-10 10s-10-4.477-10-10 4.477-10 10-10zM10 10c0-5.523 4.477-10 10-10s10 4.477 10 10-4.477 10-10 10c0 5.523-4.477 10-10 10S0 25.523 0 20s4.477-10 10-10zm10 8c4.418 0 8-3.582 8-8s-3.582-8-8-8-8 3.582-8 8 3.582 8 8 8zm40 40c4.418 0 8-3.582 8-8s-3.582-8-8-8-8 3.582-8 8 3.582 8 8 8z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .uv-hero-breadcrumb {
            position: relative; z-index: 1;
            display: flex; align-items: center; gap: 8px;
            font-size: .8rem; color: rgba(255,255,255,.7); margin-bottom: 28px;
        }
        .uv-hero-breadcrumb a { color: rgba(255,255,255,.85); text-decoration: none; }
        .uv-hero-breadcrumb a:hover { color: #fff; }
        .uv-hero-breadcrumb .sep { opacity: .5; }
        .uv-hero-actions {
            position: absolute; top: 24px; left: 24px; z-index: 1;
            display: flex; gap: 8px;
        }
        .uv-hero-actions .btn {
            font-size: .8rem; padding: 6px 14px; border-radius: 8px;
            background: rgba(255,255,255,.15); border: 1px solid rgba(255,255,255,.25);
            color: #fff; backdrop-filter: blur(6px);
        }
        .uv-hero-actions .btn:hover { background: rgba(255,255,255,.28); color: #fff; }
        .uv-hero-actions .btn-danger-ghost {
            background: rgba(239,68,68,.25); border-color: rgba(239,68,68,.4);
        }
        .uv-hero-actions .btn-danger-ghost:hover { background: rgba(239,68,68,.45); }

        /* ════════════════════════════════════════
           IDENTITY CARD (overlaps hero bottom)
        ════════════════════════════════════════ */
        .uv-identity {
            position: relative; z-index: 2;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 8px 32px rgba(0,0,0,.10);
            padding: 0;
            margin: -70px 0 24px;
            overflow: hidden;
            display: flex;
            align-items: stretch;
        }
        .uv-id-accent {
            width: 6px; flex-shrink: 0;
            background: linear-gradient(180deg, <?= $grad[0] ?>, <?= $grad[1] ?>);
        }
        .uv-id-body {
            flex: 1; display: flex; align-items: center;
            flex-wrap: wrap; gap: 20px; padding: 24px 28px;
        }
        .uv-avatar-wrap { position: relative; flex-shrink: 0; }
        .uv-avatar {
            width: 80px; height: 80px; border-radius: 50%;
            background: linear-gradient(135deg, <?= $grad[0] ?>, <?= $grad[1] ?>);
            color: #fff; font-size: 1.6rem; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 16px rgba(0,0,0,.18);
            border: 4px solid #fff;
        }
        .uv-online-dot {
            position: absolute; bottom: 4px; right: 4px;
            width: 16px; height: 16px; border-radius: 50%;
            background: #10b981; border: 3px solid #fff;
        }
        .uv-id-info { flex: 1; min-width: 200px; }
        .uv-id-name {
            font-size: 1.35rem; font-weight: 700; color: #1f2937;
            margin-bottom: 4px; display: flex; align-items: center; gap: 10px;
        }
        .uv-id-sub {
            font-size: .82rem; color: #8a8fa3; display: flex;
            flex-wrap: wrap; gap: 14px; margin-top: 8px;
        }
        .uv-id-sub span { display: flex; align-items: center; gap: 5px; }
        .uv-id-sub i { font-size: .8rem; }
        .uv-id-badges { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 10px; }

        /* ════════════════════════════════════════
           KPI STRIP
        ════════════════════════════════════════ */
        .uv-kpi-strip {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }
        @media(max-width:768px){ .uv-kpi-strip { grid-template-columns: repeat(2,1fr); } }
        .uv-kpi {
            background: #fff; border-radius: 14px;
            border: 1px solid #e9ecef;
            padding: 18px 20px;
            display: flex; flex-direction: column;
            transition: transform .2s, box-shadow .2s;
            position: relative; overflow: hidden;
        }
        .uv-kpi:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,.09); }
        .uv-kpi::after {
            content: '';
            position: absolute; top: 0; right: 0;
            width: 4px; height: 100%;
            background: var(--kpi-color, #6366f1);
            border-radius: 0 14px 14px 0;
        }
        .uv-kpi-icon {
            width: 40px; height: 40px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1rem; color: #fff; margin-bottom: 12px;
            background: var(--kpi-color, #6366f1);
        }
        .uv-kpi-val { font-size: 1.5rem; font-weight: 700; color: #1f2937; line-height: 1; }
        .uv-kpi-lbl { font-size: .75rem; color: #8a8fa3; margin-top: 4px; }

        /* ════════════════════════════════════════
           LAYOUT GRID
        ════════════════════════════════════════ */
        .uv-grid {
            display: grid;
            grid-template-columns: 1fr 320px;
            gap: 24px; align-items: start;
        }
        @media(max-width:992px){ .uv-grid { grid-template-columns: 1fr; } }

        /* ════════════════════════════════════════
           SECTION CARDS (same family as ov-card / ed-card)
        ════════════════════════════════════════ */
        .uv-card {
            background: #fff; border-radius: 14px;
            border: 1px solid #e9ecef;
            margin-bottom: 20px; overflow: hidden;
        }
        .uv-card-head {
            display: flex; align-items: center; gap: 10px;
            padding: 14px 20px; background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
        }
        .uv-card-head i {
            width: 32px; height: 32px; border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: .85rem; flex-shrink: 0;
        }
        .uv-card-head h6 { margin: 0; font-weight: 700; font-size: .9rem; }
        .uv-card-body { padding: 20px; }

        /* ── info rows ── */
        .uv-row {
            display: flex; align-items: baseline;
            justify-content: space-between;
            padding: 10px 0; border-bottom: 1px dashed #f0f0f0;
            font-size: .875rem;
        }
        .uv-row:last-child { border-bottom: none; }
        .uv-row-label { color: #8a8fa3; flex-shrink: 0; margin-left: 10px; }
        .uv-row-val { font-weight: 600; }

        /* ── orders table ── */
        .uv-orders { width: 100%; border-collapse: collapse; font-size: .85rem; }
        .uv-orders th {
            background: #f8f9fa; padding: 10px 14px;
            font-size: .75rem; font-weight: 700; color: #6b7280;
            border-bottom: 2px solid #e9ecef; white-space: nowrap;
        }
        .uv-orders td { padding: 11px 14px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
        .uv-orders tbody tr:last-child td { border-bottom: none; }
        .uv-orders tbody tr:hover td { background: #fafafa; }
        .uv-orders .o-price { direction: ltr; text-align: left; font-weight: 600; }

        /* ── activity timeline ── */
        .uv-timeline { display: flex; flex-direction: column; }
        .uv-tl-item { display: flex; gap: 14px; position: relative; padding-bottom: 20px; }
        .uv-tl-item:last-child { padding-bottom: 0; }
        .uv-tl-line {
            position: absolute; right: 15px; top: 32px; bottom: 0;
            width: 2px; background: #e9ecef;
        }
        .uv-tl-item:last-child .uv-tl-line { display: none; }
        .uv-tl-dot {
            width: 32px; height: 32px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: .75rem; flex-shrink: 0; z-index: 1;
        }
        .uv-tl-body { padding-top: 5px; }
        .uv-tl-body strong { display: block; font-size: .85rem; }
        .uv-tl-body small { color: #8a8fa3; font-size: .78rem; }

        /* ── description box ── */
        .uv-desc-box {
            background: #f8f9fa; border: 1px solid #e9ecef;
            border-radius: 10px; padding: 14px 16px;
            font-size: .875rem; line-height: 1.8; color: #374151;
            white-space: pre-line;
        }

        /* ── meta sidebar pill rows ── */
        .uv-meta-pill {
            display: flex; align-items: center; gap: 12px;
            padding: 12px 14px; border-radius: 10px;
            background: #f8f9fa; border: 1px solid #e9ecef;
            margin-bottom: 10px; font-size: .85rem;
        }
        .uv-meta-pill:last-child { margin-bottom: 0; }
        .uv-meta-pill-icon {
            width: 36px; height: 36px; border-radius: 9px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: .85rem;
        }
        .uv-meta-pill-content { flex: 1; min-width: 0; }
        .uv-meta-pill-lbl { font-size: .72rem; color: #8a8fa3; display: block; margin-bottom: 1px; }
        .uv-meta-pill-val { font-weight: 700; color: #1f2937; font-size: .88rem; }

        /* ── quick action buttons ── */
        .uv-actions { display: flex; flex-direction: column; gap: 10px; }
        .uv-actions .btn { border-radius: 10px; font-size: .875rem; padding: 10px 14px; }

        /* ── no-orders placeholder ── */
        .uv-empty {
            text-align: center; padding: 36px 20px;
            color: #9ca3af;
        }
        .uv-empty i { font-size: 2.5rem; display: block; margin-bottom: 10px; }

        /* ── top id badge pill ── */
        .uv-id-pill {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(99,102,241,.1); color: #6366f1;
            border-radius: 50px; padding: 3px 10px; font-size: .75rem; font-weight: 700;
        }

        /* print */
        @media print {
            #main-wrapper > nav, #main-wrapper > .nav-header,
            .uv-hero-actions, .uv-actions, .deznav { display: none !important; }
            .content-body { margin: 0 !important; padding: 0 !important; }
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

            <?php if (isset($_GET['updated'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fa fa-check-circle ml-1"></i>اطلاعات کاربر با موفقیت به‌روزرسانی شد.
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            <?php endif; ?>

            <!-- ══════════════════════════════════
                 HERO
            ══════════════════════════════════ -->
            <div class="uv-hero">
                <div class="uv-hero-breadcrumb">
                    <a href="index.php"><i class="fa fa-home"></i></a>
                    <span class="sep">/</span>
                    <a href="user_list.php">کاربران</a>
                    <span class="sep">/</span>
                    <span><?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?></span>
                </div>

                <div class="uv-hero-actions">
                    <a href="user_edit.php?id=<?= $id ?>" class="btn">
                        <i class="fa fa-edit ml-1"></i>ویرایش
                    </a>
                    <button onclick="window.print()" class="btn">
                        <i class="fa fa-print ml-1"></i>چاپ
                    </button>
                    <a href="user_list.php?delete=<?= $id ?>"
                       onclick="return confirm('کاربر #<?= $id ?> حذف شود؟')"
                       class="btn btn-danger-ghost">
                        <i class="fa fa-trash ml-1"></i>حذف
                    </a>
                </div>
            </div>

            <!-- ══════════════════════════════════
                 IDENTITY CARD
            ══════════════════════════════════ -->
            <div class="uv-identity">
                <div class="uv-id-accent"></div>
                <div class="uv-id-body">
                    <div class="uv-avatar-wrap">
                        <div class="uv-avatar"><?= htmlspecialchars($initials, ENT_QUOTES, 'UTF-8') ?></div>
                        <?php if ($isOnline): ?><div class="uv-online-dot"></div><?php endif; ?>
                    </div>
                    <div class="uv-id-info">
                        <div class="uv-id-name">
                            <?= htmlspecialchars($user['name'] ?: '—', ENT_QUOTES, 'UTF-8') ?>
                            <span class="uv-id-pill"><i class="fa fa-hashtag"></i><?= $id ?></span>
                        </div>
                        <div class="uv-id-sub">
                            <?php if (!empty($user['mobile'])): ?>
                                <span><i class="fa fa-phone"></i><?= htmlspecialchars($user['mobile'], ENT_QUOTES, 'UTF-8') ?></span>
                            <?php endif; ?>
                            <?php if (!empty($user['email'])): ?>
                                <span><i class="fa fa-envelope"></i><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></span>
                            <?php endif; ?>
                            <span><i class="fa fa-calendar"></i>عضو از <?= jdate("Y/m/d", strtotime($user['created_at'])) ?></span>
                        </div>
                        <div class="uv-id-badges">
                            <?php if ($isVerified): ?>
                                <span class="badge badge-success"><i class="fa fa-shield-alt ml-1"></i>موبایل تایید شده</span>
                            <?php else: ?>
                                <span class="badge badge-warning"><i class="fa fa-clock ml-1"></i>موبایل تایید نشده</span>
                            <?php endif; ?>
                            <?php if ($isOnline): ?>
                                <span class="badge badge-primary"><i class="fa fa-circle ml-1" style="font-size:.5rem"></i>آنلاین</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">آفلاین</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ══════════════════════════════════
                 KPI STRIP
            ══════════════════════════════════ -->
            <div class="uv-kpi-strip">
                <div class="uv-kpi" style="--kpi-color:#6366f1">
                    <div class="uv-kpi-icon"><i class="fa fa-shopping-cart"></i></div>
                    <div class="uv-kpi-val"><?= number_format($ordersCount) ?></div>
                    <div class="uv-kpi-lbl">تعداد سفارش‌ها</div>
                </div>
                <div class="uv-kpi" style="--kpi-color:#10b981">
                    <div class="uv-kpi-icon"><i class="fa fa-coins"></i></div>
                    <div class="uv-kpi-val" style="font-size:1.15rem"><?= $paidTotal > 0 ? number_format((int)$paidTotal) : '۰' ?></div>
                    <div class="uv-kpi-lbl">مجموع خرید تأیید‌شده (تومان)</div>
                </div>
                <div class="uv-kpi" style="--kpi-color:#f59e0b">
                    <div class="uv-kpi-icon"><i class="fa fa-envelope"></i></div>
                    <div class="uv-kpi-val"><?= number_format($msgCount) ?></div>
                    <div class="uv-kpi-lbl">پیام‌های تماس</div>
                </div>
                <div class="uv-kpi" style="--kpi-color:<?= $isOnline ? '#10b981' : '#8a8fa3' ?>">
                    <div class="uv-kpi-icon"><i class="fa fa-signal"></i></div>
                    <div class="uv-kpi-val" style="font-size:1rem"><?= $isOnline ? 'آنلاین' : 'آفلاین' ?></div>
                    <div class="uv-kpi-lbl">وضعیت نشست</div>
                </div>
            </div>

            <!-- ══════════════════════════════════
                 MAIN GRID
            ══════════════════════════════════ -->
            <div class="uv-grid">

                <!-- ── RIGHT COLUMN (main content) ── -->
                <div>

                    <!-- ── CONTACT INFO ── -->
                    <div class="uv-card">
                        <div class="uv-card-head">
                            <i class="fa fa-id-card" style="background:#6366f1"></i>
                            <h6>اطلاعات تماس و حساب</h6>
                        </div>
                        <div class="uv-card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="uv-row">
                                        <span class="uv-row-label">نام کامل</span>
                                        <span class="uv-row-val"><?= htmlspecialchars($user['name'] ?: '—', ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>
                                    <div class="uv-row">
                                        <span class="uv-row-label">شماره موبایل</span>
                                        <span class="uv-row-val" dir="ltr"><?= htmlspecialchars($user['mobile'] ?: '—', ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>
                                    <div class="uv-row">
                                        <span class="uv-row-label">ایمیل</span>
                                        <span class="uv-row-val" dir="ltr"><?= htmlspecialchars($user['email'] ?: '—', ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="uv-row">
                                        <span class="uv-row-label">تاریخ عضویت</span>
                                        <span class="uv-row-val"><?= jdate("Y/m/d H:i", strtotime($user['created_at'])) ?></span>
                                    </div>
                                    <div class="uv-row">
                                        <span class="uv-row-label">آخرین بروزرسانی</span>
                                        <span class="uv-row-val"><?= jdate("Y/m/d H:i", strtotime($user['updated_at'] ?? $user['created_at'])) ?></span>
                                    </div>
                                    <div class="uv-row">
                                        <span class="uv-row-label">شناسه کاربری</span>
                                        <span class="uv-row-val">#<?= $id ?></span>
                                    </div>
                                </div>
                            </div>
                            <?php if (!empty($user['description'])): ?>
                                <div class="mt-3">
                                    <p class="text-muted small mb-2">توضیحات:</p>
                                    <div class="uv-desc-box"><?= htmlspecialchars($user['description'], ENT_QUOTES, 'UTF-8') ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- ── ORDERS ── -->
                    <div class="uv-card">
                        <div class="uv-card-head">
                            <i class="fa fa-shopping-cart" style="background:#10b981"></i>
                            <h6>سفارش‌های این کاربر</h6>
                            <span class="badge badge-success mr-auto"><?= $ordersCount ?> سفارش</span>
                        </div>
                        <?php if ($orders): ?>
                            <div style="overflow-x:auto">
                                <table class="uv-orders">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>عنوان سفارش</th>
                                        <th>وضعیت</th>
                                        <th class="text-left">مبلغ</th>
                                        <th>تاریخ</th>
                                        <th width="60"></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($orders as $o):
                                        $sid  = (int) $o['order_status_id'];
                                        $si   = $statusMap[$sid] ?? ['cls'=>'dark','icon'=>'fa-circle','label'=>'نامشخص'];
                                        $amt  = ($o['quoted_total'] > 0) ? $o['quoted_total'] : $o['grand_total'];
                                        ?>
                                        <tr>
                                            <td class="text-muted">#<?= $o['id'] ?></td>
                                            <td><strong><?= htmlspecialchars($o['order_name'] ?: ('سفارش #' . $o['id']), ENT_QUOTES, 'UTF-8') ?></strong></td>
                                            <td>
                                                <span class="badge badge-<?= $si['cls'] ?>">
                                                    <i class="fa <?= $si['icon'] ?> ml-1" style="font-size:.65rem"></i>
                                                    <?= htmlspecialchars($si['label'], ENT_QUOTES, 'UTF-8') ?>
                                                </span>
                                            </td>
                                            <td class="o-price"><?= $amt > 0 ? number_format((int)$amt) . ' ت' : '—' ?></td>
                                            <td><?= jdate("Y/m/d", strtotime($o['created_at'])) ?></td>
                                            <td>
                                                <a href="order_view.php?id=<?= $o['id'] ?>"
                                                   class="btn btn-sm btn-outline-info"
                                                   title="مشاهده">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php if ($ordersCount > 10): ?>
                                <div class="text-center p-3 border-top" style="font-size:.82rem;color:#8a8fa3">
                                    <i class="fa fa-info-circle ml-1"></i>
                                    نمایش ۱۰ سفارش اخیر از <?= $ordersCount ?> سفارش کل
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="uv-empty">
                                <i class="fa fa-shopping-cart"></i>
                                <p class="mb-0">این کاربر هنوز سفارشی ثبت نکرده است.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- ── ACTIVITY TIMELINE ── -->
                    <?php if ($orders): ?>
                        <div class="uv-card">
                            <div class="uv-card-head">
                                <i class="fa fa-clock-rotate-left" style="background:#8b5cf6"></i>
                                <h6>آخرین فعالیت‌ها</h6>
                            </div>
                            <div class="uv-card-body">
                                <div class="uv-timeline">
                                    <?php
                                    $tlColors = ['#6366f1','#10b981','#f59e0b','#0ea5e9','#8b5cf6'];
                                    foreach (array_slice($orders, 0, 5) as $i => $o):
                                        $sid = (int) $o['order_status_id'];
                                        $si  = $statusMap[$sid] ?? ['cls'=>'dark','icon'=>'fa-circle','label'=>'نامشخص'];
                                        $col = $tlColors[$i % count($tlColors)];
                                        ?>
                                        <div class="uv-tl-item">
                                            <div class="uv-tl-line"></div>
                                            <div class="uv-tl-dot" style="background:<?= $col ?>;color:#fff">
                                                <i class="fa <?= $si['icon'] ?>"></i>
                                            </div>
                                            <div class="uv-tl-body">
                                                <strong>
                                                    <?= htmlspecialchars($o['order_name'] ?: 'سفارش #' . $o['id'], ENT_QUOTES, 'UTF-8') ?>
                                                    &mdash; <span class="badge badge-<?= $si['cls'] ?>" style="font-size:.65rem"><?= htmlspecialchars($si['label'], ENT_QUOTES, 'UTF-8') ?></span>
                                                </strong>
                                                <small><?= jdate("Y/m/d H:i", strtotime($o['created_at'])) ?></small>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    <!-- membership event always at bottom -->
                                    <div class="uv-tl-item">
                                        <div class="uv-tl-line"></div>
                                        <div class="uv-tl-dot" style="background:#e9ecef;color:#9ca3af">
                                            <i class="fa fa-user-plus"></i>
                                        </div>
                                        <div class="uv-tl-body">
                                            <strong>ثبت‌نام در سایت</strong>
                                            <small><?= jdate("Y/m/d H:i", strtotime($user['created_at'])) ?></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>

                <!-- ── LEFT SIDEBAR ── -->
                <div>

                    <!-- quick actions -->
                    <div class="uv-card">
                        <div class="uv-card-head">
                            <i class="fa fa-bolt" style="background:#f59e0b"></i>
                            <h6>عملیات سریع</h6>
                        </div>
                        <div class="uv-card-body">
                            <div class="uv-actions">
                                <a href="user_edit.php?id=<?= $id ?>" class="btn btn-primary btn-block">
                                    <i class="fa fa-edit ml-2"></i>ویرایش اطلاعات
                                </a>
                                <a href="user_list.php" class="btn btn-light btn-block">
                                    <i class="fa fa-list ml-2"></i>بازگشت به لیست
                                </a>
                                <button onclick="window.print()" class="btn btn-light btn-block">
                                    <i class="fa fa-print ml-2"></i>چاپ پروفایل
                                </button>
                                <hr class="my-1">
                                <a href="user_list.php?delete=<?= $id ?>"
                                   onclick="return confirm('کاربر #<?= $id ?> حذف شود؟')"
                                   class="btn btn-outline-danger btn-block">
                                    <i class="fa fa-trash ml-2"></i>حذف این کاربر
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- meta pills sidebar -->
                    <div class="uv-card">
                        <div class="uv-card-head">
                            <i class="fa fa-circle-info" style="background:#0ea5e9"></i>
                            <h6>اطلاعات کلی</h6>
                        </div>
                        <div class="uv-card-body">

                            <div class="uv-meta-pill">
                                <div class="uv-meta-pill-icon" style="background:#6366f1">
                                    <i class="fa fa-hashtag"></i>
                                </div>
                                <div class="uv-meta-pill-content">
                                    <span class="uv-meta-pill-lbl">شناسه کاربر</span>
                                    <span class="uv-meta-pill-val">#<?= $id ?></span>
                                </div>
                            </div>

                            <div class="uv-meta-pill">
                                <div class="uv-meta-pill-icon" style="background:<?= $isOnline ? '#10b981' : '#9ca3af' ?>">
                                    <i class="fa fa-signal"></i>
                                </div>
                                <div class="uv-meta-pill-content">
                                    <span class="uv-meta-pill-lbl">وضعیت نشست</span>
                                    <span class="uv-meta-pill-val"><?= $isOnline ? 'آنلاین' : 'آفلاین' ?></span>
                                </div>
                            </div>

                            <div class="uv-meta-pill">
                                <div class="uv-meta-pill-icon" style="background:<?= $isVerified ? '#10b981' : '#f59e0b' ?>">
                                    <i class="fa fa-shield-alt"></i>
                                </div>
                                <div class="uv-meta-pill-content">
                                    <span class="uv-meta-pill-lbl">تایید موبایل</span>
                                    <span class="uv-meta-pill-val"><?= $isVerified ? 'تایید شده' : 'تایید نشده' ?></span>
                                </div>
                            </div>

                            <div class="uv-meta-pill">
                                <div class="uv-meta-pill-icon" style="background:#8b5cf6">
                                    <i class="fa fa-shopping-cart"></i>
                                </div>
                                <div class="uv-meta-pill-content">
                                    <span class="uv-meta-pill-lbl">تعداد سفارش‌ها</span>
                                    <span class="uv-meta-pill-val"><?= $ordersCount ?> سفارش</span>
                                </div>
                            </div>

                            <div class="uv-meta-pill">
                                <div class="uv-meta-pill-icon" style="background:#f59e0b">
                                    <i class="fa fa-envelope"></i>
                                </div>
                                <div class="uv-meta-pill-content">
                                    <span class="uv-meta-pill-lbl">پیام‌های تماس</span>
                                    <span class="uv-meta-pill-val">
                                        <?= $msgCount ?> پیام
                                        <?php if ($unreadCount > 0): ?>
                                            <span class="badge badge-danger mr-1" style="font-size:.65rem"><?= $unreadCount ?> خوانده‌نشده</span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>

                            <div class="uv-meta-pill">
                                <div class="uv-meta-pill-icon" style="background:#0ea5e9">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <div class="uv-meta-pill-content">
                                    <span class="uv-meta-pill-lbl">تاریخ عضویت</span>
                                    <span class="uv-meta-pill-val"><?= jdate("Y/m/d", strtotime($user['created_at'])) ?></span>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- financial summary -->
                    <?php if ($ordersCount > 0): ?>
                        <div class="uv-card">
                            <div class="uv-card-head">
                                <i class="fa fa-coins" style="background:#10b981"></i>
                                <h6>خلاصه مالی</h6>
                            </div>
                            <div class="uv-card-body" style="font-size:.875rem">
                                <div class="uv-row">
                                    <span class="uv-row-label">جمع کل سفارش‌ها</span>
                                    <span class="uv-row-val"><?= $ordersTotal > 0 ? number_format((int)$ordersTotal) . ' ت' : '—' ?></span>
                                </div>
                                <div class="uv-row">
                                    <span class="uv-row-label">خریدهای تأیید/ارسال‌شده</span>
                                    <span class="uv-row-val text-success"><?= $paidTotal > 0 ? number_format((int)$paidTotal) . ' ت' : '—' ?></span>
                                </div>
                                <div class="uv-row">
                                    <span class="uv-row-label">تعداد سفارش‌ها</span>
                                    <span class="uv-row-val"><?= $ordersCount ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>

            </div>
            <!-- /uv-grid -->

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

</body>
</html>
