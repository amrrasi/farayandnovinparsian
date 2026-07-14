<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once "../../cms/myadmin/inc/config.php";

function respond(bool $status, string $message, array $extra = []): void
{
    echo json_encode(array_merge([
        'status'  => $status,
        'message' => $message,
    ], $extra), JSON_UNESCAPED_UNICODE);

    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    http_response_code(405);

    respond(false, 'Method Not Allowed');
}

$id  = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$qty = filter_input(INPUT_POST, 'qty', FILTER_VALIDATE_INT);

$qty = $qty ?: 1;

if (!$id || $qty < 1) {

    http_response_code(400);

    respond(false, 'اطلاعات نامعتبر است.');
}

try {

    $stmt = $pdo->prepare("
        SELECT id, name, price, availability
        FROM   product
        WHERE  id = :id
          AND  active  = 1
          AND  deleted = 0
        LIMIT 1
    ");

    $stmt->execute([':id' => $id]);

    $product = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    error_log($e->getMessage());

    http_response_code(500);

    respond(false, 'خطا در ارتباط با پایگاه داده.');
}

if (!$product) {

    http_response_code(404);

    respond(false, 'محصول پیدا نشد.');
}

if ((int) $product['availability'] !== 1) {

    http_response_code(409);

    respond(false, 'این محصول در حال حاضر موجود نیست.');
}

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {

    $_SESSION['cart'] = [];
}

if (isset($_SESSION['cart'][$id])) {

    $_SESSION['cart'][$id]['qty'] += $qty;

} else {

    $_SESSION['cart'][$id] = [
        'qty'  => $qty,
        'time' => time(),
    ];
}

$productCount = count($_SESSION['cart']);

respond(true, 'محصول به سبد خرید اضافه شد.', [
    'count' => $productCount,
    'item'  => [
        'id'   => $product['id'],
        'name' => $product['name'],
    ],
]);