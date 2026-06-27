<?php
require_once "inc/check.php";

$error = false;
$text = '';

// حذف آیتم
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];

    $stmt = $mysqli->prepare("UPDATE `product_menu` SET `deleted` = '1' WHERE `id` = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: productGroup_list.php?deleted=1");
        exit;
    } else {
        $error = true;
        $text = "خطا در حذف: " . $stmt->error;
    }
}

// تابع بازگشتی برای نمایش ردیف‌های جدول
function renderTableRows($mysqli, &$i, $parent_id = 0, $level = 0) {
    $stmt = $mysqli->prepare("SELECT id, name, description, active, myorder 
                              FROM product_menu 
                              WHERE deleted = 0 AND parent_id = ? 
                              ORDER BY myorder ASC");
    $stmt->bind_param("i", $parent_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        ?>
        <tr>
            <td><?= $i++ ?></td>
            <td><?= str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;— ", $level) . htmlspecialchars($row['name']) ?></td>

            <td>
                <?php
                $description = strip_tags($row['description'] ?? '');

                if (mb_strlen($description, 'UTF-8') > 30) {
                    echo mb_substr($description, 0, 30, 'UTF-8') . '...';
                } else {
                    echo $description;
                }
                ?>
            </td>
            <td>
                <?= $row['active'] ? '<span class="badge badge-success">فعال</span>' : '<span class="badge badge-danger">غیرفعال</span>' ?>
            </td>
            <td><?= (int)$row['myorder'] ?></td>
            <td>
                <a href="productGroup_edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">ویرایش</a>
                <a onclick="return del_confirm();" href="productGroup_list.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger">حذف</a>
            </td>
        </tr>
        <?php
        renderTableRows($mysqli, $i, $row['id'], $level + 1);
    }
}
?>
<!DOCTYPE html>
<html lang="fa">


<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo setting('site_name') ?></title>
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="images/favicon.jpg">
    <link href="vendor/jqvmap/css/jqvmap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="vendor/chartist/css/chartist.min.css">
    <!-- Vectormap -->
    <link href="vendor/jqvmap/css/jqvmap.min.css" rel="stylesheet">
    <link href="vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="vendor/owl-carousel/owl.carousel.css" rel="stylesheet">

</head>
<script>
    function del_confirm() {
        return confirm('آیا برای حذف مطمئن هستید؟');
    }
</script>
<?php if (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
    <div class="alert alert-success">حذف با موفقیت انجام شد</div>
<?php endif; ?>
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
                <h4>لیست منوی محصولات</h4>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">جدول کلی</li>
                </ol>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">منوی محصولات</h4>
                            <a href="productGroup_add.php">
                                <button type="button" class="btn btn-primary">افزودن منوی محصول</button>
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-responsive-md">
                                    <thead>
                                    <tr>
                                        <th class="width80"><strong>ردیف</strong></th>
                                        <th><strong>نام</strong></th>
                                        <th><strong>توضیحات</strong></th>
                                        <th><strong>وضعیت</strong></th>
                                        <th><strong>ترتیب</strong></th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $i = 1;
                                    renderTableRows($mysqli, $i, 0, 0);
                                    if ($i === 1): ?>
                                        <tr><td colspan="7" class="text-center">موردی یافت نشد</td></tr>
                                    <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--**********************************
        List end
    ***********************************-->

    <!--**********************************
        Footer start
    ***********************************-->
  <?php require_once "inc/footer.php" ?>
    <!--**********************************
        Footer end
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
</script>
</body>

</html>