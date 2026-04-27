<?php
require_once 'includes/autoload.php';

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    echo "Koneksi berhasil!";
    $stmt = $conn->query("SELECT * FROM users");
    echo "<pre>"; print_r($stmt->fetchAll()); echo "</pre>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>