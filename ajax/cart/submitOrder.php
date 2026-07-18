<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once "../../cms/myadmin/inc/config.php";

function respond(bool $ok, string $msg, array $extra = []): void {
    echo json_encode(array_merge(['status' => $ok, 'message' => $msg], $extra), JSON_UNESCAPED_UNICODE);
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

if (empty($_SESSION['cart'])) {
    respond(false, 'سبد خرید خالی است');
}

$fullName   = trim($_POST['full_name']   ?? '');
$phone      = trim($_POST['phone']       ?? '');
$order_name = trim($_POST['order_name']       ?? '');
$email      = trim($_POST['email']       ?? '');
$address    = trim($_POST['address']     ?? '');
$postalCode = trim($_POST['postal_code'] ?? '');
$orderNote  = trim($_POST['order_note']  ?? '');

if (!$fullName || !$phone || !$address) {
    http_response_code(400);
    respond(false, 'فیلدهای اجباری تکمیل نشده‌اند');
}

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
    http_response_code(500);
    respond(false, 'خطا در ارتباط با پایگاه داده');
}


$itemMap = [];
foreach ($rows as $row) {
    $id  = (int) $row['id'];
    $qty = (int) ($_SESSION['cart'][$id]['qty'] ?? 1);
    $itemMap[$id] = [
        'qty'   => $qty,
        'price' => (float) $row['price'],
        'name'  => $row['name'],
    ];
}

$subtotal    = 0;
$shippingFee = 0;
$grandTotal  = 0;

try {
    $pdo->beginTransaction();

    $orderStmt = $pdo->prepare("
        INSERT INTO orders
            (user_id, full_name, order_name, phone, email, address, postal_code, note,
             subtotal, shipping_fee, grand_total, order_status_id, created_at)
        VALUES
            (:uid, :name, :order_name, :phone, :email, :addr, :postal, :note,
             0, 0, 0, 1, NOW())
    ");

    $orderStmt->execute([
        ':uid'          => $_SESSION['user']['id'],
        ':name'         => $fullName,
        ':order_name'   => $order_name,
        ':phone'        => $phone,
        ':email'        => $email,
        ':addr'         => $address,
        ':postal'       => $postalCode,
        ':note'         => $orderNote,
    ]);

    $orderId = (int) $pdo->lastInsertId();

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
    http_response_code(500);
    respond(false, 'خطا در ثبت سفارش. لطفاً دوباره تلاش کنید.');
}

$_SESSION['cart'] = [];

respond(true, 'سفارش با موفقیت ثبت شد. کارشناسان ما پس از بررسی با قیمت نهایی با شما تماس خواهند گرفت.', [
    'order_id' => $orderId,
    'redirect' => 'profile.php?tab=orders',
]);