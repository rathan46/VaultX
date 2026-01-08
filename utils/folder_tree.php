<?php
function getFolders(PDO $pdo, $userId, $parentId = null) {
    $stmt = $pdo->prepare(
        "SELECT * FROM folders
         WHERE user_id = ? AND parent_id " .
         ($parentId === null ? "IS NULL" : "= ?")
    );

    $params = [$userId];
    if ($parentId !== null) $params[] = $parentId;

    $stmt->execute($params);
    $folders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($folders as &$f) {
        $f['children'] = getFolders($pdo, $userId, $f['id']);
    }
    return $folders;
}
