<?php
header('Content-Type: application/json');
require_once '../config.php';
require_once '../functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit;
}

// --- دریافت و اعتبارسنجی ورودی ---
$app_token = isset($_POST['app_token']) ? $_POST['app_token'] : '';
$user_token = isset($_POST['user_token']) ? $_POST['user_token'] : '';
$client_identifier = isset($_POST['client_identifier']) ? $_POST['client_identifier'] : '';
$os_type = isset($_POST['os_type']) ? $_POST['os_type'] : '';
$os_version = isset($_POST['os_version']) ? $_POST['os_version'] : '';
$device_model = isset($_POST['device_model']) ? $_POST['device_model'] : '';
$user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

if (empty($app_token) || empty($user_token) || empty($client_identifier) || empty($os_type) || empty($os_version) || empty($device_model)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required parameters: app_token, user_token, client_identifier, os_type, os_version, device_model']);
    exit;
}

// --- اعتبارسنجی توکن‌ها ---
$stmt = $pdo->prepare("SELECT u.id as user_id, a.id as app_id 
                       FROM users u, apps a 
                       WHERE u.user_token = ? AND a.app_token = ? AND a.user_id = u.id");
$stmt->execute([$user_token, $app_token]);
$auth = $stmt->fetch();
if (!$auth) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Invalid tokens']);
    exit;
}
$app_id = $auth['app_id'];

// --- منطق UPSERT ---
$sql = "
    INSERT INTO devices (app_id, client_identifier, os_type, os_version, device_model, user_agent)
    VALUES (?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
        os_type = VALUES(os_type),
        os_version = VALUES(os_version),
        device_model = VALUES(device_model),
        user_agent = VALUES(user_agent)
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$app_id, $client_identifier, $os_type, $os_version, $device_model, $user_agent]);

echo json_encode(['status' => 'success', 'message' => 'Device information saved successfully']);
?>
