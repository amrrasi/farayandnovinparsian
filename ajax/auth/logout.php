<?php

declare(strict_types=1);

require_once "../../cms/myadmin/inc/config.php";
require_once "../../cms/myadmin/inc/auth_helpers.php";

if (!empty($_SESSION['user']['id'])) {

    try {

        $stmt = $mysqli->prepare("
            UPDATE user
            SET
                is_login = 0,
                session_token = NULL,
                session_expires = NULL
            WHERE id = ?
        ");

        $stmt->bind_param("i", $_SESSION['user']['id']);
        $stmt->execute();
        $stmt->close();

    } catch (mysqli_sql_exception $e) {

        error_log($e->getMessage());
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

setcookie('remember_token', '', [
    'expires'  => time() - 42000,
    'path'     => '/',
    'secure'   => !empty($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax',
]);

session_destroy();

header('Location: ../../entry.php');
exit;