<?php
require_once "../cms/myadmin/inc/config.php";
header("Content-Type: application/json; charset=UTF-8");


require '../vendor/autoload.php';

$host = "localhost";
$user = "amir";
$pass = "amirdbpass83";
$dbname = "plastic";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "❌ خطا در اتصال به دیتابیس"]);
    exit;
}

$conn->set_charset("utf8mb4");

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$message = trim($_POST['message'] ?? '');

if (empty($name) || empty($email) || empty($message)) {
    echo json_encode(["status" => "error", "message" => "⚠ لطفا همه فیلدها را پر کنید."]);
    exit;
}

if (!empty($phone)) {
    if (!preg_match('/^[0-9]+$/', $phone)) {
        echo json_encode([
            "status" => "error",
            "message" => "⚠ شماره تماس فقط باید شامل اعداد باشد."
        ]);
        exit;
    }

    if (strlen($phone) != 11) {
        echo json_encode([
            "status" => "error",
            "message" => "⚠ شماره تماس باید 11 رقم باشد."
        ]);
        exit;
    }
}

try {
    $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, phone, message) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $phone, $message);
    $stmt->execute();
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "❌ خطا در ذخیره پیام در دیتابیس"]);
    $conn->close();
    exit;
}

try {

    echo json_encode([
        "status" => "success",
        "message" => "✅ پیام شما با موفقیت ارسال شد"
    ]);

} catch (Exception $e) {
    echo json_encode([
        "status" => "warning",
        "message" => "پیام شما ذخیره شد ولی ایمیل تأیید به دلیل خطا ارسال نشد ❌"
    ]);
}

$conn->close();
