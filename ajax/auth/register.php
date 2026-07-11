<?php

declare(strict_types=1);

require_once "../../cms/myadmin/inc/config.php";
require_once "../../cms/myadmin/inc/auth_helpers.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    jsonRespond(false, 'Method Not Allowed');
}

verifyCsrf();

$name            = trim((string)($_POST['name'] ?? ''));
$mobile          = trim((string)($_POST['mobile'] ?? ''));
$email           = trim((string)($_POST['email'] ?? ''));
$password        = (string)($_POST['password'] ?? '');
$confirmPassword = (string)($_POST['confirmPassword'] ?? '');
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

    $stmt = $mysqli->prepare("
        SELECT id
        FROM user
        WHERE deleted = 0
        AND (
            mobile = ?
            OR (email IS NOT NULL AND email = ?)
        )
        LIMIT 1
    ");

    $stmt->bind_param("ss", $mobile, $email);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->fetch_assoc()) {
        $stmt->close();
        jsonRespond(false, 'کاربری با این شماره موبایل یا ایمیل قبلاً ثبت‌نام کرده است.');
    }

    $stmt->close();

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $mysqli->prepare("
        INSERT INTO user
        (
            name,
            mobile,
            email,
            password,
            deleted,
            mobile_verified,
            is_login
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            0,
            0,
            1
        )
    ");

    $stmt->bind_param(
        "ssss",
        $name,
        $mobile,
        $email,
        $passwordHash
    );

    $stmt->execute();

    $userId = $mysqli->insert_id;

    $stmt->close();

} catch (Exception $e) {
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

jsonRespond(
    true,
    'ثبت‌نام با موفقیت انجام شد.',
    [
        'redirect' => $redirect
    ]
);