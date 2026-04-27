<?php
class Loan {
    private $db;
    
    const DENDA_PER_HARI = 2000;
    const DENDA_RUSAK = 50000;
    const DENDA_HILANG = 200000;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function createLoan($siswaId, $itemId) {
        $tgl_pinjam = date('Y-m-d H:i:s');
        $batas_waktu = date('Y-m-d H:i:s', strtotime('+7 days'));
        $stmt = $this->db->prepare("INSERT INTO loans (id_siswa, id_item, tgl_pinjam, batas_waktu, status) VALUES (?, ?, ?, ?, 'dipinjam')");
        if ($stmt->execute([$siswaId, $itemId, $tgl_pinjam, $batas_waktu])) {
            $item = new Item();
            $item->updateStatus($itemId, 'dipinjam');
            return true;
        }
        return false;
    }
    
    public function getAllLoans() {
        $stmt = $this->db->query("SELECT l.*, u.nama_lengkap AS siswa, i.nama_item 
                                  FROM loans l 
                                  JOIN users u ON l.id_siswa = u.id 
                                  JOIN items i ON l.id_item = i.id 
                                  ORDER BY l.tgl_pinjam DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getLoansByStudent($siswaId) {
        $stmt = $this->db->prepare("SELECT l.*, i.nama_item, i.barcode 
                                    FROM loans l 
                                    JOIN items i ON l.id_item = i.id 
                                    WHERE l.id_siswa = ? 
                                    ORDER BY l.tgl_pinjam DESC");
        $stmt->execute([$siswaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getLoansByDate($date) {
        $stmt = $this->db->prepare("SELECT l.*, u.nama_lengkap AS siswa, i.nama_item 
                                    FROM loans l 
                                    JOIN users u ON l.id_siswa = u.id 
                                    JOIN items i ON l.id_item = i.id 
                                    WHERE DATE(l.tgl_pinjam) = ? 
                                    ORDER BY l.tgl_pinjam DESC");
        $stmt->execute([$date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // === METHOD BARU UNTUK PENCARIAN BERDASARKAN ID ATAU NIS SISWA ===
    public function getLoansByStudentIdOrNis($search) {
        $stmt = $this->db->prepare("SELECT l.*, u.nama_lengkap AS siswa, u.nis, i.nama_item 
                                    FROM loans l 
                                    JOIN users u ON l.id_siswa = u.id 
                                    JOIN items i ON l.id_item = i.id 
                                    WHERE u.id = ? OR u.nis = ? 
                                    ORDER BY l.tgl_pinjam DESC");
        $stmt->execute([$search, $search]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function returnLoan($loanId, $statusReturn, $dendaTambahan = 0) {
        $loan = $this->getLoanById($loanId);
        if (!$loan) return false;
        
        $tgl_kembali = date('Y-m-d H:i:s');
        $telat_hari = 0;
        if (strtotime($tgl_kembali) > strtotime($loan['batas_waktu'])) {
            $telat_hari = floor((strtotime($tgl_kembali) - strtotime($loan['batas_waktu'])) / (60*60*24));
        }
        $dendaTelat = $telat_hari * self::DENDA_PER_HARI;
        
        $dendaRusakHilang = 0;
        if ($statusReturn == 'rusak') $dendaRusakHilang = self::DENDA_RUSAK;
        elseif ($statusReturn == 'hilang') $dendaRusakHilang = self::DENDA_HILANG;
        
        $totalDenda = $dendaTelat + $dendaRusakHilang + $dendaTambahan;
        
        $stmt = $this->db->prepare("UPDATE loans SET tgl_kembali = ?, denda = ?, status = ? WHERE id = ?");
        $result = $stmt->execute([$tgl_kembali, $totalDenda, $statusReturn, $loanId]);
        
        if ($result) {
            $item = new Item();
            if ($statusReturn == 'kembali') $item->updateStatus($loan['id_item'], 'tersedia');
            elseif ($statusReturn == 'rusak') $item->updateStatus($loan['id_item'], 'rusak');
            elseif ($statusReturn == 'hilang') $item->updateStatus($loan['id_item'], 'hilang');
        }
        return $result;
    }
    
    public function getLoanById($id) {
        $stmt = $this->db->prepare("SELECT * FROM loans WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function checkLateLoan($siswaId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM loans 
                                    WHERE id_siswa = ? AND status = 'dipinjam' AND batas_waktu < NOW()");
        $stmt->execute([$siswaId]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res['total'] > 0;
    }
    
    public function getReport($type) {
        $sql = "";
        if ($type == 'dipinjam') {
            $sql = "SELECT l.*, u.nama_lengkap AS siswa, i.nama_item FROM loans l 
                    JOIN users u ON l.id_siswa=u.id JOIN items i ON l.id_item=i.id 
                    WHERE l.status='dipinjam' ORDER BY l.batas_waktu ASC";
        } elseif ($type == 'kembali') {
            $sql = "SELECT l.*, u.nama_lengkap AS siswa, i.nama_item FROM loans l 
                    JOIN users u ON l.id_siswa=u.id JOIN items i ON l.id_item=i.id 
                    WHERE l.status='kembali' ORDER BY l.tgl_kembali DESC";
        } elseif ($type == 'rusak') {
            $sql = "SELECT l.*, u.nama_lengkap AS siswa, i.nama_item FROM loans l 
                    JOIN users u ON l.id_siswa=u.id JOIN items i ON l.id_item=i.id 
                    WHERE l.status='rusak'";
        } elseif ($type == 'hilang') {
            $sql = "SELECT l.*, u.nama_lengkap AS siswa, i.nama_item FROM loans l 
                    JOIN users u ON l.id_siswa=u.id JOIN items i ON l.id_item=i.id 
                    WHERE l.status='hilang'";
        } else return [];
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>