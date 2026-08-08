<?php

$new_orders = [];
$new_orders_count = 0;

$orderStmt = $mysqli->prepare("
    SELECT
        `id`,
        `full_name`,
        `created_at`
    FROM `orders`
    WHERE `deleted` = 0
    ORDER BY `created_at` DESC
    LIMIT 8
");

if ($orderStmt) {
    $orderStmt->execute();

    $orderResult = $orderStmt->get_result();

    while ($row = $orderResult->fetch_assoc()) {
        $new_orders[] = $row;
    }

    $orderStmt->close();

    $new_orders_count = count($new_orders);
}


/* -------------------------------------------------------------------------
 | New Contact Messages
 * ------------------------------------------------------------------------- */

$new_msgs = [];
$new_msgs_count = 0;

$msgStmt = $mysqli->prepare("
    SELECT
        `id`,
        `fullname`,
        `subject`,
        `created_at`
    FROM `contact_messages`
    WHERE `seen` = 0
      AND `deleted` = 0
    ORDER BY `created_at` DESC
    LIMIT 8
");

if ($msgStmt) {
    $msgStmt->execute();

    $msgResult = $msgStmt->get_result();

    while ($row = $msgResult->fetch_assoc()) {
        $new_msgs[] = $row;
    }

    $msgStmt->close();

    $new_msgs_count = count($new_msgs);
}


/* -------------------------------------------------------------------------
 | Admin Notes
 |
 | Database structure:
 |
 | id
 | admin_id
 | note
 | deleted
 | created_at
 | updated_at
 |
 * ------------------------------------------------------------------------- */

$admin_notes = [];

$notesStmt = $mysqli->prepare("
    SELECT
        n.`id`,
        n.`admin_id`,
        n.`note`,
        n.`deleted`,
        n.`created_at`,
        n.`updated_at`,
        a.`username` AS author
    FROM `admin_notes` n
    LEFT JOIN `admins` a
        ON a.`id` = n.`admin_id`
    WHERE n.`deleted` = 0
    ORDER BY n.`created_at` DESC
    LIMIT 20
");

if ($notesStmt) {
    $notesStmt->execute();

    $notesResult = $notesStmt->get_result();

    while ($row = $notesResult->fetch_assoc()) {
        $admin_notes[] = $row;
    }

    $notesStmt->close();
}


/*
|--------------------------------------------------------------------------
| POST ACTIONS
|--------------------------------------------------------------------------
*/

if (
        $_SERVER['REQUEST_METHOD'] === 'POST' &&
        isset($_POST['action'])
) {

    /* ---------------------------------------------------------------------
     | Add Note
     * --------------------------------------------------------------------- */

    if ($_POST['action'] === 'add_note') {

        $note = trim($_POST['note'] ?? '');

        $author = isset($_SESSION['usr_id'])
                ? (int) $_SESSION['usr_id']
                : 0;

        if ($author > 0 && $note !== '') {

            $ins = $mysqli->prepare("
                INSERT INTO `admin_notes`
                (
                    `admin_id`,
                    `note`,
                    `deleted`
                )
                VALUES (?, ?, 0)
            ");

            if ($ins) {

                $ins->bind_param(
                        'is',
                        $author,
                        $note
                );

                $ins->execute();

                $ins->close();
            }
        }

        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }


    /* ---------------------------------------------------------------------
     | Delete Note
     |
     | Soft Delete
     * --------------------------------------------------------------------- */

    if ($_POST['action'] === 'delete_note') {

        $noteId = (int) ($_POST['note_id'] ?? 0);

        if ($noteId > 0) {

            $del = $mysqli->prepare("
                UPDATE `admin_notes`
                SET
                    `deleted` = 1,
                    `updated_at` = NOW()
                WHERE `id` = ?
                  AND `deleted` = 0
            ");

            if ($del) {

                $del->bind_param(
                        'i',
                        $noteId
                );

                $del->execute();

                $del->close();
            }
        }

        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

?>


<!-- =====================================================================
     SIDE HEADER
===================================================================== -->

<div class="nav-header">

    <a href="./" class="brand-logo">

        <img
                class="brand-title"
                src="images/logo.webp"
                alt="<?= htmlspecialchars(setting('name')) ?>"
        >

    </a>


    <div class="nav-control">

        <div class="hamburger">

            <span class="line"></span>
            <span class="line"></span>
            <span class="line"></span>

        </div>

    </div>

</div>


<!-- =====================================================================
     MAIN HEADER
===================================================================== -->

<div class="header">

    <div class="header-content">

        <nav
                class="navbar navbar-expand"
                style="height:100%;padding:0;"
        >

            <div
                    class="collapse navbar-collapse justify-content-between"
                    style="height:100%;"
            >


                <!-- =====================================================
                     LEFT
                ====================================================== -->

                <div
                        class="header-left d-flex align-items-center"
                        style="gap:12px;"
                >

                    <div class="hd-clock">

                        <!-- Clock Icon -->

                        <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="15"
                                height="15"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2"
                        >

                            <circle
                                    cx="12"
                                    cy="12"
                                    r="10"
                            />

                            <polyline
                                    points="12 6 12 12 16 14"
                            />

                        </svg>


                        <span id="hd-clock-time">
                            --:--:--
                        </span>


                        <span
                                id="hd-clock-date"
                                style="color:var(--hd-muted);font-size:12px;"
                        >
                            ---
                        </span>

                    </div>

                </div>


                <!-- =====================================================
                     RIGHT
                ====================================================== -->

                <ul
                        class="navbar-nav header-right d-flex align-items-center"
                        style="
                        flex-direction:row;
                        gap:8px;
                        margin:0;
                        padding:0;
                        list-style:none;
                    "
                >


                    <!-- =================================================
                         ORDERS
                    ================================================== -->

                    <li
                            class="nav-item"
                            style="position:relative;"
                    >

                        <a
                                href="javascript:void(0)"
                                class="hd-icon-btn"
                                id="hd-btn-orders"
                                title="سفارش‌های جدید"
                        >

                            <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                            >

                                <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-1.35 2.7A1 1 0 007 17h11M17 17a2 2 0 110 4 2 2 0 010-4zm-8 0a2 2 0 110 4 2 2 0 010-4z"
                                />

                            </svg>


                            <?php if ($new_orders_count > 0): ?>

                                <span class="hd-badge hd-badge-order">
                                    <?= $new_orders_count ?>
                                </span>

                            <?php endif; ?>

                        </a>


                        <!-- Orders Dropdown -->

                        <div
                                class="hd-dropdown"
                                id="hd-drop-orders"
                        >

                            <div class="hd-panel-head">

                                <h6>
                                    سفارش‌های جدید
                                </h6>

                                <span class="hd-count hd-count-order">
                                    <?= $new_orders_count ?> مورد
                                </span>

                            </div>


                            <ul class="hd-notif-list">

                                <?php if (empty($new_orders)): ?>

                                    <li class="hd-empty">

                                        <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                width="32"
                                                height="32"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                                stroke-width="1.2"
                                        >

                                            <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-1.35 2.7A1 1 0 007 17h11"
                                            />

                                        </svg>

                                        <br>

                                        سفارش جدیدی وجود ندارد

                                    </li>

                                <?php else: ?>

                                    <?php foreach ($new_orders as $order): ?>

                                        <li>

                                            <a
                                                    class="hd-notif-item"
                                                    href="order_view.php?id=<?= (int) $order['id'] ?>"
                                            >

                                                <span class="hd-notif-icon hd-notif-icon-order">

                                                    <i class="fa fa-shopping-cart"></i>

                                                </span>


                                                <div class="hd-notif-body">

                                                    <strong>
                                                        سفارش از
                                                        <?= htmlspecialchars($order['full_name']) ?>
                                                    </strong>

                                                    <small>
                                                        <?= jdate(
                                                                "d F Y H:i",
                                                                strtotime($order['created_at'])
                                                        ) ?>
                                                    </small>

                                                </div>

                                            </a>

                                        </li>

                                    <?php endforeach; ?>

                                <?php endif; ?>

                            </ul>


                            <div class="hd-panel-footer">

                                <a href="order_list.php">
                                    مشاهده همه سفارش‌ها &larr;
                                </a>

                            </div>

                        </div>

                    </li>


                    <!-- =================================================
                         MESSAGES
                    ================================================== -->

                    <li
                            class="nav-item"
                            style="position:relative;"
                    >

                        <a
                                href="javascript:void(0)"
                                class="hd-icon-btn"
                                id="hd-btn-msgs"
                                title="پیام‌های جدید"
                        >

                            <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                            >

                                <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                />

                            </svg>


                            <?php if ($new_msgs_count > 0): ?>

                                <span class="hd-badge hd-badge-msg">
                                    <?= $new_msgs_count ?>
                                </span>

                            <?php endif; ?>

                        </a>


                        <!-- Messages Dropdown -->

                        <div
                                class="hd-dropdown"
                                id="hd-drop-msgs"
                        >

                            <div class="hd-panel-head">

                                <h6>
                                    پیام‌های جدید
                                </h6>

                                <span class="hd-count hd-count-msg">
                                    <?= $new_msgs_count ?> مورد
                                </span>

                            </div>


                            <ul class="hd-notif-list">

                                <?php if (empty($new_msgs)): ?>

                                    <li class="hd-empty">

                                        <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                width="32"
                                                height="32"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                                stroke-width="1.2"
                                        >

                                            <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                            />

                                        </svg>

                                        <br>

                                        پیام جدیدی وجود ندارد

                                    </li>

                                <?php else: ?>

                                    <?php foreach ($new_msgs as $msg): ?>

                                        <li>

                                            <a
                                                    class="hd-notif-item"
                                                    href="messagesList.php?id=<?= (int) $msg['id'] ?>"
                                            >

                                                <span class="hd-notif-icon hd-notif-icon-msg">

                                                    <i class="fa fa-envelope"></i>

                                                </span>


                                                <div class="hd-notif-body">

                                                    <strong>

                                                        <?= htmlspecialchars($msg['fullname']) ?>

                                                        —

                                                        <?= htmlspecialchars($msg['subject']) ?>

                                                    </strong>


                                                    <small>

                                                        <?= jdate(
                                                                "d F Y H:i",
                                                                strtotime($msg['created_at'])
                                                        ) ?>

                                                    </small>

                                                </div>

                                            </a>

                                        </li>

                                    <?php endforeach; ?>

                                <?php endif; ?>

                            </ul>


                            <div class="hd-panel-footer">

                                <a href="messagesList.php">
                                    مشاهده همه پیام‌ها &larr;
                                </a>

                            </div>

                        </div>

                    </li>


                    <!-- =================================================
                         ADMIN NOTES
                    ================================================== -->

                    <li
                            class="nav-item"
                            style="position:relative;"
                    >

                        <a
                                href="javascript:void(0)"
                                class="hd-icon-btn"
                                id="hd-btn-notes"
                                title="یادداشت‌های ادمین"
                        >

                            <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                            >

                                <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                />

                            </svg>


                            <?php if (count($admin_notes) > 0): ?>

                                <span class="hd-badge hd-badge-accent">
                                    <?= count($admin_notes) ?>
                                </span>

                            <?php endif; ?>

                        </a>


                        <!-- Notes Dropdown -->

                        <div
                                class="hd-dropdown hd-notes-panel"
                                id="hd-drop-notes"
                        >

                            <div class="hd-panel-head">

                                <h6>
                                    یادداشت‌های ادمین
                                </h6>

                                <span class="hd-count hd-count-note">

                                    <?= count($admin_notes) ?>

                                    یادداشت

                                </span>

                            </div>


                            <!-- Notes List -->

                            <ul class="hd-notes-list">

                                <?php if (empty($admin_notes)): ?>

                                    <li class="hd-empty">

                                        <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                width="32"
                                                height="32"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                                stroke-width="1.2"
                                        >

                                            <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5"
                                            />

                                        </svg>

                                        <br>

                                        یادداشتی ثبت نشده

                                    </li>

                                <?php else: ?>

                                    <?php foreach ($admin_notes as $note): ?>

                                        <li class="hd-note-card">

                                            <!-- Note Content -->

                                            <div class="hd-note-content">

                                                <p>
                                                    <?= nl2br(
                                                            htmlspecialchars($note['note'])
                                                    ) ?>
                                                </p>

                                            </div>


                                            <!-- Note Meta -->

                                            <div class="hd-note-meta">

                                                <span>

                                                    <strong>
                                                        <?= htmlspecialchars(
                                                                $note['author'] ?? 'مدیر'
                                                        ) ?>
                                                    </strong>

                                                    ·

                                                    <?= jdate(
                                                            "d M Y H:i",
                                                            strtotime($note['created_at'])
                                                    ) ?>

                                                </span>


                                                <!-- Delete -->

                                                <div class="hd-note-actions">

                                                    <form
                                                            method="POST"
                                                            style="display:inline;"
                                                            onsubmit="return confirm('آیا از حذف این یادداشت مطمئن هستید؟');"
                                                    >

                                                        <input
                                                                type="hidden"
                                                                name="action"
                                                                value="delete_note"
                                                        >

                                                        <input
                                                                type="hidden"
                                                                name="note_id"
                                                                value="<?= (int) $note['id'] ?>"
                                                        >


                                                        <button
                                                                type="submit"
                                                                class="btn-del"
                                                                title="حذف یادداشت"
                                                        >
                                                            🗑
                                                        </button>

                                                    </form>

                                                </div>

                                            </div>

                                        </li>

                                    <?php endforeach; ?>

                                <?php endif; ?>

                            </ul>


                            <!-- =================================================
                                 ADD NOTE
                            ================================================== -->

                            <div class="hd-add-note">

                                <button
                                        type="button"
                                        class="hd-add-note-toggle"
                                        id="hd-note-toggle-btn"
                                >

                                    <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="13"
                                            height="13"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            stroke-width="2.5"
                                    >

                                        <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M12 4v16m8-8H4"
                                        />

                                    </svg>

                                    یادداشت جدید

                                </button>


                                <form
                                        method="POST"
                                        class="hd-note-form"
                                        id="hd-note-form"
                                >

                                    <input
                                            type="hidden"
                                            name="action"
                                            value="add_note"
                                    >


                                    <textarea
                                            name="note"
                                            placeholder="متن یادداشت..."
                                            required
                                    ></textarea>


                                    <button
                                            type="submit"
                                            class="hd-btn-submit"
                                    >
                                        ذخیره یادداشت
                                    </button>

                                </form>

                            </div>

                        </div>

                    </li>


                    <!-- =================================================
                         PROFILE
                    ================================================== -->

                    <li class="nav-item hd-profile-wrap">

                        <a
                                href="javascript:void(0)"
                                class="hd-profile-btn"
                                id="hd-btn-profile"
                        >

                            <img
                                    class="hd-profile-avatar"
                                    src="images/profile/noimage.png"
                                    alt=""
                            >


                            <span class="hd-profile-name">

                                <span>
                                    سلام،
                                </span>

                                <?= htmlspecialchars(
                                        $_SESSION['usr_username'] ?? 'مدیر'
                                ) ?>

                            </span>


                            <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="12"
                                    height="12"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2.5"
                                    style="
                                    color:var(--hd-muted);
                                    flex-shrink:0;
                                "
                            >

                                <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M19 9l-7 7-7-7"
                                />

                            </svg>

                        </a>


                        <!-- Profile Dropdown -->

                        <div
                                class="hd-profile-dropdown"
                                id="hd-drop-profile"
                        >

                            <div class="hd-profile-info">

                                <strong>

                                    <?= htmlspecialchars(
                                            $_SESSION['usr_username'] ?? 'مدیر'
                                    ) ?>

                                </strong>

                                <small>
                                    مدیر سیستم
                                </small>

                            </div>


                            <ul class="hd-profile-menu">

                                <!-- Profile -->

                                <li>

                                    <a href="app-profile.html">

                                        <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                                stroke-width="2"
                                        >

                                            <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                            />

                                        </svg>

                                        پروفایل

                                    </a>

                                </li>


                                <!-- Settings -->

                                <li>

                                    <a href="setting.php">

                                        <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                                stroke-width="2"
                                        >

                                            <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426.608-2.296.07-2.572-1.065z"
                                            />

                                            <circle
                                                    cx="12"
                                                    cy="12"
                                                    r="3"
                                            />

                                        </svg>

                                        تنظیمات

                                    </a>

                                </li>


                                <li class="divider"></li>


                                <!-- Logout -->

                                <li class="danger">

                                    <a href="logout.php">

                                        <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                                stroke-width="2"
                                        >

                                            <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                                            />

                                        </svg>

                                        خروج از سیستم

                                    </a>

                                </li>

                            </ul>

                        </div>

                    </li>

                </ul>

            </div>

        </nav>

    </div>

</div>


<!-- =====================================================================
     HEADER JAVASCRIPT
===================================================================== -->

