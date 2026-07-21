<?php

$productMenus = [];

$result = $mysqli->query("
    SELECT
        id,
        name
    FROM product_menu
    WHERE active='1'
    AND deleted='0'
    ORDER BY myorder ASC
");

while($row = $result->fetch_assoc()){

    $productMenus[] = $row;

}

?>
<nav class="hp-nav">
    <div class="hp-nav-inner">

        <a class="hp-logo" href="./">
            <img src="assets/images/logo.png"
                 alt="<?= htmlspecialchars(setting('name') ?? 'ParsEMC') ?>"
                 height="52" loading="eager">
        </a>

        <ul class="hp-links" id="hpLinks">
            <li><a href="./"><span class="fa fa-house"></span> خانه</a></li>

            <li class="hp-has-drop">

                <a href="/products/all-product">

                    محصولات

                    <span class="hp-caret">▼</span>

                </a>

                <ul class="hp-drop">

                    <?php foreach($productMenus as $menu): ?>

                        <li>

                            <a href="/products/<?= url_slug($menu['name']) ?>">

                    <span class="hp-drop-icon">

                        <i class="fa-solid fa-server"></i>

                    </span>

                                <?= htmlspecialchars($menu['name']) ?>

                            </a>

                        </li>

                    <?php endforeach; ?>


                </ul>

            </li>

            <li><a href="team/">تیم ما</a></li>
            <li><a href="blogs/">وبلاگ</a></li>
            <li><a href="contact-us/">ارتباط با ما</a></li>
            <li><a href="about-us/">درباره ما</a></li>
        </ul>

        <div class="hp-actions">
            <a href="cart/" class="hp-icon-btn" title="سبد خرید">
                <span class="cart-badge"><?= $cartCount ?></span>
                <i class="fas fa-shopping-cart"></i>
            </a>
            <?php
                if (isset($_SESSION['user']['id'])){
                    $accountLink = "profile";
                }else{
                    $accountLink = "entry";
                }

            ?>
            <a href="<?= $accountLink ?>" class="hp-icon-btn" title="حساب کاربری">
                <i class="fas fa-user"></i>
            </a>
            <a href="contact-us/" class="hp-btn-cta">ثبت درخواست پشتیبانی</a>

            <button id="theme-toggle" class="theme-toggle" aria-label="تغییر تم">
                <i class="fas fa-moon"></i>
            </button>
        </div>

        <button class="hp-burger" id="hpBurger" aria-label="باز/بستن منو">
            <span></span><span></span><span></span>
        </button>

    </div>
</nav>

<div class="hp-drawer" id="hpDrawer">

    <a href="./" class="hp-drawer-link">خانه</a>
    <details>

        <summary class="hp-drawer-summary">

            محصولات

            <span class="hp-drawer-arrow">▼</span>

        </summary>

        <div class="hp-drawer-sub">

            <?php foreach($productMenus as $menu): ?>

                <a href="/products/<?=  url_slug($menu['name'])  ?>">

                    <i class="fa-solid fa-server me-2"></i>

                    <?= htmlspecialchars($menu['name']) ?>

                </a>

            <?php endforeach; ?>

            <a class="drawer-all" href="/products/all-product">

                <i class="fa-solid fa-grid-2 me-2"></i>

                مشاهده همه محصولات

            </a>

        </div>

    </details>

    <a href="team/" class="hp-drawer-link">تیم ما</a>
    <a href="blogs/" class="hp-drawer-link">وبلاگ</a>
    <a href="contact-us/" class="hp-drawer-link">ارتباط با ما</a>
    <a href="about-us/" class="hp-drawer-link">درباره ما</a>

    <div class="hp-drawer-divider"></div>

    <a href="cart/" class="hp-icon-btn" style="width:100%;border-radius:12px;gap:10px;padding:12px;justify-content:center">
        <i class="fas fa-shopping-cart"></i> سبد خرید
        <span class="cart-badge"><?= $cartCount ?></span>
    </a>
    <a href="contact-us/" class="hp-drawer-cta">درخواست پشتیبانی</a>

</div>
