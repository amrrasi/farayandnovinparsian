<?php

declare(strict_types=1);

date_default_timezone_set('Asia/Tehran');

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

error_reporting(E_ALL);

ini_set('log_errors', '1');
ini_set('error_log', __DIR__.'/../logs/php-error.log');

ini_set('upload_max_filesize', '50M');
ini_set('post_max_size', '50M');
ini_set('memory_limit', '256M');
ini_set('max_execution_time', '300');


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

if(session_status() === PHP_SESSION_NONE){

    session_start();

}

if(
    !isset($_SESSION['created']) ||
    (time() - $_SESSION['created']) > 1800
){

    session_regenerate_id(true);

    $_SESSION['created'] = time();

}

require_once __DIR__.'/functions.php';
require_once __DIR__.'/slug.php';
require_once __DIR__.'/jdf.php';

$dbhost = 'localhost';
$dbname = 'farayand_novin';
$dbuser = 'amir';
$dbpass = 'amirdbpass83';

define('BASE_URL','http://farayan_movin.local/');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);


try{

    $mysqli = new mysqli(
        $dbhost,
        $dbuser,
        $dbpass,
        $dbname
    );

    $mysqli->set_charset('utf8mb4');

}catch(Exception $e){

    error_log($e->getMessage());

    exit('خطا در اتصال به پایگاه داده');

}

try{

    $pdo = new PDO(

        "mysql:host={$dbhost};dbname={$dbname};charset=utf8mb4",

        $dbuser,

        $dbpass,

        [

            PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,

            PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,

            PDO::ATTR_EMULATE_PREPARES=>false

        ]

    );

}catch(PDOException $e){

    error_log($e->getMessage());

    exit('خطا در اتصال به پایگاه داده');

}

$settings = [];

try{

    $result = $mysqli->query("
        SELECT
            setting_name,
            setting_value
        FROM setting
    ");

    while($row = $result->fetch_assoc()){

        $settings[$row['setting_name']] = $row['setting_value'];

    }

}catch(Exception $e){

    error_log($e->getMessage());

}

function setting(string $key,$default=null)
{

    global $settings;

    return $settings[$key] ?? $default;

}

// Number of distinct products in the cart, not total quantity.
$cartCount = !empty($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
$global_base_address =
    '<base href="http://farayand_novin.local/" />';
$baseAddress = 'http://farayand_novin.local/';

//$global_base_address =
//    '<base href="http://192.168.1.106/" />';
//$baseAddress = 'http://192.168.1.106/';
?>