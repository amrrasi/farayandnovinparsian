<?php

declare(strict_types=1);

require_once "../../cms/myadmin/inc/config.php";
require_once "../../cms/myadmin/inc/auth_helpers.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    jsonRespond(false, 'Method Not Allowed');
}

verifyCsrf();

$identifier = trim((string)($_POST['identifier'] ?? ''));
$password   = (string)($_POST['password'] ?? '');
$remember   = !empty($_POST['remember']);
$redirect   = sanitizeRedirect($_POST['redirect'] ?? null);

if ($identifier === '' || $password === '') {
    jsonRespond(false, 'شماره موبایل/ایمیل و رمز عبور را وارد کنید.');
}

try {

    $stmt = $mysqli->prepare("
        SELECT id, name, mobile, email, password
        FROM user
        WHERE (mobile = ? OR email = ?)
        AND deleted = 0
        LIMIT 1
    ");

    if (!$stmt) {
        throw new Exception($mysqli->error);
    }

    $stmt->bind_param("ss", $identifier, $identifier);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    $stmt->close();

} catch (Exception $e) {

    error_log($e->getMessage());

    http_response_code(500);
    jsonRespond(false, 'خطا در ارتباط با پایگاه داده.');
}

if (!$user || !password_verify($password, $user['password'])) {
    jsonRespond(false, 'شماره موبایل/ایمیل یا رمز عبور اشتباه است.');
}

establishUserSession($user);

try {

    $stmt = $mysqli->prepare("
        UPDATE user
        SET is_login = 1,
            updated_at = NOW()
        WHERE id = ?
    ");

    if (!$stmt) {
        throw new Exception($mysqli->error);
    }

    $stmt->bind_param("i", $user['id']);
    $stmt->execute();
    $stmt->close();

    if ($remember) {

        $token      = bin2hex(random_bytes(32));
        $tokenHash  = hash('sha256', $token);
        $expiresAt  = date('Y-m-d H:i:s', time() + 60 * 60 * 24 * 30);

        $stmt = $mysqli->prepare("
            UPDATE user
            SET session_token = ?,
                session_expires = ?
            WHERE id = ?
        ");

        if (!$stmt) {
            throw new Exception($mysqli->error);
        }

        $stmt->bind_param(
            "ssi",
            $tokenHash,
            $expiresAt,
            $user['id']
        );

        $stmt->execute();
        $stmt->close();

        setcookie('remember_token', $user['id'] . ':' . $token, [
            'expires'  => time() + 60 * 60 * 24 * 30,
            'path'     => '/',
            'secure'   => !empty($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

} catch (Exception $e) {

    error_log($e->getMessage());
}

jsonRespond(true, 'خوش آمدید!', [
    'redirect' => $redirect
]);