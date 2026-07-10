<?php

declare(strict_types=1);

require_once "../../cms/myadmin/inc/config.php";
require_once "../../cms/myadmin/inc/auth_helpers.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    jsonRespond(false, 'Method Not Allowed');
}

verifyCsrf();

$identifier = trim((string) ($_POST['identifier'] ?? ''));
$password   = (string) ($_POST['password'] ?? '');
$remember   = !empty($_POST['remember']);
$redirect   = sanitizeRedirect($_POST['redirect'] ?? null);

if ($identifier === '' || $password === '') {
    jsonRespond(false, 'شماره موبایل/ایمیل و رمز عبور را وارد کنید.');
}

try {

    $stmt = $pdo->prepare("
        SELECT id, name, mobile, email, password
        FROM   user
        WHERE  (mobile = :identifier OR email = :identifier)
          AND  deleted = 0
        LIMIT  1
    ");

    $stmt->execute([':identifier' => $identifier]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    error_log($e->getMessage());

    http_response_code(500);

    jsonRespond(false, 'خطا در ارتباط با پایگاه داده.');
}

if (!$user || !password_verify($password, $user['password'])) {

    jsonRespond(false, 'شماره موبایل/ایمیل یا رمز عبور اشتباه است.');
}

establishUserSession($user);

try {

    $pdo->prepare("
        UPDATE user
        SET    is_login = 1, updated_at = NOW()
        WHERE  id = :id
    ")->execute([':id' => $user['id']]);

    if ($remember) {

        $token       = bin2hex(random_bytes(32));
        $tokenHash   = hash('sha256', $token);
        $expiresAt   = date('Y-m-d H:i:s', time() + 60 * 60 * 24 * 30);

        $pdo->prepare("
            UPDATE user
            SET    session_token = :token, session_expires = :expires
            WHERE  id = :id
        ")->execute([
            ':token'   => $tokenHash,
            ':expires' => $expiresAt,
            ':id'      => $user['id'],
        ]);

        setcookie('remember_token', $user['id'] . ':' . $token, [
            'expires'  => time() + 60 * 60 * 24 * 30,
            'path'     => '/',
            'secure'   => !empty($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

} catch (PDOException $e) {

    error_log($e->getMessage());
}

jsonRespond(true, 'خوش آمدید!', ['redirect' => $redirect]);
