<?php

declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

/**
 * Requires PHPMailer to be installed via Composer:
 *     composer require phpmailer/phpmailer
 *
 * Adjust the autoload path below if your vendor/ folder lives somewhere else.
 */
require_once __DIR__ . '/../../../vendor/autoload.php';

/**
 * SMTP settings — fill these in with your real mail provider credentials.
 * For Gmail you need an "app password", not your normal account password.
 */
const SMTP_HOST        = 'smtp.example.com';
const SMTP_PORT        = 587;
const SMTP_USERNAME    = 'user@example.com';
const SMTP_PASSWORD    = 'CHANGE_ME';
const SMTP_SECURE      = PHPMailer::ENCRYPTION_STARTTLS; // or ENCRYPTION_SMTPS for port 465
const MAIL_FROM_EMAIL  = 'no-reply@example.com';
const MAIL_FROM_NAME   = 'تعامل';

/**
 * Sends an HTML email.
 *
 * @return array{ok:bool, error:?string}
 */
function sendMail(string $toEmail, string $toName, string $subject, string $htmlBody): array
{
    $mail = new PHPMailer(true);

    try {

        $mail->CharSet = 'UTF-8';

        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->Port       = SMTP_PORT;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE;

        $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody  = strip_tags($htmlBody);

        $mail->send();

        return ['ok' => true, 'error' => null];

    } catch (PHPMailerException $e) {

        error_log('PHPMailer error: ' . $mail->ErrorInfo);

        return ['ok' => false, 'error' => $mail->ErrorInfo];
    }
}

/**
 * Builds the OTP email body. Kept in one place so the template is easy to restyle.
 */
function otpEmailBody(string $name, string $otp, int $minutesValid): string
{
    $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');

    return <<<HTML
    <div dir="rtl" style="font-family:Tahoma,Arial,sans-serif;background:#F7F3EB;padding:32px;">
        <div style="max-width:420px;margin:0 auto;background:#ffffff;border-radius:16px;padding:32px;border:1px solid #eee;">
            <p style="font-size:15px;color:#2B241C;">سلام {$safeName} عزیز،</p>
            <p style="font-size:14px;color:#6C6258;line-height:1.9;">
                کد تایید بازیابی رمز عبور شما:
            </p>
            <div style="text-align:center;margin:24px 0;">
                <span style="display:inline-block;font-size:28px;font-weight:700;letter-spacing:6px;
                    background:linear-gradient(135deg,#0EA5E9,#7C3AED);-webkit-background-clip:text;
                    -webkit-text-fill-color:transparent;">{$otp}</span>
            </div>
            <p style="font-size:13px;color:#6C6258;">
                این کد تا {$minutesValid} دقیقه دیگر معتبر است. اگر این درخواست را شما نداده‌اید، این ایمیل را نادیده بگیرید.
            </p>
        </div>
    </div>
    HTML;
}
