<?php
require_once '../../cms/myadmin/inc/config.php';

$uid = $_SESSION['user']['id'];

$stmt = $pdo->prepare("
    SELECT cm.*,
           (SELECT COUNT(*) FROM message_replies mr WHERE mr.message_id = cm.id) AS reply_count,
           (SELECT MAX(mr.created_at) FROM message_replies mr WHERE mr.message_id = cm.id) AS last_reply_at
    FROM `contact_messages` cm
    WHERE cm.user_id = :uid AND cm.deleted = 0
    ORDER BY COALESCE(last_reply_at, cm.created_at) DESC
");
$stmt->execute([':uid' => $uid]);
$messages = $stmt->fetchAll();
?>
<div class="pf-messages" id="pf-messages-root">
    <div class="pf-panel-title">
        <h2>پیام‌های پشتیبانی</h2>
        <span class="text-muted"><?= count($messages) ?> پیام</span>
    </div>

    <?php if (!$messages): ?>
        <div class="pf-empty">
            <i class="fa-solid fa-comment-slash"></i>
            <p>هنوز پیامی برای پشتیبانی ارسال نکرده‌اید.</p>
        </div>
    <?php else: ?>
        <div class="pf-msg-list">
            <?php foreach ($messages as $m):
                $snippet = mb_substr(strip_tags($m['message']), 0, 90, 'UTF-8');
                if (mb_strlen(strip_tags($m['message']), 'UTF-8') > 90) $snippet .= '…';
            ?>
            <button class="pf-msg-row <?= !$m['seen'] ? 'is-unread' : '' ?>" data-message-id="<?= (int)$m['id'] ?>">
                <span class="pf-msg-dot" aria-hidden="true"></span>
                <span class="pf-msg-main">
                    <span class="pf-msg-subject"><?= htmlspecialchars($m['subject'], ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="pf-msg-snippet"><?= htmlspecialchars($snippet, ENT_QUOTES, 'UTF-8') ?></span>
                </span>
                <span class="pf-msg-meta">
                    <?php if ($m['reply_count'] > 0): ?>
                        <span class="badge pf-badge--info"><?= (int)$m['reply_count'] ?> پاسخ</span>
                    <?php endif; ?>
                    <span class="pf-msg-date"><?= profile_format_date($m['last_reply_at'] ?? $m['created_at']) ?></span>
                </span>
            </button>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="pf-thread-overlay" id="pf-thread-overlay" hidden>
        <div class="pf-thread-panel">
            <div id="pf-thread-body"><span class="pf-spinner"></span></div>
        </div>
    </div>
</div>
