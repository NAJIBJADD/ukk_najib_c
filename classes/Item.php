<?php
class Item {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function getAllItems() {
        $stmt = $this->db->query("SELECT * FROM items ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getItemById($id) {
        $stmt = $this->db->prepare("SELECT * FROM items WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getItemByBarcode($barcode) {
        $stmt = $this->db->prepare("SELECT * FROM items WHERE barcode = ? AND status = 'tersedia'");
        $stmt->execute([$barcode]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function addItem($nama, $kategori, $deskripsi) {
        $barcode = 'BAR-' . strtoupper(uniqid());
        $stmt = $this->db->prepare("INSERT INTO items (barcode, nama_item, kategori, deskripsi) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$barcode, $nama, $kategori, $deskripsi]);
    }
    
    public function updateItem($id, $nama, $kategori, $deskripsi, $status) {
        $stmt = $this->db->prepare("UPDATE items SET nama_item = ?, kategori = ?, deskripsi = ?, status = ? WHERE id = ?");
        return $stmt->execute([$nama, $kategori, $deskripsi, $status, $id]);
    }
    
    public function deleteItem($id) {
        $stmt = $this->db->prepare("DELETE FROM items WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function updateStatus($id, $status) {
        $stmt = $this->db->prepare("UPDATE items SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }
}
?>