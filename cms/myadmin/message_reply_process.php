<?php
require_once "inc/check.php";

$message_id = isset($_POST['message_id']) ? (int)$_POST['message_id'] : 0;
$body       = trim($_POST['body'] ?? '');

if ($message_id <= 0 || $body === '') {
    header("Location: messagesList.php");
    exit;
}

$stmt = $mysqli->prepare("
    SELECT `id`, `fullname`, `email`, `subject`
    FROM `contact_messages`
    WHERE `id` = ? AND `deleted` = 0
    LIMIT 1
");
$stmt->bind_param("i", $message_id);
$stmt->execute();
$ticket = $stmt->get_result()->fetch_assoc();

if (!$ticket) {
    header("Location: messagesList.php");
    exit;
}

// insert the reply
$insert = $mysqli->prepare("
    INSERT INTO `message_replies` (`message_id`, `sender_type`, `body`, `created_at`)
    VALUES (?, 'admin', ?, NOW())
");
$insert->bind_param("is", $message_id, $body);
$insert->execute();

if (!empty($ticket['email']) && filter_var($ticket['email'], FILTER_VALIDATE_EMAIL)) {
    $autoloadPath = __DIR__ . '/../../vendor/autoload.php';
    if (file_exists($autoloadPath)) {
        require_once $autoloadPath;

        if (class_exists(\PHPMailer\PHPMailer\PHPMailer::class)) {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = '';
                $mail->SMTPAuth   = true;
                $mail->Username   = '';
                $mail->Password   = '';
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port       = 465;
                $mail->CharSet    = 'UTF-8';

                $siteName = function_exists('setting') ? setting('name') : 'فناپ';
                $safeBody = nl2br(htmlspecialchars($body, ENT_QUOTES, 'UTF-8'));
                $safeName = htmlspecialchars($ticket['fullname'], ENT_QUOTES, 'UTF-8');
                $safeSubj = htmlspecialchars($ticket['subject'], ENT_QUOTES, 'UTF-8');

                $mail->setFrom('', $siteName);
                $mail->addAddress($ticket['email'], $ticket['fullname']);
                $mail->isHTML(true);
                $mail->Subject = "پاسخ جدید برای درخواست شما: {$safeSubj}";
                $mail->Body = "
                    <div dir='rtl' style='font-family:Tahoma,Arial,sans-serif;'>
                        <p>سلام <b>{$safeName}</b>،</p>
                        <p>پاسخ جدیدی برای درخواست شما با موضوع «{$safeSubj}» ثبت شد:</p>
                        <blockquote style='background:#f8fafc;border-radius:8px;padding:14px 18px;'>{$safeBody}</blockquote>
                        <p>با احترام،<br>تیم پشتیبانی {$siteName}</p>
                    </div>
                ";
                $mail->AltBody = "سلام {$ticket['fullname']}،\n\nپاسخ جدیدی برای درخواست شما ثبت شد:\n\n{$body}";

                $mail->send();
            } catch (\PHPMailer\PHPMailer\Exception $e) {
            }
        }
    }
}

header("Location: messages.php?id={$message_id}&sent=1");
exit;