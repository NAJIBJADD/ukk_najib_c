<?php
class Database {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        require_once __DIR__ . '/../config/db_config.php';
        $this->pdo = $GLOBALS['pdo'];
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