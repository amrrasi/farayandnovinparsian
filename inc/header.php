<header class="hero-section">

    <div class="noise"></div>
    <div class="grid"></div>

    <div class="gradient gradient-1"></div>
    <div class="gradient gradient-2"></div>
    <div class="gradient gradient-3"></div>

    <nav class="navbar navbar-expand-xl navbar-dark fixed-top glass-nav">

        <div class="container">

            <a class="navbar-brand" href="#">
                <img src="assets/images/logo.webp" alt="<?= $global_setting_array['name'] ?>">
            </a>

            <button
                    class="navbar-toggler"
                    data-bs-toggle="collapse"
                    data-bs-target="#mainNav">

                <span class="navbar-toggler-icon"></span>

            </button>

            <div class="collapse navbar-collapse" id="mainNav">

                <ul class="navbar-nav mx-auto">

                    <li class="nav-item">
                        <a href="./">خانه</a>
                    </li>


                    <li class="nav-item has-submenu">

                        <a href="#">راهکارها</a>

                        <ul class="submenu">

                            <li><a href="#">سازمانی</a></li>
                            <li><a href="#">استارتاپی</a></li>
                            <li><a href="#">فین‌تک</a></li>

                        </ul>

                    </li>

                </ul>

                <div class="nav-actions">

                    <a href="#" class="icon-btn">
                        <i class="fas fa-shopping-cart"></i>
                    </a>

                    <a href="#" class="icon-btn">
                        <i class="fas fa-user"></i>
                    </a>

                </div>

            </div>

        </div>

    </nav>

    <div class="container mt-5">

        <div class="row align-items-center min-vh-100">

            <div class="col-lg-6 order-2 order-lg-1">

                <div class="hero-content">

                    <span class="mini-badge">

                       اطمینان، سرعت و امنیت در کنار ما

                    </span>

                    <h1>
                        <?= $global_setting_array['name'] ?>
                    </h1>

                    <p>

                        فرآیند نوین اطلاعات پارسیان، تامین‌کننده تخصصی استوریج‌های EMC در ایران. ارائه راهکارهای
                        ذخیره‌سازی پیشرفته برای کسب‌وکارهای مدرن. با تکیه بر دانش فنی و تجربه، بهترین انتخاب را برای
                        مدیریت داده‌های حیاتی شما فراهم می‌کنیم.

                    </p>

                    <div class="hero-buttons">

                        <a href="#" class="btn-primary-custom">
                            ثبت سفارش
                        </a>

                        <a href="#" class="btn-glass">
                            مشاهده خدمات
                        </a>

                    </div>

                </div>

            </div>

            <div class="col-lg-6 order-1 order-lg-2">

                <div class="hero-dashboard">

                    <div class="dashboard-card main-card">

                        <div class="circle"></div>

                        <h4>متن تستی</h4>

                        <div class="graph"></div>

                    </div>

                    <div class="floating-card card-1">
                        متن تستی
                    </div>

                    <div class="floating-card card-2">
                        متن تستی
                    </div>

                    <div class="floating-card card-3">
                        متن تستی
                    </div>

                </div>

            </div>

        </div>

    </div>

</header>
