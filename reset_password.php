<?php
require_once 'includes/autoload.php';
$pdo = Database::getInstance()->getConnection();
$hash = password_hash('123456', PASSWORD_DEFAULT);
$pdo->prepare("UPDATE users SET password = ?")->execute([$hash]);
echo "Password semua user direset ke 123456. <a href='login.php'>Login</a>";