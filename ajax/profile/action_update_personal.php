<?php
require_once '../../cms/myadmin/inc/config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'ok' => false,
        'error' => 'method_not_allowed'
    ]);
    exit;
}

$uid = $_SESSION['user']['id'];

$name        = trim($_POST['name'] ?? '');
$email       = trim($_POST['email'] ?? '');
$description = trim($_POST['description'] ?? '');

$errors = [];

/* Validation */

if ($name === '' || mb_strlen($name, 'UTF-8') > 150) {
    $errors['name'] = 'نام معتبر نیست.';
}

if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'ایمیل معتبر نیست.';
}

if (mb_strlen($description, 'UTF-8') > 1000) {
    $errors['description'] = 'توضیحات بیش از حد مجاز طولانی است.';
}

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode([
        'ok' => false,
        'errors' => $errors
    ]);
    exit;
}

/* Check duplicate email */

if ($email !== '') {

    $stmt = $pdo->prepare("
        SELECT id
        FROM `user`
        WHERE email = :email
          AND id != :uid
          AND deleted = 0
        LIMIT 1
    ");

    $stmt->execute([
        ':email' => $email,
        ':uid'   => $uid
    ]);

    if ($stmt->fetch()) {
        http_response_code(422);

        echo json_encode([
            'ok' => false,
            'errors' => [
                'email' => 'این ایمیل قبلاً استفاده شده است.'
            ]
        ]);

        exit;
    }
}

/* Update user */

$stmt = $pdo->prepare("
    UPDATE `user`
    SET
        name = :name,
        email = :email,
        description = :description,
        updated_at = NOW()
    WHERE id = :uid
");

$stmt->execute([
    ':name'        => $name,
    ':email'       => $email !== '' ? $email : null,
    ':description' => $description !== '' ? $description : null,
    ':uid'         => $uid
]);

/* Refresh session */

$stmt = $pdo->prepare("
    SELECT *
    FROM `user`
    WHERE id = :uid
    LIMIT 1
");

$stmt->execute([
    ':uid' => $uid
]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    $_SESSION['user'] = $user;
}

/* Response */

echo json_encode([
    'ok' => true,
    'name' => $user['name'] ?? $name
]);