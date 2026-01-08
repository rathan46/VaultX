<?php
function logActivity(PDO $pdo, $userId, $action, $fileName = null, $targetUser = null) {
    $stmt = $pdo->prepare(
        "INSERT INTO activity_log (user_id, action, file_name, target_user)
         VALUES (?, ?, ?, ?)"
    );
    $stmt->execute([$userId, $action, $fileName, $targetUser]);
}
