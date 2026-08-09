<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../cms/myadmin/inc/config.php";

header("Content-Type: application/json; charset=UTF-8");

// Block any cached response from being served for this endpoint
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");

require_once "../vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

/**
 * Send a JSON response and terminate.
 *
 * @param string $status  "success" | "warning" | "error"
 * @param string $message Human-readable Persian message.
 * @param array  $extra   Optional extra keys (e.g. ["ticket" => "..."])
 */
function respond(string $status, string $message, array $extra = []): void
{
    echo json_encode(
        array_merge(["status" => $status, "message" => $message], $extra),
        JSON_UNESCAPED_UNICODE
    );
    exit;
}

/*──────────────────────────────────────────
 Method guard
──────────────────────────────────────────*/
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond('error', 'روش درخواست نامعتبر است.');
}

/*──────────────────────────────────────────
 FIX 1: Identify the logged-in user.
 Adjust the session key to match whatever your auth system uses,
 e.g. $_SESSION['user_id'], $_SESSION['auth']['id'], etc.
──────────────────────────────────────────*/
$userId = isset($_SESSION['user']['id']) ? (int) $_SESSION['user']['id'] : null;
// If your CMS stores it under a different key, change the line above, e.g.:
//   $userId = isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null;
// A null user_id means the form was submitted by a guest (not logged in).

/*──────────────────────────────────────────
 Sanitise & collect inputs
──────────────────────────────────────────*/
$fullname = trim($_POST['fullname'] ?? '');
$mobile   = trim($_POST['mobile']   ?? '');
$email    = trim($_POST['email']    ?? '');
$subject  = trim($_POST['subject']  ?? '');
$message  = trim($_POST['message']  ?? '');
$privacy  = isset($_POST['privacy']);

/*──────────────────────────────────────────
 Server-side validation
──────────────────────────────────────────*/
if ($fullname === '' || $mobile === '' || $subject === '' || $message === '') {
    respond('error', 'لطفاً همه فیلدهای الزامی را پر کنید.');
}

if (mb_strlen($fullname) < 3) {
    respond('error', 'نام باید حداقل ۳ کاراکتر باشد.');
}

if (!$privacy) {
    respond('error', 'لطفاً با قوانین سایت و نحوه پردازش اطلاعات موافقت کنید.');
}

if (!preg_match('/^09[0-9]{9}$/', $mobile)) {
    respond('error', 'شماره موبایل معتبر نیست (باید ۱۱ رقم و با ۰۹ شروع شود).');
}

if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond('error', 'ایمیل وارد شده معتبر نیست.');
}

if (mb_strlen($subject) < 2) {
    respond('error', 'موضوع درخواست باید حداقل ۲ کاراکتر باشد.');
}

if (mb_strlen($message) < 20) {
    respond('error', 'متن پیام باید حداقل ۲۰ کاراکتر باشد.');
}

if (mb_strlen($message) > 1000) {
    respond('error', 'متن پیام نباید بیشتر از ۱۰۰۰ کاراکتر باشد.');
}

/*──────────────────────────────────────────
 File upload
 FIX 2: Broken path '..    /cms/...' → '../cms/...'
──────────────────────────────────────────*/
$attachmentRelPath = null;
$attachmentAbsPath = null;

if (!empty($_FILES['attachment']['name'])) {
    $file = $_FILES['attachment'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        respond('error', 'خطا در بارگذاری فایل (کد: ' . $file['error'] . ').');
    }

    $allowedExt = ['pdf', 'jpg', 'jpeg', 'png', 'docx'];
    $maxSize    = 5 * 1024 * 1024; // 5 MB

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowedExt, true)) {
        respond('error', 'فرمت فایل مجاز نیست. (فقط PDF, JPG, PNG, DOCX)');
    }

    if ($file['size'] > $maxSize) {
        respond('error', 'حجم فایل نباید بیشتر از ۵ مگابایت باشد.');
    }

    // FIX 2: removed the stray spaces in the path
    $uploadDir = '../cms/myupload/contact/';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
        respond('error', 'ایجاد پوشه آپلود با خطا مواجه شد.');
    }

    $safeName          = bin2hex(random_bytes(8)) . '.' . $ext;
    $attachmentAbsPath = $uploadDir . $safeName;

    if (!move_uploaded_file($file['tmp_name'], $attachmentAbsPath)) {
        respond('error', 'ذخیره فایل با خطا مواجه شد.');
    }

    $attachmentRelPath = 'cms/myupload/contact/' . $safeName;
}

