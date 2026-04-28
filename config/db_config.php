<?php
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'perpustakaan_digital';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}
$pdo->exec("SET time_zone = '+07:00'");
?>