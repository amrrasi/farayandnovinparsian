<?php
require_once '../../cms/myadmin/inc/config.php';

$uid = $_SESSION['user']['id'];

$stmt = $pdo->prepare("
    SELECT o.*, s.status_name
    FROM `all_orders` o
    LEFT JOIN `order_status` s ON s.id = o.order_status_id
    WHERE o.user_id = :uid AND o.deleted = 0
    ORDER BY o.created_at DESC
");
$stmt->execute([':uid' => $uid]);
$orders = $stmt->fetchAll();

// status -> badge tone map (id => css modifier)
$statusTone = [
    1 => 'warning',  // pending payment
    2 => 'info',     // processing
    3 => 'info',     // shipped
    4 => 'success',  // delivered
    5 => 'danger',   // cancelled
    6 => 'danger',   // returned
];
?>
<div class="pf-orders">
    <div class="pf-panel-title">
        <h2>سفارش‌های من</h2>
        <span class="text-muted"><?= count($orders) ?> سفارش</span>
    </div>

    <?php if (!$orders): ?>
        <div class="pf-empty">
            <i class="fa-solid fa-box-open"></i>
            <p>هنوز سفارشی ثبت نکرده‌اید.</p>
        </div>
    <?php else: ?>
        <div class="pf-order-list">
            <?php foreach ($orders as $order):
                $tone = $statusTone[$order['order_status_id']] ?? 'info';

                $itemsStmt = $pdo->prepare("SELECT * FROM `all_suborders` WHERE all_orders_id = :oid ORDER BY id ASC");
                $itemsStmt->execute([':oid' => $order['id']]);
                $items = $itemsStmt->fetchAll();
            ?>
            <details class="pf-order-card">
                <summary class="pf-order-summary">
                    <div class="pf-order-summary-main">
                        <span class="pf-order-number">فاکتور #<?= htmlspecialchars($order['order_number'], ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="pf-order-date"><?= profile_format_date($order['created_at']) ?></span>
                    </div>
                    <div class="pf-order-summary-side">
                        <span class="badge pf-badge--<?= $tone ?>"><?= htmlspecialchars($order['status_name'] ?? 'نامشخص', ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="pf-order-price"><?= profile_format_price((int)$order['final_total_price']) ?></span>
                        <i class="fa-solid fa-chevron-down pf-order-chevron"></i>
                    </div>
                </summary>

                <div class="pf-order-detail">
                    <?php if ($items): ?>
                        <ul class="pf-order-items">
                            <?php foreach ($items as $item): ?>
                            <li>
                                <span class="pf-order-item-name"><?= htmlspecialchars($item['product_name'] ?? 'محصول حذف‌شده', ENT_QUOTES, 'UTF-8') ?></span>
                                <span class="pf-order-item-qty">× <?= (int)$item['quantity'] ?></span>
                                <span class="pf-order-item-price"><?= profile_format_price((int)$item['product_unit_price'] * (int)$item['quantity']) ?></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted pf-order-no-items">ریز اقلام این سفارش ثبت نشده.</p>
                    <?php endif; ?>

                    <div class="pf-order-totals">
                        <div><span>جمع محصولات</span><span><?= profile_format_price((int)$order['total_price']) ?></span></div>
                        <?php if ((float)$order['discount_amount'] > 0): ?>
                        <div><span>تخفیف<?= $order['discount_code'] ? ' (' . htmlspecialchars($order['discount_code'], ENT_QUOTES, 'UTF-8') . ')' : '' ?></span><span>- <?= profile_format_price((int)$order['discount_amount']) ?></span></div>
                        <?php endif; ?>
                        <?php if ((int)$order['wallet_balance'] > 0): ?>
                        <div><span>پرداخت از کیف پول</span><span>- <?= profile_format_price((int)$order['wallet_balance']) ?></span></div>
                        <?php endif; ?>
                        <div class="pf-order-total-final"><span>مبلغ نهایی</span><span><?= profile_format_price((int)$order['final_total_price']) ?></span></div>
                    </div>

                    <?php if (!empty($order['description'])): ?>
                        <p class="pf-order-note"><i class="fa-solid fa-note-sticky"></i> <?= htmlspecialchars($order['description'], ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>
                </div>
            </details>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
