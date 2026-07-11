<?php
require_once '../../cms/myadmin/inc/config.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'method_not_allowed']);
    exit;
}

$uid            = $_SESSION['user']['id'];
$currentPass    = $_POST['current_password'] ?? '';
$newPass        = $_POST['new_password'] ?? '';
$newPassConfirm = $_POST['new_password_confirm'] ?? '';

$errors = [];

if (mb_strlen($newPass, 'UTF-8') < 8) {
    $errors['new_password'] = 'رمز عبور جدید باید حداقل ۸ کاراکتر باشد.';
}

if ($newPass !== $newPassConfirm) {
    $errors['new_password_confirm'] = 'تکرار رمز عبور با رمز جدید یکسان نیست.';
}

if ($errors) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'errors' => $errors]);
    exit;
}

$stmt = $pdo->prepare("SELECT `password` FROM `user` WHERE id = :uid LIMIT 1");
$stmt->execute([':uid' => $uid]);
$row = $stmt->fetch();

if (!$row || !password_verify($currentPass, $row['password'])) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'errors' => ['current_password' => 'رمز عبور فعلی درست نیست.']]);
    exit;
}

$newHash = password_hash($newPass, PASSWORD_BCRYPT, ['cost' => 12]);

// Regenerate session_token to invalidate any other logged-in sessions/devices,
// mirroring the OTP module's session_token/session_expires pattern.
$newToken = bin2hex(random_bytes(32));

$update = $pdo->prepare("
    UPDATE `user`
    SET `password` = :password, `session_token` = :token, `updated_at` = NOW()
    WHERE `id` = :uid
");
$update->execute([
    ':password' => $newHash,
    ':token'    => $newToken,
    ':uid'      => $uid,
]);

// keep the CURRENT session valid by mirroring the fresh token, if your
// login module stores it in the session too. Adjust key name if needed.
$_SESSION['session_token'] = $newToken;

echo json_encode(['ok' => true]);
