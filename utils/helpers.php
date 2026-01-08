<?php
function generateFileName() {
    return bin2hex(random_bytes(16)) . '.vaultx';
}

function generateUserUID(PDO $pdo) {
    do {
        $uid = random_int(1000000000000000, 9999999999999999);
        $stmt = $pdo->prepare("SELECT id FROM users WHERE user_uid = ?");
        $stmt->execute([$uid]);
    } while ($stmt->fetch());

    return $uid;
}
