<?php
require_once  'cms/myadmin/inc/config.php';
require_once  'cms/myadmin/inc/auth_helpers.php';
if (empty($_SESSION['user']['id'])) {
    header('Location: ../entry/');
    exit;
}

$ordersCountStmt = $pdo->prepare("SELECT COUNT(*) FROM `orders` WHERE `user_id` = :uid AND `deleted` = 0");
$ordersCountStmt->execute([':uid' => $_SESSION['user']['id']]);
$ordersCount = (int) $ordersCountStmt->fetchColumn();

$unreadMsgStmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM `contact_messages` cm
    WHERE cm.user_id  = :uid
      AND cm.deleted  = 0
      AND cm.seen     = 1
      AND EXISTS (
          SELECT 1 FROM `message_replies` mr
          WHERE mr.message_id  = cm.id
            AND mr.sender_type = 'admin'
            AND mr.id = (
                SELECT id FROM `message_replies`
                WHERE message_id = cm.id
                ORDER BY created_at DESC, id DESC
                LIMIT 1
            )
      )
");
$unreadMsgStmt->execute([':uid' => $_SESSION['user']['id']]);
$unreadMessages = (int) $unreadMsgStmt->fetchColumn();

$initials = profile_initials($_SESSION['user']['name']);

?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= htmlspecialchars(getCsrfToken(), ENT_QUOTES, 'UTF-8') ?>">
    <title><?= htmlspecialchars(setting('name'), ENT_QUOTES, 'UTF-8') ?> | حساب کاربری</title>
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
    <link rel="stylesheet" href="assets/css/pages/profile.css?v=<?= filemtime(__DIR__ . '/assets/css/pages/profile.css') ?>">
</head>
<body>

<?php require_once "inc/header.php" ?>

<div class="pf-shell container-site mt-5">


    <aside class="pf-side card-glass">

        <div class="pf-side-head">
            <div class="pf-avatar" aria-hidden="true">
                <?= htmlspecialchars($initials, ENT_QUOTES, 'UTF-8') ?>
            </div>
            <div class="pf-side-id">
                <strong class="pf-side-name">
                    <?= htmlspecialchars($_SESSION['user']['name'], ENT_QUOTES, 'UTF-8') ?>
                </strong>
                <span class="pf-side-mobile">
                    <?= htmlspecialchars($_SESSION['user']['mobile'], ENT_QUOTES, 'UTF-8') ?>
                </span>
            </div>
        </div>

        <nav class="pf-nav" role="tablist" aria-label="بخش‌های پروفایل">
            <span class="pf-nav-rail" aria-hidden="true"></span>

            <button class="pf-nav-item is-active" data-tab="overview" role="tab" aria-selected="true" type="button">
                <i class="fa-solid fa-gauge-high"></i>
                <span>نمای کلی</span>
            </button>

            <button class="pf-nav-item" data-tab="orders" role="tab" aria-selected="false" type="button">
                <i class="fa-solid fa-bag-shopping"></i>
                <span>سفارش‌ها</span>
                <?php if ($ordersCount > 0): ?>
                    <span class="pf-nav-count"><?= $ordersCount ?></span>
                <?php endif; ?>
            </button>

            <button class="pf-nav-item" data-tab="messages" role="tab" aria-selected="false" type="button">
                <i class="fa-solid fa-envelope"></i>
                <span>پشتیبانی</span>
                <?php if ($unreadMessages > 0): ?>
                    <span class="pf-nav-count pf-nav-count--alert"><?= $unreadMessages ?></span>
                <?php endif; ?>
            </button>

            <button class="pf-nav-item" data-tab="personal" role="tab" aria-selected="false" type="button">
                <i class="fa-solid fa-id-card"></i>
                <span>اطلاعات</span>
            </button>

            <button class="pf-nav-item" data-tab="security" role="tab" aria-selected="false" type="button">
                <i class="fa-solid fa-lock"></i>
                <span>امنیت</span>
            </button>
        </nav>

        <a href="ajax/auth/logout.php" class="pf-logout" title="خروج از حساب">
            <i class="fa-solid fa-arrow-right-from-bracket"></i>
            <span>خروج</span>
        </a>
    </aside>

    <main class="pf-main">
        <div id="pf-toast" class="pf-toast" role="status" aria-live="polite"></div>

        <div class="pf-panel card-glass" id="pf-panel">
            <div class="pf-panel-loading" id="pf-loading">
                <span class="pf-spinner"></span>
            </div>
            <div class="pf-panel-body" id="pf-panel-body"></div>
        </div>
    </main>

</div>

<?php require_once "inc/footer.php" ?>

<script src="assets/js/jquery.js"></script>
<script src="assets/js/jquery.nice-select.min.js"></script>
<script src="assets/js/owl.carousel.min.js"></script>
<script src="assets/js/bootstrap.bundle.js"></script>
<script src="assets/js/main.js"></script>
<script>
    window.PF_USER = {
        id:   <?= (int) $_SESSION['user']['id'] ?>,
        name: <?= json_encode($_SESSION['user']['name'], JSON_UNESCAPED_UNICODE) ?>
    };
    window.PF_CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';
</script>
<script src="assets/js/pages/profile.js"></script>
<script src="assets/js/pages/profile_messages.js"></script>
</body>
</html>