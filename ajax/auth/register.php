<?php

declare(strict_types=1);

require_once "../../cms/myadmin/inc/config.php";
require_once "../../cms/myadmin/inc/auth_helpers.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    jsonRespond(false, 'Method Not Allowed');
}

verifyCsrf();

$name            = trim((string) ($_POST['name'] ?? ''));
$mobile          = trim((string) ($_POST['mobile'] ?? ''));
$email           = trim((string) ($_POST['email'] ?? ''));
$password        = (string) ($_POST['password'] ?? '');
$confirmPassword = (string) ($_POST['confirmPassword'] ?? '');
$terms           = !empty($_POST['terms']);
$redirect        = sanitizeRedirect($_POST['redirect'] ?? null);

$email = $email !== '' ? $email : null;

if (mb_strlen($name) < 3) {
    jsonRespond(false, 'نام باید حداقل ۳ حرف باشد.');
}

if (!isValidIranianMobile($mobile)) {
    jsonRespond(false, 'شماره موبایل معتبر نیست. مثال: 09123456789');
}

if ($email !== null && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonRespond(false, 'ایمیل وارد شده معتبر نیست.');
}

if (!isStrongEnoughPassword($password)) {
    jsonRespond(false, 'رمز عبور باید حداقل ۸ کاراکتر و شامل حرف و عدد باشد.');
}

if ($password !== $confirmPassword) {
    jsonRespond(false, 'رمز عبور و تکرار آن یکسان نیستند.');
}

if (!$terms) {
    jsonRespond(false, 'برای ادامه باید با قوانین موافقت کنید.');
}

try {

    $dupStmt = $pdo->prepare("
        SELECT id
        FROM   user
        WHERE  deleted = 0
          AND  (mobile = :mobile OR (email IS NOT NULL AND email = :email))
        LIMIT  1
    ");

    $dupStmt->execute([
        ':mobile' => $mobile,
        ':email'  => $email,
    ]);

    if ($dupStmt->fetch()) {
        jsonRespond(false, 'کاربری با این شماره موبایل یا ایمیل قبلاً ثبت‌نام کرده است.');
    }

    $insert = $pdo->prepare("
        INSERT INTO user (name, mobile, email, password, deleted, mobile_verified, is_login)
        VALUES (:name, :mobile, :email, :password, 0, 0, 1)
    ");

    $insert->execute([
        ':name'     => $name,
        ':mobile'   => $mobile,
        ':email'    => $email,
        ':password' => password_hash($password, PASSWORD_DEFAULT),
    ]);

    $userId = (int) $pdo->lastInsertId();

} catch (PDOException $e) {

    error_log($e->getMessage());

    http_response_code(500);

    jsonRespond(false, 'خطا در ثبت‌نام. دوباره تلاش کنید.');
}

establishUserSession([
    'id'     => $userId,
    'name'   => $name,
    'mobile' => $mobile,
    'email'  => $email,
]);

jsonRespond(true, 'ثبت‌نام با موفقیت انجام شد.', ['redirect' => $redirect]);
