<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once "../../cms/myadmin/inc/config.php";

function respond(bool $ok, string $msg): void {
    echo json_encode(['status' => $ok, 'message' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

if (empty($_SESSION['user']['id'])) {
    http_response_code(401);
    respond(false, 'لطفاً وارد حساب خود شوید');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    respond(false, 'Method Not Allowed');
}

$csrf = $_POST['csrf_token'] ?? '';
if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrf)) {
    http_response_code(403);
    respond(false, 'توکن نامعتبر');
}

$orderId = (int) ($_POST['order_id'] ?? 0);
if ($orderId <= 0) {
    respond(false, 'شناسه سفارش نامعتبر است');
}

$uid = (int) $_SESSION['user']['id'];

$check = $pdo->prepare("
    SELECT id FROM orders
    WHERE id = :oid AND user_id = :uid AND order_status_id = 2 AND deleted = 0
    LIMIT 1
");
$check->execute([':oid' => $orderId, ':uid' => $uid]);

if (!$check->fetch()) {
    http_response_code(403);
    respond(false, 'سفارش یافت نشد یا امکان آپلود رسید در این مرحله وجود ندارد');
}

if (empty($_FILES['receipt']) || $_FILES['receipt']['error'] !== UPLOAD_ERR_OK) {
    respond(false, 'لطفاً فایل رسید را انتخاب کنید');
}

$allowed  = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
$finfo    = finfo_open(FILEINFO_MIME_TYPE);
$mime     = finfo_file($finfo, $_FILES['receipt']['tmp_name']);
finfo_close($finfo);

if (!in_array($mime, $allowed, true)) {
    respond(false, 'فرمت فایل مجاز نیست (JPG، PNG، WebP، PDF)');
}

if ($_FILES['receipt']['size'] > 5 * 1024 * 1024) {
    respond(false, 'حجم فایل بیش از ۵ مگابایت است');
}

$uploadDir = "receipts/";
$fullPath  = __DIR__ . '../../assets/images/' . $uploadDir;

if (!is_dir($fullPath)) {
    mkdir($fullPath, 0755, true);
}

$ext         = strtolower(pathinfo($_FILES['receipt']['name'], PATHINFO_EXTENSION));
$fileName    = 'receipt_' . $uid . '_' . $orderId . '_' . time() . '.' . $ext;
$destination = $fullPath . $fileName;

if (!move_uploaded_file($_FILES['receipt']['tmp_name'], $destination)) {
    http_response_code(500);
    respond(false, 'خطا در ذخیره فایل. لطفاً دوباره تلاش کنید.');
}

try {
    $upd = $pdo->prepare("
        UPDATE orders
        SET receipt_path    = :path,
            order_status_id = 3,
            updated_at      = NOW()
        WHERE id = :oid AND user_id = :uid
    ");
    $upd->execute([
        ':path' => $uploadDir . $fileName,
        ':oid'  => $orderId,
        ':uid'  => $uid,
    ]);
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    respond(false, 'خطا در به‌روزرسانی سفارش');
}

respond(true, 'رسید با موفقیت ارسال شد. کارشناسان ما پس از بررسی با شما تماس خواهند گرفت.');