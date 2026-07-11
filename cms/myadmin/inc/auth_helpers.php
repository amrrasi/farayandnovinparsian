<?php

declare(strict_types=1);

function jsonRespond(bool $status, string $message, array $extra = []): void
{
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode(array_merge([
        'status'  => $status,
        'message' => $message,
    ], $extra), JSON_UNESCAPED_UNICODE);

    exit;
}

function verifyCsrf(): void
{
    $sent  = $_POST['csrf'] ?? '';
    $saved = $_SESSION['csrf_token'] ?? '';

    if ($saved === '' || $sent === '' || !hash_equals($saved, $sent)) {

        http_response_code(419);

        jsonRespond(false, 'نشست شما منقضی شده است، صفحه را رفرش کنید.');
    }
}

function sanitizeRedirect(?string $path, string $fallback = '/'): string
{
    if (!$path) {
        return $fallback;
    }

    $path = trim($path);

    if ($path === '' || $path[0] !== '/' || str_starts_with($path, '//')) {
        return $fallback;
    }

    return $path;
}

function isValidIranianMobile(string $mobile): bool
{
    return (bool) preg_match('/^09\d{9}$/', $mobile);
}

function isStrongEnoughPassword(string $password): bool
{
    // At least 8 characters, containing at least one letter and one digit.
    return strlen($password) >= 8
        && preg_match('/[A-Za-z]/', $password)
        && preg_match('/\d/', $password);
}

function establishUserSession(array $user): void
{
    session_regenerate_id(true);

    $_SESSION['user'] = [
        'id'     => (int) $user['id'],
        'name'   => $user['name'],
        'mobile' => $user['mobile'],
        'email'  => $user['email'],
    ];
}
