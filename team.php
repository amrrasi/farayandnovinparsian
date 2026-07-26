<?php
require_once "cms/myadmin/inc/config.php";
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title> <?= setting('name') ?> | تیم ما</title>
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
          href="assets/css/pages/team.css?v=<?php echo filemtime('assets/css/pages/team.css'); ?>">
<body>

<?php require_once "inc/header.php" ?>


<main class="team-page">

    <section class="team-hero">

        <div class="hero-grid"></div>

        <div class="container">

            <div class="hero-content">

                <span class="hero-subtitle">

                    تیم حرفه‌ای ما

                </span>

                <h1>

                    افرادی که پشت هر پروژه
                    <span> <?= setting('name') ?> </span>
                    قرار دارند.

                </h1>

                <p>

                    ما فقط یک تیم نیستیم؛
                    مجموعه‌ای از متخصصان حوزه شبکه،
                    هوشمندسازی، امنیت و توسعه نرم‌افزار هستیم
                    که با تجربه و دانش، پروژه‌های سازمانی را
                    از ایده تا اجرا همراهی می‌کنیم.

                </p>

            </div>

        </div>

    </section>


    <section class="founder-section">

        <div class="container">

            <div class="founder-card">

                <div class="row align-items-center">

                    <div class="col-lg-5">

                        <div class="founder-image">

                            <div class="image-bg"></div>

                            <img
                                    src="assets/images/team/team.png"
                                    alt="Founder">

                            <span class="founder-badge">

                                 Founder & CEO

                            </span>

                        </div>

                    </div>

                    <div class="col-lg-7">

                        <div class="founder-content">

                            <span class="section-mini-title">

                                بنیانگذار مجموعه

                            </span>

                            <h2>

                                بهرام مرمری

                            </h2>

                            <h5>

                                مدیرعامل

                            </h5>

                            <p>

                                بیش از ۲۵ سال تجربه در زمینه
                                طراحی زیرساخت شبکه،
                                توسعه نرم‌افزار،
                                هوشمندسازی ساختمان،
                                سیستم‌های حفاظتی
                                و راهکارهای سازمانی.

                            </p>

                            <blockquote>

                                کیفیت،
                                نتیجه انجام کارهای بزرگ نیست؛
                                نتیجه انجام درست جزئیات است.

                            </blockquote>


                            <div class="founder-tech">

                                <span>PHP</span>

                                <span>Linux</span>

                                <span>MySQL</span>

                                <span>Docker</span>

                                <span>Cisco</span>

                                <span>Mikrotik</span>

                            </div>


                            <div class="founder-social">

                                <a href="#">

                                    <i class="fab fa-linkedin-in"></i>

                                </a>

                                <a href="#">

                                    <i class="fab fa-github"></i>

                                </a>

                                <a href="#">

                                    <i class="fas fa-envelope"></i>

                                </a>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </section>

    <section class="team-dna">

        <div class="container">

            <div class="section-heading">

            <span>

                TEAM DNA

            </span>

                <h2>

                    ارزش‌هایی که تیم ما بر پایه آن رشد کرده است

                </h2>

                <p>

                    هر پروژه موفق حاصل همکاری متخصصانی است که
                    کیفیت، خلاقیت، امنیت و مسئولیت‌پذیری را
                    در اولویت قرار می‌دهند.

                </p>

            </div>


            <div class="dna-wrapper">

                <div class="dna-item">

                    <div class="dna-icon">

                        <i class="fa-solid fa-award"></i>

                    </div>

                    <h4>

                        کیفیت

                    </h4>

                    <small>

                        Quality First

                    </small>

                </div>


                <div class="dna-item">

                    <div class="dna-icon">

                        <i class="fa-solid fa-lightbulb"></i>

                    </div>

                    <h4>

                        خلاقیت

                    </h4>

                    <small>

                        Creative Thinking

                    </small>

                </div>


                <div class="dna-item active">

                    <div class="dna-icon">

                        <i class="fa-solid fa-shield-halved"></i>

                    </div>

                    <h4>

                        امنیت

                    </h4>

                    <small>

                        Security Matters

                    </small>

                </div>


                <div class="dna-item">

                    <div class="dna-icon">

                        <i class="fa-solid fa-headset"></i>

                    </div>

                    <h4>

                        پشتیبانی

                    </h4>

                    <small>

                        Always Available

                    </small>

                </div>

            </div>

        </div>

    </section>

    <section class="core-team">

        <div class="container">

            <div class="section-heading">

            <span>

                CORE TEAM

            </span>

                <h2>

                    اعضای اصلی فرآیند نوین

                </h2>

            </div>


            <div class="team-grid">


                <article class="team-card">

                    <div class="member-image">

                        <img src="assets/images/team/team.png" alt="">

                        <span class="member-status online">

                        Available

                    </span>

                    </div>

                    <div class="member-content">

                        <h3>

                           آقای نصیری

                        </h3>

                        <span class="member-role">

                        Network Engineer

                    </span>

                        <p>

                            طراحی و اجرای زیرساخت شبکه،
                            تجهیزات Cisco،
                            Mikrotik
                            و امنیت سازمانی.

                        </p>

                        <div class="member-skills">

                            <span>Cisco</span>

                            <span>Mikrotik</span>

                            <span>Routing</span>

                        </div>

                    </div>

                </article>


                <article class="team-card">

                    <div class="member-image">

                        <img src="assets/images/team/team.png" alt="">

                        <span class="member-status busy">

                        On Project

                    </span>

                    </div>

                    <div class="member-content">

                        <h3>

                            رضا مرمری

                        </h3>

                        <span class="member-role">

                        Backend Developer

                    </span>

                        <p>

                            توسعه سامانه‌های اختصاصی،
                            API
                            و طراحی بانک اطلاعاتی.

                        </p>

                        <div class="member-skills">

                            <span>PHP</span>

                            <span>MySQL</span>

                            <span>Linux</span>

                        </div>

                    </div>

                </article>


                <article class="team-card">

                    <div class="member-image">

                        <img src="assets/images/team/team.png" alt="">

                        <span class="member-status online">

                        Available

                    </span>

                    </div>

                    <div class="member-content">

                        <h3>

                            امیررضا عسکری

                        </h3>

                        <span class="member-role">

                        Smart Home Specialist

                    </span>

                        <p>

                            طراحی و اجرای
                            سیستم‌های هوشمند،
                            KNX
                            و BMS.

                        </p>

                        <div class="member-skills">

                            <span>KNX</span>

                            <span>BMS</span>

                            <span>IoT</span>

                        </div>

                    </div>

                </article>

            </div>

        </div>

    </section>

    <section class="technical-team">

        <div class="container">

            <div class="section-heading">

            <span>

                TECHNICAL TEAM

            </span>

                <h2>

                    متخصصان پشت صحنه پروژه‌ها

                </h2>

                <p>

                    هر پروژه موفق، حاصل همکاری افراد متخصص در حوزه‌های مختلف است.

                </p>

            </div>

            <div class="technical-layout">

                <article class="team-card large">

                    <div class="member-image">

                        <img src="assets/images/team/team.png" alt="">

                        <span class="member-status online">

                        Available

                    </span>

                    </div>

                    <div class="member-content">

                        <h3>

                            محمد رضایی

                        </h3>

                        <span class="member-role">

                        Front-End Developer

                    </span>

                        <p>

                            طراحی رابط کاربری، توسعه صفحات وب،
                            بهینه‌سازی تجربه کاربری و رابط‌های واکنش‌گرا.

                        </p>

                        <div class="member-skills">

                            <span>HTML</span>

                            <span>CSS</span>

                            <span>JavaScript</span>

                            <span>Bootstrap</span>

                        </div>

                    </div>

                </article>


                <article class="team-card large offset">

                    <div class="member-image">

                        <img src="assets/images/team/team.png" alt="">

                        <span class="member-status busy">

                        On Project

                    </span>

                    </div>

                    <div class="member-content">

                        <h3>

                            حسین احمدی

                        </h3>

                        <span class="member-role">

                        CCTV & Security

                    </span>

                        <p>

                            طراحی و اجرای سیستم‌های نظارتی،
                            دوربین مداربسته و کنترل تردد.

                        </p>

                        <div class="member-skills">

                            <span>CCTV</span>

                            <span>NVR</span>

                            <span>Access Control</span>

                        </div>

                    </div>

                </article>


                <article class="team-card center-card">

                    <div class="member-image">

                        <img src="assets/images/team/team.png" alt="">

                        <span class="member-status online">

                        Available

                    </span>

                    </div>

                    <div class="member-content">

                        <h3>

                            امیر عباسی

                        </h3>

                        <span class="member-role">

                        Technical Support

                    </span>

                        <p>

                            پشتیبانی پروژه‌ها،
                            عیب‌یابی،
                            مانیتورینگ و نگهداری زیرساخت.

                        </p>

                        <div class="member-skills">

                            <span>Support</span>

                            <span>Linux</span>

                            <span>Monitoring</span>

                        </div>

                    </div>

                </article>

            </div>

        </div>

    </section>


    <section class="team-skills">

        <div class="container">

            <div class="section-heading">

            <span>

                OUR EXPERTISE

            </span>

                <h2>

                    مهارت‌های اصلی تیم

                </h2>

            </div>

            <div class="skills-grid">

                <div class="skill-item">

                    <span>PHP</span>

                    <div class="skill-bar">

                        <span style="width:95%"></span>

                    </div>

                </div>

                <div class="skill-item">

                    <span>Linux</span>

                    <div class="skill-bar">

                        <span style="width:90%"></span>

                    </div>

                </div>

                <div class="skill-item">

                    <span>Network</span>

                    <div class="skill-bar">

                        <span style="width:96%"></span>

                    </div>

                </div>

                <div class="skill-item">

                    <span>Smart Building</span>

                    <div class="skill-bar">

                        <span style="width:92%"></span>

                    </div>

                </div>

                <div class="skill-item">

                    <span>Security</span>

                    <div class="skill-bar">

                        <span style="width:94%"></span>

                    </div>

                </div>

                <div class="skill-item">

                    <span>UI / UX</span>

                    <div class="skill-bar">

                        <span style="width:88%"></span>

                    </div>

                </div>

            </div>

        </div>

    </section>


