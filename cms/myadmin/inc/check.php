<?php

require_once "inc/config.php";
require_once "inc/functions.php";

$sessionTimeout = 1800;

if (
    !isset($_SESSION['logged_in']) ||
    $_SESSION['logged_in'] !== true ||
    !isset($_SESSION['usr_id'])
) {
    header("Location: index.php");
    exit;
}

if (
    isset($_SESSION['LAST_ACTIVITY']) &&
    (time() - $_SESSION['LAST_ACTIVITY']) > $sessionTimeout
) {
    session_unset();
    session_destroy();

    header("Location: index.php?error=session");
    exit;
}

$_SESSION['LAST_ACTIVITY'] = time();

if (
    isset($_SESSION['USER_AGENT']) &&
    $_SESSION['USER_AGENT'] !== ($_SERVER['HTTP_USER_AGENT'] ?? '')
) {
    session_unset();
    session_destroy();

    header("Location: index.php?error=session");
    exit;
}

function hasAccess($access)
{
    return isset($_SESSION['usr_access'])
        && $_SESSION['usr_access'] === $access;
}