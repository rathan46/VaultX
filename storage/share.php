<?php
require_once '../config/database.php';
require_once '../utils/auth_check.php';
require_once '../utils/activity_logger.php';

$stmt = $pdo->prepare("SELECT id FROM users WHERE username=?");
$stmt->execute([$_POST['username']]);
$u = $stmt->fetch();

$stmt = $pdo->prepare(
    "INSERT IGNORE INTO file_shares (file_id, owner_id, shared_with)
     VALUES (?, ?, ?)"
);
$stmt->execute([$_POST['file_id'], $_SESSION['user_id'], $u['id']]);

logActivity($pdo, $_SESSION['user_id'], 'shared', $fileName, $_POST['username']);

header("Location: ../public/dashboard.php");
