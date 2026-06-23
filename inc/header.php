<?php

?>
<nav class="hp-nav">
    <div class="hp-nav-inner">

        <!-- Logo -->
        <a class="hp-logo" href="./">
            <img src="assets/images/logo.png"
                 alt="<?= htmlspecialchars($global_setting_array['name'] ?? 'ParsEMC') ?>"
                 height="52" loading="eager">
        </a>

        <!-- Desktop nav links -->
        <ul class="hp-links" id="hpLinks">
            <li><a href="./"><span class="fa fa-house"></span> خانه</a></li>

            <li class="hp-has-drop">
                <a href="#">راهکارها <span class="hp-caret">▼</span></a>
                <ul class="hp-drop">
                    <li><a href="#"><span class="hp-drop-icon">🏢</span>سازمانی</a></li>
                    <li><a href="#"><span class="hp-drop-icon">🚀</span>استارتاپی</a></li>
                    <li><a href="#"><span class="hp-drop-icon">💳</span>فین‌تک</a></li>
                </ul>
            </li>

            <li class="hp-has-drop">
                <a href="#">محصولات <span class="hp-caret">▼</span></a>
                <ul class="hp-drop">
                    <li><a href="#"><span class="hp-drop-icon">🖥️</span>Dell EMC PowerStore</a></li>
                    <li><a href="#"><span class="hp-drop-icon">💾</span>Unity XT</a></li>
                    <li><a href="#"><span class="hp-drop-icon">⚡</span>PowerMax</a></li>
                    <li><a href="#"><span class="hp-drop-icon">☁️</span>Isilon NAS</a></li>
                </ul>
            </li>

            <li><a href="#">خدمات</a></li>
            <li><a href="#">پشتیبانی</a></li>
            <li><a href="#">درباره ما</a></li>
        </ul>

        <!-- Right-side actions -->
        <div class="hp-actions">
            <a href="#" class="hp-icon-btn" title="سبد خرید">
                <i class="fas fa-shopping-cart"></i>
            </a>
            <a href="#" class="hp-icon-btn" title="پروفایل کاربری">
                <i class="fas fa-user"></i>
            </a>
            <a href="#" class="hp-btn-cta">ثبت سفارش</a>
        </div>

        <!-- Hamburger (mobile only) -->
        <button class="hp-burger" id="hpBurger" aria-label="باز/بستن منو">
            <span></span><span></span><span></span>
        </button>

    </div>
</nav>

<div class="hp-drawer" id="hpDrawer">

    <a href="./" class="hp-drawer-link">خانه</a>

    <details>
        <summary class="hp-drawer-summary">راهکارها <span class="hp-drawer-arrow">▼</span></summary>
        <div class="hp-drawer-sub">
            <a href="#">🏢 سازمانی</a>
            <a href="#">🚀 استارتاپی</a>
            <a href="#">💳 فین‌تک</a>
        </div>
    </details>

    <details>
        <summary class="hp-drawer-summary">محصولات <span class="hp-drawer-arrow">▼</span></summary>
        <div class="hp-drawer-sub">
            <a href="#">🖥️ Dell EMC PowerStore</a>
            <a href="#">💾 Unity XT</a>
            <a href="#">⚡ PowerMax</a>
            <a href="#">☁️ Isilon NAS</a>
        </div>
    </details>

    <a href="#" class="hp-drawer-link">خدمات</a>
    <a href="#" class="hp-drawer-link">پشتیبانی</a>
    <a href="#" class="hp-drawer-link">درباره ما</a>

    <div class="hp-drawer-divider"></div>

    <a href="#" class="hp-icon-btn" style="width:100%;border-radius:12px;gap:10px;padding:12px;justify-content:center">
        <i class="fas fa-shopping-cart"></i> سبد خرید
    </a>
    <a href="#" class="hp-drawer-cta">ثبت سفارش</a>

</div>

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
