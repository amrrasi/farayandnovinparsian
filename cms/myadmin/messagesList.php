<?php
require_once "inc/check.php";
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    $stmt = $mysqli->prepare("UPDATE `contact_messages` SET `deleted` = 1 WHERE `id` = ?");
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        header("Location: messagesList.php?deleted=1");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fa">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo setting('name') ?></title>
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="images/favicon.jpg">
    <link href="vendor/jqvmap/css/jqvmap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="vendor/chartist/css/chartist.min.css">
    <!-- Vectormap -->
    <link href="vendor/jqvmap/css/jqvmap.min.css" rel="stylesheet">
    <link href="vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="vendor/owl-carousel/owl.carousel.css" rel="stylesheet">

    <style>
        /* ===== Message list — page-scoped styles ===== */
        .msg-toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            background: #fff;
            border-radius: 12px;
            padding: 14px 18px;
            margin-bottom: 18px;
            box-shadow: 0 1px 3px rgba(20, 40, 80, .06);
        }

        .msg-toolbar .msg-search {
            position: relative;
            flex: 1 1 260px;
            max-width: 360px;
        }

        .msg-toolbar .msg-search input {
            width: 100%;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 8px 36px 8px 12px;
            font-size: 13px;
            outline: none;
            transition: border-color .15s;
        }

        .msg-toolbar .msg-search input:focus {
            border-color: #6366f1;
        }

        .msg-toolbar .msg-search i {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 13px;
        }

        .msg-filters {
            display: flex;
            gap: 6px;
        }

        .msg-filters button {
            border: 1px solid #e5e7eb;
            background: #f9fafb;
            color: #4b5563;
            font-size: 12px;
            padding: 6px 14px;
            border-radius: 999px;
            cursor: pointer;
            transition: all .15s;
        }

        .msg-filters button.active {
            background: #4f46e5;
            border-color: #4f46e5;
            color: #fff;
        }

        .msg-count-badge {
            font-size: 11px;
            background: #eef2ff;
            color: #4f46e5;
            border-radius: 999px;
            padding: 2px 9px;
            margin-right: 6px;
        }

        .msg-list {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(20, 40, 80, .06);
            overflow: hidden;
        }

        .msg-item {
            position: relative;
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 18px;
            border-bottom: 1px solid #f1f2f4;
            transition: background .12s;
        }

        .msg-item:last-child {
            border-bottom: none;
        }

        .msg-item:hover {
            background: #f9fafb;
        }

        .msg-item.is-unread {
            background: #fef9f5;
        }

        .msg-item.is-unread:hover {
            background: #fdf2ea;
        }

        .msg-avatar {
            flex: 0 0 auto;
            width: 42px;
            height: 42px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: bold;
            color: #fff;
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            position: relative;
        }

        .msg-item.is-unread .msg-avatar::after {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #ef4444;
            border: 2px solid #fff;
        }

        .msg-main {
            flex: 1 1 auto;
            min-width: 0;
        }

        .msg-top-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 2px;
        }

        .msg-name {
            font-size: 14px;
            color: #111827;
            font-weight: 600;
        }

        .msg-item.is-unread .msg-name {
            font-weight: 700;
        }

        .msg-subject-badge {
            font-size: 11px;
            background: #f3f4f6;
            color: #4b5563;
            border-radius: 6px;
            padding: 1px 8px;
        }

        .msg-item.is-unread .msg-subject-badge {
            background: #eef2ff;
            color: #4f46e5;
        }

        .msg-attach-icon {
            font-size: 11px;
            color: #9ca3af;
        }

        .msg-snippet {
            font-size: 12.5px;
            color: #6b7280;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 100%;
        }

        .msg-meta {
            flex: 0 0 auto;
            text-align: left;
            font-size: 11px;
            color: #9ca3af;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 6px;
            margin-left: 8px;
        }

        .msg-meta .msg-date {
            white-space: nowrap;
        }

        .msg-meta .msg-contact {
            white-space: nowrap;
            color: #6b7280;
        }

        .msg-actions {
            flex: 0 0 auto;
            display: flex;
            gap: 6px;
        }

        .msg-action-btn {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #e5e7eb;
            color: #6b7280;
            background: #fff;
            text-decoration: none;
            transition: all .15s;
        }

        .msg-action-btn:hover {
            background: #4f46e5;
            border-color: #4f46e5;
            color: #fff;
        }

        .msg-action-btn.danger:hover {
            background: #ef4444;
            border-color: #ef4444;
        }

        /* ---- hover preview ---- */
        .msg-preview {
            display: none;
            position: absolute;
            z-index: 20;
            top: 100%;
            right: 18px;
            left: 18px;
            margin-top: -6px;
            background: #111827;
            color: #e5e7eb;
            font-size: 12.5px;
            line-height: 1.9;
            border-radius: 10px;
            padding: 14px 16px;
            box-shadow: 0 12px 28px rgba(17, 24, 39, .25);
            max-height: 160px;
            overflow-y: auto;
        }

        .msg-item:hover .msg-preview {
            display: block;
        }

        .msg-empty {
            text-align: center;
            padding: 60px 20px;
            color: #9ca3af;
        }

        .msg-empty i {
            font-size: 34px;
            display: block;
            margin-bottom: 10px;
            color: #d1d5db;
        }

        @media (max-width: 767px) {
            .msg-meta { display: none; }
            .msg-snippet { white-space: normal; }
        }
    </style>
