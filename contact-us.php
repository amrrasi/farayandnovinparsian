<?php
require_once "cms/myadmin/inc/config.php";
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title> <?= setting('name') ?> | ارتباط با ما</title>
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

    <link rel="stylesheet"
          href="assets/css/pages/contact-us.css?v=<?php echo filemtime('assets/css/pages/contact-us.css'); ?>">
<body>

<!-- header -->
<?php require_once "inc/header.php" ?>

<!--=========================================
Contact Form
==========================================-->

<div class="contact-form-container mt-5">

    <div class="contact-form-card">

        <!-- Top -->

        <div class="form-top">

            <div class="form-title">

                <span class="mini-badge">

                    <i class="fa-solid fa-paper-plane"></i>

                    ثبت درخواست

                </span>

                <h2>

                    گفت‌وگو را از اینجا شروع کنیم

                </h2>

                <p>

                    اطلاعات شما نزد ما محفوظ خواهد ماند.
                    پس از ثبت درخواست، کارشناسان ما در سریع‌ترین زمان ممکن با شما تماس خواهند گرفت.

                </p>

            </div>

            <!-- Progress -->

            <div class="form-progress">

                <div class="progress-line">

                    <div class="progress-fill"></div>

                </div>

                <span class="progress-text">

                    تکمیل فرم

                    <strong>

                        0%

                    </strong>

                </span>

            </div>

        </div>

        <!-- Ajax Messages -->

        <div id="formAlert"></div>

        <form

                id="contactForm"

                method="post"

                action="contact_process.php"

                enctype="multipart/form-data"

                autocomplete="off"

                novalidate>

            <div class="row gy-4">

                <!--================================-->

                <!-- Full Name -->

                <!--================================-->

                <div class="col-lg-6">

                    <div class="floating-input">

                        <input

                                id="fullname"

                                name="fullname"

                                type="text"

                                required>

                        <label>

                            نام و نام خانوادگی

                        </label>

                        <i class="fa-regular fa-user input-icon"></i>

                        <span class="validation-icon">

                            <i class="fa-solid fa-circle-check"></i>

                        </span>

                    </div>

                    <small class="error-text"></small>

                </div>

                <!--================================-->

                <!-- Mobile -->

                <!--================================-->

                <div class="col-lg-6">

                    <div class="floating-input">

                        <input

                                id="mobile"

                                name="mobile"

                                type="tel"

                                maxlength="11"

                                inputmode="numeric"

                                required>

                        <label>

                            شماره موبایل

                        </label>

                        <i class="fa-solid fa-mobile-screen-button input-icon"></i>

                        <span class="validation-icon">

                            <i class="fa-solid fa-circle-check"></i>

                        </span>

                    </div>

                    <small class="error-text"></small>

                </div>

                <!--================================-->

                <!-- Email -->

                <!--================================-->

                <div class="col-lg-6">

                    <div class="floating-input">

                        <input

                                id="email"

                                name="email"

                                type="email">

                        <label>

                            ایمیل (اختیاری)

                        </label>

                        <i class="fa-regular fa-envelope input-icon"></i>

                        <span class="validation-icon">

                            <i class="fa-solid fa-circle-check"></i>

                        </span>

                    </div>

                    <small class="error-text"></small>

                </div>

                <!--================================-->

                <!-- Subject -->

                <!--================================-->
                <div class="col-lg-6">

                    <div class="floating-input">

                        <input
                                id="subject"

                                name="subject"

                                type="text"

                                required>
                        <label>

                            موضوع درخواست
                        </label>

                        <i class="fa-regular fa-code-pull-request input-icon"></i>

                        <span class="validation-icon">

                            <i class="fa-solid fa-circle-check"></i>

                        </span>
                    </div>

                    <small class="error-text"></small>

                </div>

                <!--================================-->

                <!-- Message -->

                <!--================================-->

                <div class="col-12">

                    <div class="floating-textarea">

                        <textarea

                                id="message"

                                name="message"

                                rows="7"

                                maxlength="1000"

                                required></textarea>

                        <label>

                            متن درخواست...

                        </label>

                    </div>

                    <div class="textarea-bottom">

                        <small>

                            لطفاً درخواست خود را کامل شرح دهید.

                        </small>

                        <span>

                            <strong id="charCounter">

                                0

                            </strong>

                            /1000
                        </span>

                    </div>

                    <small class="error-text"></small>

                </div>

                <!--================================-->

                <!-- Upload -->

                <!--================================-->

                <div class="col-12">

                    <div class="upload-box">

                        <input

                                type="file"

                                id="attachment"

                                name="attachment"

                                hidden>

                        <label for="attachment">

                            <i class="fa-solid fa-cloud-arrow-up"></i>

                            <span>

                                در صورت نیاز فایل خود را بارگذاری کنید

                            </span>

                            <small>

                                PDF - JPG - PNG - DOCX

                            </small>

                        </label>

                    </div>

                </div>

                <!--================================-->

                <!-- Privacy -->

                <!--================================-->

                <div class="col-12">

                    <div class="privacy-box">

                        <input

                                type="checkbox"

                                id="privacy"

                                name="privacy"

                                required>

                        <label for="privacy">

                            با قوانین سایت و نحوه پردازش اطلاعات موافق هستم.

                        </label>

                    </div>

                </div>

                <!--================================-->

                <!-- Submit -->

                <!--================================-->

                <div class="col-12">

                    <button

                            id="submitBtn"

                            type="submit"

                            class="submit-btn"

                            disabled>

                        <span class="btn-loader">

                            <i class="fa-solid fa-spinner fa-spin"></i>

                        </span>

                        <span class="btn-icon">

                            <i class="fa-solid fa-paper-plane"></i>

                        </span>

                        <span class="btn-text">

                            ارسال درخواست

                        </span>

                    </button>

                </div>

            </div>

        </form>

    </div>

</div>

<!-- footer -->
<?php require_once "inc/footer.php" ?>


<script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
<script src="assets/js/jquery.js"></script>
<script src="assets/js/jquery.nice-select.min.js"></script>
<script src="assets/js/owl.carousel.min.js"></script>
<script src="assets/js/bootstrap.js"></script>
<script src="assets/js/bootstrap.bundle.js"></script>
<script src="assets/js/main.js"></script>
<script src="assets/js/pages/contact-us.js"></script>
<script src="assets/js/contact_submit.js"></script>

</body>
</html>