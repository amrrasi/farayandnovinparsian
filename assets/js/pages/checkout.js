/**
 * checkout.js
 * Handles: receipt upload, pre-invoice modal, print, PDF download, order submit
 */
'use strict';

(function () {

    const DATA = window.CHECKOUT_DATA || {};
    const CSRF = DATA.csrfToken || '';

    // ─────────────────────────────────────
    //  Toast
    // ─────────────────────────────────────
    function showToast(msg, type = 'success') {
        const toast  = document.getElementById('cartToast');
        const msgEl  = document.getElementById('cartToastMsg');
        const iconEl = toast.querySelector('i');
        if (!toast) return;
        msgEl.textContent = msg;
        toast.className   = 'cart-toast ' + type;
        iconEl.className  = type === 'success'
            ? 'fas fa-circle-check'
            : 'fas fa-circle-xmark';
        void toast.offsetWidth;
        toast.classList.add('show');
        clearTimeout(toast._t);
        toast._t = setTimeout(() => toast.classList.remove('show'), 3200);
    }

    // ─────────────────────────────────────
    //  Copy card number
    // ─────────────────────────────────────
    const copyBtn = document.getElementById('copyCardBtn');
    if (copyBtn) {
        copyBtn.addEventListener('click', function () {
            const num = document.getElementById('bankCardNumber')?.textContent?.replace(/\s|-/g, '') || DATA.bankCard || '';
            navigator.clipboard.writeText(num).then(() => {
                showToast('شماره کارت کپی شد');
            }).catch(() => {
                showToast('خطا در کپی', 'error');
            });
        });
    }

    // ─────────────────────────────────────
    //  Receipt upload
    // ─────────────────────────────────────
    const uploadZone    = document.getElementById('uploadZone');
    const receiptInput  = document.getElementById('receiptFile');
    const uploadPreview = document.getElementById('uploadPreview');
    const uploadName    = document.getElementById('uploadFileName');
    const removeFile    = document.getElementById('removeFile');

    let uploadedFile = null;

    function setFile(file) {
        if (!file) return;
        const maxSize = 5 * 1024 * 1024;
        if (file.size > maxSize) {
            showToast('حجم فایل بیش از ۵ مگابایت است', 'error');
            return;
        }
        uploadedFile = file;
        uploadName.textContent = file.name;
        uploadPreview.classList.add('show');
    }

    if (receiptInput) {
        receiptInput.addEventListener('change', function () {
            setFile(this.files[0]);
        });
    }

    if (uploadZone) {
        uploadZone.addEventListener('dragover', function (e) {
            e.preventDefault();
            this.classList.add('drag-over');
        });
        uploadZone.addEventListener('dragleave', function () {
            this.classList.remove('drag-over');
        });
        uploadZone.addEventListener('drop', function (e) {
            e.preventDefault();
            this.classList.remove('drag-over');
            setFile(e.dataTransfer.files[0]);
        });
    }

    if (removeFile) {
        removeFile.addEventListener('click', function () {
            uploadedFile = null;
            if (receiptInput) receiptInput.value = '';
            uploadPreview.classList.remove('show');
        });
    }

    // ─────────────────────────────────────
    //  Invoice modal
    // ─────────────────────────────────────
    const modal       = document.getElementById('invoiceModal');
    const showInvBtn  = document.getElementById('btnShowInvoice');
    const closeModal  = document.getElementById('btnCloseModal');

    function openModal() {
        // Fill dynamic fields from form
        const name    = document.getElementById('fullName')?.value  || '—';
        const phone   = document.getElementById('phone')?.value     || '—';
        const address = document.getElementById('address')?.value   || '—';

        const invName    = document.getElementById('inv-name');
        const invPhone   = document.getElementById('inv-phone');
        const invAddress = document.getElementById('inv-address');
        const invDate    = document.getElementById('invoiceDate');

        if (invName)    invName.textContent    = name;
        if (invPhone)   invPhone.textContent   = phone;
        if (invAddress) invAddress.textContent = address;
        if (invDate)    invDate.textContent    = new Date().toLocaleDateString('fa-IR');

        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeModalFn() {
        modal.classList.remove('open');
        document.body.style.overflow = '';
    }

    if (showInvBtn) showInvBtn.addEventListener('click', openModal);
    if (closeModal) closeModal.addEventListener('click', closeModalFn);

    // Close on backdrop click
    if (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === modal) closeModalFn();
        });
    }

    // Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal?.classList.contains('open')) closeModalFn();
    });

    // ─────────────────────────────────────
    //  Print
    // ─────────────────────────────────────
    const btnPrint = document.getElementById('btnPrint');
    if (btnPrint) {
        btnPrint.addEventListener('click', function () {
            window.print();
        });
    }

    // ─────────────────────────────────────
    //  Download PDF  (html2canvas + jsPDF via CDN)
    // ─────────────────────────────────────
    const btnDownload = document.getElementById('btnDownload');
    if (btnDownload) {
        btnDownload.addEventListener('click', async function () {

            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> در حال آماده‌سازی…';

            try {
                // Lazy-load libraries
                await loadScript('https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js');
                await loadScript('https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js');

                const paper = document.getElementById('invoicePaper');

                const canvas = await html2canvas(paper, {
                    scale: 2,
                    useCORS: true,
                    backgroundColor: getComputedStyle(document.documentElement)
                        .getPropertyValue('--clr-bg').trim() || '#F7F3EB',
                });

                const { jsPDF } = window.jspdf;
                const pdf  = new jsPDF({ orientation: 'p', unit: 'mm', format: 'a4' });
                const imgW = 210;
                const imgH = (canvas.height * imgW) / canvas.width;

                pdf.addImage(canvas.toDataURL('image/jpeg', .92), 'JPEG', 0, 0, imgW, imgH);
                pdf.save((DATA.invoiceNo || 'invoice') + '.pdf');

                showToast('فایل PDF دانلود شد');

            } catch (err) {
                console.error(err);
                showToast('خطا در تهیه PDF. لطفاً از چاپ استفاده کنید.', 'error');
            }

            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-download"></i> دریافت PDF';
        });
    }

    function loadScript(src) {
        return new Promise((resolve, reject) => {
            if (document.querySelector(`script[src="${src}"]`)) { resolve(); return; }
            const s = document.createElement('script');
            s.src = src;
            s.onload  = resolve;
            s.onerror = reject;
            document.head.appendChild(s);
        });
    }

    // ─────────────────────────────────────
    //  Submit order
    // ─────────────────────────────────────
    const btnSubmit = document.getElementById('btnSubmitOrder');
    if (btnSubmit) {
        btnSubmit.addEventListener('click', async function () {

            // Validate form
            const fullName = document.getElementById('fullName')?.value.trim();
            const phone    = document.getElementById('phone')?.value.trim();
            const address  = document.getElementById('address')?.value.trim();

            if (!fullName) { showToast('نام و نام خانوادگی الزامی است', 'error'); return; }
            if (!phone)    { showToast('شماره تماس الزامی است', 'error'); return; }
            if (!address)  { showToast('آدرس تحویل الزامی است', 'error'); return; }

            if (!uploadedFile) {
                showToast('لطفاً تصویر رسید پرداخت را بارگذاری کنید', 'error');
                return;
            }

            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> در حال ثبت سفارش…';

            try {
                const formData = new FormData();
                formData.append('csrf_token', CSRF);
                formData.append('full_name', fullName);
                formData.append('phone', phone);
                formData.append('email', document.getElementById('email')?.value.trim() || '');
                formData.append('address', address);
                formData.append('postal_code', document.getElementById('postalCode')?.value.trim() || '');
                formData.append('order_note', document.getElementById('orderNote')?.value.trim() || '');
                formData.append('receipt', uploadedFile);

                const res  = await fetch(DATA.submitUrl || 'api/submitOrder.php', {
                    method: 'POST',
                    body: formData,
                });
                const data = await res.json();

                if (data.status) {
                    showToast('سفارش شما با موفقیت ثبت شد!');
                    // Redirect to success / panel after short delay
                    setTimeout(() => {
                        window.location.href = data.redirect || 'panel.php?tab=orders';
                    }, 1800);
                } else {
                    showToast(data.message || 'خطا در ثبت سفارش', 'error');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-circle-check"></i> ثبت نهایی سفارش';
                }

            } catch (err) {
                console.error(err);
                showToast('خطا در اتصال به سرور', 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-circle-check"></i> ثبت نهایی سفارش';
            }

        });
    }

})();
