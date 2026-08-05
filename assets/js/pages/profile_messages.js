function initMessagesTab() {
    'use strict';

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

    if (!root || !overlay) return;

    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';

    let activeMessageId = null;

    root.addEventListener('click', function (e) {
        const row = e.target.closest('.pf-msg-row');
        if (!row) return;
        const msgId = parseInt(row.dataset.messageId, 10);
        if (!msgId) return;
        openThread(msgId, row);
    });

    backBtn.addEventListener('click', closeThread);

    document.addEventListener('keydown', function onKeyDown(e) {
        if (e.key === 'Escape' && activeMessageId !== null) {
            closeThread();
            document.removeEventListener('keydown', onKeyDown);
        }
    });

    replyText.addEventListener('input', function () {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 140) + 'px';
    });

    sendBtn.addEventListener('click', sendReply);
    replyText.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) sendReply();
    });


    function openThread(msgId, row) {
        activeMessageId = msgId;

        overlay.style.display = '';   // remove the inline display:none set in PHP
        overlay.removeAttribute('aria-hidden');

        loadingEl.style.display = 'flex';
        bubblesEl.innerHTML     = '';
        replyText.value         = '';
        replyText.style.height  = '';

        subjectEl.textContent = row?.querySelector('.pf-msg-subject')?.textContent?.trim() || '…';
        dateEl.textContent    = '';

        row?.classList.remove('is-unread');

        fetchThread(msgId);
    }

    function closeThread() {
        overlay.style.display = 'none';
        overlay.setAttribute('aria-hidden', 'true');
        activeMessageId = null;
    }

    async function fetchThread(msgId) {
        try {
            const res  = await fetch(
                'ajax/profile/action_message_thread.php?id=' + msgId,
                { headers: { 'X-Requested-With': 'XMLHttpRequest' } }
            );

            if (res.status === 401) {
                location.href = '/entry/';
                return;
            }

            const data = await res.json();
            loadingEl.style.display = 'none';

            if (!data.status) {
                bubblesEl.innerHTML =
                    '<p class="pf-thread-error">' + escHtml(data.message || 'خطا در بارگذاری') + '</p>';
                return;
            }

            if (data.subject)    subjectEl.textContent = data.subject;
            if (data.created_at) dateEl.textContent    = data.created_at;

            renderBubbles(data.messages || []);
            scrollToBottom();

        } catch (err) {
            loadingEl.style.display = 'none';
            bubblesEl.innerHTML =
                '<p class="pf-thread-error">خطا در اتصال به سرور</p>';
        }
    }

    function renderBubbles(msgs) {
        bubblesEl.innerHTML = '';
        if (!msgs.length) {
            bubblesEl.innerHTML =
                '<p class="text-muted text-center small">هنوز پیامی ارسال نشده.</p>';
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
        body.className = 'pf-bubble-body';
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
        if (!text || activeMessageId === null) return;

        sendBtn.disabled  = true;
        replyText.disabled = true;
        sendBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

        const fd = new FormData();
        fd.append('csrf_token',  CSRF);
        fd.append('message_id',  activeMessageId);
        fd.append('reply',       text);

        try {
            const res  = await fetch('ajax/profile/action_send_reply.php', {
                method: 'POST',
                body:   fd,
            });

            if (res.status === 401) {
                location.href = '/entry/';
                return;
            }

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
                showThreadError(data.message || 'خطا در ارسال پیام');
            }
        } catch {
            showThreadError('خطا در اتصال به سرور');
        } finally {
            sendBtn.disabled   = false;
            replyText.disabled = false;
            sendBtn.innerHTML  = '<i class="fa-solid fa-paper-plane"></i><span>ارسال</span>';
        }
    }

    function showThreadError(msg) {
        const err = document.createElement('p');
        err.className   = 'pf-thread-error';
        err.textContent = msg;
        bubblesEl.appendChild(err);
        scrollToBottom();
        setTimeout(() => err.remove(), 4000);
    }

    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }
}