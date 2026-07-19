<?php
require_once '../../cms/myadmin/inc/config.php';

$uid = $_SESSION['user']['id'];

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

$lastMsgStmt = $pdo->prepare("
    SELECT * FROM `contact_messages`
    WHERE user_id = :uid AND deleted = 0
    ORDER BY created_at DESC LIMIT 1
");
$lastMsgStmt->execute([':uid' => $uid]);
$lastMsg = $lastMsgStmt->fetch();

$totalSpentStmt = $pdo->prepare("
    SELECT COALESCE(SUM(quoted_total),0) FROM `orders`
    WHERE user_id = :uid AND deleted = 0 AND order_status_id = 4
");
$totalSpentStmt->execute([':uid' => $uid]);
$totalSpent = (int) $totalSpentStmt->fetchColumn();
?>
<div class="pf-overview">

    <div class="pf-welcome mb-3">
        <h1 class="fs-5 fw-bold">سلام، <?= htmlspecialchars($_SESSION['user']['name'], ENT_QUOTES, 'UTF-8') ?> عزیز 👋</h1>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-4">
            <div class="pf-stat-card">
                <i class="fa-solid fa-wallet"></i>
                <div>
                    <span class="pf-stat-num"><?= profile_format_price($totalSpent) ?></span>
                    <span class="pf-stat-label">مجموع خریدهای تکمیل‌شده</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="pf-stat-card">
                <i class="fa-solid fa-bag-shopping"></i>
                <div>
                    <span class="pf-stat-num">
                        <?= $lastOrder
                                ? htmlspecialchars($lastOrder['status_name'] ?? 'ثبت شده', ENT_QUOTES, 'UTF-8')
                                : 'بدون سفارش' ?>
                    </span>
                    <span class="pf-stat-label">وضعیت آخرین سفارش</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="pf-stat-card">
                <i class="fa-solid fa-envelope-open-text"></i>
                <div>
                    <span class="pf-stat-num">
                        <?= $lastMsg ? profile_format_date($lastMsg['created_at']) : 'ندارید' ?>
                    </span>
                    <span class="pf-stat-label">آخرین پیام پشتیبانی</span>
                </div>
            </div>
        </div>
    </div>

    <div class="d-grid gap-2 d-sm-flex flex-sm-wrap pf-quick-actions">
        <button class="btn btn-outline" data-goto-tab="orders">
            <i class="fa-solid fa-bag-shopping"></i> مشاهده سفارش‌ها
        </button>
        <button class="btn btn-outline" data-goto-tab="messages">
            <i class="fa-solid fa-envelope"></i> پیام‌های پشتیبانی
        </button>
        <button class="btn btn-outline" data-goto-tab="personal">
            <i class="fa-solid fa-id-card"></i> ویرایش اطلاعات
        </button>
    </div>

</div>