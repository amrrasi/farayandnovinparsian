<?php

require_once __DIR__ . "/../inc/config.php";

// 🔑 Get your free key from Telegram: @navasan_contact_bot
const NAVASAN_API_KEY = "free7NM31nZQRXcMNSRmVh3R85Qjepm1";

// item codes: usd_sell (تهران فروش), usd_buy (تهران خرید)
const NAVASAN_ITEM = "usd_sell";

function getDollarPrice()
{
    $url = "http://api.navasan.tech/latest/?api_key=" . NAVASAN_API_KEY . "&item=" . NAVASAN_ITEM;

    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_USERAGENT => "Mozilla/5.0",
        CURLOPT_SSL_VERIFYPEER => true,
    ]);

    $res = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($res === false) {
        error_log("USD bot cURL error: " . $curlError);
        return null;
    }

    if ($httpCode !== 200) {
        error_log("USD bot HTTP error: $httpCode — response: $res");
        return null;
    }

    $data = json_decode($res, true);

    // Navasan returns: { "usd_sell": { "value": "161300", "change": ..., ... } }
    if (!isset($data[NAVASAN_ITEM]['value'])) {
        error_log("USD bot unexpected response: " . $res);
        return null;
    }

    return (int)$data[NAVASAN_ITEM]['value'];
}

/* DB */
function getLastPrice($mysqli)
{
    $res = $mysqli->query("
        SELECT price
        FROM usd_prices
        ORDER BY id DESC
        LIMIT 1
    ");

    $row = $res->fetch_assoc();

    return (int)($row['price'] ?? 0);
}

/* MAIN */
$price = getDollarPrice();

if (!$price) {
    echo "❌ API error";
    exit;
}

$last = getLastPrice($mysqli);

if ($price == $last) {
    echo "⏸ No change: " . number_format($price);
    exit;
}

$stmt = $mysqli->prepare("
    INSERT INTO usd_prices (price, source)
    VALUES (?, 'navasan_api')
");

$stmt->bind_param("i", $price);
$stmt->execute();

echo "✅ Saved: " . number_format($price);