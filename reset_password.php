<?php
require_once 'includes/autoload.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    $password = '123456';
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ?");
    $stmt->execute([$hashed]);
    echo "Password berhasil direset ke 123456. <a href='login.php'>Login</a>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>