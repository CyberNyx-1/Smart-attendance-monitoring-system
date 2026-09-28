<?php
// Returns the server's current IP address so the Android app
// can discover it without hardcoding. Students hit this endpoint
// after scanning the QR code to know which server to POST to.

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

echo json_encode([
    "success" => true,
    "server_ip" => $_SERVER['SERVER_ADDR'] ?? $_SERVER['LOCAL_ADDR'] ?? 'unknown',
    "server_url" => "http://" . $_SERVER['HTTP_HOST'],
    "checkin_endpoint" => "http://" . $_SERVER['HTTP_HOST'] . "/QR/api/qr_checkin.php"
]);
?>
