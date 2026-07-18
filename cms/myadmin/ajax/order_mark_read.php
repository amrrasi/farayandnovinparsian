<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once "../../inc/check.php";

if (empty($_SESSION['admin'])) {
    http_response_code(401);
    echo json_encode(['ok' => false]);
    exit;
}

$id   = (int) ($_POST['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(['ok' => false]);
    exit;
}

$stmt = $mysqli->prepare("UPDATE orders SET visited = 1 WHERE id = ? AND deleted = 0");
$stmt->bind_param("i", $id);
$stmt->execute();

echo json_encode(['ok' => true]);