<?php
require_once "cms/myadmin/inc/config.php"
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title> <?= setting('name') ?> | About Us</title>
    <?= $global_base_address ?>

    <link rel="stylesheet" href="assets/css/base/animate.min.css">
    <link rel="stylesheet" href="assets/css/base/flaticon.css">
    <link rel="stylesheet" href="assets/css/base/fontawesome.min.css">
    <link rel="stylesheet" href="assets/css/base/magnific-popup.min.css">
    <link rel="stylesheet" href="assets/css/base/nice-select.css">
    <link rel="stylesheet" href="assets/css/base/owl.carousel.min.css">

    <link rel="stylesheet" href="assets/fonts/font.css">

    <link rel="stylesheet" href="assets/css/base/bootstrap.rtl.css">
    <link rel="stylesheet" href="assets/css/base/base.css">
    <link rel="stylesheet" href="assets/css/layout/header.css">
    <link rel="stylesheet" href="assets/css/layout/footer.css">
    <link rel="stylesheet" href="assets/css/about-us.css?v=<?php echo filemtime('assets/css/about-us.css'); ?>">
<body>
<!-- header -->
<?php require_once "inc/header.php"?>




<!-- footer -->
<?php require_once "inc/footer.php"?>


<script src="assets/js/jquery.js"></script>
<script src="assets/js/jquery.nice-select.min.js"></script>
<script src="assets/js/owl.carousel.min.js"></script>
<script src="assets/js/bootstrap.js"></script>
<script src="assets/js/bootstrap.bundle.js"></script>
<script src="assets/js/main.js"></script>

</body>
</html>
