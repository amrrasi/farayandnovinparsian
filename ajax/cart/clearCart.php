<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once "../../cms/myadmin/inc/config.php";

function respond(bool $ok, string $msg): void {
    echo json_encode(['status' => $ok, 'message' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); respond(false, 'Method Not Allowed');
}

$_SESSION['cart'] = [];
respond(true, 'سبد خرید پاک شد');
