<?php
require_once '../../cms/myadmin/inc/config.php';

header('Content-Type: text/html; charset=utf-8');

$uid = $_SESSION['user']['id'];
$messageId = (int) ($_GET['id'] ?? 0);

$msgStmt = $pdo->prepare("SELECT * FROM `contact_messages` WHERE id = :id AND user_id = :uid AND deleted = 0 LIMIT 1");
$msgStmt->execute([':id' => $messageId, ':uid' => $uid]);
$message = $msgStmt->fetch();

if (!$message) {
    http_response_code(404);
    echo '<div class="pf-empty"><i class="fa-solid fa-triangle-exclamation"></i><p>پیام یافت نشد.</p></div>';
    exit;
}

// mark as seen by user (this flag is used by admin panel too, so only
// touch it if you want "seen" to mean "seen by either side" — otherwise
// consider adding a separate `seen_by_user` column instead).
// Left commented out on purpose to avoid clobbering the admin's own
// "seen" tracking:
// $pdo->prepare("UPDATE contact_messages SET seen = 1 WHERE id = :id")->execute([':id' => $messageId]);

$repliesStmt = $pdo->prepare("SELECT * FROM `message_replies` WHERE message_id = :id ORDER BY created_at ASC, id ASC");
$repliesStmt->execute([':id' => $messageId]);
$replies = $repliesStmt->fetchAll();
?>
<div class="pf-thread">
    <div class="pf-thread-head">
        <button class="pf-thread-back" id="pf-thread-back" type="button" aria-label="بازگشت">
            <i class="fa-solid fa-arrow-right"></i>
        </button>
        <div>
            <strong><?= htmlspecialchars($message['subject'], ENT_QUOTES, 'UTF-8') ?></strong>
            <span class="text-muted pf-thread-date">ارسال شده در <?= profile_format_date($message['created_at']) ?></span>
        </div>
    </div>

    <div class="pf-thread-scroll">
        <div class="pf-bubble pf-bubble--user">
            <p><?= nl2br(htmlspecialchars($message['message'], ENT_QUOTES, 'UTF-8')) ?></p>
            <span class="pf-bubble-time"><?= profile_format_date($message['created_at']) ?></span>
        </div>

        <?php foreach ($replies as $r):
            $isAdmin = $r['sender_type'] === 'admin';
        ?>
        <div class="pf-bubble <?= $isAdmin ? 'pf-bubble--admin' : 'pf-bubble--user' ?>">
            <?php if ($isAdmin): ?><span class="pf-bubble-tag">پشتیبانی</span><?php endif; ?>
            <p><?= nl2br(htmlspecialchars($r['body'], ENT_QUOTES, 'UTF-8')) ?></p>
            <span class="pf-bubble-time"><?= profile_format_date($r['created_at']) ?></span>
        </div>
        <?php endforeach; ?>
    </div>

    <form class="pf-thread-reply" id="pf-reply-form" data-message-id="<?= (int)$message['id'] ?>">
        <textarea name="body" placeholder="پاسخ خود را بنویسید…" required maxlength="2000"></textarea>
        <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-paper-plane"></i>
            <span>ارسال پاسخ</span>
        </button>
    </form>
</div>
