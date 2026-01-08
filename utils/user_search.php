<?php
require_once '../config/database.php';
require_once '../utils/auth_check.php';

/*
 * This file provides username suggestions
 * Used during file sharing (autocomplete)
 */

header('Content-Type: application/json');

$q = trim($_GET['q'] ?? '');

/* Basic validation */
if ($q === '' || strlen($q) < 1) {
    echo json_encode([]);
    exit;
}

/* Fetch matching usernames (excluding current user) */
$stmt = $pdo->prepare(
    "SELECT username
     FROM users
     WHERE username LIKE ?
       AND id != ?
     ORDER BY username ASC
     LIMIT 5"
);

$stmt->execute([
    $q . '%',
    $_SESSION['user_id']
]);

$usernames = $stmt->fetchAll(PDO::FETCH_COLUMN);

/* Output JSON */
echo json_encode($usernames);
exit;
