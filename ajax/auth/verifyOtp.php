<?php

declare(strict_types=1);

require_once "../../cms/myadmin/inc/config.php";
require_once "../../cms/myadmin/inc/auth_helpers.php";

const OTP_MAX_ATTEMPTS = 5;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    jsonRespond(false, 'Method Not Allowed');
}

verifyCsrf();

$email            = trim((string) ($_POST['email'] ?? ''));
$otp              = trim((string) ($_POST['otp'] ?? ''));
$newPassword      = (string) ($_POST['newPassword'] ?? '');
$confirmPassword  = (string) ($_POST['confirmPassword'] ?? '');

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonRespond(false, 'ایمیل نامعتبر است.');
}

if (!preg_match('/^\d{6}$/', $otp)) {
    jsonRespond(false, 'کد تایید باید ۶ رقم باشد.');
}

if (!isStrongEnoughPassword($newPassword)) {
    jsonRespond(false, 'رمز عبور جدید باید حداقل ۸ کاراکتر و شامل حرف و عدد باشد.');
}

if ($newPassword !== $confirmPassword) {
    jsonRespond(false, 'رمز عبور و تکرار آن یکسان نیستند.');
}

try {

    $stmt = $pdo->prepare("
        SELECT id, otp_hash, otp_expires, otp_attempts
        FROM   user
        WHERE  email = :email
          AND  deleted = 0
        LIMIT  1
    ");

    $stmt->execute([':email' => $email]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || empty($user['otp_hash']) || empty($user['otp_expires'])) {
        jsonRespond(false, 'ابتدا درخواست کد تایید بدهید.');
    }

    if (strtotime($user['otp_expires']) < time()) {
        jsonRespond(false, 'کد تایید منقضی شده است. دوباره درخواست دهید.');
    }

    if ((int) $user['otp_attempts'] >= OTP_MAX_ATTEMPTS) {
        jsonRespond(false, 'تعداد تلاش‌های مجاز به پایان رسیده. کد جدید درخواست دهید.');
    }

    if (!hash_equals($user['otp_hash'], hash('sha256', $otp))) {

        $pdo->prepare("
            UPDATE user SET otp_attempts = otp_attempts + 1 WHERE id = :id
        ")->execute([':id' => $user['id']]);

        jsonRespond(false, 'کد وارد شده صحیح نیست.');
    }

    $pdo->prepare("
        UPDATE user
        SET    password = :password,
               otp_hash = NULL,
               otp_expires = NULL,
               otp_attempts = 0,
               session_token = NULL,
               session_expires = NULL,
               updated_at = NOW()
        WHERE  id = :id
    ")->execute([
        ':password' => password_hash($newPassword, PASSWORD_DEFAULT),
        ':id'       => $user['id'],
    ]);

} catch (Exception $e) {

    error_log($e->getMessage());

    http_response_code(500);

    jsonRespond(false, 'خطا در ارتباط با پایگاه داده.');
}

jsonRespond(true, 'رمز عبور با موفقیت تغییر کرد. اکنون وارد شوید.');
