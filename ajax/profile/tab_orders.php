<?php
require_once '../../cms/myadmin/inc/config.php';

$uid = (int) $_SESSION['user']['id'];

$stmt = $pdo->prepare("
    SELECT o.*, s.status_name
    FROM `orders` o
    LEFT JOIN `order_status` s ON s.id = o.order_status_id
    WHERE o.user_id = :uid AND o.deleted = 0
    ORDER BY o.created_at DESC
");
$stmt->execute([':uid' => $uid]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$statusTone = [
        1 => 'warning',
        2 => 'info',
        3 => 'warning',
        4 => 'success',
        5 => 'success',
        6 => 'danger',
];

$statusIcon = [
        1 => 'fa-clock',
        2 => 'fa-credit-card',
        3 => 'fa-magnifying-glass',
        4 => 'fa-circle-check',
        5 => 'fa-truck',
        6 => 'fa-box-archive',
];
?>
<div class="pf-orders">
    <div class="pf-panel-title">
        <h2>سفارش‌های من</h2>
        <span class="badge bg-secondary"><?= count($orders) ?> سفارش</span>
    </div>

    <?php if (!$orders): ?>
        <div class="pf-empty">
            <i class="fa-solid fa-box-open"></i>
            <p>هنوز سفارشی ثبت نکرده‌اید.</p>
        </div>
    <?php else: ?>
        <div class="pf-order-list">
            <?php foreach ($orders as $order):
                $statusId = (int) $order['order_status_id'];
                $tone     = $statusTone[$statusId] ?? 'info';
                $icon     = $statusIcon[$statusId] ?? 'fa-circle';

                $itemsStmt = $pdo->prepare("
                    SELECT * FROM `order_items`
                    WHERE order_id = :oid ORDER BY id ASC
                ");
                $itemsStmt->execute([':oid' => $order['id']]);
                $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

                $hasQuote         = isset($order['quoted_total']) && $order['quoted_total'] > 0;
                $canUploadReceipt = ($statusId === 2 && empty($order['receipt_path']));
                $receiptUploaded  = ($statusId === 3 && !empty($order['receipt_path']));
                ?>
                <details class="pf-order-card" id="order-card-<?= $order['id'] ?>">
                    <summary class="pf-order-summary">
                        <div class="pf-order-summary-main">
                            <span class="pf-order-number">سفارش <?= htmlspecialchars($order['order_name'], ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="pf-order-date"><?= profile_format_date($order['created_at']) ?></span>
                        </div>
                        <div class="pf-order-summary-side">
                        <span class="badge pf-badge--<?= $tone ?>">
                            <i class="fa-solid <?= $icon ?>"></i>
                            <?= htmlspecialchars($order['status_name'] ?? 'نامشخص', ENT_QUOTES, 'UTF-8') ?>
                        </span>
                            <span class="pf-order-price">
                            <?php if ($hasQuote): ?>
                                <?= profile_format_price((int) $order['quoted_total']) ?>
                            <?php else: ?>
                                <span class="pf-price-pending">در انتظار قیمت</span>
                            <?php endif; ?>
                        </span>
                            <i class="fa-solid fa-chevron-down pf-order-chevron"></i>
                        </div>
                    </summary>

                    <div class="pf-order-detail">

                        <!-- اقلام -->
                        <?php if ($items): ?>
                            <ul class="pf-order-items list-unstyled">
                                <?php foreach ($items as $item): ?>
                                    <li class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="pf-order-item-name flex-grow-1">
                                    <?= htmlspecialchars($item['product_name'] ?? 'محصول حذف‌شده', ENT_QUOTES, 'UTF-8') ?>
                                </span>
                                        <span class="pf-order-item-qty text-muted">× <?= (int)$item['qty'] ?></span>
                                        <span class="pf-order-item-price">
                                    <?= (float)$item['price'] > 0
                                            ? profile_format_price((int)((float)$item['price'] * (int)$item['qty']))
                                            : '—' ?>
                                </span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-muted small">ریز اقلام ثبت نشده.</p>
                        <?php endif; ?>

                        <!-- مجموع -->
                        <?php if ($hasQuote): ?>
                            <div class="pf-order-totals">
                                <div class="pf-order-total-final">
                                    <span>مبلغ نهایی (اعلام‌شده توسط کارشناس)</span>
                                    <span><?= profile_format_price((int)$order['quoted_total']) ?></span>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="pf-order-quote-notice">
                                <i class="fa-solid fa-circle-info"></i>
                                کارشناسان ما پس از بررسی، مبلغ نهایی را اعلام خواهند کرد.
                            </div>
                        <?php endif; ?>

                        <!-- یادداشت مشتری -->
                        <?php if (!empty($order['note'])): ?>
                            <p class="pf-order-note">
                                <i class="fa-solid fa-note-sticky"></i>
                                <?= htmlspecialchars($order['note'], ENT_QUOTES, 'UTF-8') ?>
                            </p>
                        <?php endif; ?>

                        <!-- یادداشت ادمین -->
                        <?php if (!empty($order['admin_note'])): ?>
                            <div class="pf-order-admin-note">
                                <i class="fa-solid fa-comment-dots"></i>
                                <div>
                                    <strong>پیام کارشناس:</strong>
                                    <?= htmlspecialchars($order['admin_note'], ENT_QUOTES, 'UTF-8') ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- آپلود رسید -->
                        <?php if ($canUploadReceipt): ?>
                            <div class="pf-upload-card" id="receipt-box-<?= $order['id'] ?>">
                                <div class="pf-upload-header">
                                    <div class="pf-upload-icon">
                                        <i class="fa-solid fa-file-invoice-dollar"></i>
                                    </div>
                                    <div class="pf-upload-info">
                                        <h4>بارگذاری رسید پرداخت</h4>
                                        <p>
                                            لطفاً پس از واریز
                                            <strong><?= profile_format_price((int)$order['quoted_total']) ?></strong>
                                            به شماره کارت
                                            <strong dir="ltr"><?= setting('card-number') ?></strong>،
                                            تصویر رسید را بارگذاری کنید.
                                        </p>
                                    </div>
                                </div>

                                <div class="pf-upload-zone" id="upload-zone-<?= $order['id'] ?>"
                                     data-order="<?= $order['id'] ?>">
                                    <input type="file"
                                           id="receipt-file-<?= $order['id'] ?>"
                                           class="pf-receipt-input"
                                           accept="image/jpeg,image/png,image/webp,application/pdf"
                                           data-order="<?= $order['id'] ?>">
                                    <div class="pf-upload-circle">
                                        <i class="fa-solid fa-cloud-arrow-up"></i>
                                    </div>
                                    <div class="pf-upload-content">
                                        <h5>فایل را اینجا بکشید یا <span>کلیک کنید</span></h5>
                                        <p>JPG، PNG، WebP یا PDF</p>
                                        <small>حداکثر ۵ مگابایت</small>
                                    </div>
                                </div>

                                <div class="pf-upload-preview hidden" id="receipt-preview-<?= $order['id'] ?>">
                                    <div class="pf-preview-icon">
                                        <i class="fa-solid fa-file-check"></i>
                                    </div>
                                    <div class="pf-preview-info">
                                        <span class="pf-upload-name" id="receipt-name-<?= $order['id'] ?>"></span>
                                        <small>آماده ارسال</small>
                                    </div>
                                    <button type="button" class="pf-remove-file" data-order="<?= $order['id'] ?>">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>

                                <button type="button"
                                        class="btn-upload-receipt"
                                        id="btn-upload-<?= $order['id'] ?>"
                                        data-order="<?= $order['id'] ?>"
                                        disabled>
                                    <i class="fa-solid fa-paper-plane"></i>
                                    ارسال رسید
                                </button>

                                <div class="pf-upload-msg hidden" id="upload-msg-<?= $order['id'] ?>"></div>
                            </div>

                        <?php elseif ($receiptUploaded): ?>
                            <div class="pf-receipt-uploaded-notice">
                                <i class="fa-solid fa-hourglass-half"></i>
                                رسید شما ارسال شده و در انتظار تأیید کارشناس است.
                            </div>
                        <?php endif; ?>

                    </div><!-- /pf-order-detail -->
                </details>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>