<?php
require_once '../../cms/myadmin/inc/config.php';
require_once '../../cms/myadmin/inc/auth_helpers.php';

header('Content-Type: application/json; charset=utf-8');

if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'XMLHttpRequest') {
    http_response_code(403);
    echo json_encode(['status' => false, 'message' => 'دسترسی مستقیم مجاز نیست.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => false, 'message' => 'روش درخواست مجاز نیست.']);
    exit;
}

if (empty($_SESSION['user']['id'])) {
    http_response_code(401);
    echo json_encode(['status' => false, 'message' => 'لطفاً وارد حساب کاربری خود شوید.']);
    exit;
}


verifyCsrf();

$uid       = (int) $_SESSION['user']['id'];
$messageId = (int) ($_POST['message_id'] ?? 0);
$body      = trim($_POST['reply'] ?? '');

if ($messageId <= 0 || $body === '') {
    http_response_code(422);
    echo json_encode(['status' => false, 'message' => 'اطلاعات ارسالی ناقص است.']);
    exit;
}

if (mb_strlen($body, 'UTF-8') > 2000) {
    http_response_code(422);
    echo json_encode(['status' => false, 'message' => 'متن پیام بیش از حد مجاز است (حداکثر ۲۰۰۰ کاراکتر).']);
    exit;
}

$check = $pdo->prepare("
    SELECT id FROM `contact_messages`
    WHERE id = :id AND user_id = :uid AND deleted = 0
    LIMIT 1
");
$check->execute([':id' => $messageId, ':uid' => $uid]);

if (!$check->fetch()) {
    http_response_code(403);
    echo json_encode(['status' => false, 'message' => 'دسترسی غیرمجاز.']);
    exit;
}

$now = date('Y-m-d H:i:s');

$insert = $pdo->prepare("
    INSERT INTO `message_replies` (`message_id`, `sender_type`, `user_id`, `body`, `created_at`)
    VALUES (:mid, 'user', :uid, :body, :now)
");
$insert->execute([
    ':mid'  => $messageId,
    ':uid'  => $uid,
    ':body' => $body,
    ':now'  => $now,
]);

$pdo->prepare("UPDATE `contact_messages` SET seen = 0 WHERE id = :id")
    ->execute([':id' => $messageId]);

echo json_encode([
    'status'     => true,
    'created_at' => profile_format_date($now),
]);