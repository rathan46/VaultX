<?php
require_once '../config/database.php';
require_once '../utils/auth_check.php';
require_once '../utils/helpers.php';
require_once '../utils/activity_logger.php';

if (!isset($_FILES['file'])) {
    header("Location: ../public/dashboard.php");
    exit;
}

$dbUserId = $_SESSION['user_id'];
$folderId = $_POST['folder_id'] ?? null;

/* ===== ENCRYPTION SETUP ===== */
$key = hash('sha256', $_SESSION['user_uid'], true);
$iv  = random_bytes(16);

/* Generate stored filename */
$stored = generateFileName();

/* Read uploaded file */
$plain = file_get_contents($_FILES['file']['tmp_name']);

/* Encrypt */
$cipher = openssl_encrypt(
    $plain,
    'AES-256-CBC',
    $key,
    OPENSSL_RAW_DATA,
    $iv
);

/* Save IV + encrypted data */
file_put_contents(UPLOAD_DIR . $stored, $iv . $cipher);

/* ===== SAVE METADATA (IMPORTANT FIX) ===== */
$stmt = $pdo->prepare(
    "INSERT INTO files
     (user_id, original_name, stored_name, file_size, folder_id)
     VALUES (?, ?, ?, ?, ?)"
);

$stmt->execute([
    $dbUserId,
    $_FILES['file']['name'],
    $stored,
    $_FILES['file']['size'],
    $folderId ?: null
]);

/* Log activity */
logActivity($pdo, $dbUserId, 'uploaded', $_FILES['file']['name']);

/* ===== REDIRECT BACK TO SAME FOLDER ===== */
header(
    "Location: ../public/dashboard.php" .
    ($folderId ? "?folder=$folderId" : "")
);
exit;
