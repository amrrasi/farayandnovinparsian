<?php
require_once "inc/check.php";

$error = false;
$text = '';

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    die("شناسه معتبر نیست.");
}

$stmt = $mysqli->prepare("SELECT * FROM product_menu WHERE id = ? AND deleted = 0");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$menu = $result->fetch_assoc();
$stmt->close();

if (!$menu) {
    die("منوی مورد نظر یافت نشد.");
}

$menus = [];
$stmt = $mysqli->prepare("SELECT id, name, description, parent_id FROM product_menu WHERE deleted = 0 AND id != ? ORDER BY myorder ASC, id ASC");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $menus[] = $row;
}
$stmt->close();

function buildMenuOptions($menus, $parent_id = 0, $level = 0, $selected = 0) {
    $html = '';
    foreach ($menus as $m) {
        if ($m['parent_id'] == $parent_id) {
            $prefix = str_repeat('-- ', $level);
            $isSelected = ($m['id'] == $selected) ? 'selected' : '';
            $html .= '<option value="' . htmlspecialchars($m['id']) . '" ' . $isSelected . '>' . $prefix . htmlspecialchars($m['name']) . '</option>';
            $html .= buildMenuOptions($menus, $m['id'], $level + 1, $selected);
        }
    }
    return $html;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['Form'] === "Submitted") {
    $name = trim($_POST['name'] ?? '');
    $active = $_POST['active'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $myorder = trim($_POST['myorder'] ?? '0');
    $parent_id = intval($_POST['parent_id'] ?? 0);

    if ($name === '') {
        $error = true;
        $text = "لطفا قسمت نام را پر کنید";
    } elseif (!in_array($active, ['0', '1'])) {
        $error = true;
        $text = "وضعیت فعال‌بودن معتبر نیست";
    } else {
        $stmt = $mysqli->prepare("UPDATE product_menu SET name = ?, description = ?, active = ?, myorder = ?, parent_id = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("ssiiii", $name, $description, $active, $myorder, $parent_id, $id);
            if ($stmt->execute()) {
                $text = "منوی پکیج با موفقیت بروزرسانی شد";
                $menu['name'] = $name;
                $menu['description'] = $description;
                $menu['active'] = $active;
                $menu['myorder'] = $myorder;
                $menu['parent_id'] = $parent_id;
            } else {
                $error = true;
                $text = "خطا در بروزرسانی اطلاعات: " . $stmt->error;
            }
        } else {
            $error = true;
            $text = "خطا در آماده‌سازی کوئری: " . $mysqli->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa">


<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo $global_setting_array['name'] ?></title>
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
        form start
    ***********************************-->
    <div class="content-body">
        <div class="container-fluid">
            <div class="page-titles">
                <h4>محصولات</h4>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">مدیریت منوی محصولات</li>
                </ol>
            </div>
            <!-- row -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">ورود اطلاعات منوی محصول</h4>
                        </div>
                        <?php if ($text): ?>
                            <div class="alert alert-<?= $error ? 'danger' : 'success' ?> mt-3">
                                <?= htmlspecialchars($text) ?>
                            </div>

                            <div class="mt-3 text-center">
                                <a href="productGroup_list.php" class="btn btn-primary">
                                    <i class="fa fa-arrow-right ml-1"></i>
                                    بازگشت به لیست
                                </a>
                            </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <div class="basic-form">
                                <form method="post" action="">
                                    <input type="hidden" name="Form" value="Submitted">

                                    <div class="form-group mb-4">وضعیت:
                                        <label class="radio-inline mr-3">
                                            <input type="radio" name="active" value="1" <?= $menu['active'] == '1' ? 'checked' : '' ?>> فعال
                                        </label>
                                        <label class="radio-inline mr-3">
                                            <input type="radio" name="active" value="0" <?= $menu['active'] == '0' ? 'checked' : '' ?>> غیرفعال
                                        </label>
                                    </div>

                                    <div class="form-group col-lg-9 col-sm-12 mb-4">
                                        <div class="row">
                                            <div class="col-3"><label>انتخاب منوی مادر</label></div>
                                            <div class="col-9">
                                                <select name="parent_id" class="form-control input-default">
                                                    <option value="0" <?= $menu['parent_id'] == 0 ? 'selected' : '' ?>>-- منوی اصلی --</option>
                                                    <?php
                                                    echo buildMenuOptions($menus, 0, 0, $menu['parent_id']);
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="form-group col-lg-9 col-sm-12 mb-4">
                                        <div class="row">
                                            <div class="col-3"><label>نام منوی پکیج</label></div>
                                            <div class="col-9">
                                                <input type="text" name="name" class="form-control input-default" value="<?= htmlspecialchars($menu['name']) ?>" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group mb-4">
                                        <label>توضیحات</label>
                                        <textarea class="form-control" name="description" id="description" rows="10"><?php echo htmlspecialchars($menu['description']); ?></textarea>
                                    </div>

                                    <!-- لود CKEditor -->
                                    <script src="https://cdn.ckeditor.com/4.22.1/full/ckeditor.js"></script>
                                    <script>
                                        document.addEventListener("DOMContentLoaded", function() {
                                            CKEDITOR.replace('description', {
                                                allowedContent: true,
                                                versionCheck: false
                                            });
                                        });
                                    </script>

                                    <div class="form-group col-lg-9 col-sm-12 mb-4">
                                        <div class="row">
                                            <div class="col-3"><label>ترتیب</label></div>
                                            <div class="col-3">
                                                <input type="number" name="myorder" class="form-control input-default" min="0" value="<?= (int)$menu['myorder'] ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn light btn-primary">ذخیره تغییرات</button>
                                </form>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--**********************************
        form end
    ***********************************-->

    <!--**********************************
        Footer start
    ***********************************-->
    <div class="footer">
        <div class="copyright">
            <p>کپی رایت © ارائه توسط <?php echo $global_setting_array['website_name_per']?> <?php echo jdate('Y') ?></p>
        </div>
    </div>
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