<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once "../cms/myadmin/inc/config.php";

function respond(bool $ok, string $msg, array $extra = []): void {
    echo json_encode(array_merge(['status' => $ok, 'message' => $msg], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

// ── Auth ─────────────────────────────────────────────────
if (empty($_SESSION['user_id'])) {
    http_response_code(401); respond(false, 'لطفاً وارد حساب خود شوید');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); respond(false, 'Method Not Allowed');
}

// ── CSRF ─────────────────────────────────────────────────
$csrf = $_POST['csrf_token'] ?? '';
if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrf)) {
    http_response_code(403); respond(false, 'توکن نامعتبر');
}

// ── Cart ─────────────────────────────────────────────────
if (empty($_SESSION['cart'])) {
    respond(false, 'سبد خرید خالی است');
}

// ── Inputs ───────────────────────────────────────────────
$fullName   = trim($_POST['full_name']   ?? '');
$phone      = trim($_POST['phone']       ?? '');
$email      = trim($_POST['email']       ?? '');
$address    = trim($_POST['address']     ?? '');
$postalCode = trim($_POST['postal_code'] ?? '');
$orderNote  = trim($_POST['order_note']  ?? '');

if (!$fullName || !$phone || !$address) {
    http_response_code(400); respond(false, 'فیلدهای اجباری تکمیل نشده‌اند');
}

// ── Receipt upload ────────────────────────────────────────
if (empty($_FILES['receipt']) || $_FILES['receipt']['error'] !== UPLOAD_ERR_OK) {
    respond(false, 'لطفاً تصویر رسید پرداخت را بارگذاری کنید');
}

$allowed   = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
$finfo     = finfo_open(FILEINFO_MIME_TYPE);
$mimeType  = finfo_file($finfo, $_FILES['receipt']['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, $allowed, true)) {
    respond(false, 'فرمت فایل مجاز نیست (JPG، PNG، PDF)');
}

if ($_FILES['receipt']['size'] > 5 * 1024 * 1024) {
    respond(false, 'حجم فایل بیش از ۵ مگابایت است');
}

$uploadDir = "../uploads/receipts/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$ext         = pathinfo($_FILES['receipt']['name'], PATHINFO_EXTENSION);
$receiptName = 'receipt_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
$receiptPath = $uploadDir . $receiptName;

if (!move_uploaded_file($_FILES['receipt']['tmp_name'], $receiptPath)) {
    http_response_code(500); respond(false, 'خطا در بارگذاری فایل');
}

// ── Rebuild cart & calculate total ───────────────────────
$ids = array_map('intval', array_keys($_SESSION['cart']));
$in  = implode(',', $ids);

try {
    $rows = $pdo->query("
        SELECT id, name, price
        FROM product
        WHERE id IN ($in) AND active = 1 AND deleted = 0
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500); respond(false, 'خطا در پایگاه داده');
}

$subtotal = 0;
$itemMap  = [];
foreach ($rows as $row) {
    $id  = (int) $row['id'];
    $qty = (int) ($_SESSION['cart'][$id]['qty'] ?? 1);
    $subtotal += $qty * (float) $row['price'];
    $itemMap[$id] = ['qty' => $qty, 'price' => $row['price'], 'name' => $row['name']];
}

$shippingFee = $subtotal >= 500000 ? 0 : 35000;
$grandTotal  = $subtotal + $shippingFee;

// ── Insert order ──────────────────────────────────────────
try {
    $pdo->beginTransaction();

    $orderStmt = $pdo->prepare("
        INSERT INTO orders
            (user_id, full_name, phone, email, address, postal_code, note,
             subtotal, shipping_fee, grand_total, receipt_path, status, created_at)
        VALUES
            (:uid, :name, :phone, :email, :addr, :postal, :note,
             :sub, :ship, :grand, :receipt, 'pending', NOW())
    ");

    $orderStmt->execute([
        ':uid'     => $_SESSION['user_id'],
        ':name'    => $fullName,
        ':phone'   => $phone,
        ':email'   => $email,
        ':addr'    => $address,
        ':postal'  => $postalCode,
        ':note'    => $orderNote,
        ':sub'     => $subtotal,
        ':ship'    => $shippingFee,
        ':grand'   => $grandTotal,
        ':receipt' => 'uploads/receipts/' . $receiptName,
    ]);

    $orderId = (int) $pdo->lastInsertId();

    // Insert order items
    $itemStmt = $pdo->prepare("
        INSERT INTO order_items (order_id, product_id, product_name, price, qty, total)
        VALUES (:oid, :pid, :pname, :price, :qty, :total)
    ");

    foreach ($itemMap as $productId => $item) {
        $itemStmt->execute([
            ':oid'   => $orderId,
            ':pid'   => $productId,
            ':pname' => $item['name'],
            ':price' => $item['price'],
            ':qty'   => $item['qty'],
            ':total' => $item['qty'] * $item['price'],
        ]);
    }

    $pdo->commit();

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log($e->getMessage());
    http_response_code(500); respond(false, 'خطا در ثبت سفارش. لطفاً دوباره تلاش کنید.');
}

// ── Clear cart ────────────────────────────────────────────
$_SESSION['cart'] = [];

respond(true, 'سفارش با موفقیت ثبت شد', [
    'order_id' => $orderId,
    'redirect' => 'panel.php?tab=orders&order=' . $orderId,
]);
