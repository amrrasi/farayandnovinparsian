<?php
require_once "cms/myadmin/inc/config.php";

$slug = trim($_GET['category'] ?? '', '/');
if (empty($slug)) {
    header('Location: ' . $baseAddress . 'products');
    exit;
}

$stmt = $pdo->prepare("
    SELECT p.*,
           pm.name        AS category_name
    FROM   product p
    LEFT JOIN product_menu pm  ON pm.id  = p.product_menu_id
    LEFT JOIN product_menu pmp ON pmp.id = pm.parent_id
    WHERE  p.seo_slug = :slug
      AND  p.active   = 1
      AND  p.deleted  = 0
    LIMIT  1
");
$stmt->execute([':slug' => $slug]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);
$product['category_slug'] = url_slug($product['category_name']);

if (!$product) {
    header('Location: ' . $baseAddress . '404');
    exit;
}

$pdo->prepare("UPDATE product SET visit = visit + 1 WHERE id = :id")
        ->execute([':id' => $product['id']]);
$product['visit']++;

$attributes = [];
if (!empty($product['attribute'])) {
    $decoded = json_decode($product['attribute'], true);
    if (is_array($decoded)) {
        foreach ($decoded as $item) {
            $item = trim($item);
            if ($item === '') continue;
            if (strpos($item, ':') !== false) {
                [$key, $val] = explode(':', $item, 2);
                $attributes[] = ['key' => trim($key), 'val' => trim($val), 'raw' => $item];
            } else {
                $attributes[] = ['key' => null, 'val' => null, 'raw' => $item];
            }
        }
    }
}

$relatedStmt = $pdo->prepare("
    SELECT id, name, seo_slug, thumbnail, price
    FROM   product
    WHERE  product_menu_id = :mid
      AND  id             != :id
      AND  active          = 1
      AND  deleted         = 0
    ORDER BY visit DESC
    LIMIT 4
");
$relatedStmt->execute([
        ':mid' => $product['product_menu_id'],
        ':id' => $product['id'],
]);
$related = $relatedStmt->fetchAll(PDO::FETCH_ASSOC);

$prev = $pdo->prepare("
    SELECT id, name, seo_slug
    FROM   product
    WHERE  id < :id
      AND  product_menu_id = :mid
      AND  active = 1 AND deleted = 0
    ORDER BY id DESC
    LIMIT 1
");
$prev->execute([':id' => $product['id'], ':mid' => $product['product_menu_id']]);
$prevProduct = $prev->fetch(PDO::FETCH_ASSOC);

$next = $pdo->prepare("
    SELECT id, name, seo_slug
    FROM   product
    WHERE  id > :id
      AND  product_menu_id = :mid
      AND  active = 1 AND deleted = 0
    ORDER BY id ASC
    LIMIT 1
");
$next->execute([':id' => $product['id'], ':mid' => $product['product_menu_id']]);
$nextProduct = $next->fetch(PDO::FETCH_ASSOC);

$pageUrl = $baseAddress . 'product/' . htmlspecialchars($product['seo_slug']);
$pageTitle = htmlspecialchars($product['seo_title'] ?: $product['name']);
$pageDesc = htmlspecialchars($product['seo_description'] ?? '');
$pageRobots = htmlspecialchars($product['meta_robots'] ?? 'index,follow');
$canonical = $product['canonical_url'] ? htmlspecialchars($product['canonical_url']) : $pageUrl;

$categorySlug = url_slug($product['name'])
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= $pageTitle ?> | <?= htmlspecialchars(setting('name')) ?></title>
    <meta name="description" content="<?= $pageDesc ?>">
    <meta name="robots" content="<?= $pageRobots ?>">
    <link rel="canonical" href="<?= $canonical ?>">
    <?php if (!empty($product['seo_keywords'])): ?>
        <meta name="keywords" content="<?= htmlspecialchars($product['seo_keywords']) ?>">
    <?php endif; ?>

    <meta property="og:type" content="product">
    <meta property="og:title" content="<?= $pageTitle ?>">
    <meta property="og:description" content="<?= $pageDesc ?>">
    <meta property="og:url" content="<?= $pageUrl ?>">
    <meta property="og:image" content="<?= htmlspecialchars($product['thumbnail']) ?>">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= $pageTitle ?>">
    <meta name="twitter:description" content="<?= $pageDesc ?>">
    <meta name="twitter:image" content="<?= htmlspecialchars($product['thumbnail']) ?>">

    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "Product",
            "name": <?= json_encode($product['name'], JSON_UNESCAPED_UNICODE) ?>,
        "description": <?= json_encode($product['seo_description'] ?? '', JSON_UNESCAPED_UNICODE) ?>,
        "image": <?= json_encode($product['thumbnail'], JSON_UNESCAPED_UNICODE) ?>,
        "url": "<?= $pageUrl ?>",
        "offers": {
            "@type": "Offer",
            "price": "<?= $product['price'] ?>",
            "priceCurrency": "IRR",
            "availability": "https://schema.org/InStock"
        }
    }
    </script>

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
          href="assets/css/pages/product-single.css?v=<?= filemtime('assets/css/pages/product-single.css') ?>">
</head>
<body>

<?php require_once "inc/header.php" ?>

<!--==========================================
PRODUCT HERO
===========================================-->
<section class="product-single mt-5">
    <div class="container">

        <nav class="breadcrumb" aria-label="مسیر">
            <a href="<?= $baseAddress ?>">خانه</a>
            <i class="fa-solid fa-angle-left" aria-hidden="true"></i>
            <a href="<?= $baseAddress ?>products">محصولات</a>
            <?php if (!empty($product['category_name'])): ?>
                <i class="fa-solid fa-angle-left" aria-hidden="true"></i>
                <a href="<?= $baseAddress ?>products/<?= htmlspecialchars($product['category_slug']) ?>">
                    <?= htmlspecialchars($product['category_name']) ?>
                </a>
            <?php endif; ?>
            <i class="fa-solid fa-angle-left" aria-hidden="true"></i>
            <span><?= htmlspecialchars($product['name']) ?></span>
        </nav>

        <div class="row gy-5">

            <div class="col-lg-7">
                <div class="product-gallery">
                    <div class="gallery-main">
                        <img
                                id="mainProductImage"
                                src="cms/<?= htmlspecialchars($product['thumbnail']) ?>"
                                alt="<?= htmlspecialchars($product['name']) ?>"
                                loading="eager">
                    </div>
                    <div class="gallery-thumbs">
                        <button class="active" aria-label="تصویر اصلی">
                            <img src="cms/<?= htmlspecialchars($product['thumbnail']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <aside class="product-aside">

                    <?php if (!empty($product['category_name'])): ?>
                        <span class="product-label">
                            <?= htmlspecialchars($product['category_name']) ?>
                        </span>
                    <?php endif; ?>

                    <h1><?= htmlspecialchars($product['name']) ?></h1>

                    <?php if (!empty($product['seo_description'])): ?>
                        <p class="product-short">
                            <?= htmlspecialchars($product['seo_description']) ?>
                        </p>
                    <?php endif; ?>

                    <div class="product-meta">
                        <div>
                            <span>دسته‌بندی</span>
                            <strong><?= htmlspecialchars($product['category_name'] ?? '—') ?></strong>
                        </div>
                        <div>
                            <span>وضعیت</span>
                            <strong class="text-success">موجود</strong>
                        </div>
                    </div>

                    <div class="price-box">
                        <small>قیمت محصول</small>
                        <?php if ($product['price'] > 0): ?>
                            <strong><?= number_format($product['price']) ?> تومان</strong>
                        <?php else: ?>
                            <strong class="price-on-request">جهت استعلام قیمت تماس بگیرید <br> <a style="font-size: 16px; color: var(--clr-text)"
                                        href="tel:<?= setting('phone') ?>"><?= setting('phone') ?></a> </strong>
                        <?php endif; ?>
                    </div>

                    <div class="qty-box">
                        <button class="minus" aria-label="کاهش تعداد">−</button>
                        <input type="number" id="qty" min="1" value="1" aria-label="تعداد">
                        <button class="plus" aria-label="افزایش تعداد">+</button>
                    </div>

                    <div class="buy-buttons">
                        <button class="btn-main add-cart" data-id="<?= $product['id'] ?>">
                            <i class="fa-solid fa-cart-plus"></i>
                            افزودن به سبد خرید
                        </button>
                        <button class="btn-border">خرید سریع</button>
                    </div>

                    <div class="aside-tools">
                        <button>
                            <i class="fa-solid fa-share-nodes"></i>
                            اشتراک‌گذاری
                        </button>
                    </div>

                    <div class="guarantee-box">
                        <div>
                            <i class="fa-solid fa-shield"></i>
                            ضمانت اصالت کالا
                        </div>
                        <div>
                            <i class="fa-solid fa-truck-fast"></i>
                            ارسال سریع
                        </div>
                        <div>
                            <i class="fa-solid fa-headset"></i>
                            پشتیبانی تخصصی
                        </div>
                    </div>

                </aside>
            </div>

        </div>
    </div>
</section>

<section class="product-content-section">
    <div class="container">
        <div class="row">

            <div class="col-lg-12">

                <div class="product-tabs" role="tablist">
                    <button class="active" data-tab="overview" role="tab" aria-selected="true">معرفی محصول</button>
                    <?php if (!empty($attributes)): ?>
                        <button data-tab="features" role="tab" aria-selected="false">ویژگی‌ها</button>
                        <button data-tab="specs" role="tab" aria-selected="false">مشخصات فنی</button>
                    <?php endif; ?>
                    <!--                    <button data-tab="downloads" role="tab" aria-selected="false">دانلودها</button>-->
                </div>

                <div class="tab-content active" id="overview" role="tabpanel">
                    <div class="content-card">
                        <h2><?= htmlspecialchars($product['name']) ?></h2>
                        <div class="description">
                            <?= $product['description'] ?>
                        </div>
                    </div>
                </div>

                <?php if (!empty($attributes)): ?>

                    <div class="tab-content" id="features" role="tabpanel">
                        <div class="content-card">
                            <h2>ویژگی‌های کلیدی</h2>
                            <div class="feature-grid">
                                <?php foreach ($attributes as $attr): ?>
                                    <div class="feature-item">
                                        <i class="fa-solid fa-circle-check"></i>
                                        <span><?= htmlspecialchars($attr['raw']) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="tab-content" id="specs" role="tabpanel">
                        <div class="content-card">
                            <h2>مشخصات فنی</h2>
                            <?php
                            $specRows = array_filter($attributes, fn($a) => $a['key'] !== null);
                            ?>
                            <?php if (!empty($specRows)): ?>
                                <table class="spec-table">
                                    <tbody>
                                    <?php foreach ($specRows as $attr): ?>
                                        <tr>
                                            <th><?= htmlspecialchars($attr['key']) ?></th>
                                            <td><?= htmlspecialchars($attr['val']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p class="text-muted">مشخصات فنی موجود نیست.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                <?php endif; ?>

                <!-- Downloads -->
<!--                <div class="tab-content" id="downloads" role="tabpanel">-->
<!--                    <div class="content-card">-->
<!--                        <h2>فایل‌های محصول</h2>-->
<!--                        <div class="download-list">-->
<!--                            <a href="#">-->
<!--                                <i class="fa-solid fa-file-pdf"></i>-->
<!--                                دیتاشیت محصول-->
<!--                            </a>-->
<!--                            <a href="#">-->
<!--                                <i class="fa-solid fa-book"></i>-->
<!--                                راهنمای نصب-->
<!--                            </a>-->
<!--                            <a href="#">-->
<!--                                <i class="fa-solid fa-download"></i>-->
<!--                                آخرین Firmware-->
<!--                            </a>-->
<!--                        </div>-->
<!--                    </div>-->
<!--                </div>-->

            </div>

        </div>
    </div>
</section>


<!--==========================================
RELATED PRODUCTS
===========================================-->
<?php if (!empty($related)): ?>
    <section class="related-products">
        <div class="container">

            <div class="section-head">
                <div>
                    <small>ممکن است نیازتان باشد !</small>
                    <h2>محصولات مرتبط</h2>
                </div>
                <?php if (!empty($product['category_slug'])): ?>
                    <a href="<?= $baseAddress ?>products/<?= htmlspecialchars($product['category_slug']) ?>">
                        مشاهده همه
                        <i class="fa-solid fa-arrow-left-long"></i>
                    </a>
                <?php endif; ?>
            </div>

            <div class="row g-4">
                <?php foreach ($related as $rel): ?>
                    <div class="col-lg-3 col-md-6">
                        <article class="related-card">
                            <a href="<?= $baseAddress ?>product/<?= htmlspecialchars($rel['seo_slug']) ?>"
                               class="image">
                                <img
                                        src="cms/<?= htmlspecialchars($rel['thumbnail']) ?>"
                                        alt="<?= htmlspecialchars($rel['name']) ?>"
                                        loading="lazy">
                            </a>
                            <div class="content">
                                <small><?= htmlspecialchars($product['category_name'] ?? '') ?></small>
                                <h3>
                                    <a href="<?= $baseAddress ?>product/<?= htmlspecialchars($rel['seo_slug']) ?>">
                                        <?= htmlspecialchars($rel['name']) ?>
                                    </a>
                                </h3>
                                <?php if ($rel['price'] > 0): ?>
                                    <strong><?= number_format($rel['price']) ?> تومان</strong>
                                <?php else: ?>
                                    <strong class="price-on-request">تماس بگیرید</strong>
                                <?php endif; ?>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>

        </div>
    </section>
<?php endif; ?>


<?php if ($prevProduct || $nextProduct): ?>
    <section class="product-navigation">
        <div class="container">
            <div class="navigation-wrapper">

                <div>
                    <?php if ($prevProduct): ?>
                        <a href="<?= $baseAddress ?>product/<?= htmlspecialchars($prevProduct['seo_slug']) ?>">
                            <small>محصول قبلی</small>
                            <span><?= htmlspecialchars($prevProduct['name']) ?></span>
                        </a>
                    <?php else: ?>
                        <div class="nav-placeholder"></div>
                    <?php endif; ?>
                </div>

                <div>
                    <?php if ($nextProduct): ?>
                        <a href="<?= $baseAddress ?>product/<?= htmlspecialchars($nextProduct['seo_slug']) ?>">
                            <small>محصول بعدی</small>
                            <span><?= htmlspecialchars($nextProduct['name']) ?></span>
                        </a>
                    <?php else: ?>
                        <div class="nav-placeholder"></div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </section>
<?php endif; ?>


<?php require_once "inc/aside-product.php" ?>
<?php require_once "inc/footer.php" ?>

<script src="assets/js/jquery.js"></script>
<script src="assets/js/jquery.nice-select.min.js"></script>
<script src="assets/js/owl.carousel.min.js"></script>
<script src="assets/js/bootstrap.bundle.js"></script>
<script src="assets/js/main.js"></script>
<script src="assets/js/pages/product-single.js?v=<?= filemtime('assets/js/pages/product-single.js') ?>"></script>

</body>
</html>