<?php
require_once "cms/myadmin/inc/config.php";

$slug = trim($_GET['slug'] ?? '', '/');

if (empty($slug)) {
    header('Location: ' . $baseAddress . 'blogs');
    exit;
}

$stmt = $pdo->prepare("
    SELECT *
    FROM page
    WHERE seo_slug = :slug
      AND active   = 1
      AND deleted  = 0
    LIMIT 1
");
$stmt->execute([':slug' => $slug]);
$blog = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$blog) {
    http_response_code(404);
    header('Location: ' . $baseAddress . '404');
    exit;
}

$pdo->prepare("UPDATE page SET visit = visit + 1 WHERE id = :id")
        ->execute([':id' => $blog['id']]);
$blog['visit']++;

function wordCountFa(string $html): int
{
    $text = html_entity_decode(strip_tags($html));
    $text = preg_replace('/\s+/u', ' ', trim($text));
    if ($text === '') return 0;
    return count(preg_split('/\s+/u', $text));
}

$words   = wordCountFa($blog['body']);
$minutes = max(1, (int) ceil($words / 220));

if ($words < 700)      $level = 'مبتدی';
elseif ($words < 1500) $level = 'متوسط';
else                   $level = 'حرفه‌ای';

$days = (int) floor((time() - strtotime($blog['created_at'])) / 86400);

if ($days <= 7)               { $badge = 'NEW';     $badgeClass = 'badge-new'; }
elseif ($blog['visit'] >= 1000) { $badge = 'HOT';   $badgeClass = 'badge-hot'; }
elseif ($words >= 1800)       { $badge = 'PRO';     $badgeClass = 'badge-pro'; }
else                          { $badge = 'ARTICLE'; $badgeClass = 'badge-read'; }

