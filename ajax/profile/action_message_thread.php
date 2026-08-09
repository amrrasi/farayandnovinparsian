<?php
require_once '../../cms/myadmin/inc/config.php';
require_once '../../cms/myadmin/inc/auth_helpers.php';

header('Content-Type: application/json; charset=utf-8');

if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'XMLHttpRequest') {
    http_response_code(403);
    echo json_encode(['status' => false, 'message' => 'دسترسی مستقیم مجاز نیست.']);
    exit;
}

if (empty($_SESSION['user']['id'])) {
    http_response_code(401);
    echo json_encode(['status' => false, 'message' => 'لطفاً وارد حساب کاربری خود شوید.']);
    exit;
}

$uid       = (int) $_SESSION['user']['id'];
$messageId = (int) ($_GET['id'] ?? 0);

if ($messageId <= 0) {
    http_response_code(422);
    echo json_encode(['status' => false, 'message' => 'شناسه نامعتبر است.']);
    exit;
}

$msgStmt = $pdo->prepare("
    SELECT id, subject, message, seen, created_at
    FROM `contact_messages`
    WHERE id = :id AND user_id = :uid AND deleted = 0
    LIMIT 1
");
$msgStmt->execute([':id' => $messageId, ':uid' => $uid]);
$msg = $msgStmt->fetch(PDO::FETCH_ASSOC);

if (!$msg) {
    http_response_code(404);
    echo json_encode(['status' => false, 'message' => 'پیام یافت نشد.']);
    exit;
}

// Mark as seen when the user opens the thread
// seen=1 means "user has read the latest admin reply"
if ((int) $msg['seen'] !== 1) {
    $pdo->prepare("UPDATE `contact_messages` SET seen = 1 WHERE id = :id AND user_id = :uid")
        ->execute([':id' => $messageId, ':uid' => $uid]);
}

$repliesStmt = $pdo->prepare("
    SELECT sender_type AS sender, body, created_at
    FROM `message_replies`
    WHERE message_id = :id
    ORDER BY created_at ASC, id ASC
");
$repliesStmt->execute([':id' => $messageId]);
$replies = $repliesStmt->fetchAll(PDO::FETCH_ASSOC);

$messages = [];

$messages[] = [
    'sender'     => 'user',
    'body'       => $msg['message'],
    'created_at' => profile_format_date($msg['created_at']),
];

foreach ($replies as $r) {
    $messages[] = [
        'sender'     => $r['sender'],
        'body'       => $r['body'],
        'created_at' => profile_format_date($r['created_at']),
    ];
}

echo json_encode([
    'status'     => true,
    'subject'    => $msg['subject'],
    'created_at' => profile_format_date($msg['created_at']),
    'messages'   => $messages,
]);