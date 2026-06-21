<?php

declare(strict_types=1);

date_default_timezone_set('Asia/Tehran');

header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-XSS-Protection: 1; mode=block');

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => !empty($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Strict'
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['created'])) {

    session_regenerate_id(true);

    $_SESSION['created'] = time();
}

if (isset($_SESSION['created']) && (time() - $_SESSION['created']) > 1800) {

    session_regenerate_id(true);

    $_SESSION['created'] = time();
}

ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');

error_reporting(E_ALL);

ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/php-error.log');

ini_set('upload_max_filesize', '50M');
ini_set('post_max_size', '50M');
ini_set('max_execution_time', '300');
ini_set('memory_limit', '256M');

require_once __DIR__ . '/jdf.php';
require_once __DIR__ . '/slug.php';
require_once __DIR__ . '/functions.php';

$dbhost = 'localhost';
$dbname = 'farayand_novin';
$dbuser = 'amir';
$dbpass = 'amirdbpass83';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {

    $mysqli = new mysqli(
        $dbhost,
        $dbuser,
        $dbpass,
        $dbname
    );

    $mysqli->set_charset('utf8mb4');

} catch (Exception $e) {

    error_log($e->getMessage());

    die('خطا در اتصال به پایگاه داده');
}

try {

    $pdo = new PDO(
        "mysql:host={$dbhost};dbname={$dbname};charset=utf8mb4",
        $dbuser,
        $dbpass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false
        ]
    );

} catch (PDOException $e) {

    error_log($e->getMessage());

    die('خطا در اتصال به پایگاه داده');
}

$global_setting_array = [];

try {

    $result = $mysqli->query("
        SELECT setting_name, setting_value
        FROM setting
    ");

    while ($row = $result->fetch_assoc()) {

        $global_setting_array[
        $row['setting_name']
        ] = $row['setting_value'];
    }

} catch (Exception $e) {

    error_log($e->getMessage());
}

$global_base_address_per =
    '<base href="http://farayan_movin.local/" />';


$cartCount = 0;

if (
    isset($_SESSION['cart']) &&
    is_array($_SESSION['cart'])
) {
    $cartCount = count($_SESSION['cart']);
}
?>
