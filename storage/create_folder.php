<?php
require_once '../config/database.php';
require_once '../utils/auth_check.php';

$name = trim($_POST['folder_name']);
$parent = $_POST['parent_id'] ?: null;

$stmt = $pdo->prepare(
    "INSERT INTO folders (user_id, name, parent_id)
     VALUES (?, ?, ?)"
);
$stmt->execute([$_SESSION['user_id'], $name, $parent]);

header("Location: ../public/dashboard.php");
