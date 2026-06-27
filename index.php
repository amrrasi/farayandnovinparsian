<?php
require_once "cms/myadmin/inc/config.php"
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title> <?= $global_setting_array['name'] ?> </title>


    <link rel="stylesheet" href="assets/css/animate.min.css">
    <link rel="stylesheet" href="assets/css/flaticon.css">
    <link rel="stylesheet" href="assets/css/fontawesome.min.css">
    <link rel="stylesheet" href="assets/css/magnific-popup.min.css">
    <link rel="stylesheet" href="assets/css/nice-select.css">
    <link rel="stylesheet" href="assets/css/owl.carousel.min.css">
    <link rel="stylesheet" href="assets/fonts/font.css">
    <link rel="stylesheet" href="assets/css/bootstrap.rtl.css">
    <link rel="stylesheet" href="assets/css/header.css">
    <link rel="stylesheet" href="assets/css/style.css">

</head>
<body>

<?php require_once "inc/header.php"?>

<!-- Hero Section -->
<section class="hp-hero">

    <div class="hp-grid" aria-hidden="true"></div>

    <div class="hp-hero-inner">

        <div>
            <div class="hp-badge">
                <span class="hp-dot" aria-hidden="true"></span>
                تامین‌کننده رسمی Dell EMC در ایران
            </div>
            <h1 class="hp-h1">
                ذخیره‌سازی <em>هوشمند</em><br>
                برای کسب‌وکار شما
            </h1>
            <p class="hp-p">
                <?= $global_setting_array['name'] ?>، ارائه‌دهنده تخصصی استوریج‌های Dell EMC.
                راهکارهای SAN، NAS و بکاپ برای سازمان‌های بزرگ و استارتاپ‌های پیشرو.
            </p>
            <div class="hp-btns">
                <a href="#" class="hp-btn-primary">ثبت سفارش</a>
                <a href="#" class="hp-btn-ghost">مشاهده محصولات</a>
            </div>
        </div>

        <div class="hp-dash">
            <div class="hp-card">
                <div class="hp-card-hd">
                    <div class="hp-card-ico"><i class="fas fa-server"></i></div>
                    <div>
                        <div class="hp-card-t">پنل مدیریت استوریج</div>
                        <div class="hp-card-s">Dell EMC PowerStore — Live</div>
                    </div>
                </div>

                <div class="hp-stats">
                    <div class="hp-stat"><div class="hp-sv">99.9٪</div><div class="hp-sl">آپتایم</div></div>
                    <div class="hp-stat"><div class="hp-sv">420+</div><div class="hp-sl">مشتری</div></div>
                    <div class="hp-stat"><div class="hp-sv">15PB</div><div class="hp-sl">ظرفیت</div></div>
                </div>

                <div class="hp-chart-lbl">
                    <span>مصرف ظرفیت فضا</span><span>امروز</span>
                </div>
                <div class="hp-bars">
                    <div class="hp-bar-row">
                        <span class="hp-bar-n">SAN</span>
                        <div class="hp-track"><div class="hp-fill" data-w="0.82"></div></div>
                        <span class="hp-bar-pct">82٪</span>
                    </div>
                    <div class="hp-bar-row">
                        <span class="hp-bar-n">NAS</span>
                        <div class="hp-track"><div class="hp-fill" data-w="0.61"></div></div>
                        <span class="hp-bar-pct">61٪</span>
                    </div>
                    <div class="hp-bar-row">
                        <span class="hp-bar-n">Backup</span>
                        <div class="hp-track"><div class="hp-fill" data-w="0.45"></div></div>
                        <span class="hp-bar-pct">45٪</span>
                    </div>
                </div>

                <div class="hp-status">
                    <span class="hp-stxt">
                        <span class="hp-sdot" aria-hidden="true"></span>تمام سیستم‌ها فعال
                    </span>
                    <span class="hp-sup">بروزرسانی: همین الان</span>
                </div>
            </div>

            <div class="hp-chip hp-chip-1" aria-hidden="true">
                <div class="hp-chi"><i class="fas fa-bolt"></i></div>
                <div><div class="hp-cv">1.2ms</div><div class="hp-cl">تأخیر I/O</div></div>
            </div>
            <div class="hp-chip hp-chip-2" aria-hidden="true">
                <div class="hp-chi"><i class="fas fa-lock"></i></div>
                <div><div class="hp-cv">AES-256</div><div class="hp-cl">رمزنگاری</div></div>
            </div>
            <div class="hp-chip hp-chip-3" aria-hidden="true">
                <div class="hp-chi"><i class="fas fa-chart-bar"></i></div>
                <div><div class="hp-cv">99.99٪</div><div class="hp-cl">دسترس‌پذیری</div></div>
            </div>
        </div>

    </div>
</section>

