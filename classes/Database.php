<?php
class Database {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        require_once __DIR__ . '/../config/db_config.php';
        // Pastikan variabel $pdo terdefinisi setelah require
        if (isset($pdo)) {
            $this->pdo = $pdo;
        } else {
            throw new Exception('Koneksi database gagal: $pdo tidak ditemukan di db_config.php');
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->pdo;
    }
}
?>