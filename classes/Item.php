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
        $stmt = $this->db->prepare("SELECT * FROM items WHERE barcode = ? AND status = 'tersedia' AND stok > 0");
        $stmt->execute([$barcode]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function addItem($nama, $kategori, $deskripsi, $stok = 1, $harga = 0) {
        $barcode = 'BAR-' . strtoupper(uniqid());
        $stmt = $this->db->prepare("INSERT INTO items (barcode, nama_item, kategori, deskripsi, stok, harga) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$barcode, $nama, $kategori, $deskripsi, $stok, $harga]);
    }
    
    public function updateItem($id, $nama, $kategori, $deskripsi, $status, $stok, $harga) {
        $stmt = $this->db->prepare("UPDATE items SET nama_item = ?, kategori = ?, deskripsi = ?, status = ?, stok = ?, harga = ? WHERE id = ?");
        return $stmt->execute([$nama, $kategori, $deskripsi, $status, $stok, $harga, $id]);
    }
    
    public function deleteItem($id) {
        $stmt = $this->db->prepare("DELETE FROM items WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function updateStatus($id, $status) {
        $stmt = $this->db->prepare("UPDATE items SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }
    
    public function kurangiStok($id, $jumlah = 1) {
        $stmt = $this->db->prepare("UPDATE items SET stok = stok - ? WHERE id = ? AND stok >= ?");
        $stmt->execute([$jumlah, $id, $jumlah]);
        $affected = $stmt->rowCount();
        if ($affected) {
            $stmt2 = $this->db->prepare("UPDATE items SET status = 'habis' WHERE id = ? AND stok = 0");
            $stmt2->execute([$id]);
        }
        return $affected > 0;
    }
    
    public function tambahStok($id, $jumlah = 1) {
        $stmt = $this->db->prepare("UPDATE items SET stok = stok + ? WHERE id = ?");
        $stmt->execute([$jumlah, $id]);
        $stmt2 = $this->db->prepare("UPDATE items SET status = 'tersedia' WHERE id = ? AND stok > 0");
        $stmt2->execute([$id]);
        return $stmt->rowCount() > 0;
    }
    
    public function getHarga($id) {
        $stmt = $this->db->prepare("SELECT harga FROM items WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (int)$result['harga'] : 0;
    }
}
?>