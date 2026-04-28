<?php
class Log {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function add(int $userId, string $activity, string $description = ''): bool {
        $stmt = $this->db->prepare("INSERT INTO logs (user_id, kegiatan, keterangan, timestamp) VALUES (?, ?, ?, NOW())");
        return $stmt->execute([$userId, $activity, $description]);
    }

    public function getAllLogs(int $limit = 100): array {
        $sql = "SELECT l.*, u.nama_lengkap FROM logs l JOIN users u ON l.user_id = u.id ORDER BY l.timestamp DESC LIMIT " . (int)$limit;
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLatestLogs(int $limit = 5): array {
        $sql = "SELECT l.*, u.nama_lengkap FROM logs l JOIN users u ON l.user_id = u.id ORDER BY l.timestamp DESC LIMIT " . (int)$limit;
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function clearAllLogs(): bool {
        $stmt = $this->db->prepare("TRUNCATE TABLE logs");
        return $stmt->execute();
    }
}
?>