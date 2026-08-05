(() => {
    'use strict';

    const navItems  = [...document.querySelectorAll('.pf-nav-item')];
    const rail      = document.querySelector('.pf-nav-rail');
    const panelBody = document.getElementById('pf-panel-body');
    const loadingEl = document.getElementById('pf-loading');
    const toastEl   = document.getElementById('pf-toast');

    const TAB_ENDPOINTS = {
        overview : 'ajax/profile/tab_overview.php',
        orders   : 'ajax/profile/tab_orders.php',
        messages : 'ajax/profile/tab_messages.php',
        personal : 'ajax/profile/tab_personal.php',
        security : 'ajax/profile/tab_security.php',
    };

    let currentTab        = null;
    let requestController = null;

    // ── Toast ─────────────────────────────────────────────────────────────────
    let toastTimer;

    function showToast(message, error = false) {
        if (!toastEl) return;
        toastEl.textContent = message;
        toastEl.classList.toggle('pf-toast--error', error);
        toastEl.classList.add('is-visible');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => toastEl.classList.remove('is-visible'), 3200);
    }

    // ── Sliding rail ──────────────────────────────────────────────────────────
    function moveRailTo(btn) {
        if (!rail || !btn) return;
        rail.style.transform = `translateY(${btn.offsetTop}px)`;
        rail.style.height    = `${btn.offsetHeight}px`;
    }

    // ── Tab loader ────────────────────────────────────────────────────────────
    async function loadTab(tabName, options = {}) {
        const { pushState = true } = options;
        const endpoint = TAB_ENDPOINTS[tabName];
        if (!endpoint) return;

        // Cancel any in-flight request
        if (requestController) requestController.abort();
        requestController = new AbortController();

        currentTab = tabName;
        updateActiveTab(tabName);
        loadingEl?.classList.add('is-visible');

        try {
            const res = await fetch(endpoint, {
                signal:  requestController.signal,
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });

            if (res.status === 401) {
                location.href = '/entry/';
                return;
            }

            const html = await res.text();

            // Swap content with animation re-trigger
            panelBody.classList.remove('pf-panel-body');
            panelBody.innerHTML = html;
            void panelBody.offsetWidth;          // force reflow
            panelBody.classList.add('pf-panel-body');

            // Wire up tab-specific JS AFTER the HTML is in the DOM
            bindPanelEvents(tabName);

            if (pushState) {
                history.pushState({ tab: tabName }, '', `/profile?tab=${tabName}`);
            }

        } catch (err) {
            if (err.name === 'AbortError') return;
            panelBody.innerHTML = `
                <div class="pf-empty">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <p>خطا در بارگذاری اطلاعات</p>
                </div>`;
        } finally {
            loadingEl?.classList.remove('is-visible');
        }
    }

    // ── Active nav state ──────────────────────────────────────────────────────
    function updateActiveTab(tab) {
        navItems.forEach(btn => {
            const active = btn.dataset.tab === tab;
            btn.classList.toggle('is-active', active);
            btn.setAttribute('aria-selected', active ? 'true' : 'false');
            if (active) moveRailTo(btn);
        });
    }

    // ── Nav click ─────────────────────────────────────────────────────────────
    navItems.forEach(btn => {
        btn.addEventListener('click', () => {
            const tab = btn.dataset.tab;
            if (tab !== currentTab) loadTab(tab);
        });
    });

    // ── data-goto-tab links inside tab panels ─────────────────────────────────
    document.addEventListener('click', e => {
        const btn = e.target.closest('[data-goto-tab]');
        if (btn) loadTab(btn.dataset.gotoTab);
    });

    // ── Browser back / forward ────────────────────────────────────────────────
    window.addEventListener('popstate', () => {
        const params = new URLSearchParams(location.search);
        const tab    = params.get('tab') || 'overview';
        loadTab(TAB_ENDPOINTS[tab] ? tab : 'overview', { pushState: false });
    });

    // ── Resize: keep rail aligned ─────────────────────────────────────────────
    window.addEventListener('resize', () => {
        const active = document.querySelector('.pf-nav-item.is-active');
        if (active) moveRailTo(active);
    });

    // ── Bind tab-specific behaviour AFTER HTML injection ──────────────────────
    function bindPanelEvents(tab) {
        if (tab === 'orders')   bindOrdersTab();
        if (tab === 'messages') initMessagesTab();   // defined in profile-messages.js
        if (tab === 'personal') bindPersonalForm();
        if (tab === 'security') bindSecurityForm();
    }

    // ── Orders / receipt upload ───────────────────────────────────────────────
    function bindOrdersTab() {
        const CSRF    = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const fileMap = {};

        document.querySelectorAll('.receipt-file-input').forEach(inp => {
            inp.addEventListener('change', function () {
                const id   = this.dataset.order;
                const file = this.files[0];
                if (!file) return;

                fileMap[id] = file;

                const preview = document.getElementById('receipt-preview-' + id);
                if (preview) {
                    preview.src = URL.createObjectURL(file);
                    preview.classList.remove('hidden');
                }

                const upBtn = document.getElementById('btn-upload-' + id);
                if (upBtn) upBtn.disabled = false;
            });
        });

        document.querySelectorAll('.btn-clear-receipt').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.dataset.order;
                delete fileMap[id];

                const inp = document.getElementById('receipt-file-' + id);
                if (inp) inp.value = '';

                document.getElementById('receipt-preview-' + id)?.classList.add('hidden');

                const upBtn = document.getElementById('btn-upload-' + id);
                if (upBtn) upBtn.disabled = true;
            });
        });

        document.querySelectorAll('.btn-upload-receipt').forEach(btn => {
            btn.addEventListener('click', async function () {
                const id   = this.dataset.order;
                const file = fileMap[id];
                if (!file) return;

                this.disabled  = true;
                this.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> در حال ارسال…';

                const fd    = new FormData();
                fd.append('csrf_token', CSRF);
                fd.append('order_id',   id);
                fd.append('receipt',    file);

                const msgEl = document.getElementById('upload-msg-' + id);

                try {
                    const res  = await fetch('ajax/profile/uploadReceipt.php', { method: 'POST', body: fd });

                    if (res.status === 401) { location.href = '/entry/'; return; }

                    const data = await res.json();

                    if (msgEl) {
                        msgEl.textContent = data.message || (data.status ? 'رسید با موفقیت ارسال شد.' : 'خطا در ارسال');
                        msgEl.className   = 'pf-upload-msg ' + (data.status ? 'success' : 'error');
                        msgEl.classList.remove('hidden');
                    }

                    if (data.status) {
                        showToast(data.message || 'رسید با موفقیت ارسال شد.');
                        setTimeout(() => loadTab('orders', { pushState: false }), 1200);
                    } else {
                        this.disabled  = false;
                        this.innerHTML = '<i class="fa-solid fa-paper-plane"></i> ارسال رسید';
                        showToast(data.message || 'خطا در ارسال رسید', true);
                    }

                } catch {
                    this.disabled  = false;
                    this.innerHTML = '<i class="fa-solid fa-paper-plane"></i> ارسال رسید';
                    if (msgEl) {
                        msgEl.textContent = 'خطا در اتصال به سرور';
                        msgEl.className   = 'pf-upload-msg error';
                        msgEl.classList.remove('hidden');
                    }
                    showToast('خطا در اتصال به سرور', true);
                }
            });
        });
    }

    // ── Personal-info form ────────────────────────────────────────────────────
    function bindPersonalForm() {
        const form = document.getElementById('pf-personal-form');
        if (!form) return;

        form.onsubmit = async e => {
            e.preventDefault();
            const res  = await fetch('ajax/profile/action_update_personal.php', { method: 'POST', body: new FormData(form) });
            const data = await res.json();
            if (data.ok) {
                showToast('اطلاعات ذخیره شد');
                setTimeout(() => location.reload(), 1000);
            }
        };
    }

    // ── Password-change form ──────────────────────────────────────────────────
    function bindSecurityForm() {
        const form = document.getElementById('pf-password-form');
        if (!form) return;

        form.onsubmit = async e => {
            e.preventDefault();
            const res  = await fetch('ajax/profile/action_update_password.php', { method: 'POST', body: new FormData(form) });
            const data = await res.json();
            if (data.ok) {
                showToast('رمز عبور تغییر کرد');
                form.reset();
            }
        };
    }

    // ── Boot ──────────────────────────────────────────────────────────────────
    const params   = new URLSearchParams(location.search);
    const firstTab = params.get('tab') || 'overview';
    loadTab(TAB_ENDPOINTS[firstTab] ? firstTab : 'overview', { pushState: false });

})();