</head>
<script language="JavaScript">
    function del_confirm() {
        return confirm('آیا برای حذف مطمئن هستید؟');
    }
</script>

<body>

<!--*******************
    Preloader start
********************-->
<div id="preloader">
    <div class="sk-three-bounce">
        <div class="sk-child sk-bounce1"></div>
        <div class="sk-child sk-bounce2"></div>
        <div class="sk-child sk-bounce3"></div>
    </div>
</div>
<!--*******************
    Preloader end
********************-->

<!--**********************************
    Main wrapper start
***********************************-->
<div id="main-wrapper">

    <?php require_once "inc/header.php" ?>

    <?php require_once "inc/aside.php" ?>

    <!--**********************************
        List start
    ***********************************-->
    <div class="content-body" style="min-height: 804px;">
        <div class="container-fluid">
            <div class="page-titles">
                <h4>پیام‌های کاربران</h4>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">مدیریت پیام‌ها</li>
                </ol>
            </div>

            <?php if (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
                <div class="alert alert-success">پیام با موفقیت حذف شد</div>
            <?php endif; ?>

            <?php
            $stmt = $mysqli->prepare("
                SELECT `id`, `fullname`, `mobile`, `email`, `subject`, `message`, `attachment_path`, `seen`, `created_at`
                FROM `contact_messages`
                WHERE `deleted` = 0
                ORDER BY `created_at` DESC
            ");
            $stmt->execute();
            $result = $stmt->get_result();
            $total_count = $result->num_rows;

            // count unread without consuming the result set twice
            $rows = [];
            $unread_count = 0;
            while ($row = $result->fetch_assoc()) {
                if ((int)$row['seen'] === 0) $unread_count++;
                $rows[] = $row;
            }
            ?>

            <!-- Toolbar -->
            <div class="msg-toolbar">
                <div class="msg-search">
                    <input type="text" id="msgSearchInput" placeholder="جستجو در نام، موضوع یا متن پیام...">
                    <i class="fa fa-search"></i>
                </div>
                <div class="msg-filters">
                    <button type="button" class="active" data-filter="all">
                        همه <span class="msg-count-badge"><?= $total_count ?></span>
                    </button>
                    <button type="button" data-filter="unread">
                        خوانده‌نشده <span class="msg-count-badge"><?= $unread_count ?></span>
                    </button>
                    <button type="button" data-filter="read">خوانده‌شده</button>
                </div>
            </div>

            <!-- List -->
            <div class="msg-list" id="msgList">
                <?php if (count($rows) > 0): ?>
                    <?php foreach ($rows as $row):
                        $is_unread = ((int)$row['seen'] === 0);
                        $initial   = mb_substr(trim($row['fullname']) !== '' ? $row['fullname'] : '?', 0, 1);
                        $email     = $row['email'] !== null && $row['email'] !== '' ? $row['email'] : '—';
                        $searchStr = mb_strtolower($row['fullname'] . ' ' . $row['subject'] . ' ' . $row['message']);
                        ?>
                        <div class="msg-item <?= $is_unread ? 'is-unread' : '' ?>"
                             data-status="<?= $is_unread ? 'unread' : 'read' ?>"
                             data-search="<?= htmlspecialchars($searchStr, ENT_QUOTES, 'UTF-8') ?>">

                            <div class="msg-avatar"><?= htmlspecialchars($initial) ?></div>

                            <div class="msg-main">
                                <div class="msg-top-row">
                                    <span class="msg-name"><?= htmlspecialchars($row['fullname']) ?></span>
                                    <span class="msg-subject-badge"><?= htmlspecialchars($row['subject']) ?></span>
                                    <?php if (!empty($row['attachment_path'])): ?>
                                        <i class="fa fa-paperclip msg-attach-icon" title="دارای پیوست"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="msg-snippet"><?= htmlspecialchars(mb_substr($row['message'], 0, 90)) ?></div>
                            </div>

                            <div class="msg-meta">
                                <span class="msg-date"><?= jdate("d F Y H:i", strtotime($row['created_at'])) ?></span>
                                <span class="msg-contact"><?= htmlspecialchars($email) ?> · <?= htmlspecialchars($row['mobile']) ?></span>
                            </div>

                            <div class="msg-actions">
                                <a href="messages.php?id=<?= $row['id'] ?>" class="msg-action-btn" title="مشاهده">
                                    <i class="fa fa-eye"></i>
                                </a>
                                <a href="messagesList.php?delete_id=<?= $row['id'] ?>"
                                   onclick="return del_confirm();"
                                   class="msg-action-btn danger" title="حذف">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </div>

                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="msg-empty">
                        <i class="fa fa-inbox"></i>
                        هیچ پیامی یافت نشد
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!--**********************************
        List end
    ***********************************-->

    <?php require_once "inc/footer.php" ?>

</div>

<!--**********************************
    Main wrapper end
***********************************-->

<!--**********************************
    Scripts
***********************************-->
<!-- Required vendors -->
<script src="vendor/global/global.min.js"></script>
<script src="vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
<script src="vendor/chart.js/Chart.bundle.min.js"></script>
<script src="js/custom.min.js"></script>
<script src="js/deznav-init.js"></script>
<script src="vendor/owl-carousel/owl.carousel.js"></script>

<!-- Chart piety plugin files -->
<script src="vendor/peity/jquery.peity.min.js"></script>

<!-- Apex Chart -->
<script src="vendor/apexchart/apexchart.js"></script>

<!-- Dashboard 1 -->
<script src="js/dashboard/dashboard-1.js"></script>

<script>
    function carouselReview() {
        jQuery('.testimonial-one').owlCarousel({
            loop: true,
            margin: 10,
            nav: false,
            center: true,
            dots: false,
            navText: ['<i class="fa fa-caret-left"></i>', '<i class="fa fa-caret-right"></i>'],
            responsive: {
                0: { items: 2 },
                400: { items: 3 },
                700: { items: 5 },
                991: { items: 6 },
                1200: { items: 4 },
                1600: { items: 5 }
            }
        })
    }

    jQuery(window).on('load', function () {
        setTimeout(function () {
            carouselReview();
        }, 1000);
    });

    // ---- message list: search + filter (client-side) ----
    (function () {
        var searchInput = document.getElementById('msgSearchInput');
        var filterBtns   = document.querySelectorAll('.msg-filters button');
        var items        = document.querySelectorAll('#msgList .msg-item');
        var currentFilter = 'all';

        function applyFilters() {
            var query = (searchInput.value || '').trim().toLowerCase();

            items.forEach(function (item) {
                var matchesSearch = query === '' || item.getAttribute('data-search').indexOf(query) !== -1;
                var matchesFilter = currentFilter === 'all' || item.getAttribute('data-status') === currentFilter;
                item.style.display = (matchesSearch && matchesFilter) ? '' : 'none';
            });
        }

        searchInput.addEventListener('input', applyFilters);

        filterBtns.forEach(function (btn) {
            btn.addEventListener('click', function () {
                filterBtns.forEach(function (b) { b.classList.remove('active'); });
                btn.classList.add('active');
                currentFilter = btn.getAttribute('data-filter');
                applyFilters();
            });
        });
    })();
</script>
</body>

</html>