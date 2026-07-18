<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once "../../inc/check.php";

function respond(bool $ok, string $msg): void
{
    echo json_encode(['ok' => $ok, 'message' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

/* فقط ادمین */
if (empty($_SESSION['admin'])) {
    http_response_code(401);
    respond(false, 'دسترسی غیرمجاز');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    respond(false, 'Method Not Allowed');
}

$orderId    = (int)   ($_POST['order_id']    ?? 0);
$newStatus  = (int)   ($_POST['new_status']  ?? 0);
$quotedTotal = (float) ($_POST['quoted_total'] ?? 0);
$adminNote  = trim(   $_POST['admin_note']   ?? '');

if ($orderId <= 0 || $newStatus < 1 || $newStatus > 6) {
    respond(false, 'داده‌های ورودی نامعتبر است');
}

/* بررسی وجود سفارش */
$check = $mysqli->prepare("SELECT id FROM orders WHERE id = ? AND deleted = 0 LIMIT 1");
$check->bind_param("i", $orderId);
$check->execute();
if (!$check->get_result()->fetch_assoc()) {
    respond(false, 'سفارش یافت نشد');
}

/* به‌روزرسانی */
$stmt = $mysqli->prepare("
    UPDATE orders
    SET order_status_id = ?,
        quoted_total    = ?,
        admin_note      = ?,
        updated_at      = NOW()
    WHERE id = ? AND deleted = 0
");
$stmt->bind_param("idsi", $newStatus, $quotedTotal, $adminNote, $orderId);

if ($stmt->execute()) {
    respond(true, 'سفارش با موفقیت به‌روزرسانی شد');
} else {
    http_response_code(500);
    respond(false, 'خطا در پایگاه داده');
}