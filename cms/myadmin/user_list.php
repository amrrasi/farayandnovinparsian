<?php
require_once "inc/check.php";

$error   = false;
$errText = "";

/* ═══════════════════════════════════════════════════
 | حذف کاربر (soft-delete)
 * ══════════════════════════════════════════════════*/
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id   = (int) $_GET['delete'];
    $stmt = $mysqli->prepare("UPDATE user SET deleted=1 WHERE id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: user_list.php?deleted=1");
        exit;
    }
    $error   = true;
    $errText = "خطا در حذف کاربر";
}

/* ═══════════════════════════════════════════════════
 | فیلترها
 * ══════════════════════════════════════════════════*/
$search = trim($_GET['search'] ?? '');
$status = trim($_GET['status'] ?? ''); // '', 'verified', 'unverified', 'online'

/* ═══════════════════════════════════════════════════
 | آمار کارت‌های بالا
 * ══════════════════════════════════════════════════*/
$stats             = [];
$stats['all']      = (int) $mysqli->query("SELECT COUNT(*) c FROM user WHERE deleted=0")->fetch_assoc()['c'];
$stats['verified'] = (int) $mysqli->query("SELECT COUNT(*) c FROM user WHERE deleted=0 AND mobile_verified=1")->fetch_assoc()['c'];
$stats['online']   = (int) $mysqli->query("SELECT COUNT(*) c FROM user WHERE deleted=0 AND is_login=1")->fetch_assoc()['c'];
$stats['new30']    = (int) $mysqli->query("SELECT COUNT(*) c FROM user WHERE deleted=0 AND created_at >= (NOW() - INTERVAL 30 DAY)")->fetch_assoc()['c'];
$stats['withOrder']= (int) $mysqli->query("
    SELECT COUNT(DISTINCT u.id) c
    FROM user u
    INNER JOIN orders o ON o.user_id = u.id AND o.deleted = 0
    WHERE u.deleted = 0
")->fetch_assoc()['c'];
$stats['messages'] = (int) $mysqli->query("SELECT COUNT(*) c FROM contact_messages WHERE deleted=0")->fetch_assoc()['c'];

/* ═══════════════════════════════════════════════════
 | Query اصلی با فیلتر
 * ══════════════════════════════════════════════════*/
$sql = "
    SELECT u.*,
           (SELECT COUNT(*) FROM orders o WHERE o.user_id = u.id AND o.deleted = 0) AS orders_count,
           (SELECT COALESCE(SUM(COALESCE(o.quoted_total, o.grand_total)),0)
              FROM orders o WHERE o.user_id = u.id AND o.deleted = 0
              AND o.order_status_id IN (4,5))                                       AS orders_total
    FROM user u
    WHERE u.deleted = 0
";
$params = [];
$types  = "";

if ($search !== '') {
    $sql   .= " AND (u.name LIKE ? OR u.mobile LIKE ? OR u.email LIKE ? OR u.id LIKE ?)";
    $like   = "%{$search}%";
    array_push($params, $like, $like, $like, $like);
    $types .= "ssss";
}
if ($status === 'verified') {
    $sql .= " AND u.mobile_verified = 1";
} elseif ($status === 'unverified') {
    $sql .= " AND u.mobile_verified = 0";
} elseif ($status === 'online') {
    $sql .= " AND u.is_login = 1";
}
$sql .= " ORDER BY u.created_at DESC";

$stmt = $mysqli->prepare($sql);
if (count($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

/* ═══════════════════════════════════════════════════
 | badge های وضعیت
 * ══════════════════════════════════════════════════*/
function verifiedBadge(int $v): string
{
    return $v
        ? '<span class="badge badge-success">تایید شده</span>'
        : '<span class="badge badge-warning">تایید نشده</span>';
}
function sessionBadge(int $isLogin): string
{
    return $isLogin
        ? '<span class="badge badge-primary"><i class="fa fa-circle" style="font-size:.5rem"></i> آنلاین</span>'
        : '<span class="badge badge-secondary">آفلاین</span>';
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>مدیریت کاربران — <?= setting('name') ?></title>
    <link rel="icon" type="image/png" href="images/favicon.jpg">
    <link href="vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
    <link href="assets/css/fontawesome.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <style>

        .ord-stat-card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,.07);
            transition: transform .2s, box-shadow .2s;
        }
        .ord-stat-card:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,.12); }
        .ord-stat-card .card-body { padding: 20px 18px; }
        .ord-stat-icon {
            width: 48px; height: 48px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem; color: #fff; flex-shrink: 0;
        }
        .ord-stat-num  { font-size: 1.65rem; font-weight: 700; line-height: 1.1; }
        .ord-stat-lbl  { font-size: .78rem; color: #8a8fa3; margin-top: 3px; }

        /* modal — user detail */
        #userModal .modal-header  { background: #f8f9fa; border-bottom: 1px solid #e9ecef; }
        #userModal .modal-title   { font-weight: 700; }
        #userModal .detail-label  { font-size: .75rem; color: #8a8fa3; margin-bottom: 2px; }
        #userModal .detail-value  { font-size: .92rem; font-weight: 600; margin-bottom: 14px; }
        #userModal .section-head  {
            font-size: .78rem; font-weight: 700; text-transform: uppercase;
            color: #8a8fa3; letter-spacing: .06em;
            border-bottom: 1px dashed #dee2e6; padding-bottom: 6px; margin-bottom: 14px;
        }

        .user-avatar {
            width: 42px; height: 42px; border-radius: 50%;
            background: linear-gradient(135deg,#6366f1,#8b5cf6);
            color:#fff; display:flex; align-items:center; justify-content:center;
            font-weight:700; font-size:1rem; flex-shrink:0;
        }

        /* tighter table */
        .table td, .table th { vertical-align: middle !important; }
        .action-btns .btn  { margin: 1px; }

        /* filter bar */
        .filter-bar { background: #f8f9fa; border-radius: 10px; padding: 16px 18px; margin-bottom: 20px; }
    </style>
</head>
<body>

<div id="preloader">
    <div class="sk-three-bounce">
        <div class="sk-child sk-bounce1"></div>
        <div class="sk-child sk-bounce2"></div>
        <div class="sk-child sk-bounce3"></div>
    </div>
</div>

<div id="main-wrapper">

    <?php require_once "inc/header.php"; ?>
    <?php require_once "inc/aside.php"; ?>

    <div class="content-body">
        <div class="container-fluid">

            <!-- breadcrumb -->
            <div class="page-titles">
                <h4>مدیریت کاربران</h4>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item active">کاربران</li>
                </ol>
            </div>

            <!-- alerts -->
            <?php if (isset($_GET['deleted'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fa fa-check-circle ml-1"></i>کاربر با موفقیت حذف شد.
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fa fa-exclamation-circle ml-1"></i><?= $errText ?>
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            <?php endif; ?>
            <?php if (isset($_GET['updated'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fa fa-check-circle ml-1"></i>اطلاعات کاربر با موفقیت به‌روزرسانی شد.
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            <?php endif; ?>

            <!-- ══════════ STAT CARDS ══════════ -->
            <div class="row mb-4">

                <!-- کل -->
                <div class="col-xl-4 col-lg-4 col-sm-6 mb-3">
                    <div class="card ord-stat-card h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="ord-stat-icon" style="background:#6366f1">
                                <i class="fa fa-users"></i>
                            </div>
                            <div>
                                <div class="ord-stat-num"><?= number_format($stats['all']) ?></div>
                                <div class="ord-stat-lbl">کل کاربران</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- تایید شده -->
                <div class="col-xl-4 col-lg-4 col-sm-6 mb-3">
                    <div class="card ord-stat-card h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="ord-stat-icon" style="background:#10b981">
                                <i class="fa fa-shield-alt"></i>
                            </div>
                            <div>
                                <div class="ord-stat-num"><?= number_format($stats['verified']) ?></div>
                                <div class="ord-stat-lbl">موبایل تایید شده</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- نشست فعال -->
                <div class="col-xl-4 col-lg-4 col-sm-6 mb-3">
                    <div class="card ord-stat-card h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="ord-stat-icon" style="background:#0ea5e9">
                                <i class="fa fa-signal"></i>
                            </div>
                            <div>
                                <div class="ord-stat-num"><?= number_format($stats['online']) ?></div>
                                <div class="ord-stat-lbl">نشست فعال (آنلاین)</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- جدید -->
                <div class="col-xl-4 col-lg-4 col-sm-6 mb-3">
                    <div class="card ord-stat-card h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="ord-stat-icon" style="background:#f59e0b">
                                <i class="fa fa-user-plus"></i>
                            </div>
                            <div>
                                <div class="ord-stat-num"><?= number_format($stats['new30']) ?></div>
                                <div class="ord-stat-lbl">کاربران جدید (۳۰ روز)</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- دارای سفارش -->
                <div class="col-xl-4 col-lg-4 col-sm-6 mb-3">
                    <div class="card ord-stat-card h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="ord-stat-icon" style="background:#8b5cf6">
                                <i class="fa fa-shopping-cart"></i>
                            </div>
                            <div>
                                <div class="ord-stat-num"><?= number_format($stats['withOrder']) ?></div>
                                <div class="ord-stat-lbl">دارای حداقل یک سفارش</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- پیام تماس -->
                <div class="col-xl-4 col-lg-4 col-sm-6 mb-3">
                    <div class="card ord-stat-card h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="ord-stat-icon" style="background:#ef4444">
                                <i class="fa fa-envelope"></i>
                            </div>
                            <div>
                                <div class="ord-stat-num"><?= number_format($stats['messages']) ?></div>
                                <div class="ord-stat-lbl">پیام‌های تماس ثبت‌شده</div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <!-- /stat cards -->

            <!-- ══════════ MAIN CARD ══════════ -->
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h4 class="card-title mb-0">
                        <i class="fa fa-users ml-2 text-primary"></i>لیست کاربران
                    </h4>
                    <span class="badge badge-primary" style="font-size:.85rem">
                        <?= $result->num_rows ?> کاربر
                    </span>
                </div>

                <div class="card-body">

                    <!-- ── filter bar ── -->
                    <form method="get" class="filter-bar">
                        <div class="row align-items-end">
                            <div class="col-md-5 mb-2 mb-md-0">
                                <label class="form-label small mb-1">جستجو</label>
                                <input type="text"
                                       name="search"
                                       class="form-control"
                                       placeholder="نام، شماره موبایل، ایمیل یا شناسه کاربر…"
                                       value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div class="col-md-3 mb-2 mb-md-0">
                                <label class="form-label small mb-1">وضعیت</label>
                                <select name="status" class="form-control">
                                    <option value="">همه کاربران</option>
                                    <option value="verified"   <?= $status === 'verified'   ? 'selected' : '' ?>>موبایل تایید شده</option>
                                    <option value="unverified" <?= $status === 'unverified' ? 'selected' : '' ?>>موبایل تایید نشده</option>
                                    <option value="online"     <?= $status === 'online'     ? 'selected' : '' ?>>نشست فعال</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-2 mb-md-0">
                                <button class="btn btn-primary btn-block">
                                    <i class="fa fa-search ml-1"></i>جستجو
                                </button>
                            </div>
                            <div class="col-md-2">
                                <a href="user_list.php" class="btn btn-light btn-block">
                                    <i class="fa fa-times ml-1"></i>حذف فیلتر
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- ── table ── -->
                    <div class="table-responsive">
                        <table class="table table-hover table-responsive-md">
                            <thead class="thead-light">
                            <tr>
                                <th width="55">#</th>
                                <th>کاربر</th>
                                <th>موبایل</th>
                                <th>ایمیل</th>
                                <th>سفارش‌ها</th>
                                <th>وضعیت</th>
                                <th>تاریخ عضویت</th>
                                <th width="160">عملیات</th>
                            </tr>
                            </thead>
                            <tbody>

                            <?php if ($result->num_rows === 0): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="fa fa-users fa-2x d-block mb-2"></i>
                                        کاربری یافت نشد.
                                    </td>
                                </tr>
                            <?php else: ?>

                                <?php while ($row = $result->fetch_assoc()):
                                    $verified  = (int) $row['mobile_verified'];
                                    $isOnline  = (int) $row['is_login'];
                                    $initials  = mb_substr(trim($row['name']) ?: '?', 0, 1, 'UTF-8');

                                    // encode row data for modal
                                    $rowJson = htmlspecialchars(json_encode($row, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
                                    ?>

                                    <tr data-user='<?= $rowJson ?>'
                                        data-bs-toggle="modal"
                                        data-bs-target="#userModal"
                                        style="cursor:pointer"
                                        title="کلیک برای جزئیات">

                                        <td><strong>#<?= (int) $row['id'] ?></strong></td>

                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="user-avatar"><?= htmlspecialchars(mb_strtoupper($initials, 'UTF-8'), ENT_QUOTES, 'UTF-8') ?></div>
                                                <div>
                                                    <div class="font-weight-bold">
                                                        <?= htmlspecialchars($row['name'] ?: '—', ENT_QUOTES, 'UTF-8') ?>
                                                    </div>
                                                    <?php if (!empty($row['description'])): ?>
                                                        <small class="text-muted"><?= htmlspecialchars(mb_substr($row['description'], 0, 30, 'UTF-8'), ENT_QUOTES, 'UTF-8') ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>

                                        <td dir="ltr" class="text-right"><?= htmlspecialchars($row['mobile'], ENT_QUOTES, 'UTF-8') ?></td>

                                        <td><?= htmlspecialchars($row['email'] ?: '—', ENT_QUOTES, 'UTF-8') ?></td>

                                        <td>
                                            <?php if ((int) $row['orders_count'] > 0): ?>
                                                <span class="badge badge-info"><?= (int) $row['orders_count'] ?> سفارش</span>
                                            <?php else: ?>
                                                <span class="text-muted small">بدون سفارش</span>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <?= verifiedBadge($verified) ?><br>
                                            <?= sessionBadge($isOnline) ?>
                                        </td>

                                        <td>
                                            <div><?= jdate("Y/m/d", strtotime($row['created_at'])) ?></div>
                                            <small class="text-muted"><?= jdate("H:i", strtotime($row['created_at'])) ?></small>
                                        </td>

                                        <td class="action-btns" onclick="event.stopPropagation()">

                                            <a href="user_view.php?id=<?= $row['id'] ?>"
                                               class="btn btn-sm btn-info"
                                               title="مشاهده">
                                                <i class="fa fa-eye"></i>
                                            </a>

                                            <a href="user_edit.php?id=<?= $row['id'] ?>"
                                               class="btn btn-sm btn-primary"
                                               title="ویرایش">
                                                <i class="fa fa-edit"></i>
                                            </a>

                                            <a href="user_list.php?delete=<?= $row['id'] ?>"
                                               class="btn btn-sm btn-danger"
                                               title="حذف"
                                               onclick="return confirm('کاربر #<?= $row['id'] ?> حذف شود؟')">
                                                <i class="fa fa-trash"></i>
                                            </a>

                                        </td>
                                    </tr>

                                <?php endwhile; ?>
                            <?php endif; ?>

                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
            <!-- /main card -->

        </div>
    </div>
    <!-- /content-body -->

    <?php require_once "inc/footer.php"; ?>

</div>
<!-- /main-wrapper -->


<!-- ══════════════════════════════════════════════════════
     USER DETAIL MODAL
     ══════════════════════════════════════════════════════ -->
<div class="modal fade" id="userModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa fa-user ml-2 text-primary"></i>
                    <span id="mTitle">جزئیات کاربر</span>
                </h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>

            <div class="modal-body">
                <div class="row">

                    <!-- ── ستون اطلاعات ── -->
                    <div class="col-md-6 border-left pb-3">
                        <div class="section-head">اطلاعات حساب</div>

                        <div class="detail-label">نام</div>
                        <div class="detail-value" id="mName">—</div>

                        <div class="detail-label">شماره موبایل</div>
                        <div class="detail-value" id="mMobile" dir="ltr" style="text-align:right">—</div>

                        <div class="detail-label">ایمیل</div>
                        <div class="detail-value" id="mEmail">—</div>

                        <div class="detail-label">توضیحات</div>
                        <div class="detail-value" id="mDescription" style="white-space:pre-line">—</div>
                    </div>

                    <!-- ── ستون وضعیت ── -->
                    <div class="col-md-6 pb-3">
                        <div class="section-head">وضعیت</div>

                        <div class="detail-label">تایید موبایل</div>
                        <div class="detail-value" id="mVerified">—</div>

                        <div class="detail-label">وضعیت نشست</div>
                        <div class="detail-value" id="mSession">—</div>

                        <div class="detail-label">تعداد سفارش‌ها</div>
                        <div class="detail-value" id="mOrdersCount">—</div>

                        <div class="detail-label">جمع خرید (سفارش‌های تأیید/ارسال شده)</div>
                        <div class="detail-value text-success" id="mOrdersTotal">—</div>

                        <div class="detail-label">تاریخ عضویت</div>
                        <div class="detail-value" id="mCreatedAt">—</div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <a id="mViewBtn" href="#" class="btn btn-info btn-sm">
                    <i class="fa fa-eye ml-1"></i>صفحه کامل
                </a>
                <a id="mEditBtn" href="#" class="btn btn-primary btn-sm">
                    <i class="fa fa-edit ml-1"></i>ویرایش
                </a>
                <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">بستن</button>
            </div>

        </div>
    </div>
</div>
<!-- /modal -->


<!-- ══════════════════════════════════════════════════════
     SCRIPTS
     ══════════════════════════════════════════════════════ -->
<script src="vendor/global/global.min.js"></script>
<script src="vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
<script src="js/custom.min.js"></script>
<script>
    (function () {
        'use strict';

        function fmtNum(n) {
            const v = parseFloat(n);
            return (v > 0) ? v.toLocaleString('fa-IR') + ' تومان' : '—';
        }

        function txt(el, val) {
            if (el) el.textContent = val || '—';
        }

        function fmtDateTime(str) {
            if (!str) return '—';
            const d = new Date(str.replace(' ', 'T'));
            if (isNaN(d)) return str;
            return d.toLocaleString('fa-IR');
        }

        /* ─────────────────────────────────────────
           populate modal
        ───────────────────────────────────────── */
        document.querySelectorAll('tr[data-user]').forEach(tr => {
            tr.addEventListener('click', function () {
                let user;
                try { user = JSON.parse(this.dataset.user); }
                catch { return; }

                const id = user.id;

                document.getElementById('mTitle').textContent = user.name + ' (#' + id + ')';

                txt(document.getElementById('mName'),        user.name);
                txt(document.getElementById('mMobile'),      user.mobile);
                txt(document.getElementById('mEmail'),       user.email || '—');
                txt(document.getElementById('mDescription'), user.description || 'ثبت نشده');

                document.getElementById('mVerified').innerHTML =
                    (parseInt(user.mobile_verified) === 1)
                        ? '<span class="badge badge-success">تایید شده</span>'
                        : '<span class="badge badge-warning">تایید نشده</span>';

                document.getElementById('mSession').innerHTML =
                    (parseInt(user.is_login) === 1)
                        ? '<span class="badge badge-primary">آنلاین</span>'
                        : '<span class="badge badge-secondary">آفلاین</span>';

                txt(document.getElementById('mOrdersCount'), (user.orders_count || 0) + ' سفارش');
                txt(document.getElementById('mOrdersTotal'), fmtNum(user.orders_total));
                txt(document.getElementById('mCreatedAt'),   fmtDateTime(user.created_at));

                const viewBtn = document.getElementById('mViewBtn');
                const editBtn = document.getElementById('mEditBtn');
                if (viewBtn) viewBtn.href = 'user_view.php?id=' + id;
                if (editBtn) editBtn.href = 'user_edit.php?id=' + id;
            });
        });

    })();
</script>

</body>
</html>