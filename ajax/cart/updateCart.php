<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once "../../cms/myadmin/inc/config.php";

function respond(bool $ok, string $msg, array $extra = []): void {
    echo json_encode(array_merge(['status' => $ok, 'message' => $msg], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); respond(false, 'Method Not Allowed');
}

if (empty($_POST['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    if (empty($_SERVER['HTTP_X_CSRF_TOKEN']) ||
        !hash_equals($_SESSION['csrf_token'] ?? '', $_SERVER['HTTP_X_CSRF_TOKEN'])) {
        http_response_code(403); respond(false, 'توکن نامعتبر');
    }
}

$id  = filter_input(INPUT_POST, 'id',  FILTER_VALIDATE_INT);
$qty = filter_input(INPUT_POST, 'qty', FILTER_VALIDATE_INT);

if (!$id || !$qty || $qty < 1) {
    http_response_code(400); respond(false, 'اطلاعات نامعتبر');
}

if (!isset($_SESSION['cart'][$id])) {
    http_response_code(404); respond(false, 'محصول در سبد نیست');
}

$stmt = $pdo->prepare("SELECT id FROM product WHERE id = :id AND active = 1 AND deleted = 0 LIMIT 1");
$stmt->execute([':id' => $id]);
if (!$stmt->fetch()) {
    http_response_code(404); respond(false, 'محصول یافت نشد');
}

$_SESSION['cart'][$id]['qty'] = $qty;
respond(true, 'تعداد بروزرسانی شد', ['qty' => $qty]);