$relatedStmt = $pdo->prepare("
    SELECT id, namefull, seo_slug, thumb, abstract, visit, created_at, body
    FROM page
    WHERE active    = 1
      AND deleted   = 0
      AND id       != :id
      AND parent_id = :pid
    ORDER BY visit DESC
    LIMIT 3
");
$relatedStmt->execute([
        ':id'  => $blog['id'],
        ':pid' => $blog['parent_id'] ?? 0,
]);
$related = $relatedStmt->fetchAll(PDO::FETCH_ASSOC);

if (count($related) < 3) {
    $existingIds = array_merge([$blog['id']], array_column($related, 'id'));
    $placeholders = implode(',', array_fill(0, count($existingIds), '?'));
    $fallbackStmt = $pdo->prepare("
        SELECT id, namefull, seo_slug, thumb, abstract, visit, created_at, body
        FROM page
        WHERE active  = 1
          AND deleted = 0
          AND id NOT IN ($placeholders)
        ORDER BY visit DESC
        LIMIT " . (3 - count($related))
    );
    $fallbackStmt->execute(array_values($existingIds));
    $related = array_merge($related, $fallbackStmt->fetchAll(PDO::FETCH_ASSOC));
}

$pageUrl   = ($baseAddress . 'blog/' . htmlspecialchars($blog['seo_slug']));
$pageTitle = htmlspecialchars($blog['seo_title'] ?: $blog['namefull']);
$pageDesc  = htmlspecialchars($blog['seo_description'] ?: $blog['abstract']);
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= $pageTitle ?> | <?= htmlspecialchars(setting('name')) ?></title>
    <meta name="description" content="<?= $pageDesc ?>">
    <?php if (!empty($blog['seo_keywords'])): ?>
        <meta name="keywords" content="<?= htmlspecialchars($blog['seo_keywords']) ?>">
    <?php endif; ?>

    <meta property="og:type"        content="article">
    <meta property="og:title"       content="<?= $pageTitle ?>">
    <meta property="og:description" content="<?= $pageDesc ?>">
    <meta property="og:url"         content="<?= $pageUrl ?>">
    <meta property="og:image"       content="<?= htmlspecialchars($blog['thumb']) ?>">

    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:title"       content="<?= $pageTitle ?>">
    <meta name="twitter:description" content="<?= $pageDesc ?>">
    <meta name="twitter:image"       content="<?= htmlspecialchars($blog['thumb']) ?>">

    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "Article",
            "headline": <?= json_encode($blog['namefull'], JSON_UNESCAPED_UNICODE) ?>,
        "description": <?= json_encode($blog['abstract'], JSON_UNESCAPED_UNICODE) ?>,
        "image": <?= json_encode($blog['thumb'], JSON_UNESCAPED_UNICODE) ?>,
        "datePublished": "<?= date('c', strtotime($blog['created_at'])) ?>",
        "dateModified": "<?= date('c', strtotime($blog['updated_at'] ?? $blog['created_at'])) ?>",
        "url": "<?= $pageUrl ?>"
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
    <link rel="stylesheet" href="assets/css/pages/single-blog.css?v=<?= filemtime('assets/css/pages/single-blog.css') ?>">
</head>
<body class="single-blog-body">

<div class="top-reading-bar" id="topReadingBar">
    <div class="top-reading-fill" id="topReadingFill"></div>
</div>

<?php require_once "inc/header.php" ?>

<main class="single-blog-page">

    <section class="blog-breadcrumb">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb-list"
                    itemscope itemtype="https://schema.org/BreadcrumbList">

                    <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                        <a itemprop="item" href="<?= $baseAddress ?>">
                            <span itemprop="name">خانه</span>
                        </a>
                        <meta itemprop="position" content="1">
                    </li>

                    <li class="sep"><i class="fa-solid fa-chevron-left"></i></li>

                    <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                        <a itemprop="item" href="blogs/">
                            <span itemprop="name">وبلاگ</span>
                        </a>
                        <meta itemprop="position" content="2">
                    </li>

                    <li class="sep"><i class="fa-solid fa-chevron-left"></i></li>

                    <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                        <span itemprop="name" class="bc-current">
                            <?= htmlspecialchars($blog['namefull']) ?>
                        </span>
                        <meta itemprop="position" content="3">
                    </li>

                </ol>
            </nav>
        </div>
    </section>


    <header class="article-hero">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-9">

                    <div class="hero-content">

                        <span class="article-badge <?= $badgeClass ?>">
                            <?= $badge ?>
                        </span>

                        <h1><?= htmlspecialchars($blog['namefull']) ?></h1>

                        <p class="article-abstract">
                            <?= htmlspecialchars($blog['abstract']) ?>
                        </p>

                        <div class="article-meta">
                            <div>
                                <i class="fa-regular fa-calendar"></i>
                                <?= jdate('d F Y', strtotime($blog['created_at'])) ?>
                            </div>
                            <div>
                                <i class="fa-regular fa-eye"></i>
                                <?= number_format($blog['visit']) ?> بازدید
                            </div>
                            <div>
                                <i class="fa-regular fa-clock"></i>
                                حدود <?= $minutes ?> دقیقه مطالعه
                            </div>
                            <div>
                                <i class="fa-solid fa-layer-group"></i>
                                <?= $level ?>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </header>


    <section class="article-cover">
        <div class="container">
            <div class="cover-wrapper" id="coverParallax">
                <img
                        src="cms/<?= htmlspecialchars($blog['thumb']) ?>"
                        alt="<?= htmlspecialchars($blog['namefull']) ?>"
                        loading="eager"
                        id="coverImg">
                <div class="cover-overlay"></div>
            </div>
        </div>
    </section>


    <section class="article-body-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-9 col-lg-10">

                    <div class="toc-box" id="tocBox" style="display:none;">
                        <div class="toc-header">
                            <i class="fa-solid fa-list-ul"></i>
                            <span>فهرست مطالب</span>
                            <button class="toc-toggle" id="tocToggle" aria-label="بستن فهرست">
                                <i class="fa-solid fa-chevron-up"></i>
                            </button>
                        </div>
                        <nav class="toc-nav" id="tocNav"></nav>
                    </div>

                    <article class="article-body" id="articleBody">
                        <?= $blog['body'] ?>
                    </article>

                </div>
            </div>
        </div>
    </section>


    <?php if (!empty($related)): ?>
        <section class="related-articles">
            <div class="container">

                <div class="related-header">
                <span class="related-tag">
                    <i class="fa-solid fa-link"></i>
                    مقالات مرتبط
                </span>
                    <h2>بیشتر بخوانید</h2>
                </div>

                <div class="row g-4">
                    <?php foreach ($related as $rel):
                        $relWords   = wordCountFa($rel['body']);
                        $relMinutes = max(1, (int) ceil($relWords / 220));
                        $relDays    = (int) floor((time() - strtotime($rel['created_at'])) / 86400);

                        if ($relDays <= 7)            { $rBadge = 'NEW'; $rClass = 'badge-new'; }
                        elseif ($rel['visit'] >= 1000){ $rBadge = 'HOT'; $rClass = 'badge-hot'; }
                        elseif ($relWords >= 1800)    { $rBadge = 'PRO'; $rClass = 'badge-pro'; }
                        else                          { $rBadge = 'READ'; $rClass = 'badge-read'; }
                        ?>
                        <div class="col-lg-4 col-md-6">
                            <article class="related-card">
                                <a href="blog/<?= htmlspecialchars($rel['seo_slug']) ?>">
                                    <div class="related-image">
                                        <img
                                                src="cms/<?= htmlspecialchars($rel['thumb']) ?>"
                                                alt="<?= htmlspecialchars($rel['namefull']) ?>"
                                                loading="lazy">
                                        <span class="card-badge <?= $rClass ?>"><?= $rBadge ?></span>
                                    </div>
                                    <div class="related-content">
                                        <h3><?= htmlspecialchars($rel['namefull']) ?></h3>
                                        <div class="related-meta">
                                            <span><i class="fa-regular fa-clock"></i> <?= $relMinutes ?> دقیقه</span>
                                            <span><i class="fa-regular fa-eye"></i> <?= number_format($rel['visit']) ?></span>
                                        </div>
                                    </div>
                                </a>
                            </article>
                        </div>
                    <?php endforeach; ?>
                </div>

            </div>
        </section>
    <?php endif; ?>

</main>




<?php
require_once "inc/aside.php";

require_once "inc/footer.php"

?>

<script src="assets/js/jquery.js"></script>
<script src="assets/js/jquery.nice-select.min.js"></script>
<script src="assets/js/owl.carousel.min.js"></script>
<script src="assets/js/bootstrap.bundle.js"></script>
<script src="assets/js/main.js"></script>

<script>
    window.ARTICLE = {
        url:     <?= json_encode($pageUrl, JSON_UNESCAPED_UNICODE) ?>,
        title:   <?= json_encode($blog['namefull'], JSON_UNESCAPED_UNICODE) ?>,
        minutes: <?= $minutes ?>
    };
</script>

<script src="assets/js/pages/single-blog.js?v=<?= filemtime('assets/js/pages/single-blog.js') ?>"></script>

</body>
</html>