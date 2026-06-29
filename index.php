<?php
require_once "cms/myadmin/inc/config.php"
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title> <?= setting('name') ?> </title>


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
          href="assets/css/pages/style.css?v=<?php echo filemtime('assets/css/pages/style.css'); ?>">

</head>
<body>

<?php require_once "inc/header.php" ?>

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
                <?= setting('site_name') ?>، ارائه‌دهنده تخصصی استوریج‌های Dell EMC.
                راهکارهای SAN، NAS و بکاپ برای سازمان‌های بزرگ و استارتاپ‌های پیشرو.
            </p>
            <div class="hp-btns">
                <a href="#" class="hp-btn-primary">ثبت سفارش</a>
                <a href="#prducts" class="hp-btn-ghost">مشاهده محصولات</a>
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
                    <div class="hp-stat">
                        <div class="hp-sv">99.9٪</div>
                        <div class="hp-sl">آپتایم</div>
                    </div>
                    <div class="hp-stat">
                        <div class="hp-sv">420+</div>
                        <div class="hp-sl">مشتری</div>
                    </div>
                    <div class="hp-stat">
                        <div class="hp-sv">15PB</div>
                        <div class="hp-sl">ظرفیت</div>
                    </div>
                </div>

                <div class="hp-chart-lbl">
                    <span>مصرف ظرفیت فضا</span><span>امروز</span>
                </div>
                <div class="hp-bars">
                    <div class="hp-bar-row">
                        <span class="hp-bar-n">SAN</span>
                        <div class="hp-track">
                            <div class="hp-fill" data-w="0.82"></div>
                        </div>
                        <span class="hp-bar-pct">82٪</span>
                    </div>
                    <div class="hp-bar-row">
                        <span class="hp-bar-n">NAS</span>
                        <div class="hp-track">
                            <div class="hp-fill" data-w="0.61"></div>
                        </div>
                        <span class="hp-bar-pct">61٪</span>
                    </div>
                    <div class="hp-bar-row">
                        <span class="hp-bar-n">Backup</span>
                        <div class="hp-track">
                            <div class="hp-fill" data-w="0.45"></div>
                        </div>
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
                <div>
                    <div class="hp-cv">1.2ms</div>
                    <div class="hp-cl">تأخیر I/O</div>
                </div>
            </div>
            <div class="hp-chip hp-chip-2" aria-hidden="true">
                <div class="hp-chi"><i class="fas fa-lock"></i></div>
                <div>
                    <div class="hp-cv">AES-256</div>
                    <div class="hp-cl">رمزنگاری</div>
                </div>
            </div>
            <div class="hp-chip hp-chip-3" aria-hidden="true">
                <div class="hp-chi"><i class="fas fa-chart-bar"></i></div>
                <div>
                    <div class="hp-cv">99.99٪</div>
                    <div class="hp-cl">دسترس‌پذیری</div>
                </div>
            </div>
        </div>

    </div>
</section>


<!-- Our Customers -->
<section class="clients-section">

    <div class="container">

        <div class="services-heading">

            <span class="section-badge">

                مشتریان ما

            </span>

            <h2>

                سازمان‌هایی که به ما اعتماد کرده‌اند

            </h2>

            <p>

                همکاری با سازمان‌ها و مجموعه‌های بزرگ کشور، نتیجه سال‌ها تجربه، ارائه راهکارهای تخصصی و پشتیبانی حرفه‌ای
                در حوزه تجهیزات ذخیره‌سازی، سرور و زیرساخت فناوری اطلاعات است.

            </p>

        </div>

        <div class="clients-slider ">

            <!-- National Gas -->

            <div class="item">

                <article class="client-card">

                    <div class="client-badge">

                        مشتری سازمانی

                    </div>

                    <div class="client-logo">

                        <img src="assets/images/sherkat-gaz.png" alt="شرکت ملی گاز ایران">

                    </div>

                    <h3>

                        شرکت ملی گاز ایران

                    </h3>

                    <p>

                        همکاری در تأمین تجهیزات ذخیره‌سازی اطلاعات، زیرساخت و ارائه خدمات تخصصی فناوری اطلاعات.

                    </p>

                </article>

            </div>

            <!-- Tehran Municipality -->

            <div class="item">

                <article class="client-card">

                    <div class="client-badge">

                        مشتری سازمانی

                    </div>

                    <div class="client-logo">

                        <img src="assets/images/tehran-shahrdari.png" alt="شهرداری تهران">

                    </div>

                    <h3>

                        شهرداری تهران

                    </h3>

                    <p>

                        همکاری در اجرای پروژه‌های زیرساخت، تأمین تجهیزات و ارائه خدمات تخصصی حوزه فناوری اطلاعات.

                    </p>

                </article>

            </div>

        </div>

        <div class="clients-footer">

            <div class="clients-text">

                <strong>

                    اعتماد سازمان‌های بزرگ، بزرگ‌ترین سرمایه ماست.

                </strong>

                <span>

                    ما تلاش می‌کنیم با ارائه خدمات تخصصی، این اعتماد را هر روز مستحکم‌تر کنیم.

                </span>

            </div>

            <a href="contact-us/" class="clients-btn">

                همکاری با ما

                <i class="fa-solid fa-arrow-left"></i>

            </a>

        </div>

    </div>

