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

// وضعیت → رنگ badge
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
                $statusId = (int) $order['order_status_id'];
                $tone     = $statusTone[$statusId] ?? 'info';
                $icon     = $statusIcon[$statusId] ?? 'fa-circle';

                $itemsStmt = $pdo->prepare("
                    SELECT * FROM `order_items`
                    WHERE order_id = :oid
                    ORDER BY id ASC
                ");
                $itemsStmt->execute([':oid' => $order['id']]);
                $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

                $hasQuote    = isset($order['quoted_total']) && $order['quoted_total'] > 0;
                $displayPrice = $hasQuote
                        ? profile_format_price((int) $order['quoted_total'])
                        : '<span class="pf-price-pending">در انتظار قیمت‌دهی</span>';

                $canUploadReceipt = ($statusId === 2 && empty($order['receipt_path']));

                $receiptUploaded = ($statusId === 3 && !empty($order['receipt_path']));
                ?>
                <details class="pf-order-card" id="order-card-<?= $order['id'] ?>">
                    <summary class="pf-order-summary">
                        <div class="pf-order-summary-main">
                            <span class="pf-order-number">سفارش #<?= (int)$order['id'] ?></span>
                            <span class="pf-order-date"><?= profile_format_date($order['created_at']) ?></span>
                        </div>
                        <div class="pf-order-summary-side">
                        <span class="badge pf-badge--<?= $tone ?>">
                            <i class="fa-solid <?= $icon ?>"></i>
                            <?= htmlspecialchars($order['status_name'] ?? 'نامشخص', ENT_QUOTES, 'UTF-8') ?>
                        </span>
                            <span class="pf-order-price"><?= $displayPrice ?></span>
                            <i class="fa-solid fa-chevron-down pf-order-chevron"></i>
                        </div>
                    </summary>

                    <div class="pf-order-detail">

                        <!-- اقلام -->
                        <?php if ($items): ?>
                            <ul class="pf-order-items">
                                <?php foreach ($items as $item): ?>
                                    <li>
                                        <span class="pf-order-item-name"><?= htmlspecialchars($item['product_name'] ?? 'محصول حذف‌شده', ENT_QUOTES, 'UTF-8') ?></span>
                                        <span class="pf-order-item-qty">× <?= (int)$item['qty'] ?></span>
                                        <span class="pf-order-item-price">
                                    <?= (float)$item['price'] > 0
                                            ? profile_format_price((int)((float)$item['price'] * (int)$item['qty']))
                                            : '—' ?>
                                </span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-muted pf-order-no-items">ریز اقلام ثبت نشده.</p>
                        <?php endif; ?>

                        <!-- مجموع‌ها -->
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
                                <strong>پیام کارشناس:</strong>
                                <?= htmlspecialchars($order['admin_note'], ENT_QUOTES, 'UTF-8') ?>
                            </div>
                        <?php endif; ?>

                        <!-- ═══ آپلود رسید — فقط وقتی وضعیت «در انتظار پرداخت» است ═══ -->
                        <?php if ($canUploadReceipt): ?>
                            <div class="pf-receipt-upload-box" id="receipt-box-<?= $order['id'] ?>">
                                <div class="pf-receipt-upload-title">
                                    <i class="fa-solid fa-file-arrow-up"></i>
                                    بارگذاری رسید پرداخت
                                </div>
                                <p class="pf-receipt-upload-desc">
                                    لطفاً پس از واریز مبلغ
                                    <strong><?= profile_format_price((int)$order['quoted_total']) ?></strong>،
                                    تصویر رسید را بارگذاری کنید.
                                </p>

                                <div class="pf-upload-zone" id="upload-zone-<?= $order['id'] ?>"
                                     data-order="<?= $order['id'] ?>">
                                    <input type="file"
                                           id="receipt-file-<?= $order['id'] ?>"
                                           class="pf-receipt-input"
                                           accept="image/jpeg,image/png,image/webp,application/pdf"
                                           data-order="<?= $order['id'] ?>">
                                    <i class="fa-solid fa-cloud-arrow-up"></i>
                                    <p>تصویر رسید را اینجا بکشید یا کلیک کنید</p>
                                    <span>JPG، PNG، PDF — حداکثر ۵ مگابایت</span>
                                </div>

                                <div class="pf-upload-preview hidden" id="receipt-preview-<?= $order['id'] ?>">
                                    <i class="fa-solid fa-file-check"></i>
                                    <span class="pf-upload-name" id="receipt-name-<?= $order['id'] ?>"></span>
                                    <button type="button"
                                            class="pf-remove-file"
                                            data-order="<?= $order['id'] ?>">
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

                    </div>
                </details>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- ══ آپلود رسید — JS ══ -->
<script>
    (function () {
        'use strict';

        const CSRF     = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const fileMap  = {}; // orderId → File

        // ── کلیک روی zone → باز شدن input ──────────────────
        document.querySelectorAll('.pf-upload-zone').forEach(zone => {
            zone.addEventListener('click', function () {
                const orderId = this.dataset.order;
                document.getElementById('receipt-file-' + orderId)?.click();
            });
            zone.addEventListener('dragover', e => {
                e.preventDefault();
                zone.classList.add('drag-over');
            });
            zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
            zone.addEventListener('drop', e => {
                e.preventDefault();
                zone.classList.remove('drag-over');
                const orderId = zone.dataset.order;
                setFile(orderId, e.dataTransfer.files[0]);
            });
        });

        // ── انتخاب فایل از input ────────────────────────────
        document.querySelectorAll('.pf-receipt-input').forEach(input => {
            input.addEventListener('change', function () {
                setFile(this.dataset.order, this.files[0]);
            });
        });

        // ── حذف فایل ────────────────────────────────────────
        document.querySelectorAll('.pf-remove-file').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.dataset.order;
                delete fileMap[id];
                const input = document.getElementById('receipt-file-' + id);
                if (input) input.value = '';
                document.getElementById('receipt-preview-' + id)?.classList.add('hidden');
                const uploadBtn = document.getElementById('btn-upload-' + id);
                if (uploadBtn) uploadBtn.disabled = true;
            });
        });

        // ── ارسال رسید ──────────────────────────────────────
        document.querySelectorAll('.btn-upload-receipt').forEach(btn => {
            btn.addEventListener('click', async function () {
                const id   = this.dataset.order;
                const file = fileMap[id];
                if (!file) return;

                this.disabled = true;
                this.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> در حال ارسال…';

                const fd = new FormData();
                fd.append('csrf_token', CSRF);
                fd.append('order_id',   id);
                fd.append('receipt',    file);

                try {
                    const res  = await fetch('ajax/orders/uploadReceipt.php', {
                        method: 'POST',
                        body: fd,
                    });
                    const data = await res.json();

                    const msgEl = document.getElementById('upload-msg-' + id);
                    if (msgEl) {
                        msgEl.textContent  = data.message || (data.status ? 'رسید با موفقیت ارسال شد.' : 'خطا در ارسال');
                        msgEl.className    = 'pf-upload-msg ' + (data.status ? 'success' : 'error');
                        msgEl.classList.remove('hidden');
                    }

                    if (data.status) {
                        // کارت سفارش را پس از ۱.۵ ثانیه رفرش کن
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        this.disabled = false;
                        this.innerHTML = '<i class="fa-solid fa-paper-plane"></i> ارسال رسید';
                    }

                } catch (err) {
                    console.error(err);
                    this.disabled = false;
                    this.innerHTML = '<i class="fa-solid fa-paper-plane"></i> ارسال رسید';
                    const msgEl = document.getElementById('upload-msg-' + id);
                    if (msgEl) {
                        msgEl.textContent = 'خطا در اتصال به سرور';
                        msgEl.className   = 'pf-upload-msg error';
                        msgEl.classList.remove('hidden');
                    }
                }
            });
        });

        function setFile(orderId, file) {
            if (!file) return;
            if (file.size > 5 * 1024 * 1024) {
                alert('حجم فایل بیش از ۵ مگابایت است');
                return;
            }
            fileMap[orderId] = file;
            const nameEl = document.getElementById('receipt-name-' + orderId);
            if (nameEl) nameEl.textContent = file.name;
            document.getElementById('receipt-preview-' + orderId)?.classList.remove('hidden');
            const uploadBtn = document.getElementById('btn-upload-' + orderId);
            if (uploadBtn) uploadBtn.disabled = false;
        }

    })();
</script>