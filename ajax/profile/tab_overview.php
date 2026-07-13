<?php
require_once '../../cms/myadmin/inc/config.php';

$uid = $_SESSION['user']['id'];

// latest order
$lastOrderStmt = $pdo->prepare("
    SELECT o.*, s.status_name
    FROM `orders` o
    LEFT JOIN `order_status` s ON s.id = o.order_status_id
    WHERE o.user_id = :uid AND o.deleted = 0
    ORDER BY o.created_at DESC
    LIMIT 1
");
$lastOrderStmt->execute([':uid' => $uid]);
$lastOrder = $lastOrderStmt->fetch();

// latest message
$lastMsgStmt = $pdo->prepare("
    SELECT * FROM `contact_messages`
    WHERE user_id = :uid AND deleted = 0
    ORDER BY created_at DESC LIMIT 1
");
$lastMsgStmt->execute([':uid' => $uid]);
$lastMsg = $lastMsgStmt->fetch();

$totalSpentStmt = $pdo->prepare("SELECT COALESCE(SUM(grand_total),0) FROM `orders` WHERE user_id = :uid AND deleted = 0 AND order_status_id NOT IN (5,6)");
$totalSpentStmt->execute([':uid' => $uid]);
$totalSpent = (int) $totalSpentStmt->fetchColumn();

$memberSince = profile_format_date($_SESSION['user']['id']);
?>
<div class="pf-overview">

    <div class="pf-welcome">
        <h1>سلام، <?= htmlspecialchars($_SESSION['user']['name'], ENT_QUOTES, 'UTF-8') ?> 👋</h1>
    </div>

    <div class="pf-stat-grid">
        <div class="pf-stat-card">
            <i class="fa-solid fa-wallet"></i>
            <div>
                <span class="pf-stat-num"><?= profile_format_price($totalSpent) ?></span>
                <span class="pf-stat-label">مجموع خرید</span>
            </div>
        </div>
        <div class="pf-stat-card">
            <i class="fa-solid fa-bag-shopping"></i>
            <div>
                <span class="pf-stat-num"><?= $lastOrder ? htmlspecialchars($lastOrder['status_name'] ?? 'ثبت شده', ENT_QUOTES, 'UTF-8') : 'بدون سفارش' ?></span>
                <span class="pf-stat-label">وضعیت آخرین سفارش</span>
            </div>
        </div>
        <div class="pf-stat-card">
            <i class="fa-solid fa-envelope-open-text"></i>
            <div>
                <span class="pf-stat-num"><?= $lastMsg ? profile_format_date($lastMsg['created_at']) : 'ندارید' ?></span>
                <span class="pf-stat-label">آخرین پیام پشتیبانی</span>
            </div>
        </div>
    </div>

    <div class="pf-quick-actions">
        <button class="btn btn-outline" data-goto-tab="orders"><i class="fa-solid fa-bag-shopping"></i> مشاهده سفارش‌ها</button>
        <button class="btn btn-outline" data-goto-tab="messages"><i class="fa-solid fa-envelope"></i> پیام‌های پشتیبانی</button>
        <button class="btn btn-outline" data-goto-tab="personal"><i class="fa-solid fa-id-card"></i> ویرایش اطلاعات</button>
    </div>

    <?php if (!$_SESSION['user']['id']): ?>
    <div class="pf-notice pf-notice--warning">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <span>شماره موبایل شما هنوز تایید نشده. برای فعال‌سازی کامل حساب، شماره‌تان را تایید کنید.</span>
    </div>
    <?php endif; ?>

</div>
