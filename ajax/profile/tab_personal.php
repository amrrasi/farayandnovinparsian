<?php
require_once '../../cms/myadmin/inc/config.php';
?>
<div class="pf-personal">
    <div class="pf-panel-title">
        <h2>اطلاعات شخصی</h2>
    </div>

    <form class="pf-form" id="pf-personal-form">
        <div class="pf-field">
            <label for="pf-name">نام و نام خانوادگی</label>
            <input type="text" id="pf-name" name="name" value="<?= htmlspecialchars($_SESSION['user']['name'], ENT_QUOTES, 'UTF-8') ?>" required maxlength="150">
        </div>

        <div class="pf-field">
            <label for="pf-mobile">شماره موبایل</label>
            <div class="pf-field-locked">
                <input type="text" id="pf-mobile" value="<?= htmlspecialchars($_SESSION['user']['mobile'], ENT_QUOTES, 'UTF-8') ?>" disabled>

            </div>
            <span class="pf-field-hint">برای تغییر شماره موبایل با پشتیبانی در تماس باشید.</span>
        </div>

        <div class="pf-field">
            <label for="pf-email">ایمیل</label>
            <input type="email" id="pf-email" name="email" value="<?= htmlspecialchars($_SESSION['user']['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" maxlength="200">
        </div>

        <div class="pf-field">
            <label for="pf-description">درباره من</label>
            <textarea id="pf-description" name="description" maxlength="1000" placeholder="یک توضیح کوتاه درباره خودتان..."><?= htmlspecialchars($_SESSION['user']['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>

        <div class="pf-form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-check"></i>
                <span>ذخیره تغییرات</span>
            </button>
        </div>
    </form>
</div>
