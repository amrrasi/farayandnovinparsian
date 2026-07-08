<?php

declare(strict_types=1);

require_once "../../cms/myadmin/inc/config.php";
require_once "../../cms/myadmin/inc/auth_helpers.php";
require_once "../../cms/myadmin/inc/mailer.php";

const OTP_TTL_MINUTES  = 10;
const OTP_RESEND_AFTER = 60; // seconds

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    jsonRespond(false, 'Method Not Allowed');
}

verifyCsrf();

$email = trim((string) ($_POST['email'] ?? ''));

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonRespond(false, 'یک ایمیل معتبر وارد کنید.');
}

// Generic response shown regardless of whether the account exists, to avoid leaking
// which emails are registered.
$genericMessage = 'در صورت ثبت بودن این ایمیل در سامانه، کد تایید برای آن ارسال شد.';

try {

    $stmt = $pdo->prepare("
        SELECT id, name, email, otp_last_sent_at
        FROM   users
        WHERE  email = :email
          AND  deleted = 0
        LIMIT  1
    ");

    $stmt->execute([':email' => $email]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        jsonRespond(true, $genericMessage, ['step' => 'otp']);
    }

    if (!empty($user['otp_last_sent_at'])) {

        $secondsSinceLast = time() - strtotime($user['otp_last_sent_at']);

        if ($secondsSinceLast < OTP_RESEND_AFTER) {

            jsonRespond(true, 'کد قبلی هنوز معتبر است. لطفاً ' .
                (OTP_RESEND_AFTER - $secondsSinceLast) . ' ثانیه دیگر دوباره تلاش کنید.',
                ['step' => 'otp', 'retryAfter' => OTP_RESEND_AFTER - $secondsSinceLast]);
        }
    }

    $otp     = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $otpHash = hash('sha256', $otp);

    $pdo->prepare("
        UPDATE users
        SET    otp_hash = :hash,
               otp_expires = :expires,
               otp_attempts = 0,
               otp_last_sent_at = NOW()
        WHERE  id = :id
    ")->execute([
        ':hash'    => $otpHash,
        ':expires' => date('Y-m-d H:i:s', time() + 60 * OTP_TTL_MINUTES),
        ':id'      => $user['id'],
    ]);

    $result = sendMail(
        $user['email'],
        $user['name'],
        'کد تایید بازیابی رمز عبور',
        otpEmailBody($user['name'], $otp, OTP_TTL_MINUTES)
    );

    if (!$result['ok']) {
        // Email failed to send — don't leave the user stuck on an OTP screen with no code coming.
        jsonRespond(false, 'ارسال ایمیل با خطا مواجه شد. کمی بعد دوباره تلاش کنید.');
    }

} catch (PDOException $e) {

    error_log($e->getMessage());

    http_response_code(500);

    jsonRespond(false, 'خطا در ارتباط با پایگاه داده.');
}

jsonRespond(true, $genericMessage, ['step' => 'otp']);
