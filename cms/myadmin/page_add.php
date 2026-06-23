<?php
require_once "inc/check.php";

$error = false;
$text = '';

$parent_id = '';
$namefull = '';
$nameinmenu = '';
$thumb = '';
$body = '';
$myorder = 0;
$abstract = '';
$active = 1;
$keyword = '';
$description = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['Form'] ?? '') === "Submitted") {
    $parent_id = trim($_POST['parent_id'] ?? '');
    $namefull = trim($_POST['namefull'] ?? '');
    $nameinmenu = trim($_POST['nameinmenu'] ?? '');
    $body = trim($_POST['body'] ?? '');
    $myorder = intval($_POST['myorder'] ?? 0);
    $abstract = trim($_POST['abstract'] ?? '');
    $active = intval($_POST['active'] ?? 1);
    $keyword = trim($_POST['keyword'] ?? '');
    $description = trim($_POST['description'] ?? '');

    $uploadDir = realpath(__DIR__ . "/../myupload/page_image") . "/";

    if (!is_dir($uploadDir)) {
        throw new Exception("پوشه آپلود پیدا نشد");
    }

    /* -------------------------
       UPLOAD FILE
    --------------------------*/
    $thumbnailPath = null;

    if (!empty($_FILES['thumb']['name'])) {

        if ($_FILES['thumb']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("خطا در آپلود فایل");
        }

        $ext = strtolower(pathinfo($_FILES['thumb']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($ext, $allowed, true)) {
            throw new Exception("فرمت تصویر نامعتبر است");
        }

        $safeName = uniqid('thumb_', true) . '.' . $ext;
        $target = $uploadDir . $safeName;

        if (!move_uploaded_file($_FILES['thumb']['tmp_name'], $target)) {
            throw new Exception("آپلود فایل ناموفق بود");
        }

        $thumbnailPath = "cms/myupload/page_image/" . $safeName;
    }

    if ($namefull === '' || $nameinmenu === '') {
        $error = true;
        $text = "لطفا نام کامل و نام در منو را وارد کنید.";
    }

    if (!$error) {
        $thumbPath = $thumbnailPath ? $thumbnailPath : null;

        $stmt = $mysqli->prepare("INSERT INTO `page` 
        (`parent_id`,`namefull`,`nameinmenu`,`thumb`,`body`,`myorder`,`abstract`,`active`,`keyword`,`description`) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sssssissss", $parent_id, $namefull, $nameinmenu, $thumbPath, $body, $myorder, $abstract, $active, $keyword, $description);
            if ($stmt->execute()) {
                $text = "صفحه با موفقیت اضافه شد.";
                // پاکسازی فرم
                $parent_id = $namefull = $nameinmenu = $body = $abstract = $keyword = $description = '';
                $active = 1;
                $myorder = 0;
            } else {
                $error = true;
                $text = "خطا در ثبت اطلاعات: " . $stmt->error;
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
    <title><?php echo $global_setting_array['website_name_per'] ?></title>
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="images/favicon.jpg">
    <link href="vendor/jqvmap/css/jqvmap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="vendor/chartist/css/chartist.min.css">
    <!-- Vectormap -->
    <link href="vendor/jqvmap/css/jqvmap.min.css" rel="stylesheet">
    <link href="vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
    <link rel="stylesheet" href="vendor/select2/css/select2.min.css">
    <link href="css/style.css" rel="stylesheet">
    <link href="vendor/owl-carousel/owl.carousel.css" rel="stylesheet">

    <script src="//cdn.ckeditor.com/4.7.0/full/ckeditor.js"></script>

    <style>
        .desc-input-group {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
        }

        .desc-input-group input[type="text"] {
            flex: 1;
            margin-left: 10px;
        }

        .desc-btn {
            min-width: 34px;
            height: 34px;
            border-radius: 4px;
            font-size: 20px;
            line-height: 1;
            border: none;
            color: #fff;
            cursor: pointer;
            padding: 0 10px;
            margin-left: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .desc-btn.add {
            background-color: #28a745;
        }

        .desc-btn.remove {
            background-color: #dc3545;
        }
    </style>
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
                <h4>صفحات</h4>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">مدیریت صفحات</li>
                </ol>
            </div>
            <?php if ($text): ?>
                <div class="alert alert-<?php echo $error ? 'danger' : 'success'; ?> mt-3">
                    <?php echo htmlspecialchars($text); ?>
                </div>
            <?php endif; ?>
            <!-- row -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">ورود اطلاعات صفحه</h4>
                        </div>
                        <div class="card-body">
                            <div class="basic-form">
                                <form method="post" enctype="multipart/form-data" action="">
                                    <input type="hidden" name="Form" value="Submitted">

                                    <div class="form-group mb-4">وضعیت:
                                        <label class="radio-inline mr-3">
                                            <input type="radio" name="active"
                                                   value="1" <?php if ($active == 1) echo 'checked'; ?>> فعال
                                        </label>
                                        <label class="radio-inline mr-3">
                                            <input type="radio" name="active"
                                                   value="0" <?php if ($active == 0) echo 'checked'; ?>> غیرفعال
                                        </label>
                                    </div>


                                    <div class="form-group mb-4">
                                        <label class="mb-4 select2-label" for="id_label_single">
                                            <label>انتخاب منوی صفحه</label>
                                            <?php
                                            $options = [];
                                            $stmt = $mysqli->prepare("SELECT `id`, `name` FROM menu WHERE `active` = '1' AND `deleted` = '0' ORDER BY id ASC");
                                            $stmt->execute();
                                            $resultMenu = $stmt->get_result();
                                            while ($row = $resultMenu->fetch_assoc()) {
                                                $options[] = $row;
                                            }
                                            $stmt->close();
                                            ?>
                                            <select name="parent_id" class="select2-with-label-single js-states d-block"
                                                    id="id_label_single" required>
                                                <option value="">-- انتخاب کنید --</option>
                                                <?php foreach ($options as $opt): ?>
                                                    <option value="<?php echo htmlspecialchars($opt['id']); ?>" <?php if ($parent_id == $opt['id']) echo 'selected'; ?>>
                                                        <?php echo htmlspecialchars($opt['name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </label>
                                    </div>

                                    <div class="form-group mb-4">
                                        <label>نام کامل</label>
                                        <input type="text" name="namefull" class="form-control" required
                                               value="<?php echo htmlspecialchars($namefull); ?>">
                                    </div>

                                    <div class="form-group mb-4">
                                        <label>نام در منو</label>
                                        <input type="text" name="nameinmenu" class="form-control" required
                                               value="<?php echo htmlspecialchars($nameinmenu); ?>">
                                    </div>

                                    <div class="form-group mb-4">
                                        <label>متن کامل</label>
                                        <textarea class="form-control" name="body" id="body" rows="10"><?php echo htmlspecialchars($body); ?></textarea>
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
                                    <!-- لود CKEditor -->
                                    <script src="https://cdn.ckeditor.com/4.22.1/full/ckeditor.js"></script>
                                    <script>
                                        document.addEventListener("DOMContentLoaded", function () {
                                            CKEDITOR.replace('body', {
                                                allowedContent: true,
                                                versionCheck: false
                                            });
                                        });
                                    </script>

                                    <div class="form-group mb-4">
                                        <label>متن خلاصه</label>
                                        <textarea name="abstract" class="form-control"
                                                  rows="3"><?php echo htmlspecialchars($abstract); ?></textarea>
                                    </div>

                                    <div class="form-group mb-4">
                                        <label>کلمات کلیدی</label>
                                        <input type="text" name="keyword" class="form-control"
                                               value="<?php echo htmlspecialchars($keyword); ?>">
                                    </div>

                                    <div class="form-group mb-4">
                                        <label>توضیحات کلیدی</label>
                                        <input type="text" name="description" class="form-control"
                                               value="<?php echo htmlspecialchars($description); ?>">
                                    </div>

                                    <div class="form-group mb-4">
                                        <label>ترتیب نمایش</label>
                                        <input type="number" name="myorder" class="form-control"
                                               value="<?php echo htmlspecialchars($myorder); ?>">
                                    </div>

                                    <div class="form-group mb-4">
                                        <label>تصویر شاخص (Thumb)</label>
                                        <input type="file" name="thumb" class="form-control-file" accept="image/*">
                                    </div>



                                    <button type="submit" class="btn btn-primary">ثبت صفحه</button>
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

<script src="vendor/select2/js/select2.full.min.js"></script>
<script src="js/plugins-init/select2-init.js"></script>

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
                0: {items: 2},
                400: {items: 3},
                700: {items: 5},
                991: {items: 6},
                1200: {items: 4},
                1600: {items: 5}
            }
        })
    }

    jQuery(window).on('load', function () {
        setTimeout(function () {
            carouselReview();
        }, 1000);
    });

    document.addEventListener('DOMContentLoaded', function () {
        const thumbnailInput = document.getElementById('thumbnailInput');
        const thumbnailLabel = thumbnailInput.nextElementSibling;
        thumbnailInput.addEventListener('change', function () {
            let fileName = thumbnailInput.files.length > 0 ? thumbnailInput.files[0].name : "انتخاب فایل";
            thumbnailLabel.textContent = fileName;
        });

        // نمایش نام فایل انتخاب شده در label ویدئو
        const videoInput = document.getElementById('videoInput');
        const videoLabel = videoInput.nextElementSibling;
        videoInput.addEventListener('change', function () {
            let fileName = videoInput.files.length > 0 ? videoInput.files[0].name : "انتخاب فایل";
            videoLabel.textContent = fileName;
        });

        const wrapper = document.getElementById('attr-wrapper');

        wrapper.addEventListener('click', function (e) {
            const target = e.target;
            if (target.classList.contains('add')) {
                const newGroup = document.createElement('div');
                newGroup.className = 'attr-group d-flex align-items-center mb-2';
                newGroup.innerHTML = `
                <input type="text" name="attribute[]" value="" class="form-control input-default" placeholder="ویژگی" />
                <button type="button" class="attr-btn add btn btn-success ml-2" title="افزودن ویژگی">+</button>
                <button type="button" class="attr-btn remove btn btn-danger ml-2" title="حذف ویژگی">−</button>
            `;
                target.closest('.attr-group').insertAdjacentElement('afterend', newGroup);
            } else if (target.classList.contains('remove')) {
                const groups = wrapper.querySelectorAll('.attr-group');
                if (groups.length > 1) {
                    target.closest('.attr-group').remove();
                } else {
                    target.closest('.attr-group').querySelector('input').value = '';
                }
            }
        });
    });
</script>

</body>

</html>