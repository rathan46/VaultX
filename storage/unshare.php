<?php
require_once '../config/database.php';
require_once '../utils/auth_check.php';
require_once '../utils/activity_logger.php';

$stmt = $pdo->prepare(
    "DELETE FROM file_shares
     WHERE file_id=? AND owner_id=? AND shared_with=?"
);
$stmt->execute([$_POST['file_id'], $_SESSION['user_id'], $_POST['shared_with']]);

logActivity($pdo, $_SESSION['user_id'], 'unshared', null, $_POST['shared_with']);

header("Location: ../public/dashboard.php");
