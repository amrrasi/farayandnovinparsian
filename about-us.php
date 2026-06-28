<?php
require_once "cms/myadmin/inc/config.php"
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title> <?= setting('name') ?> | About Us</title>
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
<!-- header -->
<?php require_once "inc/header.php" ?>

<!-- ===========================================================
ABOUT HERO
=========================================================== -->

<section class="about-hero">

    <div class="about-overlay"></div>

    <div class="container">

        <div class="row align-items-center min-vh-100 g-5">

            <div class="col-lg-7">

                <div class="about-content">

                    <span class="section-badge">

                        درباره فرآیند نوین اطلاعات پارسیان

                    </span>

                    <h1>

                        ارائه راهکارهای تخصصی ذخیره‌سازی اطلاعات، سرور و زیرساخت سازمانی

                    </h1>

                    <p>

                        فرآیند نوین اطلاعات پارسیان با تکیه بر دانش فنی، تجربه اجرایی و همکاری با معتبرترین برندهای حوزه
                        تجهیزات ذخیره‌سازی، سرور و شبکه، راهکارهای تخصصی مورد نیاز سازمان‌ها، بانک‌ها، شرکت‌های خصوصی و
                        مراکز داده را ارائه می‌دهد.

                    </p>

                    <div class="about-buttons">

                        <a href="contact-us/" class="btn btn-primary">

                            دریافت مشاوره رایگان

                        </a>

                    </div>

                </div>

            </div>

            <div class="col-lg-5">

                <div class="about-image">

                    <div class="experience-card">

                        <span>

                            +15

                        </span>

                        <strong>

                            سال تجربه تخصصی

                        </strong>

                        <p>

                            در زمینه تجهیزات ذخیره‌سازی اطلاعات و زیرساخت

                        </p>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>

<!-- ===========================================================
ABOUT COMPANY
=========================================================== -->

<section class="about-company">

    <div class="container">

        <div class="row align-items-center g-5">

            <div class="col-lg-6">

                <div class="company-image">

                    <img src="assets/images/about/company-office.jpg"
                         class="img-fluid"
                         alt="شرکت فرآیند نوین">

                </div>

            </div>

            <div class="col-lg-6">

                <div class="company-content">

                    <span class="section-badge">

                        معرفی شرکت

                    </span>

                    <h2>

                        همراه مطمئن سازمان‌ها در مسیر توسعه زیرساخت فناوری اطلاعات

                    </h2>

                    <p>

                        این قسمت از طریق دیتابیس تکمیل خواهد شد.
                        معرفی کامل شرکت، سابقه فعالیت، اهداف، چشم‌انداز و مأموریت مجموعه در این بخش قرار می‌گیرد.

                    </p>

                    <p>

                        این متن می‌تواند شامل معرفی خدمات، ارزش‌های سازمانی، کیفیت اجرای پروژه‌ها، تیم متخصص و
                        همکاری‌های انجام شده باشد.

                    </p>

                    <div class="row mt-5">

                        <div class="col-6">

                            <div class="company-mini-card">

                                <i class="fas fa-server"></i>

                                <h5>

                                    تجهیزات اورجینال

                                </h5>

                            </div>

                        </div>

                        <div class="col-6">

                            <div class="company-mini-card">

                                <i class="fas fa-headset"></i>

                                <h5>

                                    پشتیبانی تخصصی

                                </h5>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>

<!-- ===========================================================
STATISTICS
=========================================================== -->

<section class="about-statistics">

    <div class="container">

        <div class="row g-4">

            <div class="col-lg-3 col-md-6">

                <div class="stat-card">

                    <h2 class="counter" data-count="15">

                        0

                    </h2>

                    <span>

                        سال تجربه

                    </span>

                </div>

            </div>

            <div class="col-lg-3 col-md-6">

                <div class="stat-card">

                    <h2 class="counter" data-count="500">

                        0

                    </h2>

                    <span>

                        پروژه موفق

                    </span>

                </div>

            </div>

            <div class="col-lg-3 col-md-6">

                <div class="stat-card">

                    <h2 class="counter" data-count="98">

                        0

                    </h2>

                    <span>

                        درصد رضایت مشتریان

                    </span>

                </div>

            </div>

            <div class="col-lg-3 col-md-6">

                <div class="stat-card">

                    <h2>

                        24/7

                    </h2>

                    <span>

                        پشتیبانی

                    </span>

                </div>

            </div>

        </div>

    </div>

</section>

<!-- ===========================================================
WHY US
=========================================================== -->

<section class="why-us">

    <div class="container">

        <div class="services-heading">

            <span class="section-badge">

                مزیت همکاری

            </span>

            <h2>

                چرا فرآیند نوین اطلاعات پارسیان؟

            </h2>

            <p>

                ارائه تجهیزات اصلی، مشاوره تخصصی، اجرای پروژه‌های سازمانی و پشتیبانی حرفه‌ای از مهم‌ترین دلایل اعتماد
                مشتریان به مجموعه ما است.

            </p>

        </div>

        <div class="row g-4">

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

<!-- ===========================================================
COMPANY TIMELINE
=========================================================== -->

<section class="company-timeline">

    <div class="container">

        <div class="services-heading">

            <span class="section-badge">
                مسیر رشد ما
            </span>

            <h2>
                همراهی با سازمان‌های بزرگ در مسیر توسعه
            </h2>

            <p>
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

<!-- ===========================================================
GOOGLE MAP
=========================================================== -->

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

            <!-- این iframe را بعداً با آدرس خودت جایگزین کن -->

            <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d356.0060644254501!2d51.43465695628917!3d35.728438579752215!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e0!3m2!1sen!2s!4v1782632764625!5m2!1sen!2s"
                    width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy"
                    referrerpolicy="strict-origin-when-cross-origin"></iframe>

        </div>

    </div>

</section>


<!-- footer -->
<?php require_once "inc/footer.php" ?>


<script src="assets/js/jquery.js"></script>
<script src="assets/js/jquery.nice-select.min.js"></script>
<script src="assets/js/owl.carousel.min.js"></script>
<script src="assets/js/bootstrap.js"></script>
<script src="assets/js/bootstrap.bundle.js"></script>
<script src="assets/js/about-us.js"></script>
<script src="assets/js/main.js"></script>

</body>
</html>
