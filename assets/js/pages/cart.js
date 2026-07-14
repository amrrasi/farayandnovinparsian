'use strict';

(function () {

    const DATA      = window.CART_DATA || {};
    const CSRF      = DATA.csrfToken   || '';
    const THRESHOLD = DATA.shippingThreshold || 500000;
    const SHIP_COST = DATA.shippingCost      || 35000;

    function showToast(msg, type = 'success') {
        const toast   = document.getElementById('cartToast');
        const msgEl   = document.getElementById('cartToastMsg');
        const iconEl  = toast.querySelector('i');
        if (!toast) return;
        msgEl.textContent = msg;
        toast.className   = 'cart-toast ' + type;
        iconEl.className  = type === 'success'
            ? 'fas fa-circle-check'
            : 'fas fa-circle-xmark';
        void toast.offsetWidth;
        toast.classList.add('show');
        clearTimeout(toast._timer);
        toast._timer = setTimeout(() => toast.classList.remove('show'), 3000);
    }

    function recalcSummary() {
        const items = document.querySelectorAll('.cart-item');
        let subtotal = 0;

        items.forEach(item => {
            const price = parseFloat(item.dataset.price) || 0;
            const qty   = parseInt(item.querySelector('.qty-value')?.textContent || '1', 10);
            subtotal   += price * qty;

            // update per-item price
            const priceEl = item.querySelector('.cart-item-price');
            if (priceEl) priceEl.textContent = ('نامشخص');
        });

        const shipping    = 'رایگان';
        const grand       = 'نامشخص';

        const subEl   = document.getElementById('summarySubtotal');
        const shipEl  = document.getElementById('summaryShipping');
        const totEl   = document.getElementById('summaryTotal');
        const badge   = document.querySelector('.cart-badge');
        const summary = document.getElementById('cartSummary');

        if (subEl)  subEl.textContent  = 'نامشخص';
        if (totEl)  totEl.textContent  = 'نامشخص';

        if (shipEl) {
            if (shipping === 0) {
                shipEl.innerHTML = '<span style="color:var(--clr-success)">رایگان</span>';
            } else {
                shipEl.innerHTML = '<span style="color:var(--clr-success)">رایگان</span>';
            }
        }

        let totalQty = 0;
        items.forEach(item => {
            totalQty += parseInt(item.querySelector('.qty-value')?.textContent || '1', 10);
        });
        if (badge) badge.textContent = totalQty;

        if (items.length === 0 && summary) {
            summary.style.display = 'none';
            showEmptyState();
        }

        // update header cart count if it exists
        const headerCount = document.getElementById('cartCount');
        if (headerCount) headerCount.textContent = totalQty;
    }

    function showEmptyState() {
        const left = document.querySelector('.cart-left');
        if (!left) return;
        left.innerHTML = `
            <div class="cart-heading"><h1>سبد خرید</h1></div>
            <div class="cart-empty reveal revealed">
                <i class="fas fa-cart-xmark"></i>
                <p>سبد خرید شما خالی است</p>
                <a href="products/" class="btn btn-primary">
                    <i class="fas fa-bag-shopping"></i>
                    مشاهده محصولات
                </a>
            </div>`;
    }

    async function apiPost(url, body) {
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-Token': CSRF,
            },
            body: new URLSearchParams(body),
        });
        return res.json();
    }

    async function changeQty(id, delta) {
        const item   = document.querySelector(`.cart-item[data-id="${id}"]`);
        if (!item) return;
        const qtyEl = item.querySelector('.qty-value');
        let qty     = parseInt(qtyEl.textContent, 10) + delta;

        if (qty < 1) {
            await removeItem(id);
            return;
        }

        qtyEl.textContent = qty;
        recalcSummary();

        try {
            const data = await apiPost(DATA.updateUrl || 'ajax/cart/updateCart.php', { id, qty });
            if (!data.status) {
                qtyEl.textContent = qty - delta;
                recalcSummary();
                showToast(data.message || 'خطا در بروزرسانی', 'error');
            }
        } catch {
            qtyEl.textContent = qty - delta;
            recalcSummary();
            showToast('خطا در اتصال به سرور', 'error');
        }
    }

    async function removeItem(id) {
        const item = document.querySelector(`.cart-item[data-id="${id}"]`);
        if (!item) return;

        item.style.transition = 'opacity .3s, transform .3s';
        item.style.opacity    = '0';
        item.style.transform  = 'translateX(30px)';

        await new Promise(r => setTimeout(r, 300));
        item.remove();
        recalcSummary();

        try {
            const data = await apiPost(DATA.removeUrl || 'ajax/cart/removeFromCart.php', { id });
            if (data.status) {
                showToast('محصول از سبد حذف شد');
            } else {
                showToast(data.message || 'خطا در حذف', 'error');
            }
        } catch {
            showToast('خطا در اتصال به سرور', 'error');
        }
    }

    async function clearCart() {
        if (!confirm('آیا مطمئن هستید؟ تمام محصولات از سبد حذف می‌شوند.')) return;

        const items = document.querySelectorAll('.cart-item');
        items.forEach(item => {
            item.style.transition = 'opacity .25s, transform .25s';
            item.style.opacity    = '0';
            item.style.transform  = 'scale(.95)';
        });

        await new Promise(r => setTimeout(r, 280));
        items.forEach(item => item.remove());
        recalcSummary();

        try {
            await apiPost(DATA.clearUrl || 'ajax/cart/clearCart.php', {});
            showToast('سبد خرید پاک شد');
        } catch {
            showToast('خطا در اتصال به سرور', 'error');
        }
    }

    document.addEventListener('click', function (e) {

        const plus = e.target.closest('.btn-plus');
        if (plus) { changeQty(plus.dataset.id, +1); return; }

        const minus = e.target.closest('.btn-minus');
        if (minus) { changeQty(minus.dataset.id, -1); return; }

        const remove = e.target.closest('.btn-remove');
        if (remove) { removeItem(remove.dataset.id); return; }

        const clear = e.target.closest('#btnClearCart');
        if (clear) { clearCart(); return; }

    });

})();
