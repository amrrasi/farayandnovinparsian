<?php
require_once "cms/myadmin/inc/config.php";

if (!isset($categorySlug)) {
    $uri  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $segs = array_filter(explode('/', trim($uri, '/')));
    $segs = array_values($segs);
    $categorySlug = isset($segs[1]) ? $segs[1] : 'all-product';
}

$menuStmt = $pdo->query("
    SELECT id, name
    FROM   product_menu
    WHERE  active  = 1
      AND  deleted = 0
      AND  (parent_id = 0 OR parent_id IS NULL)
    ORDER  BY myorder, id
");
$menuItems = $menuStmt->fetchAll(PDO::FETCH_ASSOC);

$activeMenu = null;

if ($categorySlug !== 'all-product') {
    foreach ($menuItems as $m) {
        if (url_slug($m['name']) === $categorySlug) {
            $activeMenu = $m;
            break;
        }
    }
}

if ($categorySlug === 'all-product' || $activeMenu === null) {
    $prodStmt = $pdo->query("
        SELECT p.*, pm.name AS menu_name
        FROM   product p
        LEFT   JOIN product_menu pm ON pm.id = p.product_menu_id
        WHERE  p.active  = 1
          AND  p.deleted = 0
        ORDER  BY p.myorder, p.id
    ");
} else {
    $menuId   = (int) $activeMenu['id'];
    $childIds = [$menuId];

    $childStmt = $pdo->prepare("
        SELECT id FROM product_menu
        WHERE  parent_id = ?
          AND  active    = 1
          AND  deleted   = 0
    ");
    $childStmt->execute([$menuId]);
    foreach ($childStmt->fetchAll(PDO::FETCH_COLUMN) as $cid) {
        $childIds[] = (int)$cid;
    }

    $in  = implode(',', $childIds);
    $prodStmt = $pdo->query("
        SELECT p.*, pm.name AS menu_name
        FROM   product p
        LEFT   JOIN product_menu pm ON pm.id = p.product_menu_id
        WHERE  p.product_menu_id IN ($in)
          AND  p.active  = 1
          AND  p.deleted = 0
        ORDER  BY p.myorder, p.id
    ");
}

$products = $prodStmt->fetchAll(PDO::FETCH_ASSOC);

$dna_map = [
        'powerstore'  => ['performance'=>100,'security'=>100,'scalability'=>100,'cloud'=>100],
        'powermax'    => ['performance'=>100,'security'=>100,'scalability'=>100,'cloud'=>100],
        'unity'       => ['performance'=>100,'security'=>100,'scalability'=>100,'cloud'=>100],
        'isilon'      => ['performance'=>100,'security'=>100,'scalability'=>100,'cloud'=>100],
        'catalyst'    => ['performance'=>100,'security'=>100,'scalability'=>100,'cloud'=>100],
        'nexus'       => ['performance'=>100,'security'=>100,'scalability'=>100,'cloud'=>100],
        'asa'         => ['performance'=>100,'security'=>100,'scalability'=>100,'cloud'=>100],
        'vsphere'     => ['performance'=>100,'security'=>100,'scalability'=>100,'cloud'=>100],
        'nsx'         => ['performance'=>100,'security'=>100,'scalability'=>100,'cloud'=>100],
        'vsan'        => ['performance'=>100,'security'=>100,'scalability'=>100,'cloud'=>100],
        'fortigate'   => ['performance'=>100,'security'=>100,'scalability'=>100,'cloud'=>100],
        'fortiweb'    => ['performance'=>100,'security'=>100,'scalability'=>100,'cloud'=>100],
        'default'     => ['performance'=>100,'security'=>100,'scalability'=>100,'cloud'=>100],
];

function get_dna(string $slug, array $map): array
{
    $slug = strtolower($slug);
    foreach ($map as $key => $vals) {
        if ($key !== 'default' && strpos($slug, $key) !== false) {
            return $vals;
        }
    }
    return $map['default'];
}

$pageTitle = $activeMenu
        ? htmlspecialchars($activeMenu['name'])
        : 'محصولات سازمانی';
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars(setting('name')) ?> | <?= $pageTitle ?></title>
    <meta name="description" content="<?= setting('meta_description') ?>">
    <?= $global_base_address ?>

    <link rel="stylesheet" href="assets/css/base/animate.min.css">
    <link rel="stylesheet" href="assets/css/base/flaticon.css">
    <link rel="stylesheet" href="assets/css/base/fontawesome.min.css">
    <link rel="stylesheet" href="assets/css/base/magnific-popup.min.css">
    <link rel="stylesheet" href="assets/css/base/nice-select.css">
    <link rel="stylesheet" href="assets/css/base/owl.carousel.min.css">
    <link rel="stylesheet" href="assets/fonts/font.css">
    <link rel="stylesheet" href="assets/css/base/bootstrap.rtl.css">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/images/favicon.webp">
    <link rel="stylesheet" href="assets/css/base/base.css">
    <link rel="stylesheet" href="assets/css/layout/header.css">
    <link rel="stylesheet" href="assets/css/layout/footer.css">
    <link rel="stylesheet" href="assets/css/pages/products.css?v=<?= filemtime('assets/css/pages/products.css') ?>">
</head>
<body>

<?php require_once "inc/header.php" ?>

<canvas id="bg-canvas"></canvas>

<section class="products-hero">
    <div class="container">

        <div class="hero-breadcrumb">
            <a href="./"><i class="fa-solid fa-house"></i> خانه</a>
            <i class="fa-solid fa-angle-left"></i>
            <a href="products/all-product">محصولات</a>
            <?php if ($activeMenu): ?>
                <i class="fa-solid fa-angle-left"></i>
                <strong style="color:var(--cyan-accent)"><?= htmlspecialchars($activeMenu['name']) ?></strong>
            <?php endif; ?>
        </div>

        <div class="row align-items-center g-5">

            <div class="col-lg-6">
                <div class="hero-eyebrow"> <?= setting('name') ?> </div>
                <h1 class="hero-title">
                    <?php if ($activeMenu): ?>
                        <?= htmlspecialchars($activeMenu['name']) ?>
                    <?php else: ?>
                        ذخیره‌سازی، شبکه،<br>مجازی‌سازی و امنیت<br>سازمانی
                    <?php endif; ?>
                </h1>
                <p class="hero-subtitle">
                    راهکارهای حرفه‌ای زیرساخت سازمانی — برای آن‌هایی که انتخاب‌شان اهمیت دارد.
                </p>
                <div class="hero-actions">
                    <a href="#products-list" class="btn-primary-hero">
                        مشاهده محصولات
                        <i class="fa-solid fa-arrow-down-long"></i>
                    </a>

                </div>
            </div>

            <div class="col-lg-6 d-flex justify-content-center">
                <div class="orbit-stage" id="orbitStage">

                    <div class="orbit-ring orbit-ring-1"></div>
                    <div class="orbit-ring orbit-ring-2"></div>
                    <div class="orbit-ring orbit-ring-3"></div>
                    <div class="orbit-ring orbit-ring-4"></div>
                    <div class="orbit-ring orbit-ring-5"></div>

                    <div class="orbit-core">
                        <i class="fa-solid fa-server"></i>
                    </div>

                </div>
            </div>

        </div>
    </div>
</section>


<!-- ================================================================
     QUICK STATS STRIP
     ================================================================ -->
<!--<div class="stats-strip">-->
<!--    <div class="container">-->
<!--        <div class="stats-inner">-->
<!--            <div class="stat-block">-->
<!--                <strong>--><?php //= count($products) ?><!--+</strong>-->
<!--                <span>محصول سازمانی</span>-->
<!--            </div>-->
<!--            <div class="stat-block">-->
<!--                <strong>--><?php //= count($menuItems) ?><!--+</strong>-->
<!--                <span>دسته‌بندی</span>-->
<!--            </div>-->
<!--            <div class="stat-block">-->
<!--                <strong>24/7</strong>-->
<!--                <span>پشتیبانی تخصصی</span>-->
<!--            </div>-->
<!--            <div class="stat-block">-->
<!--                <strong>Enterprise</strong>-->
<!--                <span>Grade Solutions</span>-->
<!--            </div>-->
<!--        </div>-->
<!--    </div>-->
<!--</div>-->


<!-- ================================================================
     STICKY CATEGORY NAV — from product_menu
     ================================================================ -->
<!--<nav class="sticky-cat-nav" id="catNav">-->
<!--    <div class="container">-->
<!--        <div class="cat-scroll">-->
<!---->
<!--            <a href="products/all-product"-->
<!--               class="cat-link --><?php //= $categorySlug === 'all-product' ? 'active' : '' ?><!--">-->
<!--                <span class="cat-indicator"></span>-->
<!--                همه محصولات-->
<!--            </a>-->
<!---->
<!--            --><?php //foreach ($menuItems as $m):
//                $mSlug   = make_slug($m['name']);
//                $isActive = ($categorySlug === $mSlug);
//                ?>
<!--                <a href="products/--><?php //= $mSlug ?><!--"-->
<!--                   class="cat-link --><?php //= $isActive ? 'active' : '' ?><!--">-->
<!--                    <span class="cat-indicator"></span>-->
<!--                    --><?php //= htmlspecialchars($m['name']) ?>
<!--                </a>-->
<!--            --><?php //endforeach; ?>
<!---->
<!--        </div>-->
<!--    </div>-->
<!--</nav>-->


<div class="filter-bar">
    <div class="container">
        <div class="filter-inner">

            <!-- Search -->
            <div class="search-field">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="productSearch"
                       placeholder="جستجو بین محصولات سازمانی ...">
            </div>

            <!-- Category chips (mirror of sticky nav, for quick in-page filtering) -->
            <div class="brand-chips" id="catChips">
                <button class="brand-chip <?= $categorySlug === 'all-product' ? 'active' : '' ?>"
                        data-menu-id="all">
                    همه
                </button>
                <?php foreach ($menuItems as $m): ?>
                    <button class="brand-chip <?= ($activeMenu && $activeMenu['id'] == $m['id']) ? 'active' : '' ?>"
                            data-menu-id="<?= $m['id'] ?>">
                        <?= htmlspecialchars($m['name']) ?>
                    </button>
                <?php endforeach; ?>
            </div>

        </div>
    </div>
</div>


<section class="products-masonry-section" id="products-list">
    <div class="container">

        <?php if (count($products)): ?>

            <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-4" id="masonryGrid">

                <?php foreach ($products as $product):
                    $slug = $product['seo_slug'] ?? '';
                    $dna  = get_dna($slug, $dna_map);

                    $productUrl   = "product/{$slug}";
                    $cardCategory = $product['menu_name'] ?? ($activeMenu ? $activeMenu['name'] : 'محصولات سازمانی');
                    $menuId       = $product['product_menu_id'] ?? 0;
                    ?>

                    <div class="col product-card-wrap"
                         data-menu-id="<?= (int)$menuId ?>"
                         data-name="<?= htmlspecialchars(strtolower($product['name'])) ?>">

                        <!-- The entire card is one link — image, badge, title and
                             description all navigate to the product page. -->
                        <a href="<?= $productUrl ?>" class="product-card">

                            <?php if (!empty($product['thumbnail'])): ?>
                                <div class="card-image">
                                    <img src="cms/<?= htmlspecialchars($product['thumbnail']) ?>"
                                         alt="<?= htmlspecialchars($product['name']) ?>"
                                         loading="lazy">
                                    <span class="card-brand-chip">
                                        <?= htmlspecialchars($cardCategory) ?>
                                    </span>
                                    <div class="card-hover-overlay">
                                        <span class="hover-view-pill">
                                            <i class="fa-solid fa-arrow-left"></i>
                                            مشاهده محصول
                                        </span>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="card-body">

                                <div class="card-category">
                                    <?= htmlspecialchars($cardCategory) ?>
                                </div>

                                <h3 class="card-title">
                                    <?= htmlspecialchars($product['name']) ?>
                                </h3>

                                <?php if (!empty($product['seo_description'])): ?>
                                    <p class="card-abstract">
                                        <?= htmlspecialchars($product['seo_description']) ?>
                                    </p>
                                <?php endif; ?>

                            </div>
                        </a>
                    </div>

                <?php endforeach; ?>

            </div>

            <!-- Shown only client-side when a search/filter combination matches
                 nothing (all server-rendered cards are hidden). -->
            <div class="products-empty d-none" id="productsEmptyDynamic">
                <div class="empty-icon"><i class="fa-solid fa-box-open"></i></div>
                <h2>محصولی پیدا نشد</h2>
                <p>عبارت جستجو یا دسته انتخابی نتیجه‌ای نداشت.</p>
            </div>

        <?php else: ?>
            <div class="products-empty">
                <div class="empty-icon"><i class="fa-solid fa-box-open"></i></div>
                <h2>محصولی پیدا نشد</h2>
                <p>در حال حاضر محصولی برای این دسته ثبت نشده است.</p>
                <a href="products/all-product" class="btn-primary-hero"
                   style="display:inline-flex;margin:0 auto">
                    مشاهده همه محصولات
                </a>
            </div>
        <?php endif; ?>

    </div>
</section>


<section class="consultant-section">
    <div class="container">
        <div class="consultant-inner">
            <div class="consultant-icon">
                <i class="fa-solid fa-user-tie"></i>
            </div>
            <div class="consultant-text">
                <div class="consultant-eyebrow">به کمک ما نیاز دارید ؟</div>
                <h2 class="consultant-title">بین محصولات مطمئن نیستید؟</h2>
                <p class="consultant-body">
                    اگر نمی‌دانید کدام محصول برای زیرساخت سازمان شما مناسب‌تر است،
                    کارشناسان ما آن را بررسی کرده و بهترین راهکار را پیشنهاد می‌دهند.
                    بدون هزینه، بدون تعهد.
                </p>
            </div>
            <div class="consultant-action">
                <a href="contact-us" class="btn-primary-hero">
                    <i class="fa-solid fa-calendar-check"></i>
                    مشاوره رایگان
                </a>
            </div>
        </div>
    </div>
</section>


<?php require_once "inc/footer.php" ?>

<script src="assets/js/jquery.js"></script>
<script src="assets/js/jquery.nice-select.min.js"></script>
<script src="assets/js/owl.carousel.min.js"></script>
<script src="assets/js/bootstrap.bundle.js"></script>
<script src="assets/js/main.js"></script>

<script>
    const MENU_DATA = <?= json_encode(array_map(function($m) {
        return [
                'id'   => $m['id'],
                'name' => $m['name'],
                'slug' => url_slug($m['name']),
        ];
    }, $menuItems), JSON_UNESCAPED_UNICODE) ?>;

    const CURRENT_SLUG = <?= json_encode($categorySlug) ?>;
</script>

<script src="assets/js/pages/products.js?v=<?= filemtime('assets/js/pages/products.js') ?>"></script>

</body>
</html>