</section>


<!-- Our Services -->
<section id="our-services" class="services-section">
    <div class="container">

        <div class="services-heading">

            <span class="section-badge">
                خدمات تخصصی
            </span>

            <h2>
                راهکارهای تخصصی ذخیره‌سازی
            </h2>

            <p>
                شرکت <?= setting('site_name') ?> با ارائه خدمات تخصصی در حوزه تجهیزات ذخیره‌سازی،
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
<section class="order-process" id="orderProcess">

    <div class="container">

        <div class="services-heading">

            <span class="section-badge">
                روند ثبت سفارش
            </span>

            <h2>
                مراحل ثبت سفارش و دریافت خدمات
            </h2>

            <p class="procedureP">
                برای ثبت سفارش می‌توانید به صورت آنلاین از طریق سایت اقدام کنید یا مستقیماً با کارشناسان فروش ما تماس
                بگیرید.
                در هر دو روش، تیم فرآیند نوین اطلاعات پارسیان تا زمان تحویل نهایی همراه شما خواهد بود.
            </p>

        </div>

        <div class="process-timeline">

            <span class="timeline-progress"></span>

        </div>

        <div class="row process-slider">

            <!-- Step 1 -->

            <div class="col-lg process-col">

                <div class="process-item">

                    <div class="process-top">

                        <div class="process-number">

                            <span>01</span>

                        </div>

                    </div>

                    <div class="process-icon">

                        <i class="fa-regular fa-user"></i>

                    </div>

                    <h4>

                        ثبت‌نام یا ورود

                    </h4>

                    <p class="procedureP">

                        ابتدا وارد حساب کاربری خود شوید یا در کمتر از یک دقیقه ثبت‌نام کنید تا امکان ثبت سفارش، مشاهده
                        سوابق خرید و پیگیری سفارشات برای شما فراهم شود.

                    </p>

                </div>

            </div>

            <!-- Step 2 -->

            <div class="col-lg process-col">

                <div class="process-item">

                    <div class="process-top">

                        <div class="process-number">

                            <span>02</span>

                        </div>

                    </div>

                    <div class="process-icon">

                        <i class="fa-solid fa-cart-shopping"></i>

                    </div>

                    <h4>

                        انتخاب محصولات

                    </h4>

                    <p class="procedureP">

                        محصولات موردنظر خود را بررسی کرده و به سبد خرید اضافه کنید. در صورت نیاز نیز کارشناسان ما آماده
                        ارائه مشاوره رایگان قبل از خرید هستند.

                    </p>

                </div>

            </div>

            <!-- Step 3 -->

            <div class="col-lg process-col">

                <div class="process-item">

                    <div class="process-top">

                        <div class="process-number">

                            <span>03</span>

                        </div>

                    </div>

                    <div class="process-icon">

                        <i class="fa-solid fa-file-invoice"></i>

                    </div>

                    <h4>

                        ثبت سفارش

                    </h4>

                    <p class="procedureP">

                        سفارش خود را نهایی کنید. در خریدهای سازمانی امکان دریافت پیش‌فاکتور، هماهنگی مالی و ثبت سفارش
                        تلفنی نیز وجود دارد.

                    </p>

                </div>

            </div>

            <!-- Step 4 -->

            <div class="col-lg process-col">

                <div class="process-item">

                    <div class="process-top">

                        <div class="process-number">

                            <span>04</span>

                        </div>

                    </div>

                    <div class="process-icon">

                        <i class="fa-solid fa-headset"></i>

                    </div>

                    <h4>

                        بررسی و هماهنگی

                    </h4>

                    <p class="procedureP">

                        پس از ثبت سفارش، کارشناسان فروش موجودی کالا، شرایط ارسال، زمان تحویل و سایر جزئیات را با شما
                        هماهنگ خواهند کرد.

                    </p>

                </div>

            </div>

            <!-- Step 5 -->

            <div class="col-lg process-col">

                <div class="process-item">

                    <div class="process-top">

                        <div class="process-number">

                            <span>05</span>

                        </div>

                    </div>

                    <div class="process-icon">

                        <i class="fa-solid fa-truck-fast"></i>

                    </div>

                    <h4>

                        ارسال و تحویل

                    </h4>

                    <p class="procedureP">

                        سفارش شما در کوتاه‌ترین زمان ممکن ارسال شده یا خدمات تخصصی خریداری‌شده توسط کارشناسان شرکت اجرا
                        و تحویل خواهد شد.

                    </p>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- Products -->
