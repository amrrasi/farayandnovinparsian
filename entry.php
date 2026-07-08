<?php

require_once "cms/myadmin/inc/config.php";
require_once "cms/myadmin/inc/auth_helpers.php";

// Already logged in? no reason to see this page.
if (!empty($_SESSION['user']['id'])) {
    header('Location: ' . $baseAddress);
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$redirectTarget = sanitizeRedirect($_GET['redirect'] ?? null, $baseAddress);
$pageTitle      = 'ورود و ثبت‌نام | ' . htmlspecialchars(setting('name'));
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
    <title><?= $pageTitle ?></title>

    <?= $global_base_address ?>

    <link rel="stylesheet" href="assets/css/base/animate.min.css">
    <link rel="stylesheet" href="assets/css/base/flaticon.css">
    <link rel="stylesheet" href="assets/css/base/fontawesome.min.css">
    <link rel="stylesheet" href="assets/css/base/magnific-popup.min.css">
    <link rel="stylesheet" href="assets/css/base/nice-select.css">
    <link rel="stylesheet" href="assets/css/base/owl.carousel.min.css">

    <link rel="stylesheet" href="assets/fonts/font.css">

    <link rel="stylesheet" href="assets/css/base/bootstrap.rtl.css">
    <link rel="stylesheet" href="assets/css/base/base.css">
    <link rel="stylesheet" href="assets/css/layout/header.css">
    <link rel="stylesheet" href="assets/css/layout/footer.css">
    <link rel="stylesheet" href="assets/css/pages/entry.css?v=<?= filemtime('assets/css/pages/entry.css') ?>">
</head>
<body class="entry-body">

<div class="fac-toast-container">
    <div class="fac-toast" id="facToast">
        <i class="fa-solid fa-circle-check"></i>
        <span id="facToastText"></span>
    </div>
</div>

<main class="entry-shell">

    <section class="entry-visual hide-mobile">
        <div class="entry-visual-blob" aria-hidden="true"></div>
        <div class="entry-visual-content">
            <a href="<?= $baseAddress ?>" class="entry-logo">
                <?= htmlspecialchars(setting('name')) ?>
            </a>
            <h1>خرید هوشمندانه از <span class="text-gradient">تعامل</span> شروع می‌شود</h1>
            <p>وارد حساب خود شوید یا در چند ثانیه یک حساب جدید بسازید و سفارش‌هایتان را از همینجا دنبال کنید.</p>

            <ul class="entry-perks">
                <li><i class="fa-solid fa-shield"></i> ضمانت اصالت کالا</li>
                <li><i class="fa-solid fa-headset"></i> پشتیبانی تخصصی</li>
                <li><i class="fa-solid fa-lock"></i> رمزنگاری اطلاعات شما</li>
            </ul>
        </div>
    </section>

    <section class="entry-form-panel">
        <div class="entry-card card-glass">

            <!-- LOGIN / REGISTER -->
            <div id="authTabsView">

                <div class="auth-tabs" role="tablist">
                    <span class="tab-indicator" id="tabIndicator"></span>
                    <button type="button" class="tab-btn active" data-tab="login" role="tab" aria-selected="true">ورود</button>
                    <button type="button" class="tab-btn" data-tab="register" role="tab" aria-selected="false">ثبت‌نام</button>
                </div>

                <!-- LOGIN FORM -->
                <form id="loginForm" class="auth-form active" autocomplete="on">
                    <h2>خوش برگشتید</h2>
                    <p class="auth-sub">برای ادامه خرید وارد حساب خود شوید</p>

                    <div class="form-group">
                        <label for="loginIdentifier">شماره موبایل یا ایمیل</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-user"></i>
                            <input type="text" id="loginIdentifier" name="identifier" placeholder="09123456789" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="loginPassword">رمز عبور</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" id="loginPassword" name="password" placeholder="••••••••" required>
                            <button type="button" class="toggle-pass" data-target="loginPassword" aria-label="نمایش رمز عبور">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-row-between">
                        <label class="checkbox-line">
                            <input type="checkbox" name="remember">
                            مرا به خاطر بسپار
                        </label>
                        <button type="button" class="link-btn" id="goForgotPassword">رمز عبور را فراموش کرده‌اید؟</button>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">ورود</button>
                </form>

                <!-- REGISTER FORM -->
                <form id="registerForm" class="auth-form" autocomplete="on">
                    <h2>ساخت حساب کاربری</h2>
                    <p class="auth-sub">چند ثانیه‌ای، ثبت‌نام کنید و بلافاصله وارد شوید</p>

                    <div class="form-group">
                        <label for="regName">نام و نام‌خانوادگی</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-id-card"></i>
                            <input type="text" id="regName" name="name" placeholder="نام شما" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="regMobile">شماره موبایل</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-mobile-screen"></i>
                            <input type="tel" id="regMobile" name="mobile" placeholder="09123456789" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="regEmail">ایمیل <span class="optional-tag">(اختیاری)</span></label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-envelope"></i>
                            <input type="email" id="regEmail" name="email" placeholder="example@mail.com">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="regPassword">رمز عبور</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" id="regPassword" name="password" placeholder="حداقل ۸ کاراکتر" required>
                            <button type="button" class="toggle-pass" data-target="regPassword" aria-label="نمایش رمز عبور">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        <div class="pass-strength" id="passStrength"><span></span></div>
                    </div>

                    <div class="form-group">
                        <label for="regConfirmPassword">تکرار رمز عبور</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" id="regConfirmPassword" name="confirmPassword" placeholder="••••••••" required>
                            <button type="button" class="toggle-pass" data-target="regConfirmPassword" aria-label="نمایش رمز عبور">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <label class="checkbox-line">
                        <input type="checkbox" name="terms" id="regTerms" required>
                        با <a href="<?= $baseAddress ?>terms" target="_blank">قوانین و مقررات</a> موافقم
                    </label>

                    <button type="submit" class="btn btn-primary btn-block">ایجاد حساب</button>
                </form>

            </div>

            <!-- FORGOT PASSWORD -->
            <div id="forgotPasswordView" class="hidden">

                <button type="button" class="back-btn" id="backToLogin">
                    <i class="fa-solid fa-arrow-right"></i> بازگشت به ورود
                </button>

                <!-- STEP 1: request code -->
                <form id="fpRequestForm" class="auth-form active">
                    <h2>بازیابی رمز عبور</h2>
                    <p class="auth-sub">ایمیل حساب کاربری خود را وارد کنید تا کد تایید برایتان ارسال شود</p>

                    <div class="form-group">
                        <label for="fpEmail">ایمیل</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-envelope"></i>
                            <input type="email" id="fpEmail" name="email" placeholder="example@mail.com" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block" id="fpSendBtn">ارسال کد تایید</button>
                </form>

                <!-- STEP 2: verify code + set new password -->
                <form id="fpVerifyForm" class="auth-form">
                    <h2>کد تایید را وارد کنید</h2>
                    <p class="auth-sub">کد ۶ رقمی ارسال‌شده به <strong id="fpSentToEmail"></strong> را وارد کنید</p>

                    <div class="otp-inputs" id="otpInputs">
                        <input type="text" inputmode="numeric" maxlength="1" class="otp-box" data-index="0">
                        <input type="text" inputmode="numeric" maxlength="1" class="otp-box" data-index="1">
                        <input type="text" inputmode="numeric" maxlength="1" class="otp-box" data-index="2">
                        <input type="text" inputmode="numeric" maxlength="1" class="otp-box" data-index="3">
                        <input type="text" inputmode="numeric" maxlength="1" class="otp-box" data-index="4">
                        <input type="text" inputmode="numeric" maxlength="1" class="otp-box" data-index="5">
                    </div>

                    <div class="resend-row">
                        <span id="resendTimer"></span>
                        <button type="button" class="link-btn" id="resendOtpBtn" disabled>ارسال مجدد کد</button>
                    </div>

                    <div class="form-group">
                        <label for="fpNewPassword">رمز عبور جدید</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" id="fpNewPassword" name="newPassword" placeholder="حداقل ۸ کاراکتر" required>
                            <button type="button" class="toggle-pass" data-target="fpNewPassword" aria-label="نمایش رمز عبور">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="fpConfirmPassword">تکرار رمز عبور جدید</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" id="fpConfirmPassword" name="confirmPassword" placeholder="••••••••" required>
                            <button type="button" class="toggle-pass" data-target="fpConfirmPassword" aria-label="نمایش رمز عبور">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">تغییر رمز عبور</button>
                </form>

            </div>

        </div>
    </section>

</main>

<script>
    window.ENTRY = {
        redirect: <?= json_encode($redirectTarget, JSON_UNESCAPED_UNICODE) ?>,
        loginUrl: <?= json_encode($baseAddress . 'entry.php', JSON_UNESCAPED_UNICODE) ?>
    };
</script>

 <script src="assets/js/jquery.js"></script>
 <script src="assets/js/jquery.nice-select.min.js"></script>
 <script src="assets/js/owl.carousel.min.js"></script>
 <script src="assets/js/bootstrap.js"></script>
 <script src="assets/js/bootstrap.bundle.js"></script>
 <script src="assets/js/main.js"></script>
<script src="assets/js/pages/entry.js?v=<?= filemtime('assets/js/pages/entry.js') ?>"></script>
</body>
</html>
