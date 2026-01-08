<?php
require_once '../config/database.php';
require_once '../utils/auth_check.php';
require_once '../utils/activity_logger.php';

$fileId = (int)($_POST['file_id'] ?? 0);
$userId = $_SESSION['user_id'];

/* Fetch file & verify ownership */
$stmt = $pdo->prepare(
    "SELECT stored_name FROM files WHERE id = ? AND user_id = ?"
);
$stmt->execute([$fileId, $userId]);
$file = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$file) {
    die("Unauthorized delete attempt");
}

/* Delete physical file */
$filePath = UPLOAD_DIR . $file['stored_name'];
if (file_exists($filePath)) {
    unlink($filePath);
}

/* Delete DB record (shares auto removed) */
$stmt = $pdo->prepare("DELETE FROM files WHERE id = ?");
$stmt->execute([$fileId]);

logActivity($pdo, $_SESSION['user_id'], 'deleted', $file['stored_name']);

header("Location: ../public/dashboard.php");
exit;
