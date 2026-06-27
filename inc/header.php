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
            <a href="" class="hp-btn-cta">ثبت درخواست سفارش</a>

            <button id="theme-toggle" class="theme-toggle" aria-label="تغییر تم">
                <i class="fas fa-moon"></i>
            </button>
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
    <a href="#" class="hp-drawer-cta">درخواست پشتیبانی</a>

</div>
