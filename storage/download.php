<?php
require_once '../config/database.php';
require_once '../utils/auth_check.php';
require_once '../utils/activity_logger.php';

$fileId = (int)$_GET['id'];
$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare(
    "SELECT f.*, u.user_uid
     FROM files f
     JOIN users u ON f.user_id = u.id
     LEFT JOIN file_shares s ON f.id = s.file_id
     WHERE f.id = ?
       AND (f.user_id = ? OR s.shared_with = ?)"
);
$stmt->execute([$fileId, $userId, $userId]);
$file = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$file) {
    http_response_code(403);
    die("Access denied");
}

$key = hash('sha256', $file['user_uid'], true);
$data = file_get_contents(UPLOAD_DIR . $file['stored_name']);

$iv     = substr($data, 0, 16);
$cipher = substr($data, 16);

$plain = openssl_decrypt(
    $cipher,
    'AES-256-CBC',
    $key,
    OPENSSL_RAW_DATA,
    $iv
);
logActivity($pdo, $_SESSION['user_id'], 'downloaded', $file['original_name']);

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="'.$file['original_name'].'"');

echo $plain;
exit;