/*──────────────────────────────────────────
 Database insert
 FIX 1: user_id column included in INSERT.
 Assumes your contact_messages table has a nullable `user_id` INT column.
 If it doesn't yet, run:
   ALTER TABLE contact_messages ADD COLUMN user_id INT NULL DEFAULT NULL AFTER attachment_path;
──────────────────────────────────────────*/
try {
    // Use the $pdo instance injected by config.php if available,
    // otherwise create our own connection.
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        $dbHost = "localhost";
        $dbName = "";   // ← fill in your DB name if config.php doesn't set $pdo
        $dbUser = "";   // ← fill in your DB user
        $dbPass = "";   // ← fill in your DB password
        $pdo = new PDO(
            "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
            $dbUser,
            $dbPass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }

    $stmt = $pdo->prepare(
        "INSERT INTO contact_messages
            (user_id, fullname, mobile, email, subject, message, attachment_path, created_at)
         VALUES
            (:user_id, :fullname, :mobile, :email, :subject, :message, :attachment_path, NOW())"
    );

    $stmt->execute([
        ':user_id'         => $userId,                              // FIX 1
        ':fullname'        => $fullname,
        ':mobile'          => $mobile,
        ':email'           => ($email !== '' ? $email : null),
        ':subject'         => $subject,
        ':message'         => $message,
        ':attachment_path' => $attachmentRelPath,
    ]);

    $insertId = $pdo->lastInsertId();

} catch (\PDOException $e) {
    // Log $e->getMessage() to your error log in production; never expose it to the client.
    error_log('[contact_process] DB error: ' . $e->getMessage());
    respond('error', 'خطا در ذخیره پیام در دیتابیس.');
}

/*──────────────────────────────────────────
 Confirmation email (only when email provided)
 FIX 4: guard addAttachment() so it only runs when the file actually exists.
──────────────────────────────────────────*/
if ($email !== '') {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'mail.fanapit.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'info@fanapit.com';
        $mail->Password   = '[P{u&;Z$6G(kT%1N';   // consider moving to an env variable
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        $mail->CharSet    = 'UTF-8';
        $mail->Encoding   = 'base64';

        $siteName = function_exists('setting') ? setting('name') : 'فرآیند نوین';

        $mail->setFrom('info@fanapit.com', $siteName);
        $mail->addAddress($email, $fullname);

        // FIX 4: only attach the file if it was uploaded AND the file actually exists
        if ($attachmentAbsPath !== null && file_exists($attachmentAbsPath)) {
            $mail->addAttachment($attachmentAbsPath);
        }

        $mail->isHTML(true);
        $mail->Subject = "درخواست شما با موضوع «{$subject}» دریافت شد";
        $mail->Body    = build_confirmation_email($siteName, $fullname, $subject, $message);
        $mail->AltBody = "سلام {$fullname}،\n\nدرخواست شما با موضوع \"{$subject}\" دریافت شد و به زودی توسط تیم پشتیبانی بررسی می‌شود.\n\nمتن پیام شما:\n{$message}\n\nبا احترام،\n{$siteName}";

        $mail->send();

    } catch (PHPMailerException $e) {
        error_log('[contact_process] Mail error: ' . $mail->ErrorInfo);
        // FIX 3: status is the string "warning", not boolean — consistent with JS handler
        respond('warning', 'درخواست شما با موفقیت ثبت شد، اما ارسال ایمیل تأیید با خطا مواجه شد.');
    }
}

