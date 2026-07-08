<?php

declare(strict_types=1);

require_once "../../cms/myadmin/inc/config.php";
require_once "../../cms/myadmin/inc/auth_helpers.php";

if (!empty($_SESSION['user']['id'])) {

    try {

        $pdo->prepare("
            UPDATE users
            SET    is_login = 0, session_token = NULL, session_expires = NULL
            WHERE  id = :id
        ")->execute([':id' => $_SESSION['user']['id']]);

    } catch (PDOException $e) {

        error_log($e->getMessage());
    }
}

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie('PHPSESSID', '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}

setcookie('remember_token', '', time() - 42000, '/');

session_destroy();

header('Location: ../../entry.php');

exit;
