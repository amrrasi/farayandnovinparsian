<?php
require_once "cms/myadmin/inc/config.php"
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title> <?= setting('name') ?> | درباره ما</title>
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
          href="assets/css/pages/about-us.css?v=<?php echo filemtime('assets/css/pages/about-us.css'); ?>">
<body>

<?php require_once "inc/header.php" ?>


<section class="about-company">

    <div class="container">

        <div class="row align-items-start">

            <div class="col-lg-6">

                <div class="company-content">

                    <span class="section-badge">

                        معرفی شرکت

                    </span>

                    <h2>

                        همراه مطمئن سازمان‌ها در مسیر توسعه زیرساخت فناوری اطلاعات

                    </h2>

                    <p>

                        <?= setting('name') ?>، تامین‌کننده تخصصی استوریج‌های EMC در ایران. ارائه راهکارهای
                        ذخیره‌سازی پیشرفته برای کسب‌وکارهای مدرن. با تکیه بر دانش فنی و تجربه، بهترین انتخاب را برای
                        مدیریت داده‌های حیاتی شما فراهم می‌کنیم.

                    </p>

<!--                    <div class="row mt-5">-->
<!--                        <div class="col-md-6 col-sm-12">-->
<!--                            <div class="company-mini-card">-->
<!--                                <i class="fas fa-server"></i>-->
<!--                                <h5>-->
<!--                                    تجهیزات اورجینال-->
<!--                                </h5>-->
<!--                            </div>-->
<!--                        </div>-->
<!--                        <div class="col-md-6 col-sm-12">-->
<!--                            <div class="company-mini-card">-->
<!--                                <i class="fas fa-headset"></i>-->
<!--                                <h5>-->
<!--                                    پشتیبانی تخصصی-->
<!--                                </h5>-->
<!--                            </div>-->
<!--                        </div>-->
<!--                    </div>-->
                </div>
            </div>

            <div class="col-lg-6">

                <div class="row">
                    <div class="col-md-6 col-sm-12 mt-5">
                        <div class="info-card card-glass reveal">
                            <div class="icon-box">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <h3>ایمیل</h3>
                            <p>
                                <a href="mailto:<?= setting('email') ?>"><?= setting('email') ?></a></p>
                        </div>

                    </div>
                    <div class="col-md-6 col-sm-12 mt-5">
                        <div class="info-card card-glass reveal reveal-delay-1">
                            <div class="icon-box">
                                <i class="fas fa-phone"></i>
                            </div>
                            <h3>شماره تماس</h3>
                            <p>
                                <a href="tel:<?= setting('phone') ?>"><?= setting('phone') ?> </a>
                            </p>
                        </div>

                    </div>
                    <div class="col-md-6 col-sm-12 mt-5">
                        <div class="info-card card-glass reveal reveal-delay-2">
                            <div class="icon-box">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <h3>آدرس</h3>
                            <p> <?= setting('address') ?> </p>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-12 mt-5">
                        <div class="info-card card-glass reveal reveal-delay-3">
                            <div class="icon-box">
                                <i class="fas fa-clock"></i>
                            </div>
                            <h3>ساعت کاری</h3>
                            <p><?= setting('workTime') ?></p>
                        </div>
                    </div>
                </div>

            </div>



        </div>

    </div>

</section>


<section class="why-us">

    <div class="container">

        <div class="services-heading">

            <span class="section-badge">

                مزیت همکاری با ما

            </span>

            <h2 class="mt-5">

                چرا <?= setting('name') ?>؟

            </h2>

            <p>

                ارائه تجهیزات اصلی، مشاوره تخصصی، اجرای پروژه‌های سازمانی و پشتیبانی حرفه‌ای از مهم‌ترین دلایل اعتماد
                مشتریان به مجموعه ما است.

            </p>

        </div>

        <div class="row g-4 mt-2">

            <div class="col-lg-4">

                <article class="why-card">

                    <div class="why-icon">

                        <i class="fas fa-layer-group"></i>

                    </div>

                    <h3>

                        راهکارهای تخصصی

                    </h3>

                    <p>

                        طراحی و اجرای راهکارهای ذخیره‌سازی اطلاعات، سرور و تجهیزات شبکه متناسب با نیاز هر سازمان.

                    </p>

                </article>

            </div>

            <div class="col-lg-4">

                <article class="why-card">

                    <div class="why-icon">

                        <i class="fas fa-award"></i>

                    </div>

                    <h3>

                        محصولات معتبر

                    </h3>

                    <p>

                        ارائه محصولات اصلی از برندهای معتبر جهانی همراه با ضمانت اصالت و خدمات پس از فروش.

                    </p>

                </article>

            </div>

            <div class="col-lg-4">

                <article class="why-card">

                    <div class="why-icon">

                        <i class="fas fa-user-shield"></i>

                    </div>

                    <h3>

                        همراهی تا پایان پروژه

                    </h3>

                    <p>

                        از مرحله مشاوره تا نصب، راه‌اندازی، آموزش و پشتیبانی در کنار مشتریان خواهیم بود.

                    </p>

                </article>

            </div>

        </div>

    </div>

