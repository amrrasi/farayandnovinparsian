<?php

require_once "inc/config.php";
require_once "inc/functions.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: dashboard.php");
    exit;
}

function redirect($url)
{
    header("Location: " . $url);
    exit;
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['captcha_code'] = random_int(1000, 9999);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submited'])) {

    $RedirectSucc = "dashboard.php";
    $RedirectFail = "index.php?error=login";

    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        redirect($RedirectFail);
    }

    $username = trim($_POST['usr_username'] ?? '');
    $password = trim($_POST['usr_password'] ?? '');
    $userCode = trim($_POST['usr_code'] ?? '');

    if (
        empty($username) ||
        empty($password) ||
        empty($userCode) ||
        $userCode != $_SESSION['captcha_code']
    ) {
        redirect($RedirectFail);
    }

    $stmt = $mysqli->prepare("
       SELECT id, username, password, access, mail
        FROM admins
        WHERE username = ?
        LIMIT 1
    ");

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {

        $stmt->bind_result(
            $id,
            $dbUsername,
            $dbPassword,
            $dbAccess,
            $email
        );

        $stmt->fetch();

        if (password_verify($password, $dbPassword)) {

            session_regenerate_id(true);

            $_SESSION['logged_in'] = true;
            $_SESSION['usr_id'] = $id;
            $_SESSION['usr_username'] = $dbUsername;
            $_SESSION['usr_access'] = $dbAccess;
            $_SESSION['LAST_ACTIVITY'] = time();
            $_SESSION['USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'] ?? '';

            if (function_exists('getRealIpAddr')) {

                $ip = getRealIpAddr();

                $logStmt = $mysqli->prepare("
                    INSERT INTO admin_log
                    (admin_id, type, ip)
                    VALUES (?, 'ENTER', ?)
                ");

                $logStmt->bind_param("is", $id, $ip);
                $logStmt->execute();
            }

            redirect($RedirectSucc);
        }
    }

    redirect($RedirectFail);
}
?>
<!DOCTYPE html>
<html lang="en" class="h-100">


<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>مدیریت صفحات وبسایت</title>
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="images/favicon.jpg">
    <link href="css/style.css" rel="stylesheet">

</head>

<body class="h-100">
<div class="authincation h-100">
    <div class="container h-100">
        <div class="row justify-content-center h-100 align-items-center">
            <div class="col-md-6">
                <div class="authincation-content">
                    <div class="row no-gutters">
                        <div class="col-xl-12">
                            <div class="auth-form">

                                <h4 class="text-center mb-4 text-white">وارد حساب خود شوید</h4>
                                <?php if (isset($_GET['error'])): ?>
                                    <div class="alert alert-danger text-center">اطلاعات ورود اشتباه است.</div>
                                <?php endif; ?>
                                <form action="" method="post">
                                    <div class="form-group">
                                        <label class="mb-1 text-white"><strong>نام کاربری</strong></label>
                                        <input type="text" name="usr_username" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="mb-1 text-white"><strong>رمزعبور</strong></label>
                                        <input type="password" name="usr_password" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="mb-1 text-white"><strong>کد امنیتی را وارد کنید: <?= $_SESSION['captcha_code'] ?></strong></label>

                                        <input type="text" name="usr_code" class="form-control ml-2" required
                                               placeholder="کد بالا را وارد کنید">
                                    </div>

                                    <input type="hidden" name="submited" value="1">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                                    <div class="text-center mt-5">
                                        <button type="submit" class="btn bg-white text-primary btn-block">وارد شوید
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
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
<script src="js/custom.min.js"></script>
<script src="js/deznav-init.js"></script>

</body>


</html>