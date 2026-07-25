<?php
require_once "inc/check.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header("Location: messagesList.php");
    exit;
}

$stmt = $mysqli->prepare("
    SELECT `id`, `fullname`, `mobile`, `email`, `subject`, `message`, `attachment_path`, `seen`, `created_at`
    FROM `contact_messages`
    WHERE `id` = ? AND `deleted` = 0
    LIMIT 1
");
$stmt->bind_param("i", $id);
$stmt->execute();
$ticket = $stmt->get_result()->fetch_assoc();

if (!$ticket) {
    header("Location: messagesList.php");
    exit;
}

// mark as seen the moment it's opened
if ((int)$ticket['seen'] === 0) {
    $seenStmt = $mysqli->prepare("UPDATE `contact_messages` SET `seen` = 1 WHERE `id` = ?");
    $seenStmt->bind_param("i", $id);
    $seenStmt->execute();
}

// pull the thread
$repliesStmt = $mysqli->prepare("
    SELECT `id`, `sender_type`, `body`, `created_at`
    FROM `message_replies`
    WHERE `message_id` = ?
    ORDER BY `created_at` ASC
");
$repliesStmt->bind_param("i", $id);
$repliesStmt->execute();
$replies = $repliesStmt->get_result()->fetch_all(MYSQLI_ASSOC);

$initial = mb_substr(trim($ticket['fullname']) !== '' ? $ticket['fullname'] : '?', 0, 1);
?>
<!DOCTYPE html>
<html lang="fa">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo setting('name') ?></title>
    <link rel="icon" type="image/png" sizes="16x16" href="images/favicon.jpg">
    <link href="vendor/jqvmap/css/jqvmap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="vendor/chartist/css/chartist.min.css">
    <link href="vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="vendor/owl-carousel/owl.carousel.css" rel="stylesheet">

    <style>
        /* ===== Ticket thread — page-scoped styles ===== */
        .ticket-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            background: #fff;
            border-radius: 12px;
            padding: 20px 22px;
            margin-bottom: 18px;
            box-shadow: 0 1px 3px rgba(20, 40, 80, .06);
        }

        .ticket-header .th-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .ticket-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: bold;
            color: #fff;
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            flex: 0 0 auto;
        }

        .ticket-name {
            font-size: 16px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 2px;
        }

        .ticket-subject {
            font-size: 12px;
            background: #eef2ff;
            color: #4f46e5;
            border-radius: 6px;
            padding: 2px 9px;
            display: inline-block;
        }

        .ticket-contact-line {
            font-size: 12px;
            color: #6b7280;
            margin-top: 6px;
        }

        .ticket-contact-line span + span::before {
            content: '·';
            margin: 0 6px;
            color: #d1d5db;
        }

        .ticket-meta {
            text-align: left;
            font-size: 11px;
            color: #9ca3af;
        }

        .ticket-actions {
            display: flex;
            gap: 8px;
            margin-top: 10px;
        }

        .ticket-actions a {
            font-size: 12px;
            border-radius: 8px;
            padding: 6px 14px;
            text-decoration: none;
            border: 1px solid #e5e7eb;
            color: #4b5563;
            transition: all .15s;
        }

        .ticket-actions a:hover {
            background: #f3f4f6;
        }

        .ticket-actions a.danger {
            color: #ef4444;
            border-color: #fecaca;
        }

        .ticket-actions a.danger:hover {
            background: #fef2f2;
        }

        .ticket-attachment {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: #4f46e5;
            background: #eef2ff;
            border-radius: 8px;
            padding: 6px 12px;
            text-decoration: none;
            margin-top: 10px;
        }

        .ticket-attachment:hover {
            background: #e0e7ff;
            color: #4f46e5;
        }

        /* ---- thread / chat ---- */
        .thread-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(20, 40, 80, .06);
            display: flex;
            flex-direction: column;
        }

        .thread-body {
            padding: 24px 22px;
            display: flex;
            flex-direction: column;
            gap: 18px;
            max-height: 520px;
            overflow-y: auto;
        }

        .bubble-row {
            display: flex;
            gap: 10px;
            max-width: 78%;
        }

        .bubble-row.from-user {
            align-self: flex-end;
            flex-direction: row;
        }

        .bubble-row.from-admin {
            align-self: flex-start;
            flex-direction: row-reverse;
        }

        .bubble-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            flex: 0 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
            color: #fff;
        }

        .from-user .bubble-avatar {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
        }

        .from-admin .bubble-avatar {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .bubble {
            border-radius: 14px;
            padding: 12px 15px;
            font-size: 13px;
            line-height: 1.9;
        }

        .from-user .bubble {
            background: #f3f4f6;
            color: #1f2937;
            border-top-right-radius: 4px;
        }

        .from-admin .bubble {
            background: #4f46e5;
            color: #fff;
            border-top-left-radius: 4px;
        }

        .bubble-time {
            font-size: 10px;
            color: #000000;
            margin-top: 5px;
        }

        .from-admin .bubble-time {
            color: #000000 !important;
        }

        .thread-reply-box {
            border-top: 1px solid #f1f2f4;
            padding: 16px 22px;
            background: #fafafa;
            border-radius: 0 0 12px 12px;
        }

        .thread-reply-box textarea {
            width: 100%;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 13px;
            resize: vertical;
            min-height: 70px;
            outline: none;
            font-family: inherit;
        }

        .thread-reply-box textarea:focus {
            border-color: #4f46e5;
        }

        .thread-reply-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 10px;
        }

        .thread-reply-actions button {
            background: #4f46e5;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 8px 20px;
            font-size: 13px;
            cursor: pointer;
            transition: background .15s;
        }

        .thread-reply-actions button:hover {
            background: #4338ca;
        }

        .thread-empty {
            text-align: center;
            color: #9ca3af;
            font-size: 12.5px;
            padding: 8px 0 4px 0;
        }
    </style>
