<?php
header('Content-Type: application/json');
require_once '../config.php';
require_once '../functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit;
}

$app_token = isset($_POST['app_token']) ? $_POST['app_token'] : '';
$user_token = isset($_POST['user_token']) ? $_POST['user_token'] : '';
$tag = isset($_POST['tag']) ? $_POST['tag'] : 'general';
$message = isset($_POST['message']) ? $_POST['message'] : '';
$client_identifier = isset($_POST['client_identifier']) ? $_POST['client_identifier'] : '';
$ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';

if (empty($app_token) || empty($user_token) || empty($message) || empty($client_identifier)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required parameters: app_token, user_token, message, client_identifier']);
    exit;
}

// Validate both tokens
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

$log_uuid = generateUUID();

$stmt = $pdo->prepare("INSERT INTO logs (app_id, log_uuid, client_identifier, tag, message, ip_address) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->execute([$auth['app_id'], $log_uuid, $client_identifier, $tag, $message, $ip]);

echo json_encode(['status' => 'success', 'log_uuid' => $log_uuid]);
?>