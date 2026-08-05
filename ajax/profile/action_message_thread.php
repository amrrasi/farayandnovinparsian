<?php
/**
 * ajax/profile/action_message_thread.php
 *
 * Returns the full thread (original message + all replies) as JSON.
 * Called by the user profile messages tab.
 *
 * DB tables used:
 *   contact_messages  — id, user_id, subject, message, seen, deleted, created_at
 *   message_replies   — id, message_id, sender_type ENUM('admin','user'), user_id, body, created_at
 */
require_once '../../cms/myadmin/inc/config.php';
require_once '../../cms/myadmin/inc/auth_helpers.php';  // provides profile_format_date()

header('Content-Type: application/json; charset=utf-8');

// ── XHR-only guard — reject direct browser navigation ────────────────────────
if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'XMLHttpRequest') {
    http_response_code(403);
    echo json_encode(['status' => false, 'message' => 'دسترسی مستقیم مجاز نیست.']);
    exit;
}

// ── Auth guard ──────────────────────────────────────────────────────────────
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

// ── Fetch parent message (must belong to this user and not be deleted) ──────
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

// ── Fetch replies oldest-first ───────────────────────────────────────────────
// sender_type is ENUM('admin','user') — maps directly to the JS bubble logic.
$repliesStmt = $pdo->prepare("
    SELECT sender_type AS sender, body, created_at
    FROM `message_replies`
    WHERE message_id = :id
    ORDER BY created_at ASC, id ASC
");
$repliesStmt->execute([':id' => $messageId]);
$replies = $repliesStmt->fetchAll(PDO::FETCH_ASSOC);

// ── Build messages array ─────────────────────────────────────────────────────
// First bubble = the original user message; then all replies in order.
$messages = [];

$messages[] = [
    'sender'     => 'user',
    'body'       => $msg['message'],        // contact_messages.message column
    'created_at' => profile_format_date($msg['created_at']),
];

foreach ($replies as $r) {
    $messages[] = [
        'sender'     => $r['sender'],       // 'admin' or 'user'
        'body'       => $r['body'],         // message_replies.body column
        'created_at' => profile_format_date($r['created_at']),
    ];
}

// ── NOTE on the `seen` flag ──────────────────────────────────────────────────
// seen = 1 means "seen by admin".
// We do NOT touch it here — the user opening their own thread doesn't change
// the admin-seen status. The flag flips to 0 when the user posts a new reply
// (so the admin notices), and back to 1 when the admin reads it in the CMS.

echo json_encode([
    'status'     => true,
    'subject'    => $msg['subject'],
    'created_at' => profile_format_date($msg['created_at']),
    'messages'   => $messages,
]);