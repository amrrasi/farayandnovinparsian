<?php
require_once "inc/check.php";

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {

    $id = (int)$_GET['delete'];

    $stmt = $mysqli->prepare("
        UPDATE page SET deleted = 1 WHERE id = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: page_list.php?deleted=1");
    exit;
}


$limit = 10;
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page  = max(1, $page);

$offset = ($page - 1) * $limit;

$search = trim($_GET['search'] ?? '');
$cat    = (int)($_GET['cat'] ?? 0);


$where = "WHERE p.deleted = 0";
$params = [];
$types = "";

if ($search !== '') {
    $where .= " AND p.namefull LIKE ?";
    $params[] = "%$search%";
    $types .= "s";
}

if ($cat > 0) {
    $where .= " AND p.parent_id = ?";
    $params[] = $cat;
    $types .= "i";
}


$countSQL = "
    SELECT COUNT(*) as total
    FROM page p
    $where
";

$stmt = $mysqli->prepare($countSQL);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['total'];

$totalPages = ceil($total / $limit);


$sql = "
    SELECT p.*, pm.name AS menu
    FROM page p
    LEFT JOIN menu pm ON pm.id = p.parent_id
    $where
    ORDER BY p.id DESC
    LIMIT $limit OFFSET $offset
";

$stmt = $mysqli->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();


$cats = $mysqli->query("
    SELECT id, name FROM menu WHERE deleted = 0
");
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
<?php if (isset($_GET['deleted'])): ?>
    <div class="alert alert-success">محصول حذف شد</div>
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
                <h4>لیست صفحات</h4>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">جدول کلی</li>
                </ol>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">

                        <div class="card-header">
                            <h4 class="card-title">لیست صفحات</h4>
                            <a href="page_add.php">
                                <button type="button" class="btn btn-primary">افزودن صفحه جدید</button>
                            </a>
                        </div>

                        <div class="card-body">

                            <!-- 🔍 SEARCH + FILTER -->
                            <form method="GET" class="mb-3 d-flex gap-2">

                                <input type="text"
                                       name="search"
                                       class="form-control"
                                       placeholder="جستجوی نام صفحه..."
                                       value="<?= htmlspecialchars($search ?? '') ?>">

                                <select name="cat" class="form-control">
                                    <option value="0">همه دسته‌ها</option>

                                    <?php while($c = $cats->fetch_assoc()): ?>
                                        <option value="<?= $c['id'] ?>"
                                            <?= (isset($cat) && $cat == $c['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($c['name']) ?>
                                        </option>
                                    <?php endwhile; ?>

                                </select>

                                <button class="btn btn-primary">
                                    فیلتر
                                </button>

                            </form>


                            <form method="POST">

                                <div class="table-responsive">

                                    <table class="table table-responsive-md">

                                        <thead>
                                        <tr>
                                            <th>
                                                <input type="checkbox" id="checkAll">
                                            </th>

                                            <th>#</th>
                                            <th>نام صفحه (در منو)</th>
                                            <th>دسته بندی</th>
                                            <th>وضعیت</th>
                                            <th>بازدید</th>
                                            <th>عملیات</th>
                                        </tr>
                                        </thead>

                                        <tbody>

                                        <?php $i = $offset + 1; ?>

                                        <?php while($row = $result->fetch_assoc()): ?>

                                            <tr>

                                                <td>
                                                    <input type="checkbox"
                                                           name="ids[]"
                                                           value="<?= $row['id'] ?>">
                                                </td>

                                                <td>
                                                    <strong><?= $i++ ?></strong>
                                                </td>

                                                <td>
                                                    <?= htmlspecialchars($row['nameinmenu']) ?>
                                                </td>

                                                <td>
                                                    <?= htmlspecialchars($row['menu_name'] ?? '-') ?>
                                                </td>

                                                <td>
                                                    <?php if ($row['active'] == 1): ?>
                                                        <span class="badge light badge-success">فعال</span>
                                                    <?php else: ?>
                                                        <span class="badge light badge-danger">غیرفعال</span>
                                                    <?php endif; ?>
                                                </td>

                                                <td>
                                                    <?= (int)$row['visit'] ?>
                                                </td>

                                                <td>

                                                    <div class="dropdown">
                                                        <button class="btn btn-success light sharp"
                                                                data-toggle="dropdown">
                                                            ⋮
                                                        </button>

                                                        <div class="dropdown-menu dropdown-menu-right">

                                                            <a class="dropdown-item"
                                                               href="page_edit.php?id=<?= $row['id'] ?>">
                                                                ویرایش
                                                            </a>

                                                            <a class="dropdown-item text-danger"
                                                               onclick="return confirm('حذف شود؟')"
                                                               href="page_list.php?delete=<?= $row['id'] ?>">
                                                                حذف
                                                            </a>

                                                        </div>
                                                    </div>

                                                </td>

                                            </tr>

                                        <?php endwhile; ?>

                                        </tbody>

                                    </table>

                                </div>

                            </form>

                            <!-- 📄 PAGINATION -->
                            <nav class="mt-3">

                                <ul class="pagination">

                                    <?php for($p=1; $p <= $totalPages; $p++): ?>

                                        <li class="page-item <?= ($p == $page) ? 'active' : '' ?>">

                                            <a class="page-link"
                                               href="?page=<?= $p ?>&search=<?= urlencode($search ?? '') ?>&cat=<?= $cat ?? 0 ?>">
                                                <?= $p ?>
                                            </a>

                                        </li>

                                    <?php endfor; ?>

                                </ul>

                            </nav>

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
    document.getElementById('checkAll').addEventListener('change', function () {
        let checkboxes = document.querySelectorAll('input[name="ids[]"]');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });
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