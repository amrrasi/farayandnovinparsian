<?php
require_once "inc/check.php";

header('Content-Type: application/json');

$id = (int)($_POST['id'] ?? 0);

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID نامعتبر']);
    exit;
}

$stmt = $mysqli->prepare("SELECT availability FROM product WHERE id = ? AND deleted = 0");
$stmt->bind_param("i", $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) {
    echo json_encode(['success' => false, 'message' => 'محصول پیدا نشد']);
    exit;
}

$newVal = $row['availability'] == 1 ? 0 : 1;

$stmt = $mysqli->prepare("UPDATE product SET availability = ? WHERE id = ?");
$stmt->bind_param("ii", $newVal, $id);
$stmt->execute();

echo json_encode(['success' => true, 'availability' => $newVal]);
exit;