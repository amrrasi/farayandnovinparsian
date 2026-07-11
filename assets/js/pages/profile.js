(() => {
    'use strict';

    const navItems   = Array.from(document.querySelectorAll('.pf-nav-item'));
    const rail        = document.querySelector('.pf-nav-rail');
    const panelBody   = document.getElementById('pf-panel-body');
    const loadingEl   = document.getElementById('pf-loading');
    const toastEl     = document.getElementById('pf-toast');

    const TAB_ENDPOINTS = {
        overview: 'ajax/profile/tab_overview.php',
        orders:   'ajax/profile/tab_orders.php',
        messages: 'ajax/profile/tab_messages.php',
        personal: 'ajax/profile/tab_personal.php',
        security: 'ajax/profile/tab_security.php',
    };

    let currentTab = null;

    // ---------------------------------------------------------------
    // Toast
    // ---------------------------------------------------------------
    let toastTimer = null;
    function showToast(message, isError = false) {
        toastEl.textContent = message;
        toastEl.classList.toggle('pf-toast--error', isError);
        toastEl.classList.add('is-visible');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => toastEl.classList.remove('is-visible'), 3200);
    }

    // ---------------------------------------------------------------
    // Sliding rail indicator
    // ---------------------------------------------------------------
    function moveRailTo(button) {
        if (!rail || !button) return;
        rail.style.transform = `translateY(${button.offsetTop}px)`;
        rail.style.height = `${button.offsetHeight}px`;
    }

    // ---------------------------------------------------------------
    // Tab loading
    // ---------------------------------------------------------------
    async function loadTab(tabName, { pushState = true } = {}) {
        const endpoint = TAB_ENDPOINTS[tabName];
        if (!endpoint) return;

        currentTab = tabName;
        loadingEl.classList.add('is-visible');

        navItems.forEach(btn => {
            const isActive = btn.dataset.tab === tabName;
            btn.classList.toggle('is-active', isActive);
            btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
            if (isActive) moveRailTo(btn);
        });

        try {
            const res = await fetch(endpoint, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (res.status === 401) { window.location.href = '/login.php'; return; }
            const html = await res.text();
            panelBody.innerHTML = html;
            panelBody.classList.remove('pf-panel-body');
            void panelBody.offsetWidth; // restart animation
            panelBody.classList.add('pf-panel-body');
            bindPanelEvents(tabName);
            if (pushState) history.replaceState({ tab: tabName }, '', `#${tabName}`);
        } catch (err) {
            panelBody.innerHTML = '<div class="pf-empty"><i class="fa-solid fa-triangle-exclamation"></i><p>خطا در بارگذاری اطلاعات. دوباره تلاش کنید.</p></div>';
        } finally {
            loadingEl.classList.remove('is-visible');
        }
    }

    navItems.forEach(btn => {
        btn.addEventListener('click', () => loadTab(btn.dataset.tab));
    });

    // "Go to tab" buttons living inside tab content (e.g. overview quick actions)
    document.addEventListener('click', (e) => {
        const trigger = e.target.closest('[data-goto-tab]');
        if (trigger) loadTab(trigger.dataset.gotoTab);
    });

    window.addEventListener('resize', () => {
        const active = document.querySelector('.pf-nav-item.is-active');
        if (active) moveRailTo(active);
    });

    // ---------------------------------------------------------------
    // Per-tab event bindings (re-run every time new HTML is injected)
    // ---------------------------------------------------------------
    function bindPanelEvents(tabName) {
        if (tabName === 'messages') bindMessagesTab();
        if (tabName === 'personal') bindPersonalForm();
        if (tabName === 'security') bindSecurityForm();
    }

    // ---- Messages: open thread overlay ----
    function bindMessagesTab() {
        const rows = document.querySelectorAll('.pf-msg-row');
        const overlay = document.getElementById('pf-thread-overlay');
        const threadBody = document.getElementById('pf-thread-body');

        rows.forEach(row => {
            row.addEventListener('click', async () => {
                const id = row.dataset.messageId;
                overlay.hidden = false;
                threadBody.innerHTML = '<span class="pf-spinner"></span>';
                row.classList.remove('is-unread');
                row.querySelector('.pf-msg-dot').style.background = 'transparent';

                const res = await fetch(`ajax/profile/action_message_thread.php?id=${encodeURIComponent(id)}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                threadBody.innerHTML = await res.text();
                bindThreadEvents(overlay);

                const scroll = threadBody.querySelector('.pf-thread-scroll');
                if (scroll) scroll.scrollTop = scroll.scrollHeight;
            });
        });
    }

    function bindThreadEvents(overlay) {
        const backBtn = overlay.querySelector('#pf-thread-back');
        if (backBtn) backBtn.addEventListener('click', () => { overlay.hidden = true; });

        const form = overlay.querySelector('#pf-reply-form');
        if (form) {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const textarea = form.querySelector('textarea[name="body"]');
                const body = textarea.value.trim();
                if (!body) return;

                const submitBtn = form.querySelector('button[type="submit"]');
                submitBtn.disabled = true;

                const fd = new FormData();
                fd.append('message_id', form.dataset.messageId);
                fd.append('body', body);

                try {
                    const res = await fetch('ajax/profile/action_send_reply.php', {
                        method: 'POST',
                        body: fd,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const data = await res.json();
                    if (data.ok) {
                        textarea.value = '';
                        showToast('پاسخ شما ارسال شد.');
                        // reload the thread to show the new bubble
                        const id = form.dataset.messageId;
                        const threadRes = await fetch(`ajax/profile/action_message_thread.php?id=${encodeURIComponent(id)}`, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        const threadBody = document.getElementById('pf-thread-body');
                        threadBody.innerHTML = await threadRes.text();
                        bindThreadEvents(overlay);
                        const scroll = threadBody.querySelector('.pf-thread-scroll');
                        if (scroll) scroll.scrollTop = scroll.scrollHeight;
                    } else {
                        showToast('ارسال پاسخ ناموفق بود.', true);
                    }
                } catch {
                    showToast('خطا در ارتباط با سرور.', true);
                } finally {
                    submitBtn.disabled = false;
                }
            });
        }
    }

    // ---- Personal data form ----
    function bindPersonalForm() {
        const form = document.getElementById('pf-personal-form');
        if (!form) return;

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            clearFieldErrors(form);
            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = true;

            const fd = new FormData(form);
            try {
                const res = await fetch('ajax/profile/action_update_personal.php', {
                    method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                if (data.ok) {
                    showToast('اطلاعات با موفقیت ذخیره شد.');
                    document.querySelector('.pf-side-name').textContent = data.name;
                } else if (data.errors) {
                    applyFieldErrors(form, data.errors);
                } else {
                    showToast('ذخیره تغییرات ناموفق بود.', true);
                }
            } catch {
                showToast('خطا در ارتباط با سرور.', true);
            } finally {
                submitBtn.disabled = false;
            }
        });
    }

    // ---- Security / password form ----
    function bindSecurityForm() {
        const form = document.getElementById('pf-password-form');
        if (!form) return;

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            clearFieldErrors(form);
            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = true;

            const fd = new FormData(form);
            try {
                const res = await fetch('ajax/profile/action_update_password.php', {
                    method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                if (data.ok) {
                    showToast('رمز عبور با موفقیت تغییر کرد.');
                    form.reset();
                } else if (data.errors) {
                    applyFieldErrors(form, data.errors);
                } else {
                    showToast('تغییر رمز عبور ناموفق بود.', true);
                }
            } catch {
                showToast('خطا در ارتباط با سرور.', true);
            } finally {
                submitBtn.disabled = false;
            }
        });
    }

    // ---- shared form error helpers ----
    function applyFieldErrors(form, errors) {
        Object.entries(errors).forEach(([field, message]) => {
            const input = form.querySelector(`[name="${field}"]`);
            if (!input) return;
            const fieldWrap = input.closest('.pf-field');
            fieldWrap.classList.add('has-error');
            let errEl = fieldWrap.querySelector('.pf-field-error');
            if (!errEl) {
                errEl = document.createElement('span');
                errEl.className = 'pf-field-error';
                fieldWrap.appendChild(errEl);
            }
            errEl.textContent = message;
        });
    }

    function clearFieldErrors(form) {
        form.querySelectorAll('.pf-field.has-error').forEach(f => {
            f.classList.remove('has-error');
            const err = f.querySelector('.pf-field-error');
            if (err) err.remove();
        });
    }

    // ---------------------------------------------------------------
    // Init: honour #hash on load (e.g. deep link to profile.php#orders)
    // ---------------------------------------------------------------
    const initialTab = (location.hash || '').replace('#', '');
    loadTab(TAB_ENDPOINTS[initialTab] ? initialTab : 'overview', { pushState: false });
})();
