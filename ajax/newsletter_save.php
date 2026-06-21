<?php
header('Content-Type: application/json; charset=utf-8');
require_once "../../myadmin/inc/config.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $emailField = $_POST['email'] ?? $_POST['EMAIL'] ?? null;

    if (!$emailField || !filter_var($emailField, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'message' => 'ایمیل معتبر نیست']);
        exit;
    }

    $email = trim($emailField);
    $user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

    $check = $pdo->prepare("SELECT id FROM mailing WHERE email = :email LIMIT 1");
    $check->execute([':email' => $email]);

    if ($check->rowCount() > 0) {
        echo json_encode(['status' => 'error', 'message' => 'این ایمیل قبلا ثبت شده است']);
        exit;
    }

    // ذخیره در دیتابیس
    $stmt = $pdo->prepare("INSERT INTO mailing (email, user_id, created_at) VALUES (:email, :user_id, NOW())");
    $stmt->execute([
        ':email' => $email,
        ':user_id' => $user_id
    ]);

    echo json_encode(['status' => 'success', 'message' => 'با موفقیت عضو خبرنامه شدید']);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'خطا در ذخیره اطلاعات: ' . $e->getMessage()
    ]);
}