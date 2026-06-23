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
    <link rel="stylesheet" href="assets/css/Vazirmatn-RD-FD-font-face.css">
    <link rel="stylesheet" href="assets/css/bootstrap.rtl.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/header.css">

</head>
<body>

<?php require_once "inc/header.php"?>

<section class="hp-hero">

    <div class="hp-grid" aria-hidden="true"></div>

    <div class="hp-hero-inner">

        <!-- Copy -->
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
                فرآیند نوین اطلاعات پارسیان، ارائه‌دهنده تخصصی استوریج‌های Dell EMC.
                راهکارهای SAN، NAS و بکاپ برای سازمان‌های بزرگ و استارتاپ‌های پیشرو.
            </p>
            <div class="hp-btns">
                <a href="#" class="hp-btn-primary">ثبت سفارش</a>
                <a href="#" class="hp-btn-ghost">مشاهده محصولات</a>
            </div>
        </div>

        <!-- Dashboard card -->
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

            <!-- Floating chips (desktop only) -->
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


<script src="assets/js/jquery.js"></script>
<script src="assets/js/jquery.nice-select.min.js"></script>
<script src="assets/js/owl.carousel.min.js"></script>
<script src="assets/js/bootstrap.js"></script>
<script src="assets/js/bootstrap.bundle.js"></script>
<script src="assets/js/main.js"></script>
</body>
</html>