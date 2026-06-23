<?php

require_once "inc/check.php";

$error = false;
$text = '';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    die(' صفحه نامعتبر است.');
}

/*
|--------------------------------------------------------------------------
| LOAD PRODUCT
|--------------------------------------------------------------------------
*/

$stmt = $mysqli->prepare("SELECT * FROM page WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    die('صفحه یافت نشد.');
}

$namefull = $product['namefull'];
$nameinmenu = $product['nameinmenu'];
$seoTitle = $product['seo_title'];
$seoSlug = $product['seo_slug'];
$seoDescription = $product['seo_description'];
$seoKeywords = $product['seo_keywords'];
$body = $product['body'];
$abstract = $product['abstract'];
$active = (int)$product['active'];
$myorder = (int)$product['myorder'];
$parentMenu = (int)$product['parent_id'];
$currentThumbnail = $product['thumb'];

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            throw new Exception('درخواست نامعتبر است.');
        }

        $namefull = trim($_POST['namefull'] ?? '');
        $nameinmenu = trim($_POST['nameinmenu'] ?? '');
        $seoTitle = trim($_POST['seo_title'] ?? '');
        $seoSlug = trim($_POST['seo_slug'] ?? '');
        $seoDescription = trim($_POST['seo_description'] ?? '');
        $seoKeywords = trim($_POST['seo_keywords'] ?? '');
        $body = trim($_POST['body'] ?? '');
        $abstract = trim($_POST['abstract'] ?? '');

        $active = (int)($_POST['active'] ?? 1);
        $myorder = (int)($_POST['myorder'] ?? 0);
        $parentMenu = (int)($_POST['parent_id'] ?? 0);


        if (mb_strlen($namefull) < 3) {
            throw new Exception('نام صفحه کوتاه است.');
        }

        if ($parentMenu <= 0) {
            throw new Exception('دسته‌بندی انتخاب نشده است.');
        }

        if (empty($seoTitle)) $seoTitle = $namefull;
        if (empty($seoSlug)) $seoSlug = slugify($namefull);

        if (empty($seoDescription)) {
            $seoDescription = mb_substr(strip_tags($abstract), 0, 160, 'UTF-8');
        }

        $checkSlug = $mysqli->prepare("
            SELECT id FROM page
            WHERE seo_slug = ? AND id != ?
            LIMIT 1
        ");
        $checkSlug->bind_param("si", $seoSlug, $id);
        $checkSlug->execute();
        $checkSlug->store_result();

        if ($checkSlug->num_rows > 0) {
            $seoSlug .= '-' . time();
        }

        $thumbnail = $currentThumbnail;

        if (!empty($_FILES['thumb']['name'])) {

            $imageInfo = getimagesize($_FILES['thumb']['tmp_name']);

            if (!$imageInfo) {
                throw new Exception('فایل تصویر نیست.');
            }

            $allowed = [
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/webp' => 'webp'
            ];

            $mime = $imageInfo['mime'];

            if (!isset($allowed[$mime])) {
                throw new Exception('فرمت مجاز نیست.');
            }

            $uploadDir = __DIR__ . '/../myupload/page_image/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $fileName = uniqid('page_', true) . '.' . $allowed[$mime];
            $target = $uploadDir . $fileName;

            if (!move_uploaded_file($_FILES['thumb']['tmp_name'], $target)) {
                throw new Exception('آپلود ناموفق بود.');
            }

            if ($currentThumbnail && file_exists(__DIR__ . '/../' . $currentThumbnail)) {
                unlink(__DIR__ . '/../' . $currentThumbnail);
            }

            $thumbnail = 'myupload/page_image/' . $fileName;
        }

        $mysqli->begin_transaction();

        $stmt = $mysqli->prepare("
            UPDATE page SET
                parent_id = ?,
                namefull = ?,
                nameinmenu = ?,
                seo_title = ?,
                seo_slug = ?,
                seo_description = ?,
                seo_keywords = ?,
                body = ?,
                abstract = ?,
                thumb = ?,
                active = ?,
                myorder = ?
            WHERE id = ?
        ");

        $stmt->bind_param(
                "isssssssssiii",
                $parentMenu,
                $namefull,
                $nameinmenu,
                $seoTitle,
                $seoSlug,
                $seoDescription,
                $seoKeywords,
                $body,
                $abstract,
                $thumbnail,
                $active,
                $myorder,
                $id
        );

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }

        $mysqli->commit();

        $text = "صفحه با موفقیت ویرایش شد.";

    } catch (Exception $e) {

        $mysqli->rollback();
        $error = true;
        $text = $e->getMessage();
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
    <link rel="icon" type="image/png" sizes="16x16" href="images/favicon.png">
    <link href="vendor/jqvmap/css/jqvmap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="vendor/chartist/css/chartist.min.css">
    <!-- Vectormap -->
    <link href="vendor/jqvmap/css/jqvmap.min.css" rel="stylesheet">
    <link href="vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
    <link rel="stylesheet" href="vendor/select2/css/select2.min.css">
    <link href="css/style.css" rel="stylesheet">
    <link href="vendor/owl-carousel/owl.carousel.css" rel="stylesheet">

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
                <div class="alert alert-<?= $error ? 'danger' : 'success' ?> mt-3">
                    <?= htmlspecialchars($text) ?>
                </div>

                <div class="mt-3 text-center">
                    <a href="page_list.php" class="btn btn-primary">
                        <i class="fa fa-arrow-right ml-1"></i>
                        بازگشت به لیست
                    </a>
                </div>
            <?php endif; ?>
            <!-- row -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">ویرایش اطلاعات صفحه</h4>
                        </div>
                        <div class="card-body">
                            <div class="basic-form">

                                <form method="post" enctype="multipart/form-data">

                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                                    <div class="row">

                                        <div class="col-md-6 mb-3">
                                            <label>دسته بندی</label>
                                            <select name="parent_id" class="form-control select2-selection__rendered"
                                                    required>
                                                <option value="">انتخاب کنید</option>
                                                <?php
                                                $menus = $mysqli->query("
                                                SELECT id,name
                                                FROM menu
                                                WHERE deleted = 0
                                                ORDER BY myorder ASC
                                                ");

                                                while ($menu = $menus->fetch_assoc()):
                                                ?>
                                                    <option value="<?= $menu['id'] ?>" <?= $parentMenu == $menu['id'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($menu['name']) ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">نام کامل صفحه</label>
                                            <input
                                                    type="text"
                                                    name="namefull"
                                                    class="form-control"
                                                    required
                                                    value="<?= htmlspecialchars($namefull ?? '') ?>"
                                            >
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">نام کوتاه صفحه (جهت نمایش در منو)</label>
                                            <input
                                                    type="text"
                                                    name="nameinmenu"
                                                    class="form-control"
                                                    required
                                                    value="<?= htmlspecialchars($nameinmenu ?? '') ?>"
                                            >
                                        </div>

                                        <!-- ترتیب -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">ترتیب نمایش</label>

                                            <input
                                                    type="number"
                                                    name="myorder"
                                                    class="form-control"
                                                    value="<?= $myorder ?? 0 ?>"
                                            >
                                        </div>

                                        <!-- وضعیت -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">وضعیت</label>

                                            <select
                                                    name="active"
                                                    class="form-control"
                                            >
                                                <option value="1">فعال</option>
                                                <option value="0">غیرفعال</option>
                                            </select>
                                        </div>

                                        <!-- عکس -->
                                        <div class="col-md-6 mb-3">
                                            <label>تصویر صفحه</label>
                                            <input type="file" name="thumb" id="thumb" class="form-control">
                                        </div>

                                        <div class="col-md-6 mb-3 text-center">
                                            <img src="../<?= $currentThumbnail ?>" id="preview"
                                                 style="max-height:150px;">
                                        </div>


                                        <!-- توضیحات -->
                                        <div class="col-12 mb-3">

                                            <div class="form-group mb-4">
                                                <label>متن کامل صفحه </label>
                                                <textarea class="form-control" name="body" id="body"
                                                          rows="10"><?php echo htmlspecialchars($body); ?></textarea>
                                            </div>

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
                                        </div>

                                        <div class="col-12 mb-3">

                                            <div class="form-group mb-4">
                                                <label>متن خلاصه صفحه </label>
                                                <textarea class="form-control" name="abstract" id="abstract"
                                                          rows="10"><?php echo htmlspecialchars($abstract); ?></textarea>
                                            </div>
                                        </div>

                                        <div class="card mt-4">


                                            <div class="card-header">
                                                <h5 class="mb-0">
                                                    تنظیمات سئو
                                                </h5>
                                            </div>

                                            <div class="card-body">

                                                <div class="mb-3">
                                                    <label>
                                                        عنوان سئو
                                                    </label>

                                                    <input
                                                            type="text"
                                                            id="seo_title"
                                                            name="seo_title"
                                                            class="form-control"
                                                            maxlength="70"
                                                            value="<?= htmlspecialchars($seoTitle) ?>">


                                                    <small id="seoTitleCount">
                                                        0 / 70
                                                    </small>
                                                </div>

                                                <div class="mb-3">
                                                    <label>
                                                        آدرس محصول (Slug)
                                                    </label>

                                                    <input
                                                            type="text"
                                                            id="seo_slug"
                                                            name="seo_slug"
                                                            class="form-control"
                                                            value="<?= htmlspecialchars($seoSlug) ?>">
                                                </div>

                                                <div class="mb-3">
                                                    <label>
                                                        توضیحات سئو
                                                    </label>

                                                    <textarea
                                                            id="seo_description"
                                                            name="seo_description"
                                                            rows="4"
                                                            maxlength="160"
                                                            class="form-control"
                                                            <?= htmlspecialchars($seoDescription) ?>

                                                    ></textarea>

                                                    <small id="seoDescCount">
                                                        0 / 160
                                                    </small>
                                                </div>

                                                <div class="mb-3">
                                                    <label>
                                                        کلمات کلیدی
                                                    </label>

                                                    <input
                                                            type="text"
                                                            name="seo_keywords"
                                                            class="form-control"
                                                            placeholder=""
                                                            value="<?= htmlspecialchars($seoKeywords) ?>">

                                                </div>

                                            </div>


                                        </div>

                                        <div class="card border-primary mt-3">


                                            <div class="card-header">
                                                پیش نمایش گوگل
                                            </div>

                                            <div class="card-body">

                                                <div
                                                        id="googleTitle"
                                                        style="
                                                               color:#1a0dab;
                                                               font-size:20px;
                                                               font-weight:600;
                                                           "
                                                >
                                                    عنوان محصول
                                                </div>

                                                <div
                                                        style="
                                                              color:#006621;
                                                              font-size:14px;
                                                          "
                                                >
                                                    https://site.com/product/
                                                    <span id="googleSlug"></span>
                                                </div>

                                                <div
                                                        id="googleDesc"
                                                        style="
                                                               color:#545454;
                                                               margin-top:5px;
                                                           "
                                                >
                                                    توضیحات محصول...
                                                </div>

                                            </div>


                                        </div>

                                        <!-- csrf -->
                                        <input
                                                type="hidden"
                                                name="csrf_token"
                                                value="<?= $_SESSION['csrf_token'] ?>"
                                        >

                                        <div class="col-12 text-center">

                                            <button
                                                    type="submit"
                                                    class="btn btn-primary px-5"
                                            >
                                                ثبت صفحه
                                            </button>

                                        </div>


                                    </div>

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

<script src="vendor/select2/js/select2.full.min.js"></script>
<script src="js/plugins-init/select2-init.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>

    // IMAGE PREVIEW
    $('#thumb').on('change', function () {
        let r = new FileReader();
        r.onload = e => $('#preview').attr('src', e.target.result);
        r.readAsDataURL(this.files[0]);
    });

    // LIVE SLUG CHECK
    let t;
    $('#seo_slug').on('input', function () {

        clearTimeout(t);

        t = setTimeout(() => {

            $.post('ajax/check_slug.php', {
                slug: $(this).val(),
                id: <?= $id ?>
            }, function (res) {

                $('#slugStatus').text(
                    res.exists ? '❌ گرفته شده' : '✅ آزاد'
                ).css('color', res.exists ? 'red' : 'green');

            }, 'json');

        }, 400);

    });

    /* CKEditor */
    ClassicEditor.create(
        document.querySelector('#editor')
    );

    /* Preview */
    $('#thumb').on('change', function () {

        const file = this.files[0];

        if (!file) return;

        const reader = new FileReader();

        reader.onload = function (e) {

            $('#preview')
                .attr('src', e.target.result)
                .show();

        };

        reader.readAsDataURL(file);

    });

    /* Success Message */

    <?php if(!empty($text) && !$error): ?>

    Swal.fire({
        icon: 'success',
        title: 'موفق',
        text: '<?= addslashes($text) ?>'
    });

    <?php endif; ?>

    <?php if(!empty($text) && $error): ?>

    Swal.fire({
        icon: 'error',
        title: 'خطا',
        text: '<?= addslashes($text) ?>'
    });

    <?php endif; ?>


    $('#namefull').on('keyup', function () {

        let slug = $(this)
            .val()
            .trim()
            .replace(/[^\w\u0600-\u06FF]+/g, '-')
            .replace(/--+/g, '-')
            .toLowerCase();

        $('#seo_slug').val(slug);

    });

    $('#seo_title').on('keyup', function () {

        $('#seoTitleCount').html(
            this.value.length + ' / 70'
        );

        $('#googleTitle').html(
            $(this).val()
        );

    });

    $('#seo_description').on('keyup', function () {

        $('#seoDescCount').html(
            this.value.length + ' / 160'
        );

        $('#googleDesc').html(
            $(this).val()
        );

    });

    $('#seo_slug').on('keyup', function () {

        $('#googleSlug').html(
            $(this).val()
        );

    });

</script>

</body>

</html>
