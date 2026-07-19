<?php
require_once '../../cms/myadmin/inc/config.php';
?>
<div class="pf-personal">
    <div class="pf-panel-title">
        <h2>اطلاعات شخصی</h2>
    </div>

    <form class="pf-form" id="pf-personal-form" novalidate>
        <div class="pf-field">
            <label for="pf-name" class="form-label">نام و نام خانوادگی</label>
            <input type="text"
                   class="form-control"
                   id="pf-name"
                   name="name"
                   value="<?= htmlspecialchars($_SESSION['user']['name'], ENT_QUOTES, 'UTF-8') ?>"
                   required
                   maxlength="150"
                   autocomplete="name">
        </div>

        <div class="pf-field">
            <label for="pf-mobile" class="form-label">شماره موبایل</label>
            <input type="text"
                   class="form-control"
                   id="pf-mobile"
                   value="<?= htmlspecialchars($_SESSION['user']['mobile'], ENT_QUOTES, 'UTF-8') ?>"
                   disabled
                   dir="ltr"
                   style="text-align:right">
            <span class="pf-field-hint">برای تغییر شماره موبایل با پشتیبانی در تماس باشید.</span>
        </div>

        <div class="pf-field">
            <label for="pf-email" class="form-label">ایمیل</label>
            <input type="email"
                   class="form-control"
                   id="pf-email"
                   name="email"
                   value="<?= htmlspecialchars($_SESSION['user']['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                   maxlength="200"
                   autocomplete="email"
                   dir="ltr"
                   style="text-align:right">
        </div>

        <div class="pf-field">
            <label for="pf-description" class="form-label">درباره من</label>
            <textarea class="form-control"
                      id="pf-description"
                      name="description"
                      maxlength="1000"
                      rows="3"
                      placeholder="یک توضیح کوتاه درباره خودتان..."><?= htmlspecialchars($_SESSION['user']['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>

        <div class="pf-form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-check"></i>
                <span>ذخیره تغییرات</span>
            </button>
        </div>
    </form>
</div>