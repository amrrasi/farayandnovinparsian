<?php
require_once '../../cms/myadmin/inc/config.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'method_not_allowed']);
    exit;
}

$uid        = $_SESSION['user']['id'];
$messageId  = (int) ($_POST['message_id'] ?? 0);
$body       = trim($_POST['body'] ?? '');

if ($messageId <= 0 || $body === '') {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'invalid_input']);
    exit;
}

if (mb_strlen($body, 'UTF-8') > 2000) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'too_long']);
    exit;
}

// Make sure this message actually belongs to the logged-in user.
$check = $pdo->prepare("SELECT id FROM `contact_messages` WHERE id = :id AND user_id = :uid AND deleted = 0 LIMIT 1");
$check->execute([':id' => $messageId, ':uid' => $uid]);
if (!$check->fetch()) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'forbidden']);
    exit;
}

$insert = $pdo->prepare("
    INSERT INTO `message_replies` (`message_id`, `sender_type`, `user_id`, `body`, `created_at`)
    VALUES (:mid, 'user', :uid, :body, NOW())
");
$insert->execute([
    ':mid'  => $messageId,
    ':uid'  => $uid,
    ':body' => $body,
]);

// Bounce the message back to "unseen" for admin so they know a user replied.
$pdo->prepare("UPDATE `contact_messages` SET seen = 0 WHERE id = :id")->execute([':id' => $messageId]);

echo json_encode(['ok' => true]);
