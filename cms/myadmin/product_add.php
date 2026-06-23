<?php

require_once "inc/check.php";

$error = false;
$text = '';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        if (
            !isset($_POST['csrf_token']) ||
            !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
        ) {
            throw new Exception('درخواست نامعتبر است.');
        }

        $name             = trim($_POST['name'] ?? '');
        $seoTitle         = trim($_POST['seo_title'] ?? '');
        $seoSlug          = trim($_POST['seo_slug'] ?? '');
        $seoDescription   = trim($_POST['seo_description'] ?? '');
        $seoKeywords      = trim($_POST['seo_keywords'] ?? '');

        $description      = trim($_POST['description'] ?? '');
        $price            = (int)preg_replace('/\D/', '', $_POST['price'] ?? 0);
        $active           = (int)($_POST['active'] ?? 1);
        $myorder          = (int)($_POST['myorder'] ?? 0);
        $productMenu      = (int)($_POST['product_menu'] ?? 0);

        $attributes = $_POST['attribute'] ?? [];

        $attributes = array_values(
            array_filter(
                array_map('trim', $attributes)
            )
        );

        if (mb_strlen($name) < 3) {
            throw new Exception('نام محصول کوتاه است.');
        }

        if ($productMenu <= 0) {
            throw new Exception('دسته بندی انتخاب نشده است.');
        }

        if ($price < 0) {
            throw new Exception('قیمت نامعتبر است.');
        }

        if (empty($seoTitle)) {
            $seoTitle = $name;
        }

        if (empty($seoSlug)) {
            $seoSlug = slugify($name);
        }

        if (empty($seoDescription)) {
            $seoDescription = mb_substr(
                strip_tags($description),
                0,
                160,
                'UTF-8'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | UNIQUE SLUG
        |--------------------------------------------------------------------------
        */

        $checkSlug = $mysqli->prepare("
            SELECT id
            FROM product
            WHERE seo_slug = ?
            LIMIT 1
        ");

        $checkSlug->bind_param("s", $seoSlug);
        $checkSlug->execute();
        $checkSlug->store_result();

        if ($checkSlug->num_rows > 0) {
            $seoSlug .= '-' . time();
        }

        /*
        |--------------------------------------------------------------------------
        | IMAGE
        |--------------------------------------------------------------------------
        */

        if (
            !isset($_FILES['thumbnail']) ||
            $_FILES['thumbnail']['error'] !== UPLOAD_ERR_OK
        ) {
            throw new Exception('تصویر محصول الزامی است.');
        }

        if ($_FILES['thumbnail']['size'] > (5 * 1024 * 1024)) {
            throw new Exception('حداکثر حجم تصویر 5 مگابایت است.');
        }

        $imageInfo = getimagesize(
            $_FILES['thumbnail']['tmp_name']
        );

        if (!$imageInfo) {
            throw new Exception('فایل انتخابی تصویر نیست.');
        }

        $allowedMime = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp'
        ];

        $mime = $imageInfo['mime'];

        if (!isset($allowedMime[$mime])) {
            throw new Exception(
                'فقط JPG PNG WEBP مجاز است.'
            );
        }

        $uploadDir =
            __DIR__ .
            '/../myupload/product_image/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName =
            uniqid('product_', true)
            . '.'
            . $allowedMime[$mime];

        $target =
            $uploadDir .
            $fileName;

        if (
            !move_uploaded_file(
                $_FILES['thumbnail']['tmp_name'],
                $target
            )
        ) {
            throw new Exception(
                'خطا در آپلود تصویر.'
            );
        }

        $thumbnail =
            'myupload/product_image/' .
            $fileName;

        $attributesJson = json_encode(
            $attributes,
            JSON_UNESCAPED_UNICODE
        );

        /*
        |--------------------------------------------------------------------------
        | INSERT
        |--------------------------------------------------------------------------
        */

        $mysqli->begin_transaction();

        $stmt = $mysqli->prepare("
            INSERT INTO product
            (
                product_menu_id,
                name,

                seo_title,
                seo_slug,
                seo_description,
                seo_keywords,

                attribute,
                thumbnail,
                description,

                price,
                active,
                myorder
            )
            VALUES
            (
                ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?
            )
        ");

        $stmt->bind_param(
            "issssssssiii",

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
            $myorder
        );

        if (!$stmt->execute()) {
            throw new Exception(
                $stmt->error
            );
        }

        $mysqli->commit();

        $text = 'محصول با موفقیت ثبت شد.';

    } catch (Exception $e) {

        $mysqli->rollback();

        if (
            isset($thumbnail) &&
            file_exists(
                __DIR__ . '/../' . $thumbnail
            )
        ) {
            unlink(
                __DIR__ . '/../' . $thumbnail
            );
        }

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
                            <h4 class="card-title">ورود اطلاعات محصول</h4>
                        </div>
                        <div class="card-body">
                            <div class="basic-form">
                                <form method="post" enctype="multipart/form-data" action="">
                                    <input type="hidden" name="Form" value="Submitted">

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">دسته بندی</label>

                                            <select
                                                name="product_menu"
                                                id="product_menu"
                                                class="form-control select2-selection__rendered"
                                                required>
                                                <option value="">انتخاب کنید</option>
                                                <?php
                                                $menus = $mysqli->query("
            SELECT id,name
            FROM product_menu
            WHERE deleted = 0
            ORDER BY myorder ASC
        ");

                                                while($menu = $menus->fetch_assoc()):
                                                    ?>

                                                    <option
                                                        value="<?= $menu['id'] ?>"
                                                        <?= ($productMenu ?? 0) == $menu['id'] ? 'selected' : '' ?>
                                                    >
                                                        <?= htmlspecialchars($menu['name']) ?>
                                                    </option>

                                                <?php endwhile; ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">نام محصول</label>
                                            <input
                                                    type="text"
                                                    name="name"
                                                    class="form-control"
                                                    required
                                                    value="<?= htmlspecialchars($name ?? '') ?>"
                                            >
                                        </div>

                                        <!-- قیمت -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">قیمت (دلار)</label>

                                            <input
                                                type="text"
                                                id="price_format"
                                                class="form-control"
                                                value="<?= number_format($price ?? 0) ?>"
                                            >

                                            <input
                                                type="hidden"
                                                id="price"
                                                name="price"
                                                value="<?= $price ?? 0 ?>"
                                            >
                                        </div>

                                        <!-- ترتیب -->
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">ترتیب نمایش</label>

                                            <input
                                                type="number"
                                                name="myorder"
                                                class="form-control"
                                                value="<?= $myorder ?? 0 ?>"
                                            >
                                        </div>

                                        <!-- وضعیت -->
                                        <div class="col-md-3 mb-3">
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
                                            <label class="form-label">تصویر محصول</label>

                                            <input
                                                type="file"
                                                name="thumbnail"
                                                id="thumbnail"
                                                class="form-control"
                                                accept="image/*"
                                                required
                                            >
                                        </div>

                                        <div class="col-md-6 mb-3 text-center">
                                            <img
                                                id="preview"
                                                src=""
                                                style="
                                                        max-height:200px;
                                                        display:none;
                                                        border-radius:10px;
                                                        border:1px solid #ddd;
                                                       "
                                            >
                                        </div>

                                        <!-- ویژگی ها -->
                                        <div class="col-12 mb-3">

                                            <div class="d-flex justify-content-between align-items-center mb-2">

                                                <label class="form-label mb-0">
                                                    ویژگی‌های محصول
                                                </label>

                                                <button
                                                    type="button"
                                                    id="addAttribute"
                                                    class="btn btn-success btn-sm"
                                                >
                                                    افزودن ویژگی
                                                </button>

                                            </div>

                                            <div id="attributesWrapper">

                                                <?php if(!empty($attributes)): ?>

                                                    <?php foreach($attributes as $attribute): ?>

                                                        <div class="input-group mb-2">

                                                            <input
                                                                type="text"
                                                                name="attribute[]"
                                                                class="form-control"
                                                                value="<?= htmlspecialchars($attribute) ?>"
                                                            >

                                                            <button
                                                                type="button"
                                                                class="btn btn-danger removeAttribute"
                                                            >
                                                                حذف
                                                            </button>

                                                        </div>

                                                    <?php endforeach; ?>

                                                <?php else: ?>

                                                    <div class="input-group mb-2">

                                                        <input
                                                            type="text"
                                                            name="attribute[]"
                                                            class="form-control"
                                                            placeholder="مثال : ۳ ترابایت"
                                                        >

                                                        <button
                                                            type="button"
                                                            class="btn btn-danger removeAttribute"
                                                        >
                                                            حذف
                                                        </button>

                                                    </div>

                                                <?php endif; ?>

                                            </div>

                                        </div>

                                        <!-- توضیحات -->
                                        <div class="col-12 mb-3">

                                            <div class="form-group mb-4">
                                                <label>توضیحات</label>
                                                <textarea class="form-control" name="description" id="description" rows="10"><?php echo htmlspecialchars($description); ?></textarea>
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
                                                    >

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
                                                    >
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
                                                    >
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
                                                ثبت محصول
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
   <?php require_once "inc/footer.php"?>
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

<script>


    /* CKEditor */
    ClassicEditor.create(
        document.querySelector('#editor')
    );

    /* Preview */
    $('#thumbnail').on('change', function(){

        const file = this.files[0];

        if(!file) return;

        const reader = new FileReader();

        reader.onload = function(e){

            $('#preview')
                .attr('src', e.target.result)
                .show();

        };

        reader.readAsDataURL(file);

    });

    /* Dynamic Attributes */

    $('#addAttribute').on('click', function(){

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
        function(){
            $(this)
                .closest('.input-group')
                .remove();
        }
    );

    /* Price Format */

    $('#price_format').on('input', function(){

        let value =
            this.value.replace(/\D/g,'');

        $('#price').val(value);

        this.value =
            Number(value)
                .toLocaleString('en-US');

    });

    /* Success Message */

    <?php if(!empty($text) && !$error): ?>

    Swal.fire({
        icon:'success',
        title:'موفق',
        text:'<?= addslashes($text) ?>'
    });

    <?php endif; ?>

    <?php if(!empty($text) && $error): ?>

    Swal.fire({
        icon:'error',
        title:'خطا',
        text:'<?= addslashes($text) ?>'
    });

    <?php endif; ?>


        $('#name').on('keyup', function(){

        let slug = $(this)
        .val()
        .trim()
        .replace(/[^\w\u0600-\u06FF]+/g,'-')
        .replace(/--+/g,'-')
        .toLowerCase();

        $('#seo_slug').val(slug);

    });

        $('#seo_title').on('keyup', function(){

        $('#seoTitleCount').html(
            this.value.length + ' / 70'
        );

        $('#googleTitle').html(
        $(this).val()
        );

    });

        $('#seo_description').on('keyup', function(){

        $('#seoDescCount').html(
            this.value.length + ' / 160'
        );

        $('#googleDesc').html(
        $(this).val()
        );

    });

        $('#seo_slug').on('keyup', function(){

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