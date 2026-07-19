<?php
require_once "inc/check.php";

$error   = false;
$errText = "";

/* ═══════════════════════════════════════════════════
 | حذف سفارش (soft-delete)
 * ══════════════════════════════════════════════════*/
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id   = (int) $_GET['delete'];
    $stmt = $mysqli->prepare("UPDATE orders SET deleted=1 WHERE id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: order_list.php?deleted=1");
        exit;
    }
    $error   = true;
    $errText = "خطا در حذف سفارش";
}

/* ═══════════════════════════════════════════════════
 | فیلترها
 * ══════════════════════════════════════════════════*/
$search = trim($_GET['search'] ?? '');
$status = (int) ($_GET['status'] ?? 0);

/* ═══════════════════════════════════════════════════
 | آمار کارت‌های بالا
 * ══════════════════════════════════════════════════*/
$stats       = [];
$stats['all'] = (int) $mysqli->query("SELECT COUNT(*) c FROM orders WHERE deleted=0")->fetch_assoc()['c'];
$stats['sum'] = (int) $mysqli->query("SELECT COALESCE(SUM(quoted_total),0) s FROM orders WHERE deleted=0 AND quoted_total>0")->fetch_assoc()['s'];
for ($i = 1; $i <= 6; $i++) {
    $stats[$i] = (int) $mysqli->query("SELECT COUNT(*) c FROM orders WHERE deleted=0 AND order_status_id={$i}")->fetch_assoc()['c'];
}
$stats['unread'] = (int) $mysqli->query("SELECT COUNT(*) c FROM orders WHERE deleted=0 AND visited=0")->fetch_assoc()['c'];

/* ═══════════════════════════════════════════════════
 | Query اصلی با فیلتر
 * ══════════════════════════════════════════════════*/
$sql    = "
    SELECT o.*, s.status_name
    FROM   orders o
    LEFT   JOIN order_status s ON s.id = o.order_status_id
    WHERE  o.deleted = 0
";
$params = [];
$types  = "";

if ($search !== '') {
    $sql   .= " AND (o.full_name LIKE ? OR o.phone LIKE ? OR o.order_name LIKE ? OR o.id LIKE ?)";
    $like   = "%{$search}%";
    array_push($params, $like, $like, $like, $like);
    $types .= "ssss";
}
if ($status > 0) {
    $sql   .= " AND o.order_status_id = ?";
    $params[] = $status;
    $types .= "i";
}
$sql .= " ORDER BY o.created_at DESC";

