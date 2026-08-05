<?php
/**
 * ajax/profile/tab_messages.php
 *
 * DB tables:
 *   contact_messages  cols: id, user_id, subject, message, seen, deleted, created_at
 *   message_replies   cols: id, message_id, sender_type ENUM('admin','user'), user_id, body, created_at
 *
 * seen flag semantics:
 *   0 = admin has NOT read the latest activity (set to 0 when user replies)
 *   1 = admin has read the thread
 *   We show an unread dot when reply_count > 0 AND seen = 1
 *   (meaning admin answered and the user hasn't replied since — the "new reply from admin" state).
 */
require_once '../../cms/myadmin/inc/config.php';

// ── Auth guard — must come BEFORE any use of $_SESSION['user']['id'] ─────────
if (empty($_SESSION['user']['id'])) {
    header('Location: ../entry/');
    exit;
}

$uid = (int) $_SESSION['user']['id'];

$stmt = $pdo->prepare("
    SELECT
        cm.id,
        cm.subject,
        cm.message,
        cm.seen,
        cm.created_at,
        (SELECT COUNT(*)           FROM message_replies mr WHERE mr.message_id = cm.id) AS reply_count,
        (SELECT MAX(mr.created_at) FROM message_replies mr WHERE mr.message_id = cm.id) AS last_reply_at,
        (SELECT sender_type        FROM message_replies mr WHERE mr.message_id = cm.id ORDER BY mr.created_at DESC, mr.id DESC LIMIT 1) AS last_sender
    FROM contact_messages cm
    WHERE cm.user_id = :uid AND cm.deleted = 0
    ORDER BY COALESCE(last_reply_at, cm.created_at) DESC
");
$stmt->execute([':uid' => $uid]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
    <div class="pf-messages" id="pf-messages-root">

        <div class="pf-panel-title">
            <h2>پیام‌های پشتیبانی</h2>
            <span class="badge bg-secondary"><?= count($messages) ?> پیام</span>
        </div>

        <?php if (!$messages): ?>
            <div class="pf-empty">
                <i class="fa-solid fa-comment-slash"></i>
                <p>هنوز پیامی برای پشتیبانی ارسال نکرده‌اید.</p>
            </div>
        <?php else: ?>

            <div class="pf-msg-list" id="pf-msg-list">
                <?php foreach ($messages as $m):
                    $plain   = strip_tags($m['message']);
                    $snippet = mb_substr($plain, 0, 90, 'UTF-8');
                    if (mb_strlen($plain, 'UTF-8') > 90) $snippet .= '…';

                    /*
                     * Show unread dot when the LAST reply was from admin (last_sender = 'admin')
                     * AND the thread is seen = 1 (admin has read their own reply,
                     * but the user hasn't responded yet — so it's "new for user").
                     * seen = 0 means the user just replied and admin hasn't seen it yet.
                     */
                    $isUnread = ($m['reply_count'] > 0)
                            && ($m['last_sender'] === 'admin')
                            && ((int)$m['seen'] === 1);
                    ?>
                    <button class="pf-msg-row <?= $isUnread ? 'is-unread' : '' ?>"
                            data-message-id="<?= (int) $m['id'] ?>"
                            type="button">

                        <span class="pf-msg-dot" aria-hidden="true"></span>

                        <span class="pf-msg-main">
                        <span class="pf-msg-subject">
                            <?= htmlspecialchars($m['subject'], ENT_QUOTES, 'UTF-8') ?>
                        </span>
                        <span class="pf-msg-snippet">
                            <?= htmlspecialchars($snippet, ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </span>

                        <span class="pf-msg-meta">
                        <?php if ($m['reply_count'] > 0): ?>
                            <span class="badge pf-badge--info">
                                <?= (int) $m['reply_count'] ?> پاسخ
                            </span>
                        <?php endif; ?>
                        <span class="pf-msg-date">
                            <?= profile_format_date($m['last_reply_at'] ?? $m['created_at']) ?>
                        </span>
                    </span>

                    </button>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>

        <!-- Thread overlay: sits INSIDE .pf-messages which must be position:relative (see CSS) -->
        <div class="pf-thread-overlay" id="pf-thread-overlay" aria-hidden="true" style="display:none;">
            <div class="pf-thread-panel">

                <div class="pf-thread-head" id="pf-thread-head">
                    <button class="pf-thread-back" id="pf-thread-back-btn" type="button" aria-label="بازگشت">
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-semibold text-truncate" id="pf-thread-subject">در حال بارگذاری…</div>
                        <span class="pf-thread-date" id="pf-thread-date"></span>
                    </div>
                </div>

                <div class="pf-thread-scroll" id="pf-thread-scroll">
                    <div class="pf-thread-loading-wrap" id="pf-thread-loading">
                        <span class="pf-spinner"></span>
                    </div>
                    <div id="pf-thread-bubbles" class="pf-thread-bubbles"></div>
                </div>

                <div class="pf-thread-reply" id="pf-thread-reply">
                <textarea class="pf-thread-textarea"
                          id="pf-reply-text"
                          rows="1"
                          placeholder="پاسخ خود را بنویسید…"
                          maxlength="2000"></textarea>
                    <button class="btn btn-primary" id="pf-reply-send" type="button">
                        <i class="fa-solid fa-paper-plane"></i>
                        <span>ارسال</span>
                    </button>
                </div>

            </div>
        </div>

    </div>
<?php
/*
 * NOTE: The inline <script> block has been REMOVED from here intentionally.
 *
 * When profile.js loads this tab via fetch() and writes it into panelBody with
 * innerHTML, any <script> tags inside the injected HTML are NOT executed by
 * the browser — this is a security restriction on innerHTML (CVE-safe behaviour).
 *
 * The messages tab JS now lives in:
 *   assets/js/pages/profile-messages.js
 *
 * profile.js calls initMessagesTab() from bindPanelEvents() after the HTML is
 * injected, so all event listeners are attached correctly every time the tab loads.
 *
 * See profile.js bindPanelEvents() → case 'messages'.
 */