<!--    <section class="workspace-gallery">-->
<!---->
<!--        <div class="container">-->
<!---->
<!--            <div class="section-heading">-->
<!---->
<!--            <span>-->
<!---->
<!--                WORKSPACE-->
<!---->
<!--            </span>-->
<!---->
<!--                <h2>-->
<!---->
<!--                    نگاهی به فضای کاری ما-->
<!---->
<!--                </h2>-->
<!---->
<!--            </div>-->
<!---->
<!--            <div class="gallery-grid">-->
<!---->
<!--                <div class="gallery-item">-->
<!---->
<!--                    <img src="assets/images/team/team.png" alt="">-->
<!---->
<!--                </div>-->
<!---->
<!--                <div class="gallery-item tall">-->
<!---->
<!--                    <img src="assets/images/team/team.png" alt="">-->
<!---->
<!--                </div>-->
<!---->
<!--                <div class="gallery-item">-->
<!---->
<!--                    <img src="assets/images/team/team.png" alt="">-->
<!---->
<!--                </div>-->
<!---->
<!--                <div class="gallery-item wide">-->
<!---->
<!--                    <img src="assets/images/team/team.png" alt="">-->
<!---->
<!--                </div>-->
<!---->
<!--            </div>-->
<!---->
<!--        </div>-->
<!---->
<!--    </section>-->

    <section class="team-values">

        <div class="container">

            <div class="section-heading">

            <span>

                OUR VALUES

            </span>

                <h2>

                    ارزش‌هایی که هر روز بر اساس آن‌ها کار می‌کنیم

                </h2>

            </div>

            <div class="values-grid">

                <article>

                    <i class="fa-solid fa-medal"></i>

                    <h4>

                        کیفیت

                    </h4>

                    <p>

                        هیچ پروژه‌ای بدون رعایت جزئیات تحویل داده نمی‌شود.

                    </p>

                </article>

                <article>

                    <i class="fa-solid fa-lightbulb"></i>

                    <h4>

                        نوآوری

                    </h4>

                    <p>

                        همیشه به دنبال بهترین و به‌روزترین راهکارها هستیم.

                    </p>

                </article>

                <article>

                    <i class="fa-solid fa-user-shield"></i>

                    <h4>

                        امنیت

                    </h4>

                    <p>

                        امنیت اطلاعات و زیرساخت همیشه اولویت ماست.

                    </p>

                </article>

                <article>

                    <i class="fa-solid fa-headset"></i>

                    <h4>

                        پشتیبانی

                    </h4>

                    <p>

                        پایان پروژه، شروع پشتیبانی واقعی ماست.

                    </p>

                </article>

            </div>

        </div>

    </section>


    <section class="team-stack">

        <div class="container">

            <div class="section-heading">

            <span>

                TECH STACK

            </span>

                <h2>

                    تکنولوژی‌هایی که هر روز با آن‌ها کار می‌کنیم

                </h2>

            </div>

            <div class="stack-cloud">

                <span><i class="fab fa-php"></i> PHP</span>

                <span><i class="fab fa-linux"></i> Linux</span>

                <span><i class="fas fa-database"></i> MySQL</span>

                <span><i class="fab fa-docker"></i> Docker</span>

                <span>Mikrotik</span>

                <span>Cisco</span>

                <span>Bootstrap</span>

                <span>JavaScript</span>

                <span>jQuery</span>

                <span>Ajax</span>

                <span>REST API</span>

                <span>Git</span>

            </div>

        </div>

    </section>

    <div class="team-background">

        <span></span>

        <span></span>

        <span></span>

        <span></span>

        <span></span>

        <span></span>

    </div>

</main>


<?php require_once "inc/footer.php" ?>


<script src="assets/js/jquery.js"></script>
<script src="assets/js/jquery.nice-select.min.js"></script>
<script src="assets/js/owl.carousel.min.js"></script>
<script src="assets/js/bootstrap.js"></script>
<script src="assets/js/bootstrap.bundle.js"></script>
<script src="assets/js/main.js"></script>
<script src="assets/js/pages/team.js"></script>

</body>
</html>