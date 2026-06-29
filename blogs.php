<?php
require_once "cms/myadmin/inc/config.php";

// Featured: most-visited active, non-deleted article
$featuredStmt = $pdo->prepare("
    SELECT *
    FROM page
    WHERE active = 1 AND deleted = 0
    ORDER BY visit DESC
    LIMIT 1
");
$featuredStmt->execute();
$featured = $featuredStmt->fetch(PDO::FETCH_ASSOC);

// All active blogs (excluding the featured one), ordered by myorder then date
$blogsStmt = $pdo->prepare("
    SELECT *
    FROM page
    WHERE active = 1
      AND deleted = 0
      AND id != :fid
    ORDER BY myorder ASC, created_at DESC
");
$blogsStmt->execute([':fid' => $featured['id'] ?? 0]);
$blogs = $blogsStmt->fetchAll(PDO::FETCH_ASSOC);

// Stats
$totalBlogs  = $pdo->query("SELECT COUNT(*) FROM page WHERE active=1 AND deleted=0")->fetchColumn();
$totalVisits = $pdo->query("SELECT COALESCE(SUM(visit),0) FROM page WHERE active=1 AND deleted=0")->fetchColumn();
$todayBlogs  = $pdo->query("SELECT COUNT(*) FROM page WHERE active=1 AND deleted=0 AND DATE(created_at)=CURDATE()")->fetchColumn();

// ─────────────────────────────────────────
//  BADGE / LEVEL HELPER
// ─────────────────────────────────────────
function wordCountFa($text)
{
    $text = strip_tags($text);

    $text = html_entity_decode($text);

    $text = preg_replace('/\s+/u', ' ', trim($text));

    if ($text === '') {
        return 0;
    }

    return count(preg_split('/\s+/u', $text));
}
function getBadge(array $row): array {
    $words = wordCountFa($row['body']);
    $days  = floor((time() - strtotime($row['created_at'])) / 86400);

    if ($days <= 7) {
        $badge = 'NEW'; $class = 'badge-new';
    } elseif ($row['visit'] >= 1000) {
        $badge = 'HOT'; $class = 'badge-hot';
    } elseif ($words >= 1800) {
        $badge = 'PRO'; $class = 'badge-pro';
    } else {
        $badge = 'READ'; $class = 'badge-read';
    }

    if ($words < 700)       $level = 'مبتدی';
    elseif ($words < 1500)  $level = 'متوسط';
    else                    $level = 'حرفه‌ای';

    $minutes = max(1, ceil($words / 220));

    return compact('badge', 'class', 'level', 'minutes', 'words');
}
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars(setting('name')) ?> | وبلاگ</title>
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
    <link rel="stylesheet" href="assets/css/pages/blog.css?v=<?= filemtime('assets/css/pages/blog.css') ?>">
</head>
<body>

<?php require_once "inc/header.php" ?>

<main class="blog-page">

    <!-- ══════════════════════════════════
         HERO
    ══════════════════════════════════ -->
    <section class="blog-hero">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-9 text-center">

                    <span class="hero-mini-title">
                        <i class="fa-solid fa-book-open-reader"></i>
                        <?= htmlspecialchars(setting('name')) ?>
                    </span>

                    <h1>مقالات تخصصی، آموزش‌ها و تجربیات فنی</h1>

                    <p>
                        جدیدترین آموزش‌های تخصصی، راهکارهای مهندسی، پروژه‌های اجرایی
                        و مقالات آموزشی تیم فرآیند نوین را مطالعه کنید.
                    </p>

                </div>
            </div>
        </div>
    </section>
    
    <!-- ══════════════════════════════════
         SEARCH + SORT
    ══════════════════════════════════ -->
    <section class="blog-search">
        <div class="container">
            <div class="blog-search-box">
                <div class="row align-items-center g-3">

                    <div class="col-lg-8">
                        <div class="search-input">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input
                                    id="blogSearch"
                                    type="search"
                                    placeholder="عنوان مقاله مورد نظر را جستجو کنید ..."
                                    autocomplete="off">
                            <span class="search-clear" id="searchClear" aria-label="پاک کردن">
                                <i class="fa-solid fa-xmark"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ══════════════════════════════════
         BLOG GRID
    ══════════════════════════════════ -->
    <section class="blog-grid">
        <div class="container">

            <div id="blogGrid" class="row g-4">

                <?php
                // Encode all data as JSON for JS sorting/filtering
                $blogsJson = [];

                foreach ($blogs as $i => $row):
                    $b       = getBadge($row);
                    $isWide  = (($i + 1) % 7 === 0); // every 7th card spans full row

                    // Collect data for JS
                    $blogsJson[] = [
                            'id'         => $row['id'],
                            'title'      => $row['namefull'],
                            'slug'       => $row['seo_slug'],
                            'thumb'      => $row['thumb'],
                            'abstract'   => mb_strimwidth(strip_tags($row['abstract']), 0, 150, '...'),
                            'visit'      => (int)$row['visit'],
                            'myorder'    => (int)$row['myorder'],
                            'created_at' => $row['created_at'],
                            'minutes'    => $b['minutes'],
                            'level'      => $b['level'],
                            'badge'      => $b['badge'],
                            'badgeClass' => $b['class'],
                            'date'       => jdate('d F Y', strtotime($row['created_at'])),
                            'wide'       => $isWide,
                    ];
                    ?>

                    <div class="blog-col col-lg-<?= $isWide ? '12' : '4' ?> col-md-6"
                         data-title="<?= htmlspecialchars($row['namefull']) ?>"
                         data-visit="<?= (int)$row['visit'] ?>"
                         data-order="<?= (int)$row['myorder'] ?>"
                         data-date="<?= $row['created_at'] ?>">

                        <article class="blog-card">
                            <a href="blog/<?= htmlspecialchars($row['seo_slug']) ?>">

                                <div class="blog-image">
                                    <img
                                            src="cms/<?= htmlspecialchars($row['thumb']) ?>"
                                            alt="<?= htmlspecialchars($row['namefull']) ?>"
                                            loading="lazy">

                                    <span class="card-badge <?= $b['class'] ?>"><?= $b['badge'] ?></span>

                                    <div class="image-preview">
                                        <div><i class="fa-regular fa-clock"></i> <?= $b['minutes'] ?> دقیقه</div>
                                        <div><i class="fa-regular fa-eye"></i> <?= number_format($row['visit']) ?></div>
                                        <div><i class="fa-solid fa-layer-group"></i> <?= $b['level'] ?></div>
                                        <div><i class="fa-regular fa-calendar"></i> <?= jdate('d F Y', strtotime($row['created_at'])) ?></div>
                                    </div>
                                </div>

                                <div class="blog-content">

                                    <h3><?= htmlspecialchars($row['namefull']) ?></h3>

                                    <p><?= htmlspecialchars(mb_strimwidth(strip_tags($row['abstract']), 0, 150, '...')) ?></p>

                                    <div class="reading-progress">
                                        <span style="width:<?= min($b['minutes'] * 8, 100) ?>%"></span>
                                    </div>

                                    <div class="blog-footer">
                                        <span><i class="fa-regular fa-eye"></i> <?= number_format($row['visit']) ?></span>
                                        <span><?= $b['level'] ?></span>
                                    </div>

                                </div>

                            </a>
                        </article>

                    </div>

                <?php endforeach; ?>

            </div><!-- #blogGrid -->

            <!-- Empty state -->
            <div id="blogEmpty" class="blog-empty" style="display:none;">
                <i class="fa-solid fa-magnifying-glass"></i>
                <p>مقاله‌ای با این عنوان یافت نشد.</p>
            </div>

        </div>
    </section>


    <!-- ══════════════════════════════════
         SPOTLIGHT (featured article)
    ══════════════════════════════════ -->
    <?php if (!empty($featured)): ?>
        <?php $f = getBadge($featured); ?>

        <section class="spotlight-blog">
            <div class="container">

                <article class="spotlight-card">
                    <div class="row g-0 align-items-center">

                        <div class="col-lg-6">
                            <div class="spotlight-image">
                                <img
                                        src="cms/<?= htmlspecialchars($featured['thumb']) ?>"
                                        alt="<?= htmlspecialchars($featured['namefull']) ?>"
                                        loading="lazy">
                                <span class="spotlight-badge <?= $f['class'] ?>"><?= $f['badge'] ?></span>
                                <div class="spotlight-overlay"></div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="spotlight-content">

                                <span class="spotlight-mini-title">
                                    <i class="fa-solid fa-fire"></i>
                                    مقاله منتخب
                                </span>

                                <h2><?= htmlspecialchars($featured['namefull']) ?></h2>

                                <p><?= htmlspecialchars($featured['abstract']) ?></p>

                                <div class="spotlight-info">
                                    <div>
                                        <i class="fa-regular fa-clock"></i>
                                        <?= $f['minutes'] ?> دقیقه مطالعه
                                    </div>
                                    <div>
                                        <i class="fa-solid fa-layer-group"></i>
                                        <?= $f['level'] ?>
                                    </div>
                                    <div>
                                        <i class="fa-regular fa-eye"></i>
                                        <?= number_format($featured['visit']) ?>
                                    </div>
                                </div>

                                <div class="reading-progress">
                                    <span style="width:<?= min($f['minutes'] * 8, 100) ?>%"></span>
                                </div>

                                <div class="spotlight-actions">
                                    <a href="blog/<?= htmlspecialchars($featured['seo_slug']) ?>" class="spotlight-btn">
                                        مطالعه مقاله
                                        <i class="fa-solid fa-arrow-left"></i>
                                    </a>
                                </div>

                            </div>
                        </div>

                    </div>
                </article>

            </div>
        </section>
    <?php endif; ?>




</main>

<?php require_once "inc/footer.php" ?>

<script src="assets/js/jquery.js"></script>
<script src="assets/js/jquery.nice-select.min.js"></script>
<script src="assets/js/owl.carousel.min.js"></script>
<script src="assets/js/bootstrap.bundle.js"></script>
<script src="assets/js/main.js"></script>
<script>
    window.BLOG_DATA = <?= json_encode($blogsJson, JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="assets/js/pages/blog.js?v=<?= filemtime('assets/js/pages/blog.js') ?>"></script>

</body>
</html>