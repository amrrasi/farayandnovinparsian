<?php

require_once "../inc/config.php";

header('Content-Type: application/json; charset=utf-8');

function fail($message, $code = 500) {
    http_response_code($code);
    echo json_encode(["error" => $message]);
    exit;
}

if (!isset($mysqli) || $mysqli->connect_error) {
    fail("db_connection_failed");
}

$res = $mysqli->query("
    SELECT price, created_at
    FROM usd_prices
    ORDER BY id DESC
    LIMIT 1
");

if ($res === false) {
    fail("query_failed: " . $mysqli->error);
}

$row = $res->fetch_assoc();

if (!$row) {
    // Table exists but has no rows yet — bot hasn't saved anything successfully
    fail("no_data_yet", 404);
}

// today's high/low from the prices table
$hl = $mysqli->query("
    SELECT MAX(price) AS high, MIN(price) AS low
    FROM usd_prices
    WHERE DATE(created_at) = CURDATE()
");

$hlRow = ($hl !== false) ? $hl->fetch_assoc() : null;

echo json_encode([
    "price" => (int)$row['price'],
    "time"  => $row['created_at'],
    "high"  => ($hlRow && $hlRow['high'] !== null) ? (int)$hlRow['high'] : null,
    "low"   => ($hlRow && $hlRow['low']  !== null) ? (int)$hlRow['low']  : null,
]);