<?php

$groups = [];
$result = $mysqli->query("
    SELECT
        id,
        name
    FROM product_menu
    WHERE active='1'
    AND deleted='0'
    ORDER BY myorder ASC
");

while ($row = $result->fetch_assoc()) {

    $groups[] = $row;

}
$productsByGroup = [];

$result = $mysqli->query("
    SELECT
        id,
        product_menu_id,
        name,
        seo_slug,
        thumbnail,
        description,
        price
    FROM product
    WHERE active='1'
    AND deleted='0'
    ORDER BY myorder ASC
");
while ($row = $result->fetch_assoc()) {
    $productsByGroup[$row['product_menu_id']][] = $row;
}
?>
<section id="prducts" class="home-products">
    <div class="container">
        <div class="services-heading">
            <span class="section-badge">
                محصولات
            </span>
            <h2>
                محصولات ما
            </h2>
            <p>
                انواع تجهیزات ذخیره‌سازی، سرورها و تجهیزات شبکه را متناسب با نیاز سازمان خود انتخاب کنید.
            </p>
        </div>
        <ul class="nav product-tabs justify-content-center mb-5">
            <?php
            $first = true;
            foreach ($groups as $group):
                if (empty($productsByGroup[$group['id']]))
                    continue;
                ?>
                <li class="nav-item">
                    <button
                            class="nav-link <?= $first ? 'active' : '' ?>"
                            data-bs-toggle="tab"
                            data-bs-target="#group<?= $group['id'] ?>"
                            type="button">
                        <?= htmlspecialchars($group['name']) ?>
                    </button>
                </li>
                <?php
                $first = false;
            endforeach;
            ?>
        </ul>
        <div class="tab-content">
            <?php
            $first = true;
            foreach ($groups as $group):
                if (empty($productsByGroup[$group['id']]))
                    continue;
                ?>
                <div
                        class="tab-pane fade <?= $first ? 'show active' : '' ?>"
                        id="group<?= $group['id'] ?>">
                    <div class="owl-carousel products-slider">
                        <?php foreach ($productsByGroup[$group['id']] as $product): ?>
                            <div class="item">
                                <div class="product-card">
                                    <a href="/product/<?= $product['seo_slug'] ?>">
                                        <div class="product-image">
                                            <img
                                                    src="cms/<?= htmlspecialchars($product['thumbnail']) ?>"
                                                    alt="<?= htmlspecialchars($product['name']) ?>">
                                        </div>
                                    </a>
                                    <div class="product-body">
                                        <h3>
                                            <a href="/product/<?= $product['seo_slug'] ?>">
                                                <?= htmlspecialchars($product['name']) ?>
                                            </a>
                                        </h3>
                                        <p>
                                            <?= mb_strimwidth(strip_tags($product['description']), 0, 120, '...') ?>
                                        </p>
                                        <div class="product-footer">
                                            <span class="price">
                                                <?php if ($product['price'] > 0): ?>
                                                    <?= number_format($product['price']) ?> تومان
                                                <?php else: ?>
                                                    تماس بگیرید
                                                <?php endif; ?>
                                            </span>
                                            <a href="/product/<?= $product['seo_slug'] ?>">
                                                مشاهده
                                                <i class="fa-solid fa-arrow-left ms-1"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php

                $first = false;

            endforeach;

            ?>
        </div>
        <div class="watchAllBlogs">

            <a href="products/all-product">

                <span>مشاهده تمامی محصولات</span>

                <i class="fa-solid fa-chevron-circle-left"></i>

            </a>

        </div>
    </div>
</section>


<!-- About Us -->
<section class="about-banner">

    <div class="about-overlay"></div>

    <div class="container">

        <div class="row justify-content-center">

            <div class="col-xl-8 col-lg-10 text-center">

                <span class="section-badge">

                    درباره شرکت

                </span>

                <h2>

                    <?= setting('name') ?>

                </h2>

                <p>
                    چشم‌انداز ما در فرآیند نوین اطلاعات پارسیان، تبدیل شدن به برترین ارائه‌دهنده راهکارهای ذخیره‌سازی
                    EMC و HPE در ایران است. با ارائه قطعات و خدمات تخصصی، به دنبال ایجاد ارزش افزوده برای مشتریان و
                    پیشرویی در صنعت ذخیره‌سازی داده‌ها هستیم.
                </p>

                <div class="about-features">

                    <span>

                        <i class="fa-solid fa-circle-check"></i>

                        بیش از ۱۵ سال تجربه

                    </span>

                    <span>

                        <i class="fa-solid fa-circle-check"></i>

                        مشاوره تخصصی رایگان

                    </span>

                    <span>

                        <i class="fa-solid fa-circle-check"></i>

                        پشتیبانی فنی تجهیزات

                    </span>

                </div>

                <div class="about-buttons">

                    <a href="" class="btn-primary-custom">

                        درباره ما

                    </a>

                    <a href="" class="btn-outline-custom">

                        تماس با کارشناسان

                    </a>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- Blog -->
<?php

$blogs = [];

$result = $mysqli->query("
    SELECT
        id,
        namefull,
        seo_slug,
        thumb,
        abstract,
        created_at
    FROM page
    WHERE parent_id='1'
    AND active='1'
    AND deleted='0'
    ORDER BY myorder ASC,id DESC
    LIMIT 4
");

while ($row = $result->fetch_assoc()) {

    $blogs[] = $row;

}

if (count($blogs) > 0):

    $featured = array_shift($blogs);

    ?>

    <section class="home-blog">

        <div class="container">

            <div class="services-heading">

            <span class="section-badge">
                مقالات آموزشی
            </span>

                <h2>
                    آخرین مطالب بلاگ
                </h2>

                <p>
                    جدیدترین مقالات آموزشی، اخبار فناوری و مطالب تخصصی حوزه تجهیزات ذخیره‌سازی و زیرساخت را مطالعه کنید.
                </p>

            </div>

            <div class="row g-4">

                <!-- Featured -->

                <div class="col-lg-6">

                    <article class="blog-featured">

                        <a href="/blog/<?= $featured['seo_slug'] ?>">

                            <div class="blog-image">

                                <img
                                        src="cms/<?= $featured['thumb'] ?>"
                                        alt="<?= htmlspecialchars($featured['namefull']) ?>">

                            </div>

                        </a>

                        <div class="blog-content">

                        <span class="blog-date">

                            <i class="fa-regular fa-calendar"></i>

                            <?= jdate('d F Y', strtotime($featured['created_at'])) ?>

                        </span>

                            <h3>

                                <a href="/blog/<?= $featured['seo_slug'] ?>">

                                    <?= htmlspecialchars($featured['namefull']) ?>

                                </a>

                            </h3>

                            <p>

                                <?= mb_strimwidth(strip_tags($featured['abstract']), 0, 220, '...') ?>

                            </p>

                            <a class="blog-more" href="/blog/<?= $featured['seo_slug'] ?>">

                                مطالعه مقاله

                                <i class="fa-solid fa-arrow-left"></i>

                            </a>

                        </div>

                    </article>

                </div>

                <!-- Other Articles -->

                <div class="col-lg-6">

                    <div class="row g-4">

                        <?php foreach ($blogs as $blog): ?>

                            <div class="col-12">

                                <article class="blog-mini">

                                    <a href="/blog/<?= $blog['seo_slug'] ?>">

                                        <div class="blog-mini-image">

                                            <img
                                                    src="cms/<?= $blog['thumb'] ?>"
                                                    alt="<?= htmlspecialchars($blog['namefull']) ?>">

                                        </div>

                                    </a>

                                    <div class="blog-mini-content">

                                    <span>

                                        <?= jdate('d F Y', strtotime($blog['created_at'])) ?>

                                    </span>

                                        <h4>

                                            <a href="/blog/<?= $blog['seo_slug'] ?>">

                                                <?= htmlspecialchars($blog['namefull']) ?>

                                            </a>

                                        </h4>

                                        <a href="/blog/<?= $blog['seo_slug'] ?>">

                                            ادامه مطلب

                                            <i class="fa-solid fa-arrow-left"></i>

                                        </a>

                                    </div>

                                </article>

                            </div>

                        <?php endforeach; ?>

                    </div>

                    <div class="watchAllBlogs">

                        <a href="/blogs">

                            <span>مشاهده تمامی مقالات</span>

                            <i class="fa-solid fa-arrow-left"></i>

                        </a>

                    </div>

                </div>

            </div>

        </div>

    </section>

<?php endif; ?>


<!-- footer -->
<?php require_once "inc/footer.php"?>


<script src="assets/js/jquery.js"></script>
<script src="assets/js/jquery.nice-select.min.js"></script>
<script src="assets/js/owl.carousel.min.js"></script>
<script src="assets/js/bootstrap.js"></script>
<script src="assets/js/bootstrap.bundle.js"></script>
<script src="assets/js/main.js"></script>
<script>
    $('.products-slider').owlCarousel({

        rtl: true,

        margin: 24,

        nav: true,

        dots: false,

        smartSpeed: 500,

        loop: false,

        navText: [
            '<i class="fa-solid fa-chevron-right"></i>',
            '<i class="fa-solid fa-chevron-left"></i>'
        ],

        responsive: {

            0: {
                items: 1
            },

            576: {
                items: 2
            },

            992: {
                items: 3
            },

            1400: {
                items: 4
            }

        }

    });
</script>
</body>
</html>