<?php
require_once '../../cms/myadmin/inc/config.php';
?>
<div class="pf-security">
    <div class="pf-panel-title">
        <h2>امنیت حساب</h2>
    </div>

    <form class="pf-form" id="pf-password-form">
        <div class="pf-field">
            <label for="pf-current-pass">رمز عبور فعلی</label>
            <input type="password" id="pf-current-pass" name="current_password" required autocomplete="current-password">
        </div>

        <div class="pf-field">
            <label for="pf-new-pass">رمز عبور جدید</label>
            <input type="password" id="pf-new-pass" name="new_password" required minlength="8" autocomplete="new-password">
            <span class="pf-field-hint">حداقل ۸ کاراکتر، ترکیبی از حروف و عدد پیشنهاد می‌شود.</span>
        </div>

        <div class="pf-field">
            <label for="pf-new-pass-confirm">تکرار رمز عبور جدید</label>
            <input type="password" id="pf-new-pass-confirm" name="new_password_confirm" required minlength="8" autocomplete="new-password">
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
        <h3>جلسه فعلی</h3>
        <p class="text-muted">
            اگر این دستگاه یا مرورگر متعلق به شما نیست، توصیه می‌شود بلافاصله رمز عبور خود را تغییر دهید.
        </p>
    </div>
</div>