// FIX 3: status is the string "success"
respond('success', 'درخواست شما با موفقیت ثبت شد. کارشناسان ما در سریع‌ترین زمان ممکن با شما تماس خواهند گرفت.', [
    'ticket' => $insertId,
]);


/*──────────────────────────────────────────
 Email template
──────────────────────────────────────────*/
function build_confirmation_email(string $siteName, string $fullname, string $subject, string $message): string
{
    $safeSiteName = htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8');
    $safeName     = htmlspecialchars($fullname, ENT_QUOTES, 'UTF-8');
    $safeSubject  = htmlspecialchars($subject,  ENT_QUOTES, 'UTF-8');
    $safeMessage  = nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));
    $year         = date('Y');

    return <<<HTML
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{$safeSiteName}</title>
</head>
<body style="margin:0; padding:0; background-color:#f2f4f7; font-family: Tahoma, 'Segoe UI', Arial, sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f2f4f7; padding:32px 12px;">
  <tr>
    <td align="center">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px; background-color:#ffffff; border-radius:16px; overflow:hidden; box-shadow:0 4px 24px rgba(20,40,80,0.08);">

        <!-- Header -->
        <tr>
          <td style="background:linear-gradient(135deg,#2563eb,#1d4ed8); padding:28px 32px; text-align:right;">
            <span style="color:#ffffff; font-size:20px; font-weight:bold;">{$safeSiteName}</span>
          </td>
        </tr>

        <!-- Success badge -->
        <tr>
          <td style="padding:32px 32px 0 32px; text-align:right;">
            <div style="display:inline-block; background-color:#e8f5e9; color:#2e7d32; font-size:13px; font-weight:bold; padding:6px 14px; border-radius:999px;">
              ✔ درخواست شما دریافت شد
            </div>
          </td>
        </tr>

        <!-- Body -->
        <tr>
          <td style="padding:20px 32px 8px 32px; text-align:right;">
            <p style="margin:0 0 12px 0; font-size:16px; color:#1f2937;">سلام <strong>{$safeName}</strong> 🌹</p>
            <p style="margin:0 0 20px 0; font-size:14px; line-height:1.9; color:#4b5563;">
              پیام شما با موفقیت ثبت شد و در سریع‌ترین زمان ممکن توسط تیم پشتیبانی {$safeSiteName} بررسی خواهد شد.
            </p>
          </td>
        </tr>

        <!-- Message card -->
        <tr>
          <td style="padding:0 32px 8px 32px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f8fafc; border:1px solid #e5e7eb; border-radius:12px;">
              <tr>
                <td style="padding:18px 20px; text-align:right;">
                  <div style="font-size:12px; color:#6b7280; margin-bottom:4px;">موضوع درخواست</div>
                  <div style="font-size:14px; color:#111827; font-weight:bold; margin-bottom:16px;">{$safeSubject}</div>
                  <div style="font-size:12px; color:#6b7280; margin-bottom:4px;">متن پیام</div>
                  <div style="font-size:14px; color:#374151; line-height:1.9;">{$safeMessage}</div>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- Footer -->
        <tr>
          <td style="padding:28px 32px 32px 32px; text-align:right;">
            <p style="margin:0; font-size:13px; color:#6b7280;">با احترام،<br>تیم پشتیبانی {$safeSiteName}</p>
          </td>
        </tr>

        <tr>
          <td style="background-color:#f8fafc; padding:16px 32px; text-align:center; border-top:1px solid #eef0f3;">
            <span style="font-size:11px; color:#9ca3af;">© {$year} {$safeSiteName} — این یک ایمیل خودکار است، لطفاً به آن پاسخ ندهید.</span>
          </td>
        </tr>

      </table>
    </td>
  </tr>
</table>
</body>
</html>
HTML;
}