</head>
<script language="JavaScript">
    function del_confirm() {
        return confirm('آیا برای حذف مطمئن هستید؟');
    }
</script>

<body>

<div id="preloader">
    <div class="sk-three-bounce">
        <div class="sk-child sk-bounce1"></div>
        <div class="sk-child sk-bounce2"></div>
        <div class="sk-child sk-bounce3"></div>
    </div>
</div>

<div id="main-wrapper">

    <?php require_once "inc/header.php" ?>
    <?php require_once "inc/aside.php" ?>

    <div class="content-body" style="min-height: 804px;">
        <div class="container-fluid">
            <div class="page-titles">
                <h4>مشاهده تیکت</h4>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="messagesList.php">مدیریت پیام‌ها</a></li>
                    <li class="breadcrumb-item">تیکت #<?= $ticket['id'] ?></li>
                </ol>
            </div>

            <?php if (isset($_GET['sent']) && $_GET['sent'] == 1): ?>
                <div class="alert alert-success">پاسخ شما با موفقیت ثبت شد</div>
            <?php endif; ?>

            <!-- Ticket header -->
            <div class="ticket-header">
                <div class="th-left">
                    <div class="ticket-avatar"><?= htmlspecialchars($initial) ?></div>
                    <div>
                        <div class="ticket-name"><?= htmlspecialchars($ticket['fullname']) ?></div>
                        <span class="ticket-subject"><?= htmlspecialchars($ticket['subject']) ?></span>
                        <div class="ticket-contact-line">
                            <span><?= htmlspecialchars($ticket['mobile']) ?></span>
                            <?php if (!empty($ticket['email'])): ?>
                                <span><?= htmlspecialchars($ticket['email']) ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($ticket['attachment_path'])): ?>
                            <a class="ticket-attachment" href="../../<?= htmlspecialchars($ticket['attachment_path']) ?>" target="_blank">
                                <i class="fa fa-paperclip"></i> مشاهده پیوست
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div>
                    <div class="ticket-meta"><?= jdate("d F Y H:i", strtotime($ticket['created_at'])) ?></div>
                    <div class="ticket-actions">
                        <a href="messagesList.php"><i class="fa fa-arrow-right"></i> بازگشت</a>
                        <a class="danger" href="messagesList.php?delete_id=<?= $ticket['id'] ?>" onclick="return del_confirm();">
                            <i class="fa fa-trash"></i> حذف
                        </a>
                    </div>
                </div>
            </div>

            <!-- Thread -->
            <div class="thread-card">
                <div class="thread-body" id="threadBody">

                    <div class="bubble-row from-user">
                        <div class="bubble-avatar"><?= htmlspecialchars($initial) ?></div>
                        <div>
                            <div class="bubble"><?= nl2br(htmlspecialchars($ticket['message'])) ?></div>
                            <div class="bubble-time"><?= jdate("d F Y H:i", strtotime($ticket['created_at'])) ?></div>
                        </div>
                    </div>

                    <?php if (empty($replies)): ?>
                        <div class="thread-empty">هنوز پاسخی ثبت نشده است</div>
                    <?php else: ?>
                        <?php foreach ($replies as $reply): ?>
                            <?php $fromAdmin = ($reply['sender_type'] === 'admin'); ?>
                            <div class="bubble-row <?= $fromAdmin ? 'from-admin' : 'from-user' ?>">
                                <div class="bubble-avatar"><?= $fromAdmin ? 'پ' : htmlspecialchars($initial) ?></div>
                                <div>
                                    <div class="bubble"><?= nl2br(htmlspecialchars($reply['body'])) ?></div>
                                    <div class="bubble-time"><?= jdate("d F Y H:i", strtotime($reply['created_at'])) ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                </div>

                <!-- reply box -->
                <form class="thread-reply-box" method="post" action="message_reply_process.php">
                    <input type="hidden" name="message_id" value="<?= $ticket['id'] ?>">
                    <textarea name="body" placeholder="پاسخ خود را بنویسید..." required></textarea>
                    <div class="thread-reply-actions">
                        <button type="submit"><i class="fa fa-paper-plane"></i> ارسال پاسخ</button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <?php require_once "inc/footer.php" ?>

</div>

<script src="vendor/global/global.min.js"></script>
<script src="vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
<script src="vendor/chart.js/Chart.bundle.min.js"></script>
<script src="js/custom.min.js"></script>
<script src="js/deznav-init.js"></script>
<script src="vendor/owl-carousel/owl.carousel.js"></script>
<script src="vendor/peity/jquery.peity.min.js"></script>
<script src="vendor/apexchart/apexchart.js"></script>
<script src="js/dashboard/dashboard-1.js"></script>

<script>
    // auto-scroll thread to the latest message on load
    document.addEventListener('DOMContentLoaded', function () {
        var body = document.getElementById('threadBody');
        body.scrollTop = body.scrollHeight;
    });
</script>
</body>

</html>