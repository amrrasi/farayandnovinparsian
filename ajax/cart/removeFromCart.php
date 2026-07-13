<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once "../cms/myadmin/inc/config.php";

function respond(bool $ok, string $msg, array $extra = []): void {
    echo json_encode(array_merge(['status' => $ok, 'message' => $msg], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); respond(false, 'Method Not Allowed');
}

$csrfHeader = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
$csrfPost   = $_POST['csrf_token'] ?? '';
$sessionCsrf = $_SESSION['csrf_token'] ?? '';

if (!hash_equals($sessionCsrf, $csrfPost) && !hash_equals($sessionCsrf, $csrfHeader)) {
    http_response_code(403); respond(false, 'توکن نامعتبر');
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    http_response_code(400); respond(false, 'اطلاعات نامعتبر');
}

unset($_SESSION['cart'][$id]);
respond(true, 'محصول از سبد حذف شد', ['count' => count($_SESSION['cart'] ?? [])]);
