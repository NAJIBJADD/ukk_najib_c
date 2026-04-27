<?php
class Log {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function add($userId, $kegiatan, $keterangan = '') {
        $stmt = $this->db->prepare("INSERT INTO logs (user_id, kegiatan, keterangan, timestamp) VALUES (?, ?, ?, NOW())");
        return $stmt->execute([$userId, $kegiatan, $keterangan]);
    }
    
    public function getAllLogs() {
        $stmt = $this->db->query("SELECT l.*, u.nama_lengkap FROM logs l JOIN users u ON l.user_id = u.id ORDER BY l.timestamp DESC LIMIT 100");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>