<?php
/**
 * ajax/profile/action_send_reply.php
 *
 * Inserts a user reply into message_replies and resets seen = 0
 * on the parent contact_message so the admin notices the reply.
 *
 * DB tables used:
 *   contact_messages  — id, user_id, seen, deleted
 *   message_replies   — message_id, sender_type, user_id, body, created_at
 *
 * Expected POST fields:
 *   csrf_token   — value from <meta name="csrf-token">
 *   message_id   — int, the contact_messages.id
 *   reply        — string, the reply body (max 2000 UTF-8 chars)
 *
 * Returns JSON:
 *   { status: true,  created_at: "..." }   on success
 *   { status: false, message: "..." }       on any error
 */
require_once '../../cms/myadmin/inc/config.php';

header('Content-Type: application/json; charset=utf-8');

// ── Method guard ─────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => false, 'message' => 'روش درخواست مجاز نیست.']);
    exit;
}

// ── Auth guard ───────────────────────────────────────────────────────────────
if (empty($_SESSION['user']['id'])) {
    http_response_code(401);
    echo json_encode(['status' => false, 'message' => 'لطفاً وارد حساب کاربری خود شوید.']);
    exit;
}

// ── CSRF check (uses verifyCsrf() helper from auth_helpers.php) ──────────────
// verifyCsrf() reads $_POST['csrf_token'] internally and exits on failure.
verifyCsrf();

$uid       = (int) $_SESSION['user']['id'];
$messageId = (int) ($_POST['message_id'] ?? 0);
// JS sends the textarea value under the key 'reply'
$body      = trim($_POST['reply'] ?? '');

// ── Input validation ─────────────────────────────────────────────────────────
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

// ── Ownership check ───────────────────────────────────────────────────────────
// Prevent a user from replying to another user's message by guessing the ID.
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

// ── Insert reply ─────────────────────────────────────────────────────────────
// sender_type ENUM('admin','user') — this is always 'user' here.
// user_id stores the replying user (admin replies leave user_id NULL).
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

// ── Reset seen flag ───────────────────────────────────────────────────────────
// seen = 0 means "admin has NOT yet read the latest activity".
// Flipping it here ensures the admin panel shows this thread as needing attention.
$pdo->prepare("UPDATE `contact_messages` SET seen = 0 WHERE id = :id")
    ->execute([':id' => $messageId]);

// ── Respond ───────────────────────────────────────────────────────────────────
echo json_encode([
    'status'     => true,
    'created_at' => profile_format_date($now),
]);