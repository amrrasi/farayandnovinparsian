<?php
require_once '../../cms/myadmin/inc/config.php';
if (empty($_SESSION['user']['id'])) {
    header('Location: ../entry/');
    exit;
}
?>
<div class="pf-security">
    <div class="pf-panel-title">
        <h2>امنیت حساب</h2>
    </div>

    <form class="pf-form" id="pf-password-form" novalidate>
        <div class="pf-field">
            <label for="pf-current-pass" class="form-label">رمز عبور فعلی</label>
            <input type="password"
                   class="form-control"
                   id="pf-current-pass"
                   name="current_password"
                   required
                   autocomplete="current-password">
        </div>

        <div class="pf-field">
            <label for="pf-new-pass" class="form-label">رمز عبور جدید</label>
            <input type="password"
                   class="form-control"
                   id="pf-new-pass"
                   name="new_password"
                   required
                   minlength="8"
                   autocomplete="new-password">
            <span class="pf-field-hint">حداقل ۸ کاراکتر، ترکیبی از حروف و عدد پیشنهاد می‌شود.</span>
        </div>

        <div class="pf-field">
            <label for="pf-new-pass-confirm" class="form-label">تکرار رمز عبور جدید</label>
            <input type="password"
                   class="form-control"
                   id="pf-new-pass-confirm"
                   name="new_password_confirm"
                   required
                   minlength="8"
                   autocomplete="new-password">
        </div>

        <div class="pf-form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-shield-halved"></i>
                <span>تغییر رمز عبور</span>
            </button>
        </div>
    </form>

    <div class="pf-divider"></div>

    <div class="pf-session-info">
        <h3 class="fs-6 fw-semibold mb-2">جلسه فعلی</h3>
        <p class="text-muted small">
            اگر این دستگاه یا مرورگر متعلق به شما نیست، توصیه می‌شود بلافاصله رمز عبور خود را تغییر دهید.
        </p>
    </div>
</div>