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

$email = trim((string)($_POST['email'] ?? ''));

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonRespond(false, 'یک ایمیل معتبر وارد کنید.');
}

$genericMessage = 'در صورت ثبت بودن این ایمیل در سامانه، کد تایید برای آن ارسال شد.';

try {

    $stmt = $mysqli->prepare("
        SELECT id, name, email, otp_last_sent_at
        FROM user
        WHERE email = ?
        AND deleted = 0
        LIMIT 1
    ");

    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    $stmt->close();

    // اگر ایمیل وجود نداشت
    if (!$user) {
        jsonRespond(true, $genericMessage, ['step' => 'otp']);
    }

    // بررسی محدودیت ارسال مجدد
    if (!empty($user['otp_last_sent_at'])) {

        $secondsSinceLast = time() - strtotime($user['otp_last_sent_at']);

        if ($secondsSinceLast < OTP_RESEND_AFTER) {

            jsonRespond(
                true,
                'کد قبلی هنوز معتبر است. لطفاً ' .
                (OTP_RESEND_AFTER - $secondsSinceLast) .
                ' ثانیه دیگر دوباره تلاش کنید.',
                [
                    'step' => 'otp',
                    'retryAfter' => OTP_RESEND_AFTER - $secondsSinceLast
                ]
            );
        }
    }

    $otp = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);

    $otpHash = hash('sha256', $otp);

    $expires = date(
        'Y-m-d H:i:s',
        time() + (OTP_TTL_MINUTES * 60)
    );

    $stmt = $mysqli->prepare("
        UPDATE user
        SET
            otp_hash = ?,
            otp_expires = ?,
            otp_attempts = 0,
            otp_last_sent_at = NOW()
        WHERE id = ?
    ");

    $stmt->bind_param(
        "ssi",
        $otpHash,
        $expires,
        $user['id']
    );

    $stmt->execute();

    $stmt->close();

    $mailResult = sendMail(
        $user['email'],
        $user['name'],
        'کد تایید بازیابی رمز عبور',
        otpEmailBody(
            $user['name'],
            $otp,
            OTP_TTL_MINUTES
        )
    );

    if (!$mailResult['ok']) {

        jsonRespond(
            false,
            'ارسال ایمیل با خطا مواجه شد. کمی بعد دوباره تلاش کنید.'
        );
    }

} catch (Exception $e) {

    error_log($e->getMessage());

    http_response_code(500);

    jsonRespond(
        false,
        'خطا در ارتباط با پایگاه داده.'
    );
}

jsonRespond(
    true,
    $genericMessage,
    [
        'step' => 'otp'
    ]
);