$stmt = $mysqli->prepare($sql);
if (count($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

/* ═══════════════════════════════════════════════════
 | تابع badge وضعیت
 * ══════════════════════════════════════════════════*/
function statusBadge(int $id, string $name): string
{
    $map = [
            1 => 'warning',
            2 => 'info',
            3 => 'primary',
            4 => 'success',
            5 => 'secondary',
            6 => 'danger',
    ];
    $cls = $map[$id] ?? 'dark';
    return "<span class=\"badge badge-{$cls}\">" . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . "</span>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= setting('name') ?></title>
    <!-- Favicon icon -->
    <link href="css/fontawesome.css" rel="stylesheet">
    <link rel="icon" type="image/png" sizes="16x16" href="images/favicon.png">
    <link href="vendor/jqvmap/css/jqvmap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="vendor/chartist/css/chartist.min.css">
    <!-- Vectormap -->
    <link href="vendor/jqvmap/css/jqvmap.min.css" rel="stylesheet">
    <link href="vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="vendor/owl-carousel/owl.carousel.css" rel="stylesheet">
    <style>

        .setCard {
            margin-right: 20px;
        }

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

        /* row highlight for unread */
        tr.ord-unread { background: #fffbeb !important; font-weight: 600; }
        tr.ord-unread td { border-right: 3px solid #f59e0b; }

        /* receipt thumbnail */
        .receipt-thumb {
            width: 38px; height: 38px; border-radius: 6px;
            object-fit: cover; border: 1px solid #dee2e6; cursor: pointer;
        }

        /* modal — order detail */
        #orderModal .modal-header  { background: #f8f9fa; border-bottom: 1px solid #e9ecef; }
        #orderModal .modal-title   { font-weight: 700; }
        #orderModal .detail-label  { font-size: .75rem; color: #8a8fa3; margin-bottom: 2px; }
        #orderModal .detail-value  { font-size: .92rem; font-weight: 600; margin-bottom: 14px; }
        #orderModal .section-head  {
            font-size: .78rem; font-weight: 700; text-transform: uppercase;
            color: #8a8fa3; letter-spacing: .06em;
            border-bottom: 1px dashed #dee2e6; padding-bottom: 6px; margin-bottom: 14px;
        }
        .modal-receipt-wrap { border-radius: 10px; overflow: hidden; border: 1px solid #dee2e6; }
        .modal-receipt-wrap img { width: 100%; display: block; }

        /* status change select inside modal */
        #modalStatusSelect { border-radius: 8px; }

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
                <h4>مدیریت سفارش‌ها</h4>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item active">سفارشات</li>
                </ol>
            </div>

            <!-- alerts -->
            <?php if (isset($_GET['deleted'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fa fa-check-circle ml-1"></i>سفارش با موفقیت حذف شد.
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
                    <i class="fa fa-check-circle ml-1"></i>سفارش با موفقیت به‌روزرسانی شد.
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
                                <i class="flaticon-381-box"></i>
                            </div>
                            <div class="setCard">
                                <div class="ord-stat-num"><?= number_format($stats['all']) ?></div>
                                <div class="ord-stat-lbl">کل سفارش‌ها</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- جمع فروش -->
                <div class="col-xl-4 col-lg-4 col-sm-6 mb-3">
                    <div class="card ord-stat-card h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="ord-stat-icon" style="background:#10b981">
                                <i class="fa fa-money"></i>
                            </div>
                            <div class="setCard">
                                <div class="ord-stat-num" style="font-size:1.1rem"><?= number_format($stats['sum']) ?></div>
                                <div class="ord-stat-lbl">جمع فروش (تومان)</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- در انتظار قیمت -->
                <div class="col-xl-4 col-lg-4 col-sm-6 mb-3">
                    <div class="card ord-stat-card h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="ord-stat-icon" style="background:#f59e0b">
                                <i class="fa fa-clock"></i>
                            </div>
                            <div class="setCard">
                                <div class="ord-stat-num"><?= number_format($stats[1]) ?></div>
                                <div class="ord-stat-lbl">در انتظار قیمت‌دهی</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- در انتظار پرداخت -->
                <div class="col-xl-4 col-lg-4 col-sm-6 mb-3">
                    <div class="card ord-stat-card h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="ord-stat-icon" style="background:#0ea5e9">
                                <i class="fa fa-credit-card"></i>
                            </div>
                            <div class="setCard">
                                <div class="ord-stat-num"><?= number_format($stats[2]) ?></div>
                                <div class="ord-stat-lbl">در انتظار پرداخت</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- خوانده نشده -->
                <div class="col-xl-4 col-lg-4 col-sm-6 mb-3">
                    <div class="card ord-stat-card h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="ord-stat-icon" style="background:#ef4444">
                                <i class="fa fa-bell"></i>
                            </div>
                            <div class="setCard">
                                <div class="ord-stat-num"><?= number_format($stats['unread']) ?></div>
                                <div class="ord-stat-lbl">خوانده نشده</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ارسال شده -->
                <div class="col-xl-4 col-lg-4 col-sm-6 mb-3">
                    <div class="card ord-stat-card h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="ord-stat-icon" style="background:#8b5cf6">
                                <i class="fa fa-truck"></i>
                            </div>
                            <div class="setCard">
                                <div class="ord-stat-num"><?= number_format($stats[5]) ?></div>
                                <div class="ord-stat-lbl">ارسال شده</div>
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
                        <i class="fa fa-list-alt ml-2 text-primary"></i>لیست سفارش‌ها
                    </h4>
                    <span class="badge badge-primary" style="font-size:.85rem">
                        <?= $result->num_rows ?> سفارش
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
                                       placeholder="شماره سفارش، نام مشتری، موبایل یا عنوان سفارش…"
                                       value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div class="col-md-3 mb-2 mb-md-0">
                                <label class="form-label small mb-1">وضعیت</label>
                                <select name="status" class="form-control">
                                    <option value="0">همه وضعیت‌ها</option>
                                    <option value="1" <?= $status == 1 ? 'selected' : '' ?>>در انتظار قیمت‌دهی</option>
                                    <option value="2" <?= $status == 2 ? 'selected' : '' ?>>در انتظار پرداخت</option>
                                    <option value="3" <?= $status == 3 ? 'selected' : '' ?>>در حال بررسی</option>
                                    <option value="4" <?= $status == 4 ? 'selected' : '' ?>>تأیید شده</option>
                                    <option value="5" <?= $status == 5 ? 'selected' : '' ?>>ارسال شده</option>
                                    <option value="6" <?= $status == 6 ? 'selected' : '' ?>>آرشیو / لغو شده</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-2 mb-md-0">
                                <button class="btn btn-primary btn-block">
                                    <i class="fa fa-search ml-1"></i>جستجو
                                </button>
                            </div>
                            <div class="col-md-2">
                                <a href="order_list.php" class="btn btn-light btn-block">
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
                                <th>عنوان سفارش</th>
                                <th>مشتری</th>
                                <th>مبلغ</th>
                                <th>وضعیت</th>
                                <th>رسید</th>
                                <th>تاریخ</th>
                                <th width="160">عملیات</th>
                            </tr>
                            </thead>
                            <tbody>

                            <?php if ($result->num_rows === 0): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="fa fa-inbox fa-2x d-block mb-2"></i>
                                        سفارشی یافت نشد.
                                    </td>
                                </tr>
                            <?php else: ?>

                                <?php while ($row = $result->fetch_assoc()):
                                    $sid      = (int) $row['order_status_id'];
                                    $isUnread = ((int) $row['visited'] === 0);
                                    $hasQuote = (float) $row['quoted_total'] > 0;
                                    $hasRcpt  = !empty($row['receipt_path']);

                                    // encode row data for modal
                                    $rowJson = htmlspecialchars(json_encode($row, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
                                    ?>

                                    <tr class="<?= $isUnread ? 'ord-unread' : '' ?>"
                                        data-order='<?= $rowJson ?>'
                                        data-bs-toggle="modal"
                                        data-bs-target="#orderModal"
                                        style="cursor:pointer"
                                        title="کلیک برای جزئیات">

                                        <td><strong>#<?= (int) $row['id'] ?></strong></td>

                                        <td>
                                            <div class="font-weight-bold">
                                                <?= htmlspecialchars($row['order_name'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
                                            </div>
                                            <small class="text-muted"><?= htmlspecialchars($row['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?></small>
                                        </td>

                                        <td>
                                            <div><?= htmlspecialchars($row['full_name'] ?? '—', ENT_QUOTES, 'UTF-8') ?></div>
                                            <?php if (!empty($row['email'])): ?>
                                                <small class="text-muted"><?= htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8') ?></small>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <?php if ($hasQuote): ?>
                                                <strong class="text-success d-block">
                                                    <?= number_format((int) $row['quoted_total']) ?> ت
                                                </strong>
                                                <?php if ((float) $row['grand_total'] > 0): ?>
                                                    <small class="text-muted">
                                                        از <?= number_format((int) $row['grand_total']) ?> ت
                                                    </small>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="badge badge-warning">در انتظار قیمت</span>
                                            <?php endif; ?>
                                        </td>

                                        <td><?= statusBadge($sid, $row['status_name'] ?? 'نامشخص') ?></td>

                                        <td class="text-center">
                                            <?php if ($hasRcpt): ?>
                                                <span class="text-success" title="رسید بارگذاری شده">
                                                    <i class="fa fa-check-circle fa-lg"></i>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted" title="بدون رسید">
                                                    <i class="fa fa-minus-circle fa-lg"></i>
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <div><?= jdate("Y/m/d", strtotime($row['created_at'])) ?></div>
                                            <small class="text-muted"><?= jdate("H:i", strtotime($row['created_at'])) ?></small>
                                        </td>

                                        <td class="action-btns" onclick="event.stopPropagation()">

                                            <a href="order_view.php?id=<?= $row['id'] ?>"
                                               class="btn btn-sm btn-info"
                                               title="مشاهده">
                                                <i class="fa fa-eye"></i>
                                            </a>

                                            <a href="order_edit.php?id=<?= $row['id'] ?>"
                                               class="btn btn-sm btn-primary"
                                               title="ویرایش">
                                                <i class="fa fa-edit"></i>
                                            </a>

                                            <?php if ($hasRcpt): ?>
                                                <a href="../../<?= htmlspecialchars($row['receipt_path'], ENT_QUOTES, 'UTF-8') ?>"
                                                   target="_blank"
                                                   class="btn btn-sm btn-success"
                                                   title="مشاهده رسید">
                                                    <i class="fa fa-file-image"></i>
                                                </a>
                                            <?php endif; ?>

                                            <a href="order_list.php?delete=<?= $row['id'] ?>"
                                               class="btn btn-sm btn-danger"
                                               title="حذف"
                                               onclick="return confirm('سفارش #<?= $row['id'] ?> حذف شود؟')">
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
     ORDER DETAIL MODAL
     ══════════════════════════════════════════════════════ -->
<div class="modal fade" id="orderModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa fa-receipt ml-2 text-primary"></i>
                    <span id="mTitle">جزئیات سفارش</span>
                </h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>

            <div class="modal-body">
                <div class="row">

                    <!-- ── ستون مشتری ── -->
                    <div class="col-md-4 border-left pb-3">
                        <div class="section-head">اطلاعات مشتری</div>

                        <div class="detail-label">نام و نام‌خانوادگی</div>
                        <div class="detail-value" id="mFullName">—</div>

                        <div class="detail-label">شماره موبایل</div>
                        <div class="detail-value" id="mPhone" dir="ltr" style="text-align:right">—</div>

                        <div class="detail-label">ایمیل</div>
                        <div class="detail-value" id="mEmail">—</div>

                        <div class="detail-label">آدرس</div>
                        <div class="detail-value" id="mAddress">—</div>

                        <div class="detail-label">کد پستی</div>
                        <div class="detail-value" id="mPostal" dir="ltr" style="text-align:right">—</div>
                    </div>

                    <!-- ── ستون یادداشت‌ها ── -->
                    <div class="col-md-4 pb-3">
                        <div class="section-head">یادداشت‌ها</div>

                        <div class="detail-label">عنوان سفارش</div>
                        <div class="detail-value" id="mOrderName">—</div>

                        <div class="detail-label">یادداشت مشتری</div>
                        <div class="detail-value" id="mNote" style="white-space:pre-line">—</div>

                        <div class="detail-label">یادداشت ادمین</div>
                        <div class="detail-value" id="mAdminNote" style="white-space:pre-line;color:#0ea5e9">—</div>

                        <div class="detail-label">وضعیت فعلی</div>
                        <div class="detail-value" id="mBadge"></div>
                    </div>

                    <!-- ── ستون رسید + قیمت ── -->
                    <div class="col-md-4 pb-3">
                        <div class="section-head">رسید و مبلغ</div>

                        <div id="mReceiptWrap" class="mb-3 d-none">
                            <div class="detail-label">رسید پرداخت</div>
                            <div class="modal-receipt-wrap mb-2">
                                <img id="mReceiptImg" src="" alt="رسید">
                            </div>
                            <a id="mReceiptLink" href="#" target="_blank"
                               class="btn btn-sm btn-outline-success btn-block">
                                <i class="fa fa-download ml-1"></i>دانلود / نمایش کامل
                            </a>
                        </div>

                        <div id="mNoReceipt" class="alert alert-warning py-2 small">
                            <i class="fa fa-exclamation-triangle ml-1"></i>رسیدی بارگذاری نشده
                        </div>

                        <hr>

                        <div class="detail-label">قیمت اولیه (مشتری)</div>
                        <div class="detail-value" id="mGrandTotal">—</div>

                        <div class="detail-label">قیمت نهایی (کارشناس)</div>
                        <div class="detail-value text-success font-weight-bold" id="mQuotedTotal">—</div>

                    </div>
                </div>

                <!-- ── تغییر وضعیت سریع ── -->
                <hr>
                <form id="modalStatusForm" class="row align-items-end">
                    <input type="hidden" id="modalOrderId" name="order_id">
                    <div class="col-md-4">
                        <label class="form-label small mb-1">تغییر وضعیت سفارش</label>
                        <select id="modalStatusSelect" name="new_status" class="form-control">
                            <option value="1">در انتظار قیمت‌دهی</option>
                            <option value="2">در انتظار پرداخت</option>
                            <option value="3">در حال بررسی</option>
                            <option value="4">تأیید شده</option>
                            <option value="5">ارسال شده</option>
                            <option value="6">آرشیو / لغو شده</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-1">قیمت نهایی (تومان)</label>
                        <input type="number" id="modalQuotedInput" name="quoted_total"
                               class="form-control" placeholder="اختیاری">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-1">یادداشت ادمین</label>
                        <input type="text" name="admin_note" id="modalAdminNote"
                               class="form-control" placeholder="پیام برای مشتری…">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fa fa-save ml-1"></i>ثبت
                        </button>
                    </div>
                    <div class="col-12 mt-2" id="modalMsg"></div>
                </form>

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
    (function () {
        'use strict';

        const ASSET_BASE = '../assets/images/';

        /* ─────────────────────────────────────────
           badge helper
        ───────────────────────────────────────── */
        const STATUS_MAP = {
            1: { cls: 'warning',   label: 'در انتظار قیمت‌دهی' },
            2: { cls: 'info',      label: 'در انتظار پرداخت' },
            3: { cls: 'primary',   label: 'در حال بررسی' },
            4: { cls: 'success',   label: 'تأیید شده' },
            5: { cls: 'secondary', label: 'ارسال شده' },
            6: { cls: 'danger',    label: 'آرشیو / لغو شده' },
        };

        function makeBadge(id) {
            const s = STATUS_MAP[id] || { cls: 'dark', label: 'نامشخص' };
            return `<span class="badge badge-${s.cls}">${s.label}</span>`;
        }

        function fmtNum(n) {
            const v = parseFloat(n);
            return (v > 0) ? v.toLocaleString('fa-IR') + ' تومان' : '—';
        }

        function txt(el, val) {
            if (el) el.textContent = val || '—';
        }

        /* ─────────────────────────────────────────
           populate modal
        ───────────────────────────────────────── */
        document.querySelectorAll('tr[data-order]').forEach(tr => {
            tr.addEventListener('click', function () {
                let order;
                try { order = JSON.parse(this.dataset.order); }
                catch { return; }

                const id = order.id;

                /* header */
                document.getElementById('mTitle').textContent = 'سفارش #' + id;

                /* customer */
                txt(document.getElementById('mFullName'),  order.full_name);
                txt(document.getElementById('mPhone'),     order.phone);
                txt(document.getElementById('mEmail'),     order.email || '—');
                txt(document.getElementById('mAddress'),   order.address || '—');
                txt(document.getElementById('mPostal'),    order.postal_code || '—');

                /* notes */
                txt(document.getElementById('mOrderName'),  order.order_name);
                txt(document.getElementById('mNote'),       order.note || 'ندارد');
                txt(document.getElementById('mAdminNote'),  order.admin_note || 'ندارد');

                /* badge */
                const badgeEl = document.getElementById('mBadge');
                if (badgeEl) badgeEl.innerHTML = makeBadge(parseInt(order.order_status_id));

                /* price */
                txt(document.getElementById('mGrandTotal'),  fmtNum(order.grand_total));
                txt(document.getElementById('mQuotedTotal'), fmtNum(order.quoted_total));

                /* receipt */
                const hasRcpt    = order.receipt_path && order.receipt_path !== '';
                const rcptWrap   = document.getElementById('mReceiptWrap');
                const noRcpt     = document.getElementById('mNoReceipt');
                const rcptImg    = document.getElementById('mReceiptImg');
                const rcptLink   = document.getElementById('mReceiptLink');

                if (hasRcpt) {
                    const url = ASSET_BASE + order.receipt_path;
                    if (rcptImg)  rcptImg.src  = url;
                    if (rcptLink) rcptLink.href = url;
                    rcptWrap?.classList.remove('d-none');
                    noRcpt?.classList.add('d-none');
                } else {
                    rcptWrap?.classList.add('d-none');
                    noRcpt?.classList.remove('d-none');
                }

                /* quick-status form */
                const ordIdEl   = document.getElementById('modalOrderId');
                const selEl     = document.getElementById('modalStatusSelect');
                const qInput    = document.getElementById('modalQuotedInput');
                const noteInput = document.getElementById('modalAdminNote');
                const msgEl     = document.getElementById('modalMsg');

                if (ordIdEl)   ordIdEl.value   = id;
                if (selEl)     selEl.value     = order.order_status_id;
                if (qInput)    qInput.value    = order.quoted_total > 0 ? order.quoted_total : '';
                if (noteInput) noteInput.value = order.admin_note || '';
                if (msgEl)     msgEl.innerHTML = '';

                /* footer links */
                const viewBtn = document.getElementById('mViewBtn');
                const editBtn = document.getElementById('mEditBtn');
                if (viewBtn) viewBtn.href = 'order_view.php?id=' + id;
                if (editBtn) editBtn.href = 'order_edit.php?id=' + id;

                /* mark as read via AJAX (fire & forget) */
                if (parseInt(order.visited) === 0) {
                    fetch('ajax/order_mark_read.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'id=' + id
                    }).then(() => {
                        /* remove highlight from row */
                        this.classList.remove('ord-unread');
                    }).catch(() => {});
                }
            });
        });

        /* ─────────────────────────────────────────
           modal status form submit
        ───────────────────────────────────────── */
        document.getElementById('modalStatusForm')?.addEventListener('submit', async function (e) {
            e.preventDefault();

            const btn   = this.querySelector('[type=submit]');
            const msgEl = document.getElementById('modalMsg');
            const fd    = new FormData(this);

            btn.disabled    = true;
            btn.innerHTML   = '<i class="fa fa-spinner fa-spin ml-1"></i>در حال ثبت…';
            msgEl.innerHTML = '';

            try {
                const res  = await fetch('ajax/order_update_status.php', { method: 'POST', body: fd });
                const data = await res.json();

                msgEl.innerHTML = `
                <div class="alert alert-${data.ok ? 'success' : 'danger'} py-2 small mb-0">
                    <i class="fa fa-${data.ok ? 'check' : 'times'}-circle ml-1"></i>${data.message}
                </div>`;

                if (data.ok) {
                    /* update badge in modal */
                    const newSid  = parseInt(fd.get('new_status'));
                    const badgeEl = document.getElementById('mBadge');
                    if (badgeEl) badgeEl.innerHTML = makeBadge(newSid);

                    /* update badge in table row */
                    const ordId = fd.get('order_id');
                    const row   = document.querySelector(`tr[data-order*='"id":"${ordId}"'], tr[data-order*='"id":${ordId}']`);
                    if (row) {
                        const badgeTd = row.querySelectorAll('td')[4];
                        if (badgeTd) badgeTd.innerHTML = makeBadge(newSid);
                    }

                    setTimeout(() => { msgEl.innerHTML = ''; }, 3000);
                }
            } catch {
                msgEl.innerHTML = '<div class="alert alert-danger py-2 small mb-0">خطا در اتصال به سرور</div>';
            } finally {
                btn.disabled  = false;
                btn.innerHTML = '<i class="fa fa-save ml-1"></i>ثبت';
            }
        });

    })();
</script>

</body>
</html>