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
 *   We use seen=0 + reply_count>0 to show the user an unread dot (admin replied and we haven't seen it yet).
 */
require_once '../../cms/myadmin/inc/config.php';

$uid = (int) $_SESSION['user']['id'];
if (empty($_SESSION['user']['id'])) {
    header('Location: ../entry/');
    exit;
}
$stmt = $pdo->prepare("
    SELECT
        cm.id,
        cm.subject,
        cm.message,
        cm.seen,
        cm.created_at,
        (SELECT COUNT(*)            FROM message_replies mr WHERE mr.message_id = cm.id) AS reply_count,
        (SELECT MAX(mr.created_at)  FROM message_replies mr WHERE mr.message_id = cm.id) AS last_reply_at
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

                // Show unread dot when admin has replied (reply_count > 0) but seen is still 0
                $isUnread = !$m['seen'] && $m['reply_count'] > 0;
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

    <!-- Thread overlay -->
    <div class="pf-thread-overlay" id="pf-thread-overlay" hidden>
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
                <div class="d-flex justify-content-center align-items-center h-100" id="pf-thread-loading">
                    <span class="pf-spinner"></span>
                </div>
                <div id="pf-thread-bubbles" class="d-flex flex-column gap-2"></div>
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

<script>
    (function () {
        'use strict';

        const CSRF      = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const root      = document.getElementById('pf-messages-root');
        const overlay   = document.getElementById('pf-thread-overlay');
        const backBtn   = document.getElementById('pf-thread-back-btn');
        const subjectEl = document.getElementById('pf-thread-subject');
        const dateEl    = document.getElementById('pf-thread-date');
        const loadingEl = document.getElementById('pf-thread-loading');
        const bubblesEl = document.getElementById('pf-thread-bubbles');
        const scrollEl  = document.getElementById('pf-thread-scroll');
        const replyText = document.getElementById('pf-reply-text');
        const sendBtn   = document.getElementById('pf-reply-send');

        let activeMessageId = null;

        root.addEventListener('click', function (e) {
            const row = e.target.closest('.pf-msg-row');
            if (!row) return;
            const msgId = parseInt(row.dataset.messageId, 10);
            if (!msgId) return;
            openThread(msgId, row);
        });

        backBtn.addEventListener('click', closeThread);

        replyText.addEventListener('input', function () {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 140) + 'px';
        });

        sendBtn.addEventListener('click', sendReply);
        replyText.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) sendReply();
        });

        function openThread(msgId, row) {
            activeMessageId         = msgId;
            overlay.hidden          = false;
            loadingEl.style.display = 'flex';
            bubblesEl.innerHTML     = '';
            replyText.value         = '';
            replyText.style.height  = '';
            subjectEl.textContent   = row?.querySelector('.pf-msg-subject')?.textContent?.trim() || '…';
            dateEl.textContent      = '';
            row?.classList.remove('is-unread');
            fetchThread(msgId);
        }

        function closeThread() {
            overlay.hidden  = true;
            activeMessageId = null;
        }

        async function fetchThread(msgId) {
            try {
                const res  = await fetch('ajax/profile/action_message_thread.php?id=' + msgId,
                    { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const data = await res.json();
                loadingEl.style.display = 'none';

                if (!data.status) {
                    bubblesEl.innerHTML = '<p class="text-danger text-center small">'
                        + (data.message || 'خطا در بارگذاری') + '</p>';
                    return;
                }

                if (data.subject)    subjectEl.textContent = data.subject;
                if (data.created_at) dateEl.textContent    = data.created_at;
                renderBubbles(data.messages || []);
                scrollToBottom();

            } catch (err) {
                loadingEl.style.display = 'none';
                bubblesEl.innerHTML = '<p class="text-danger text-center small">خطا در اتصال به سرور</p>';
            }
        }

        function renderBubbles(msgs) {
            bubblesEl.innerHTML = '';
            if (!msgs.length) {
                bubblesEl.innerHTML = '<p class="text-muted text-center small">هنوز پیامی ارسال نشده.</p>';
                return;
            }
            msgs.forEach(m => bubblesEl.appendChild(makeBubble(m)));
        }
        function makeBubble(m) {
            const isAdmin = m.sender === 'admin';
            const wrap    = document.createElement('div');
            wrap.className = 'pf-bubble ' + (isAdmin ? 'pf-bubble--admin' : 'pf-bubble--user');

            if (isAdmin) {
                const tag = document.createElement('span');
                tag.className   = 'pf-bubble-tag';
                tag.textContent = 'پشتیبانی';
                wrap.appendChild(tag);
            }

            const body = document.createElement('div');
            body.textContent = m.body || '';
            wrap.appendChild(body);

            if (m.created_at) {
                const time = document.createElement('span');
                time.className   = 'pf-bubble-time';
                time.textContent = m.created_at;
                wrap.appendChild(time);
            }

            return wrap;
        }

        function scrollToBottom() {
            requestAnimationFrame(() => { scrollEl.scrollTop = scrollEl.scrollHeight; });
        }

        async function sendReply() {
            const text = replyText.value.trim();
            if (!text || !activeMessageId) return;

            sendBtn.disabled  = true;
            sendBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

            const fd = new FormData();
            fd.append('csrf_token', CSRF);
            fd.append('message_id', activeMessageId);
            fd.append('reply', text);

            try {
                const res  = await fetch('ajax/profile/action_send_reply.php', { method: 'POST', body: fd });
                const data = await res.json();

                if (data.status) {
                    replyText.value        = '';
                    replyText.style.height = '';
                    bubblesEl.appendChild(makeBubble({
                        sender:     'user',
                        body:       text,
                        created_at: data.created_at || 'هم‌اکنون',
                    }));
                    scrollToBottom();
                } else {
                    alert(data.message || 'خطا در ارسال پیام');
                }
            } catch {
                alert('خطا در اتصال به سرور');
            } finally {
                sendBtn.disabled  = false;
                sendBtn.innerHTML = '<i class="fa-solid fa-paper-plane"></i><span>ارسال</span>';
            }
        }

    })();
</script>