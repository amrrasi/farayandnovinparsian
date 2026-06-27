<?php
require_once "inc/check.php";
?>
<!DOCTYPE html>
<html lang="fa-IR">
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
    <!-- HTML5 shim and Respond.js IE8 support of HTML5 tooltipss and media queries -->
    <!--[if lt IE 9]>
    <script src="js/html5shiv.js"></script>
    <script src="js/respond.min.js"></script>
    <![endif]-->

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
    <?php require_once "inc/header.php"; ?>
    <?php require_once "inc/aside.php"; ?>
    <?php
    $error = false;
    foreach ($_POST as $name => $value) {


        if (1) {
            $Update = $mysqli->query("UPDATE `setting` SET `setting_value` = '" . $value . "' WHERE `setting_name` = '" . $name . "' LIMIT 1");

            if (!$Update) {
                $error = true;
                $text = "خطا در ثبت اطلاعات";
            }
        } else {
            $error = true;
            $text = "لطفا کلیه قسمت ها را پر کنید";
        }
    }

    ?>
    <div class="content-body">

        <div class="container-fluid">

            <?php if (isset($_POST['Form']) && !$error) { ?>

                <div class="alert alert-success alert-dismissible fade show mb-4">
                    <strong>موفق!</strong><br> تنظیمات با موفقیت ذخیره شد.
                </div>

            <?php } ?>

            <div class="page-titles">
                <h4>تنظیمات سایت</h4>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">مدیریت</li>
                    <li class="breadcrumb-item active">تنظیمات عمومی</li>
                </ol>
            </div>

            <div class="card border-0 shadow-sm">

                <div class="card-header bg-transparent border-0 pb-0">

                    <div class="d-flex justify-content-between align-items-center flex-wrap">

                        <div class="row">
                            <div class="col-12">
                                <div>
                                    <h3 class="mb-1">تنظیمات سایت</h3>
                                    <p class="text-muted mb-0">
                                        مدیریت اطلاعات عمومی و تنظیمات سیستم
                                    </p>
                                </div>
                            </div>
                            <div class="col-12 mt-4">
                                <button type="submit"
                                        form="settingForm"
                                        class="btn btn-primary">

                                    <i class="fa fa-save ml-2"></i>
                                    ذخیره تغییرات

                                </button>
                            </div>
                        </div>


                    </div>

                </div>

                <div class="card-body">

                    <div class="row mb-4">

                        <div class="col-xl-4 col-lg-6">

                            <div class="input-group">

                            <span class="input-group-text">
                                <i class="fa fa-search"></i>
                            </span>

                                <input
                                        type="text"
                                        id="settingSearch"
                                        class="form-control"
                                        placeholder="جستجوی تنظیمات ..."
                                >

                            </div>

                        </div>

                    </div>

                    <form
                            id="settingForm"
                            method="post"
                            enctype="multipart/form-data">

                        <input type="hidden"
                               name="Form"
                               value="Submitted">

                        <div class="row g-4">

                            <?php

                            $row_result = $mysqli->query("
                            SELECT *
                            FROM setting
                            ORDER BY id
                        ");

                            while ($row = $row_result->fetch_assoc()) {

                                ?>

                                <div class="col-xl-6 col-lg-6 setting-item">

                                    <div class="card h-100 border shadow-sm setting-card">

                                        <div class="card-body">

                                            <label class="form-label fw-bold mb-2">

                                                <i class="fa fa-cog text-primary ml-1"></i>

                                                <?= $row['setting_description'] ?>

                                            </label>

                                            <input
                                                    type="text"
                                                    name="<?= $row['setting_name'] ?>"
                                                    value="<?= htmlspecialchars($row['setting_value']) ?>"
                                                    class="form-control">

                                            <small class="text-muted mt-2 d-block">

                                                <?= $row['setting_name'] ?>

                                            </small>

                                        </div>

                                    </div>

                                </div>

                                <?php
                            }
                            ?>

                        </div>

                        <div class="sticky-save mt-4">

                            <button
                                    type="submit"
                                    class="btn btn-primary btn-lg">

                                <i class="fa fa-save ml-2"></i>

                                ذخیره تنظیمات

                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>


    <?php require_once "inc/footer.php" ?>
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


        $('#settingSearch').on('keyup', function () {

            let value = $(this).val().toLowerCase();

            $('.setting-item').each(function () {

                $(this).toggle(
                    $(this)
                        .text()
                        .toLowerCase()
                        .indexOf(value) > -1
                );

            });

        });

    </script>
</body>

</html>