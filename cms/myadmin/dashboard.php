<?php
require_once "inc/check.php";

/* =====================================================================
   DASHBOARD STATS
   Every number/chart on this page below is pulled live from the DB.
   ===================================================================== */

/* ---- 1) Revenue from confirmed / shipped orders (status 4 & 5) ---- */
$revStmt = $mysqli->prepare("
    SELECT COALESCE(SUM(COALESCE(quoted_total, grand_total)), 0) AS total_revenue,
           COUNT(*) AS total_count
    FROM orders
    WHERE deleted = 0 AND order_status_id IN (4,5)
");
$revStmt->execute();
$revRow = $revStmt->get_result()->fetch_assoc();
$totalRevenue        = (float) $revRow['total_revenue'];
$confirmedOrdersCount = (int) $revRow['total_count'];

/* revenue in the last 30 days vs the 30 days before that -> growth % */
$rev30 = $mysqli->query("
    SELECT COALESCE(SUM(COALESCE(quoted_total, grand_total)), 0) AS r
    FROM orders
    WHERE deleted = 0 AND order_status_id IN (4,5)
      AND created_at >= (NOW() - INTERVAL 30 DAY)
")->fetch_assoc()['r'];

$revPrev30 = $mysqli->query("
    SELECT COALESCE(SUM(COALESCE(quoted_total, grand_total)), 0) AS r
    FROM orders
    WHERE deleted = 0 AND order_status_id IN (4,5)
      AND created_at >= (NOW() - INTERVAL 60 DAY)
      AND created_at <  (NOW() - INTERVAL 30 DAY)
")->fetch_assoc()['r'];

if ($revPrev30 > 0) {
    $revenueGrowth = round((($rev30 - $revPrev30) / $revPrev30) * 100, 1);
} else {
    $revenueGrowth = $rev30 > 0 ? 100.0 : 0.0;
}

/* ---- 2) Headline counters ---- */
$totalOrders   = (int) $mysqli->query("SELECT COUNT(*) AS c FROM orders WHERE deleted = 0")->fetch_assoc()['c'];
$totalUsers    = (int) $mysqli->query("SELECT COUNT(*) AS c FROM user WHERE deleted = 0")->fetch_assoc()['c'];
$totalProducts = (int) $mysqli->query("SELECT COUNT(*) AS c FROM product WHERE deleted = 0 AND active = 1")->fetch_assoc()['c'];

/* ---- 3) Unread contact tickets (kept from the original page) ---- */
$notifCountDash = $mysqli->prepare("SELECT COUNT(*) AS unreaded FROM contact_messages WHERE seen = 0 AND deleted = 0");
$notifCountDash->execute();
$notifAll = (int) $notifCountDash->get_result()->fetch_assoc()['unreaded'];

/* ---- 4) Orders still waiting for admin pricing (پیش‌فاکتور بررسی‌نشده) ---- */
$pendingPriceCount = (int) $mysqli->query("
    SELECT COUNT(*) AS c FROM orders WHERE deleted = 0 AND order_status_id = 1
")->fetch_assoc()['c'];

/* ---- 5) Best selling product (by quantity across all orders) ---- */
$topProductStmt = $mysqli->prepare("
    SELECT oi.product_id, oi.product_name, SUM(oi.qty) AS total_qty
    FROM order_items oi
    INNER JOIN orders o ON o.id = oi.order_id
    WHERE o.deleted = 0
    GROUP BY oi.product_id, oi.product_name
    ORDER BY total_qty DESC
    LIMIT 1
");
$topProductStmt->execute();
$topProduct = $topProductStmt->get_result()->fetch_assoc();

/* monthly quantity trend for that top product, last 6 months (for its sparkline) */
$topProductTrendLabels = [];
$topProductTrendQty    = [];
if ($topProduct) {
    $trendStmt = $mysqli->prepare("
        SELECT DATE_FORMAT(o.created_at, '%Y-%m') AS ym, SUM(oi.qty) AS qty
        FROM order_items oi
        INNER JOIN orders o ON o.id = oi.order_id
        WHERE o.deleted = 0 AND oi.product_id = ?
          AND o.created_at >= (NOW() - INTERVAL 6 MONTH)
        GROUP BY ym
        ORDER BY ym ASC
    ");
    $trendStmt->bind_param('i', $topProduct['product_id']);
    $trendStmt->execute();
    $trendResult = $trendStmt->get_result();
    while ($row = $trendResult->fetch_assoc()) {
        $topProductTrendLabels[] = $row['ym'];
        $topProductTrendQty[]    = (int) $row['qty'];
    }
}

/* ---- 6) Product sales share (top 4 products by quantity sold) ---- */
$shareStmt = $mysqli->prepare("
    SELECT oi.product_name, SUM(oi.qty) AS qty
    FROM order_items oi
    INNER JOIN orders o ON o.id = oi.order_id
    WHERE o.deleted = 0
    GROUP BY oi.product_id, oi.product_name
    ORDER BY qty DESC
    LIMIT 4
");
$shareStmt->execute();
$shareResult   = $shareStmt->get_result();
$productShare  = [];
$totalQtyShare = 0;
$shareColors   = ['#AC39D4', '#40D4A8', '#1EB6E7', '#461EE7'];
while ($row = $shareResult->fetch_assoc()) {
    $productShare[]  = $row;
    $totalQtyShare  += (int) $row['qty'];
}

/* ---- 7) Orders per month, last 6 months (for the overview chart) ---- */
$monthlyStmt = $mysqli->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') AS ym, COUNT(*) AS cnt,
           COALESCE(SUM(COALESCE(quoted_total, grand_total)), 0) AS rev
    FROM orders
    WHERE deleted = 0 AND created_at >= (NOW() - INTERVAL 6 MONTH)
    GROUP BY ym
    ORDER BY ym ASC
");
$monthlyLabels  = [];
$monthlyCounts  = [];
$monthlyRevenue = [];
while ($row = $monthlyStmt->fetch_assoc()) {
    $monthlyLabels[]  = $row['ym'];
    $monthlyCounts[]  = (int) $row['cnt'];
    $monthlyRevenue[] = (float) $row['rev'];
}

/* ---- 8) Latest orders, for the "recent transactions" table ---- */
$recentOrders = $mysqli->query("
    SELECT o.id, o.order_name, o.full_name, o.grand_total, o.quoted_total,
           o.order_status_id, o.created_at, s.status_name
    FROM orders o
    LEFT JOIN order_status s ON s.id = o.order_status_id
    WHERE o.deleted = 0
    ORDER BY o.created_at DESC
    LIMIT 8
")->fetch_all(MYSQLI_ASSOC);

$todayOrders = $mysqli->query("
    SELECT o.id, o.order_name, o.full_name, o.grand_total, o.quoted_total,
           o.order_status_id, o.created_at, s.status_name
    FROM orders o
    LEFT JOIN order_status s ON s.id = o.order_status_id
    WHERE o.deleted = 0 AND DATE(o.created_at) = CURDATE()
    ORDER BY o.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

/* ---- small view helpers ---- */
function fmt_toman($n) { return number_format((float) $n) . ' تومان'; }

function order_status_badge($statusId, $statusName) {
    $map = [
            1 => 'secondary', // در انتظار قیمت‌دهی
            2 => 'info',      // در انتظار پرداخت
            3 => 'warning',   // در حال بررسی
            4 => 'success',   // تأیید شده
            5 => 'primary',   // ارسال شده
            6 => 'dark',      // آرشیو / لغو شده
    ];
    $color = $map[(int) $statusId] ?? 'secondary';
    return '<span class="badge badge-' . $color . ' light">' . htmlspecialchars($statusName ?: '-') . '</span>';
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
    <link rel="icon" type="image/png" sizes="16x16" href="images/favicon.png">
    <link href="vendor/jqvmap/css/jqvmap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="vendor/chartist/css/chartist.min.css">
    <!-- Vectormap -->
    <link href="vendor/jqvmap/css/jqvmap.min.css" rel="stylesheet">
    <link href="vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="vendor/owl-carousel/owl.carousel.css" rel="stylesheet">

</head>

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

    <?php
    require_once "inc/header.php";
    require_once "inc/aside.php"
    ?>



    <!--**********************************
        Content body start
    ***********************************-->
    <div class="content-body">
        <!-- row -->
        <div class="container-fluid">
            <div class="form-head mb-4">
                <h2 class="text-black font-w600 mb-0">داشبورد وبسایت <?= setting('name') ?></h2>
            </div>
            <div class="row">
                <div class="col-xl-3 col-sm-6">
                    <div class="card bgl-primary card-body overflow-hidden p-3 rounded text-center">
                        <span class="text-black fs-14">درآمد تأیید شده</span>
                        <h3 class="text-black fs-20 mb-0 font-w600"><?= fmt_toman($totalRevenue) ?></h3>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6">
                    <div class="card bgl-success card-body overflow-hidden p-3 rounded text-center">
                        <span class="text-black fs-14">تعداد سفارش‌ها</span>
                        <h3 class="text-black fs-20 mb-0 font-w600"><?= $totalOrders ?></h3>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6">
                    <div class="card bgl-warning card-body overflow-hidden p-3 rounded text-center">
                        <span class="text-black fs-14">تعداد کاربران</span>
                        <h3 class="text-black fs-20 mb-0 font-w600"><?= $totalUsers ?></h3>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6">
                    <div class="card bgl-info card-body overflow-hidden p-3 rounded text-center">
                        <span class="text-black fs-14">محصولات فعال</span>
                        <h3 class="text-black fs-20 mb-0 font-w600"><?= $totalProducts ?></h3>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-6">
                    <div class="row">
                        <!--							<div class="col-xl-8 col-lg-6 col-md-7 col-sm-8">-->
                        <!--								<div class="card-bx stacked">-->
                        <!--									<img src="images/card/card.png" alt="" class="mw-100">-->
                        <!--									<a href="cards-center.html"><i class="fa fa-caret-down" aria-hidden="true"></i></a>-->
                        <!--								</div>-->
                        <!--							</div>-->
                        <div class="col-xl-8 col-lg-6 col-md-5 col-sm-4">
                            <div class="card bgl-primary card-body overflow-hidden p-0 d-flex rounded">
                                <div class="p-0 text-center mt-3">
                                    <span class="text-black">قیمت روز دلار</span>
                                    <h3 class="text-black fs-20 mb-0 font-w600">
                                        <span id="usd">--</span>
                                    </h3>

                                    <small id="usd-time"></small>

                                    <div class="d-flex justify-content-center" style="gap: 10px;">
                                        <small id="high" class="text-success"></small>
                                        <small id="low" class="text-danger"></small>
                                    </div>
                                </div>
                                <canvas id="lineChart" height="300" class="mt-auto line-chart-demo"></canvas>
                            </div>
                        </div>
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-header d-sm-flex d-block border-0 pb-0">
                                    <div class="pl-3 ml-auto mb-sm-0 mb-3">
                                        <h4 class="fs-20 text-black mb-1">نگاه کلی سفارش‌ها</h4>
                                        <span class="fs-12">تعداد و مبلغ سفارش‌های ثبت‌شده در ۶ ماه اخیر</span>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <a href="javascript:void(0)" class="btn btn-rounded btn-light ml-3" data-toggle="modal"
                                           data-target="#DownloadReport"><i class="las la-download text-primary scale5 ml-3"></i>دانلود گزارش
                                        </a>
                                        <!-- Modal -->
                                        <div class="modal fade" id="DownloadReport">
                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">عنوان مدل</h5>
                                                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ و با استفاده از طراحان گرافیک
                                                            است. چاپگرها و متون بلکه روزنامه و مجله در ستون و سطرآنچنان که لازم است</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-danger light" data-dismiss="modal">بستن</button>
                                                        <button type="button" class="btn btn-primary">ذخیره تغییرات</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="dropdown">
                                            <div class="btn-link" data-toggle="dropdown">
                                                <svg width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                                    <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                        <rect x="0" y="0" width="24" height="24"></rect>
                                                        <circle fill="#000000" cx="5" cy="12" r="2"></circle>
                                                        <circle fill="#000000" cx="12" cy="12" r="2"></circle>
                                                        <circle fill="#000000" cx="19" cy="12" r="2"></circle>
                                                    </g>
                                                </svg>
                                            </div>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <a class="dropdown-item" href="javascript:void(0)">حذف</a>
                                                <a class="dropdown-item" href="javascript:void(0)">ویرایش</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="chartOrdersOverview"></div>
                                    <?php if (empty($monthlyLabels)): ?>
                                        <p class="fs-14 text-center mb-0">هنوز داده‌ای برای این بازه ثبت نشده است.</p>
                                    <?php else: ?>
                                        <div class="d-flex">
                                            <div class="custom-control custom-switch toggle-switch text-right mr-4 mb-2">
                                                <input type="checkbox" class="custom-control-input" id="customSwitch11" checked>
                                                <label class="custom-control-label fs-14 text-black pr-2" for="customSwitch11">تعداد سفارش</label>
                                            </div>
                                            <div class="custom-control custom-switch toggle-switch text-right mr-4 mb-2">
                                                <input type="checkbox" class="custom-control-input" id="customSwitch12">
                                                <label class="custom-control-label fs-14 text-black pr-2" for="customSwitch12">مبلغ فروش</label>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-header d-block d-sm-flex border-0">
                                    <div class="ml-3">
                                        <h4 class="fs-20 text-black">آخرین سفارش‌ها</h4>
                                        <p class="mb-0 fs-13">۸ سفارش اخیر ثبت شده در سایت</p>
                                    </div>
                                    <div class="card-action card-tabs mt-3 mt-sm-0">
                                        <ul class="nav nav-tabs" role="tablist">
                                            <li class="nav-item">
                                                <a class="nav-link active" data-toggle="tab" href="#recentOrdersTab" role="tab">اخیر</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" data-toggle="tab" href="#todayOrdersTab" role="tab">امروز</a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="card-body tab-content p-0">
                                    <div class="tab-pane active show fade" id="recentOrdersTab" role="tabpanel">
                                        <div class="table-responsive">
                                            <table class="table table-responsive-md card-table previous-transactions">
                                                <tbody>
                                                <?php if (empty($recentOrders)): ?>
                                                    <tr>
                                                        <td class="text-center fs-14 py-4">هنوز سفارشی ثبت نشده است.</td>
                                                    </tr>
                                                <?php else: foreach ($recentOrders as $order):
                                                    $amount = $order['quoted_total'] !== null ? $order['quoted_total'] : $order['grand_total'];
                                                    $displayName = $order['order_name'] !== '' ? $order['order_name'] : ('سفارش #' . $order['id']);
                                                    ?>
                                                    <tr>
                                                        <td style="width:63px;">
                                                            <div class="rounded-circle d-flex align-items-center justify-content-center bg-light"
                                                                 style="width:48px;height:48px;">
                                                                <i class="fa fa-shopping-cart text-primary"></i>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <h6 class="fs-16 font-w600 mb-0 text-black"><?= htmlspecialchars($displayName) ?></h6>
                                                            <span class="fs-14"><?= htmlspecialchars($order['full_name']) ?></span>
                                                        </td>
                                                        <td>
                                                            <h6 class="fs-16 text-black font-w400 mb-0"><?= date('Y/m/d', strtotime($order['created_at'])) ?></h6>
                                                            <span class="fs-14"><?= date('H:i', strtotime($order['created_at'])) ?></span>
                                                        </td>
                                                        <td><span class="fs-16 text-black font-w500"><?= fmt_toman($amount) ?></span></td>
                                                        <td><?= order_status_badge($order['order_status_id'], $order['status_name']) ?></td>
                                                    </tr>
                                                <?php endforeach; endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="todayOrdersTab" role="tabpanel">
                                        <div class="table-responsive">
                                            <table class="table card-table previous-transactions">
                                                <tbody>
                                                <?php if (empty($todayOrders)): ?>
                                                    <tr>
                                                        <td class="text-center fs-14 py-4">امروز هنوز سفارشی ثبت نشده است.</td>
                                                    </tr>
                                                <?php else: foreach ($todayOrders as $order):
                                                    $amount = $order['quoted_total'] !== null ? $order['quoted_total'] : $order['grand_total'];
                                                    $displayName = $order['order_name'] !== '' ? $order['order_name'] : ('سفارش #' . $order['id']);
                                                    ?>
                                                    <tr>
                                                        <td style="width:63px;">
                                                            <div class="rounded-circle d-flex align-items-center justify-content-center bg-light"
                                                                 style="width:48px;height:48px;">
                                                                <i class="fa fa-shopping-cart text-primary"></i>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <h6 class="fs-16 font-w600 mb-0 text-black"><?= htmlspecialchars($displayName) ?></h6>
                                                            <span class="fs-14"><?= htmlspecialchars($order['full_name']) ?></span>
                                                        </td>
                                                        <td>
                                                            <h6 class="fs-16 text-black font-w400 mb-0"><?= date('Y/m/d', strtotime($order['created_at'])) ?></h6>
                                                            <span class="fs-14"><?= date('H:i', strtotime($order['created_at'])) ?></span>
                                                        </td>
                                                        <td><span class="fs-16 text-black font-w500"><?= fmt_toman($amount) ?></span></td>
                                                        <td><?= order_status_badge($order['order_status_id'], $order['status_name']) ?></td>
                                                    </tr>
                                                <?php endforeach; endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="col-xl-6">
                    <div class="row">
                        <div class="col-xl-6 col-sm-6">
                            <div class="card">
                                <div class="card-header flex-wrap border-0 pb-0">
                                    <div class="mr-3 mb-2">
                                        <p class="fs-14 mb-1">فروش کل وبسایت (سفارش‌های تأیید/ارسال شده) :</p>
                                        <span class="fs-24 text-black font-w600"><?= fmt_toman($totalRevenue) ?></span>
                                    </div>
                                    <span class="fs-12 mb-2">
											<?php if ($revenueGrowth >= 0): ?>
                                                <svg width="21" height="15" viewBox="0 0 21 15" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M0.999939 13.5C1.91791 12.4157 4.89722 9.22772 6.49994 7.5L12.4999 10.5L19.4999 1.5"
                                                      stroke="#2BC155" stroke-width="2" />
												<path
                                                        d="M6.49994 7.5C4.89722 9.22772 1.91791 12.4157 0.999939 13.5H19.4999V1.5L12.4999 10.5L6.49994 7.5Z"
                                                        fill="url(#paint0_linear)" />
												<defs>
													<linearGradient id="paint0_linear" x1="10.2499" y1="3" x2="10.9999" y2="13.5"
                                                                    gradientUnits="userSpaceOnUse">
														<stop offset="0" stop-color="#2BC155" stop-opacity="0.73" />
														<stop offset="1" stop-color="#2BC155" stop-opacity="0" />
													</linearGradient>
												</defs>
											</svg>
                                                <span class="text-success"><?= abs($revenueGrowth) ?>% (۳۰ روز)</span>
                                            <?php else: ?>
                                                <span class="text-danger">-<?= abs($revenueGrowth) ?>% (۳۰ روز)</span>
                                            <?php endif; ?>
										</span>
                                </div>
                                <div class="card-body p-0">
                                    <canvas id="dashRevenueChart" height="80"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6 col-sm-6">
                            <div class="card">
                                <div class="card-header flex-wrap border-0 pb-0">
                                    <div class="mr-3 mb-2">
                                        <p class="fs-14 mb-1">تعداد پیام های بررسی نشده (تیکت) :</p>
                                        <span class="fs-24 text-black font-w600">
                                                <?php

                                                $notifCountDash = $mysqli->prepare("SELECT COUNT(*) AS unreaded
                                                    FROM contact_messages
                                                    WHERE seen = 0
                                                      AND deleted = 0;
                                                ");

                                                $notifCountDash->execute();
                                                $answer = $notifCountDash->get_result();
                                                $notifCountDashResult = $answer->fetch_assoc();

                                                $notifAll = $notifCountDashResult['unreaded'];

                                                echo $notifAll;
                                                ?>

                                            </span> <br>
                                        <p class="fs-14 mb-1">تعداد پیش فاکتورهای بررسی نشده :</p>
                                        <span class="fs-24 text-black font-w600">
                                                    <?= $pendingPriceCount ?>
                                            </span>
                                    </div>

                                </div>

                            </div>
                        </div>
                        <div class="col-xl-12">
                            <div class="card overflow-hidden">
                                <div class="card-header d-sm-flex d-block border-0 pb-0">
                                    <div class="mb-sm-0 mb-2">
                                        <p class="fs-14 mb-1">پرفروش ترین محصول سایت: </p>
                                        <strong class="mt-3 mb-5"><?= $topProduct ? htmlspecialchars($topProduct['product_name']) : 'هنوز سفارشی ثبت نشده' ?></strong><br>
                                        <?php if ($topProduct): ?>
                                            <span class="mb-0">
												<svg width="12" height="6" viewBox="0 0 12 6" fill="none" xmlns="http://www.w3.org/2000/svg">
													<path d="M11.9999 6L5.99994 -2.62268e-07L-6.10352e-05 6" fill="#2BC155" />
												</svg>
												<strong class="fs-24 text-black ml-2 mr-3"><?= (int) $topProduct['total_qty'] ?></strong>بار فروش رفته </span>
                                        <?php endif; ?>
                                    </div>

                                </div>
                                <div class="card-body p-0">
                                    <canvas id="dashTopProductChart" height="80"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-xl-5 col-xxl-12 col-md-5">
                                            <h4 class="fs-20 text-black mb-4">سهم فروش هر محصول (بر اساس تعداد)</h4>
                                            <?php if (empty($productShare)): ?>
                                                <p class="fs-14 mb-0">هنوز آماری برای نمایش ثبت نشده است.</p>
                                            <?php else: ?>
                                                <div class="row">
                                                    <?php foreach ($productShare as $i => $p):
                                                        $pct = $totalQtyShare > 0 ? round(($p['qty'] / $totalQtyShare) * 100) : 0;
                                                        ?>
                                                        <div class="d-flex col-xl-12 col-xxl-6 col-md-12 col-sm-6 mb-4">
                                                            <svg class="ml-3" width="14" height="54" viewBox="0 0 14 54" fill="none"
                                                                 xmlns="http://www.w3.org/2000/svg">
                                                                <rect x="-6.10352e-05" width="14" height="54" rx="7" fill="<?= $shareColors[$i % 4] ?>" />
                                                            </svg>
                                                            <div>
                                                                <p class="fs-14 mb-2"><?= htmlspecialchars($p['product_name']) ?></p>
                                                                <span class="fs-18 font-w500"><span class="text-black mr-2"><?= (int) $p['qty'] ?> عدد فروخته شده</span></span>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-xl-7  col-xxl-12 col-md-7">
                                            <?php if (!empty($productShare)):
                                                $bgClasses = ['bg-secondary', 'bg-success', 'border border-2 border-primary', 'bg-info'];
                                                $textClasses = ['text-white', 'text-white', 'text-black', 'text-white'];
                                                ?>
                                                <div class="row">
                                                    <?php foreach ($productShare as $i => $p):
                                                        $pct = $totalQtyShare > 0 ? round(($p['qty'] / $totalQtyShare) * 100) : 0;
                                                        ?>
                                                        <div class="col-sm-6 mb-4">
                                                            <div class="<?= $bgClasses[$i % 4] ?> rounded text-center p-3">
                                                                <div class="d-inline-block position-relative donut-chart-sale mb-3">
																<span class="donut1"
                                                                      data-peity='{ "fill": ["rgb(255, 255, 255)", "rgba(255, 255, 255, 0.2)"], "innerRadius": 33, "radius": 10}'><?= $pct ?>/100</span>
                                                                    <small class="<?= $textClasses[$i % 4] ?>"><?= $pct ?>%</small>
                                                                </div>
                                                                <span class="fs-14 <?= $textClasses[$i % 4] ?> d-block"><?= htmlspecialchars($p['product_name']) ?></span>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--**********************************
        Content body end
    ***********************************-->

    <!--**********************************
        Footer start
    ***********************************-->
    <?php require_once "inc/footer.php"?>
    <!--**********************************
        Footer end
    ***********************************-->

    <!--**********************************
       Support ticket button start
    ***********************************-->

    <!--**********************************
       Support ticket button end
    ***********************************-->


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
        /*  testimonial one function by = owl.carousel.js */
        /*  testimonial one function by = owl.carousel.js */
        jQuery('.testimonial-one').owlCarousel({
            // rtl:true,
            loop: true,
            margin: 10,
            nav: false,
            center: true,
            dots: false,
            navText: ['<i class="fa fa-caret-left"></i>', '<i class="fa fa-caret-right"></i>'],
            responsive: {
                0: {
                    items: 2
                },
                400: {
                    items: 3
                },
                700: {
                    items: 5
                },
                991: {
                    items: 6
                },

                1200: {
                    items: 4
                },
                1600: {
                    items: 5
                }
            }
        })
    }

    jQuery(window).on('load', function () {
        setTimeout(function () {
            carouselReview();
        }, 1000);
    });




    /* ==================================================================
       Real-data charts (built from the values PHP pulled from the DB)
       ================================================================== */

    // ---- monthly order count / revenue (used by both the small sparkline and the overview chart)
    const monthlyLabels  = <?= json_encode($monthlyLabels) ?>;
    const monthlyCounts  = <?= json_encode($monthlyCounts) ?>;
    const monthlyRevenue = <?= json_encode($monthlyRevenue) ?>;

    // ---- small revenue sparkline in the "فروش کل وبسایت" card
    const revenueCtx = document.getElementById('dashRevenueChart');
    if (revenueCtx && monthlyRevenue.length) {
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: monthlyLabels,
                datasets: [{
                    data: monthlyRevenue,
                    borderColor: '#2BC155',
                    backgroundColor: 'rgba(43,193,85,0.15)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0,
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: false },
                scales: {
                    xAxes: [{ display: false }],
                    yAxes: [{ display: false }]
                },
                tooltips: { enabled: true }
            }
        });
    }

    // ---- small sparkline for the best-selling product's monthly quantity
    const topProductLabels = <?= json_encode($topProductTrendLabels) ?>;
    const topProductQty    = <?= json_encode($topProductTrendQty) ?>;
    const topProductCtx = document.getElementById('dashTopProductChart');
    if (topProductCtx && topProductQty.length) {
        new Chart(topProductCtx, {
            type: 'bar',
            data: {
                labels: topProductLabels,
                datasets: [{
                    data: topProductQty,
                    backgroundColor: '#1EB6E7',
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: false },
                scales: {
                    xAxes: [{ display: false }],
                    yAxes: [{ display: false }]
                }
            }
        });
    }

    // ---- orders overview (ApexCharts) with a toggle between count & revenue
    const overviewEl = document.querySelector('#chartOrdersOverview');
    if (overviewEl && monthlyLabels.length) {
        let overviewChart = new ApexCharts(overviewEl, {
            chart: { type: 'bar', height: 280, toolbar: { show: false } },
            series: [{ name: 'تعداد سفارش', data: monthlyCounts }],
            xaxis: { categories: monthlyLabels },
            colors: ['#3E4954'],
            plotOptions: { bar: { borderRadius: 6, columnWidth: '45%' } },
            dataLabels: { enabled: false }
        });
        overviewChart.render();

        const switchCount   = document.getElementById('customSwitch11');
        const switchRevenue = document.getElementById('customSwitch12');

        function showCountSeries() {
            overviewChart.updateSeries([{ name: 'تعداد سفارش', data: monthlyCounts }]);
        }
        function showRevenueSeries() {
            overviewChart.updateSeries([{ name: 'مبلغ فروش (تومان)', data: monthlyRevenue }]);
        }

        if (switchCount && switchRevenue) {
            switchCount.addEventListener('change', function () {
                if (this.checked) {
                    switchRevenue.checked = false;
                    showCountSeries();
                }
            });
            switchRevenue.addEventListener('change', function () {
                if (this.checked) {
                    switchCount.checked = false;
                    showRevenueSeries();
                }
            });
        }
    }

    let lastPrice = null;

    async function loadUSD() {
        const el = document.getElementById("usd");

        try {
            const res = await fetch('api/usd.php');

            if (!res.ok) {
                // e.g. 404 "no_data_yet" or 500 db error from usd.php
                let reason = res.status;
                try {
                    const errBody = await res.json();
                    if (errBody && errBody.error) reason = errBody.error;
                } catch (_) {}
                console.warn("usd.php returned an error:", reason);
                el.innerText = "در حال دریافت اطلاعات...";
                return;
            }

            const data = await res.json();

            // Guard: make sure price is actually a usable number
            const price = Number(data.price);
            if (!data || data.price === undefined || data.price === null || Number.isNaN(price)) {
                console.warn("usd.php returned unexpected payload:", data);
                el.innerText = "داده نامعتبر";
                return;
            }

            // 💥 رنگ بر اساس رشد یا کاهش
            if (lastPrice !== null) {
                if (price > lastPrice) {
                    el.classList.remove("text-danger");
                    el.classList.add("text-success");
                } else if (price < lastPrice) {
                    el.classList.remove("text-success");
                    el.classList.add("text-danger");
                }
            }

            lastPrice = price;

            // 💰 قیمت
            el.innerText = new Intl.NumberFormat('fa-IR').format(price) + " تومان";

            // 🕒 زمان
            if (data.time) {
                document.getElementById("usd-time").innerText =
                    "آپدیت: " + new Date(data.time).toLocaleTimeString('fa-IR');
            }

            // 📊 high / low
            if (data.high) {
                document.getElementById("high").innerText =
                    "High: " + new Intl.NumberFormat('fa-IR').format(data.high);
            }

            if (data.low) {
                document.getElementById("low").innerText =
                    "Low: " + new Intl.NumberFormat('fa-IR').format(data.low);
            }

        } catch (e) {
            console.error("API error", e);
            el.innerText = "خطا در اتصال";
        }
    }

    loadUSD();
    setInterval(loadUSD, 5000);
</script>
</body>

</html>