<!-- Our Services -->
<section class="services-section">
     <div class="container">

            <div class="services-heading">

            <span class="section-badge">
                خدمات تخصصی
            </span>

                <h2>
                    راهکارهای تخصصی ذخیره‌سازی
                </h2>

                <p>
                    شرکت <?= $global_setting_array['name'] ?> با ارائه خدمات تخصصی در حوزه تجهیزات ذخیره‌سازی،
                    از مرحله انتخاب تا نصب، راه‌اندازی و پشتیبانی، همراه کسب‌وکار شما خواهد بود.
                </p>

            </div>

            <div class="services-grid">

                <article class="service-card">

                    <div class="service-icon">
                        <i class="fa-solid fa-comments"></i>
                    </div>

                    <h3>مشاوره رایگان قبل از خرید</h3>

                    <p>
                        انتخاب بهترین استوریج متناسب با نیاز سازمان شما با کمک کارشناسان متخصص.
                    </p>

                    <a href="#">
                        بیشتر بدانید
                        <i class="fa-solid fa-arrow-left-long"></i>
                    </a>

                </article>

                <article class="service-card">

                    <div class="service-icon">
                        <i class="fa-solid fa-database"></i>
                    </div>

                    <h3>راهکارهای ذخیره‌سازی</h3>

                    <p>
                        طراحی و ارائه زیرساخت‌های ذخیره‌سازی امن، مقیاس‌پذیر و متناسب با نیاز سازمان.
                    </p>

                    <a href="#">
                        بیشتر بدانید
                        <i class="fa-solid fa-arrow-left-long"></i>
                    </a>

                </article>

                <article class="service-card">

                    <div class="service-icon">
                        <i class="fa-solid fa-gears"></i>
                    </div>

                    <h3>مشاوره فنی</h3>

                    <p>
                        ارائه خدمات تخصصی در طراحی، نصب، پیکربندی و بهینه‌سازی تجهیزات ذخیره‌سازی.
                    </p>

                    <a href="#">
                        بیشتر بدانید
                        <i class="fa-solid fa-arrow-left-long"></i>
                    </a>

                </article>

                <article class="service-card">

                    <div class="service-icon">
                        <i class="fa-solid fa-headset"></i>
                    </div>

                    <h3>پشتیبانی تجهیزات</h3>

                    <p>
                        پشتیبانی تخصصی، رفع مشکلات و نگهداری مستمر تجهیزات Storage و EMC.
                    </p>

                    <a href="#">
                        بیشتر بدانید
                        <i class="fa-solid fa-arrow-left-long"></i>
                    </a>

                </article>
            </div>
     </div>

</section>

<!-- Buying Procedure -->
<section class="order-process py-5">

    <div class="container">

        <div class="services-heading">

            <span class="section-badge">
                روند ثبت سفارش
            </span>

            <h2>
                مراحل ثبت سفارش و دریافت خدمات
            </h2>

            <p>
                برای ثبت سفارش می‌توانید به‌صورت آنلاین از طریق سایت اقدام کنید یا مستقیماً با کارشناسان فروش ما تماس بگیرید.
                در هر دو روش، تیم فرآیند نوین اطلاعات پارسیان تا زمان تحویل نهایی همراه شما خواهد بود.
            </p>

        </div>

        <div class="process-wrapper">

            <div class="process-line"></div>

            <div class="row g-4 justify-content-center">

                <div class="col-lg col-md-6">

                    <div class="process-item">

                        <div class="process-icon">
                            <i class="fa-regular fa-user"></i>
                        </div>

                        <span class="step">01</span>

                        <h5>ثبت‌نام یا ورود</h5>

                        <p class="processP">
                            ابتدا وارد حساب کاربری خود شوید یا در کمتر از یک دقیقه ثبت‌نام کنید تا امکان ثبت سفارش و پیگیری آن برای شما فراهم شود.
                        </p>

                    </div>

                </div>

                <div class="col-lg col-md-6">

                    <div class="process-item">

                        <div class="process-icon">
                            <i class="fa-solid fa-cart-shopping"></i>
                        </div>

                        <span class="step">02</span>

                        <h5>انتخاب محصول</h5>

                        <p class="processP">
                            محصول موردنظر خود را بررسی کرده، به سبد خرید اضافه کنید و سفارش خود را ثبت نمایید. همچنین می‌توانید جهت دریافت مشاوره با ما تماس بگیرید.
                        </p>

                    </div>

                </div>

                <div class="col-lg col-md-6">

                    <div class="process-item">

                        <div class="process-icon">
                            <i class="fa-solid fa-credit-card"></i>
                        </div>

                        <span class="step">03</span>

                        <h5>پرداخت یا درخواست پیش‌فاکتور</h5>

                        <p class="processP">
                            سفارش‌های آنلاین به‌صورت اینترنتی پرداخت می‌شوند و برای خریدهای سازمانی نیز امکان دریافت پیش‌فاکتور و هماهنگی مالی وجود دارد.
                        </p>

                    </div>

                </div>

                <div class="col-lg col-md-6">

                    <div class="process-item">

                        <div class="process-icon">
                            <i class="fa-solid fa-phone-volume"></i>
                        </div>

                        <span class="step">04</span>

                        <h5>هماهنگی توسط کارشناسان</h5>

                        <p class="processP">
                            پس از ثبت سفارش، کارشناسان فروش جهت تأیید سفارش، بررسی موجودی، هماهنگی زمان ارسال و پاسخ به سوالات احتمالی با شما تماس خواهند گرفت.
                        </p>

                    </div>

                </div>

                <div class="col-lg col-md-6">

                    <div class="process-item">

                        <div class="process-icon">
                            <i class="fa-solid fa-truck-fast"></i>
                        </div>

                        <span class="step">05</span>

                        <h5>ارسال و تحویل سفارش</h5>

                        <p class="processP">
                            پس از تأیید نهایی، سفارش آماده‌سازی شده و در سریع‌ترین زمان ممکن ارسال یا خدمات موردنظر توسط کارشناسان اجرا خواهد شد.
                        </p>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>


<script src="assets/js/jquery.js"></script>
<script src="assets/js/jquery.nice-select.min.js"></script>
<script src="assets/js/owl.carousel.min.js"></script>
<script src="assets/js/bootstrap.js"></script>
<script src="assets/js/bootstrap.bundle.js"></script>
<script src="assets/js/main.js"></script>
</body>
</html>