</section>

<section class="company-timeline">

    <div class="container">

        <div class="services-heading">

            <span class="section-badge">
                مسیر رشد ما
            </span>

            <h2 class="mt-3">
                همراهی با سازمان‌های بزرگ در مسیر توسعه
            </h2>

            <p class="mt-1">
                طی سال‌های فعالیت، فرآیند نوین اطلاعات پارسیان همواره در مسیر توسعه زیرساخت فناوری اطلاعات کشور گام
                برداشته است.
            </p>

        </div>

        <div class="timeline-wrapper">

            <div class="timeline-progress">

                <span class="timeline-progress-fill"></span>

            </div>

            <div class="timeline-list">

                <article class="timeline-card">

                    <div class="timeline-dot">
                        <span>01</span>
                    </div>

                    <div class="timeline-content">

                        <small>۱۳۹۰</small>

                        <h3>
                            تأسیس شرکت
                        </h3>

                        <p>
                            آغاز فعالیت رسمی شرکت با تمرکز بر تجهیزات ذخیره‌سازی اطلاعات و زیرساخت مراکز داده.
                        </p>

                    </div>

                </article>

                <article class="timeline-card">

                    <div class="timeline-dot">
                        <span>02</span>
                    </div>

                    <div class="timeline-content">

                        <small>۱۳۹۴</small>

                        <h3>
                            اجرای پروژه‌های سازمانی
                        </h3>

                        <p>
                            همکاری با سازمان‌ها، بانک‌ها و شرکت‌های خصوصی در پروژه‌های زیرساختی.
                        </p>

                    </div>

                </article>

                <article class="timeline-card">

                    <div class="timeline-dot">
                        <span>03</span>
                    </div>

                    <div class="timeline-content">

                        <small>۱۳۹۸</small>

                        <h3>
                            توسعه خدمات تخصصی
                        </h3>

                        <p>
                            گسترش خدمات مشاوره، طراحی، نصب، راه‌اندازی و پشتیبانی تجهیزات دیتاسنتر.
                        </p>

                    </div>

                </article>

                <article class="timeline-card">

                    <div class="timeline-dot">
                        <span>04</span>
                    </div>

                    <div class="timeline-content">

                        <small>۱۴۰۲</small>

                        <h3>
                            همکاری با سازمان‌های بزرگ
                        </h3>

                        <p>
                            اجرای پروژه‌های گسترده در سطح ملی و توسعه خدمات پشتیبانی تخصصی.
                        </p>

                    </div>

                </article>

                <article class="timeline-card timeline-last">

                    <div class="timeline-dot">

                        <i class="fas fa-star"></i>

                    </div>

                    <div class="timeline-content">

                        <small>امروز</small>

                        <h3>

                            ادامه مسیر...

                        </h3>

                        <p>

                            توسعه راهکارهای نوین ذخیره‌سازی اطلاعات، سرور و زیرساخت برای سازمان‌های کشور.

                        </p>

                    </div>

                </article>

            </div>

        </div>

    </div>

</section>

<section class="about-map">

    <div class="container">

        <div class="services-heading">

            <span class="section-badge">

                موقعیت مکانی

            </span>

            <h2 class="mt-4">

                دفتر مرکزی فرآیند نوین اطلاعات پارسیان

            </h2>

            <p>

                برای دریافت مشاوره، بازدید حضوری یا جلسات تخصصی می‌توانید به دفتر مرکزی مراجعه نمایید.

            </p>

        </div>

        <div class="map-wrapper">

            <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d356.0060644254501!2d51.43465695628917!3d35.728438579752215!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e0!3m2!1sen!2s!4v1782632764625!5m2!1sen!2s"
                    width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy"
                    referrerpolicy="strict-origin-when-cross-origin"></iframe>

        </div>

    </div>

</section>


<?php require_once "inc/footer.php" ?>

<script src="assets/js/jquery.js"></script>
<script src="assets/js/jquery.nice-select.min.js"></script>
<script src="assets/js/owl.carousel.min.js"></script>
<script src="assets/js/bootstrap.js"></script>
<script src="assets/js/bootstrap.bundle.js"></script>
<script src="assets/js/main.js"></script>
<script src="assets/js/pages/about-us.js"></script>

</body>
</html>
