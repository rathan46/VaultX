<?php
require_once '../config/database.php';
require_once '../utils/helpers.php';

$userUID = generateUserUID($pdo);
$hash = password_hash($_POST['password'], PASSWORD_DEFAULT);

$stmt = $pdo->prepare(
    "INSERT INTO users (user_uid, username, password_hash)
     VALUES (?, ?, ?)"
);
$stmt->execute([$userUID, $_POST['username'], $hash]);

header("Location: ../public/index.php");
