<?php

require_once "inc/check.php";

$error = false;
$text = '';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    die('محصول نامعتبر است.');
}

/*
|--------------------------------------------------------------------------
| LOAD PRODUCT
|--------------------------------------------------------------------------
*/

$stmt = $mysqli->prepare("SELECT * FROM product WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    die('محصول یافت نشد.');
}

$name = $product['name'];
$seoTitle = $product['seo_title'];
$seoSlug = $product['seo_slug'];
$seoDescription = $product['seo_description'];
$seoKeywords = $product['seo_keywords'];
$description = $product['description'];
$price = (int)$product['price'];
$active = (int)$product['active'];
$myorder = (int)$product['myorder'];
$productMenu = (int)$product['product_menu_id'];
$attributes = json_decode($product['attribute'], true) ?: [];
$currentThumbnail = $product['thumbnail'];

/*
|--------------------------------------------------------------------------
| CSRF
|--------------------------------------------------------------------------
*/

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/*
|--------------------------------------------------------------------------
| UPDATE
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            throw new Exception('درخواست نامعتبر است.');
        }

        $name = trim($_POST['name'] ?? '');
        $seoTitle = trim($_POST['seo_title'] ?? '');
        $seoSlug = trim($_POST['seo_slug'] ?? '');
        $seoDescription = trim($_POST['seo_description'] ?? '');
        $seoKeywords = trim($_POST['seo_keywords'] ?? '');
        $description = trim($_POST['description'] ?? '');

        $price = (int)preg_replace('/\D/', '', $_POST['price'] ?? 0);
        $active = (int)($_POST['active'] ?? 1);
        $myorder = (int)($_POST['myorder'] ?? 0);
        $productMenu = (int)($_POST['product_menu'] ?? 0);

        $attributes = array_values(array_filter(array_map('trim', $_POST['attribute'] ?? [])));

        if (mb_strlen($name) < 3) {
            throw new Exception('نام محصول کوتاه است.');
        }

        if ($productMenu <= 0) {
            throw new Exception('دسته‌بندی انتخاب نشده است.');
        }

        if ($price < 0) {
            throw new Exception('قیمت نامعتبر است.');
        }

        if (empty($seoTitle)) $seoTitle = $name;
        if (empty($seoSlug)) $seoSlug = slugify($name);

        if (empty($seoDescription)) {
            $seoDescription = mb_substr(strip_tags($description), 0, 160, 'UTF-8');
        }

        /*
        |--------------------------------------------------------------------------
        | UNIQUE SLUG
        |--------------------------------------------------------------------------
        */

        $checkSlug = $mysqli->prepare("
            SELECT id FROM product
            WHERE seo_slug = ? AND id != ?
            LIMIT 1
        ");
        $checkSlug->bind_param("si", $seoSlug, $id);
        $checkSlug->execute();
        $checkSlug->store_result();

        if ($checkSlug->num_rows > 0) {
            $seoSlug .= '-' . time();
        }

        /*
        |--------------------------------------------------------------------------
        | IMAGE (OPTIONAL)
        |--------------------------------------------------------------------------
        */

        $thumbnail = $currentThumbnail;

        if (!empty($_FILES['thumbnail']['name'])) {

            $imageInfo = getimagesize($_FILES['thumbnail']['tmp_name']);

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

            $uploadDir = __DIR__ . '/../myupload/product_image/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $fileName = uniqid('product_', true) . '.' . $allowed[$mime];
            $target = $uploadDir . $fileName;

            if (!move_uploaded_file($_FILES['thumbnail']['tmp_name'], $target)) {
                throw new Exception('آپلود ناموفق بود.');
            }

            if ($currentThumbnail && file_exists(__DIR__ . '/../' . $currentThumbnail)) {
                unlink(__DIR__ . '/../' . $currentThumbnail);
            }

            $thumbnail = 'myupload/product_image/' . $fileName;
        }

        $attributesJson = json_encode($attributes, JSON_UNESCAPED_UNICODE);

        /*
        |--------------------------------------------------------------------------
        | UPDATE DB
        |--------------------------------------------------------------------------
        */

        $mysqli->begin_transaction();

        $stmt = $mysqli->prepare("
            UPDATE product SET
                product_menu_id = ?,
                name = ?,
                seo_title = ?,
                seo_slug = ?,
                seo_description = ?,
                seo_keywords = ?,
                attribute = ?,
                thumbnail = ?,
                description = ?,
                price = ?,
                active = ?,
                myorder = ?
            WHERE id = ?
        ");

        $stmt->bind_param(
            "issssssssiiii",
            $productMenu,
            $name,
            $seoTitle,
            $seoSlug,
            $seoDescription,
            $seoKeywords,
            $attributesJson,
            $thumbnail,
            $description,
            $price,
            $active,
            $myorder,
            $id
        );

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }

        $mysqli->commit();

        $text = "محصول با موفقیت ویرایش شد.";

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
    <title><?php echo setting('site_name') ?></title>
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
                <h4>محصولات</h4>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">مدیریت محصولات</li>
                </ol>
            </div>
            <?php if ($text): ?>
                <div class="alert alert-<?= $error ? 'danger' : 'success' ?> mt-3">
                    <?= htmlspecialchars($text) ?>
                </div>

                <div class="mt-3 text-center">
                    <a href="product_list.php" class="btn btn-primary">
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
                            <h4 class="card-title">ویرایش اطلاعات محصول</h4>
                        </div>
                        <div class="card-body">
                            <div class="basic-form">
                                <!-- داخل body فقط فرم -->

                                <form method="post" enctype="multipart/form-data">

                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                                    <div class="row">

                                        <!-- CATEGORY -->
                                        <div class="col-md-6 mb-3">
                                            <label>دسته بندی</label>
                                            <select name="product_menu" class="form-control select2-selection__rendered" required>
                                                <option value="">انتخاب کنید</option>
                                                <?php
                                                $menus = $mysqli->query("SELECT id,name FROM product_menu WHERE deleted=0");
                                                while ($m = $menus->fetch_assoc()):
                                                    ?>
                                                    <option value="<?= $m['id'] ?>" <?= $productMenu == $m['id'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($m['name']) ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>

                                        <!-- NAME -->
                                        <div class="col-md-6 mb-3">
                                            <label>نام محصول</label>
                                            <input type="text" name="name" id="name" class="form-control"
                                                   value="<?= htmlspecialchars($name) ?>">
                                        </div>

                                        <!-- PRICE -->
                                        <div class="col-md-6 mb-3">
                                            <label>قیمت</label>
                                            <input type="text" id="price_format" class="form-control"
                                                   value="<?= number_format($price) ?>">
                                            <input type="hidden" name="price" id="price" value="<?= $price ?>">
                                        </div>

                                        <!-- ORDER -->
                                        <div class="col-md-3 mb-3">
                                            <label>ترتیب نمایش</label>
                                            <input type="number" name="myorder" class="form-control"
                                                   value="<?= $myorder ?>">
                                        </div>

                                        <!-- ACTIVE -->
                                        <div class="col-md-3 mb-3">
                                            <label>وضعیت</label>
                                            <select name="active" class="form-control">
                                                <option value="1" <?= $active == 1 ? 'selected' : '' ?>>فعال</option>
                                                <option value="0" <?= $active == 0 ? 'selected' : '' ?>>غیرفعال</option>
                                            </select>
                                        </div>

                                        <!-- IMAGE -->
                                        <div class="col-md-6 mb-3">
                                            <label>تصویر محصول</label>
                                            <input type="file" name="thumbnail" id="thumbnail" class="form-control">
                                        </div>

                                        <div class="col-md-6 mb-3 text-center">
                                            <img src="../<?= $currentThumbnail ?>" id="preview"
                                                 style="max-height:150px;">
                                        </div>

                                        <!-- ATTRIBUTES -->
                                        <div class="col-12 mb-3">
                                            <label>ویژگی‌ها</label>

                                            <div id="attributesWrapper">

                                                <?php foreach ($attributes as $a): ?>
                                                    <div class="input-group mb-2 attribute-item">
                                                        <span class="input-group-text drag-handle">☰</span>
                                                        <input type="text" name="attribute[]" class="form-control"
                                                               value="<?= htmlspecialchars($a) ?>">
                                                        <button type="button" class="btn btn-danger removeAttribute">
                                                            حذف
                                                        </button>
                                                    </div>
                                                <?php endforeach; ?>

                                            </div>

                                            <button type="button" id="addAttribute" class="btn btn-success btn-sm">
                                                افزودن ویژگی
                                            </button>
                                        </div>

                                        <!-- DESCRIPTION -->
                                        <div class="form-group mb-4">
                                            <label>توضیحات</label>
                                            <textarea class="form-control" name="description" id="description"
                                                      rows="10"><?php echo htmlspecialchars($description); ?></textarea>
                                        </div>

                                        <!-- لود CKEditor -->
                                        <script src="https://cdn.ckeditor.com/4.22.1/full/ckeditor.js"></script>
                                        <script>
                                            document.addEventListener("DOMContentLoaded", function () {
                                                CKEDITOR.replace('description', {
                                                    allowedContent: true,
                                                    versionCheck: false
                                                });
                                            });
                                        </script>

                                        <!-- SEO -->
                                        <div class="col-12">
                                            <h5>سئو</h5>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label>تایتل سئو | SEO Title</label>
                                            <input type="text" name="seo_title" id="seo_title" class="form-control"
                                                   value="<?= htmlspecialchars($seoTitle) ?>">
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label>لینک Slug</label>
                                            <input type="text" name="seo_slug" id="seo_slug" class="form-control"
                                                   value="<?= htmlspecialchars($seoSlug) ?>">
                                            <small id="slugStatus"></small>
                                        </div>

                                        <div class="col-12 mb-3">
                                            <label>توضیحات SEO</label>
                                            <textarea name="seo_description" id="seo_description" class="form-control">
                                            <?= htmlspecialchars($seoDescription) ?>
                                            </textarea>
                                        </div>

                                        <div class="col-12 mb-3">
                                            <label>کلمات کلیدی SEO</label>
                                            <input type="text" name="seo_keywords" class="form-control"
                                                   value="<?= htmlspecialchars($seoKeywords) ?>">
                                        </div>

                                        <div class="col-12 text-center mt-3">
                                            <button class="btn btn-primary px-5">ذخیره تغییرات</button>
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

    // DRAG
    new Sortable(document.getElementById('attributesWrapper'), {
        handle: '.drag-handle',
        animation: 150
    });

    // ADD
    $('#addAttribute').click(function () {
        $('#attributesWrapper').append(`
        <div class="input-group mb-2 attribute-item">
            <span class="input-group-text drag-handle">☰</span>
            <input type="text" name="attribute[]" class="form-control">
            <button type="button" class="btn btn-danger removeAttribute">حذف</button>
        </div>
    `);
    });

    // REMOVE
    $(document).on('click', '.removeAttribute', function () {
        $(this).closest('.attribute-item').remove();
    });

    // PRICE
    $('#price_format').on('input', function () {
        let v = this.value.replace(/\D/g, '');
        $('#price').val(v);
        this.value = Number(v).toLocaleString();
    });

    // IMAGE PREVIEW
    $('#thumbnail').on('change', function () {
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

    $('.select2').select2({
        width: '100%'
    });

    /* CKEditor */
    ClassicEditor.create(
        document.querySelector('#editor')
    );

    /* Preview */
    $('#thumbnail').on('change', function () {

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

    /* Dynamic Attributes */

    $('#addAttribute').on('click', function () {

        $('#attributesWrapper').append(`
        <div class="input-group mb-2">
            <input
                type="text"
                name="attribute[]"
                class="form-control"
            >

            <button
                type="button"
                class="btn btn-danger removeAttribute"
            >
                حذف
            </button>
        </div>
    `);

    });

    $(document).on(
        'click',
        '.removeAttribute',
        function () {
            $(this)
                .closest('.input-group')
                .remove();
        }
    );

    /* Price Format */

    $('#price_format').on('input', function () {

        let value =
            this.value.replace(/\D/g, '');

        $('#price').val(value);

        this.value =
            Number(value)
                .toLocaleString('en-US');

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


    $('#name').on('keyup', function () {

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

<!--<script>-->
<!--    function carouselReview() {-->
<!--        jQuery('.testimonial-one').owlCarousel({-->
<!--            loop: true,-->
<!--            margin: 10,-->
<!--            nav: false,-->
<!--            center: true,-->
<!--            dots: false,-->
<!--            navText: ['<i class="fa fa-caret-left"></i>', '<i class="fa fa-caret-right"></i>'],-->
<!--            responsive: {-->
<!--                0: { items: 2 },-->
<!--                400: { items: 3 },-->
<!--                700: { items: 5 },-->
<!--                991: { items: 6 },-->
<!--                1200: { items: 4 },-->
<!--                1600: { items: 5 }-->
<!--            }-->
<!--        })-->
<!--    }-->
<!---->
<!--    jQuery(window).on('load', function () {-->
<!--        setTimeout(function () {-->
<!--            carouselReview();-->
<!--        }, 1000);-->
<!--    });-->
<!---->
<!--    document.addEventListener('DOMContentLoaded', function () {-->
<!--        // نمایش نام فایل انتخاب شده در label تامبنیل-->
<!--        const thumbnailInput = document.getElementById('thumbnailInput');-->
<!--        const thumbnailLabel = thumbnailInput.nextElementSibling;-->
<!--        thumbnailInput.addEventListener('change', function () {-->
<!--            let fileName = thumbnailInput.files.length > 0 ? thumbnailInput.files[0].name : "انتخاب فایل";-->
<!--            thumbnailLabel.textContent = fileName;-->
<!--        });-->
<!---->
<!--        // نمایش نام فایل انتخاب شده در label ویدئو-->
<!--        const videoInput = document.getElementById('videoInput');-->
<!--        const videoLabel = videoInput.nextElementSibling;-->
<!--        videoInput.addEventListener('change', function () {-->
<!--            let fileName = videoInput.files.length > 0 ? videoInput.files[0].name : "انتخاب فایل";-->
<!--            videoLabel.textContent = fileName;-->
<!--        });-->
<!---->
<!--        // مدیریت افزودن و حذف ویژگی‌ها-->
<!--        const wrapper = document.getElementById('attr-wrapper');-->
<!---->
<!--        wrapper.addEventListener('click', function(e) {-->
<!--            const target = e.target;-->
<!--            if (target.classList.contains('add')) {-->
<!--                const newGroup = document.createElement('div');-->
<!--                newGroup.className = 'attr-group d-flex align-items-center mb-2';-->
<!--                newGroup.innerHTML = `-->
<!--                <input type="text" name="attribute[]" value="" class="form-control input-default" placeholder="ویژگی" />-->
<!--                <button type="button" class="attr-btn add btn btn-success ml-2" title="افزودن ویژگی">+</button>-->
<!--                <button type="button" class="attr-btn remove btn btn-danger ml-2" title="حذف ویژگی">−</button>-->
<!--            `;-->
<!--                target.closest('.attr-group').insertAdjacentElement('afterend', newGroup);-->
<!--            } else if (target.classList.contains('remove')) {-->
<!--                const groups = wrapper.querySelectorAll('.attr-group');-->
<!--                if (groups.length > 1) {-->
<!--                    target.closest('.attr-group').remove();-->
<!--                } else {-->
<!--                    target.closest('.attr-group').querySelector('input').value = '';-->
<!--                }-->
<!--            }-->
<!--        });-->
<!--    });-->
<!--</script>-->

</body>

</html>
