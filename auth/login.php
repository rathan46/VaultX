<?php
require_once '../config/database.php';

$stmt = $pdo->prepare("SELECT * FROM users WHERE username=?");
$stmt->execute([$_POST['username']]);
$user = $stmt->fetch();

if ($user && password_verify($_POST['password'], $user['password_hash'])) {
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_uid'] = $user['user_uid'];
    header("Location: ../public/dashboard.php");
}
