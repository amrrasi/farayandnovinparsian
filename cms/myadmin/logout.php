<?php

require_once 'inc/config.php';
require_once 'inc/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!empty($_SESSION['logged_in']) && !empty($_SESSION['usr_id'])) {

    $id = (int) $_SESSION['usr_id'];
    $ip = function_exists('getRealIpAddr')
        ? getRealIpAddr()
        : ($_SERVER['REMOTE_ADDR'] ?? '');

    $logStmt = $mysqli->prepare("
        INSERT INTO admin_log
        (admin_id, type, ip)
        VALUES (?, 'EXIT', ?)
    ");

    if ($logStmt) {
        $logStmt->bind_param("is", $id, $ip);

        if (!$logStmt->execute()) {
            error_log('LOGOUT LOG ERROR: ' . $logStmt->error);
        }

        $logStmt->close();
    } else {
        error_log('PREPARE FAILED: ' . $mysqli->error);
    }
}

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

session_destroy();

header('Location: index.php');
exit;
?>
