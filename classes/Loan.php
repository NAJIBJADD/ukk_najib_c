<?php
class Loan {
    private $db;
    
    // Denda tetap
    const DENDA_PER_HARI = 1000;
    const DENDA_RUSAK   = 50000;
    const DENDA_HILANG  = 200000;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Membuat peminjaman baru (default batas waktu +7 hari)
     */
    public function createLoan($siswaId, $itemId) {
        $itemObj = new Item();
        $item = $itemObj->getItemById($itemId);
        if (!$item || $item['stok'] <= 0) {
            return false;
        }
        $tgl_pinjam = date('Y-m-d H:i:s');
        $batas_waktu = date('Y-m-d H:i:s', strtotime('+7 days'));
        $stmt = $this->db->prepare("INSERT INTO loans (id_siswa, id_item, tgl_pinjam, batas_waktu, status) VALUES (?, ?, ?, ?, 'dipinjam')");
        if ($stmt->execute([$siswaId, $itemId, $tgl_pinjam, $batas_waktu])) {
            $itemObj->kurangiStok($itemId, 1);
            return true;
        }
        return false;
    }
    
    /**
     * Membuat peminjaman dengan batas waktu tertentu (misal dari request siswa)
     */
    public function createLoanWithDueDate($studentId, $itemId, $dueDate) {
        $itemObj = new Item();
        $item = $itemObj->getItemById($itemId);
        if (!$item || $item['stok'] <= 0) {
            return false;
        }
        $loanDate = date('Y-m-d H:i:s');
        $stmt = $this->db->prepare("INSERT INTO loans (id_siswa, id_item, tgl_pinjam, batas_waktu, status) VALUES (?, ?, ?, ?, 'dipinjam')");
        if ($stmt->execute([$studentId, $itemId, $loanDate, $dueDate])) {
            $itemObj->kurangiStok($itemId, 1);
            return true;
        }
        return false;
    }
    
    /**
     * Ambil semua peminjaman (lengkap dengan nama siswa & barang)
     */
    public function getAllLoans() {
        $stmt = $this->db->query("SELECT l.*, u.nama_lengkap AS siswa, i.nama_item 
                                  FROM loans l 
                                  JOIN users u ON l.id_siswa = u.id 
                                  JOIN items i ON l.id_item = i.id 
                                  ORDER BY l.tgl_pinjam DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Peminjaman berdasarkan ID siswa
     */
    public function getLoansByStudent($siswaId) {
        $stmt = $this->db->prepare("SELECT l.*, i.nama_item 
                                    FROM loans l 
                                    JOIN items i ON l.id_item = i.id 
                                    WHERE l.id_siswa = ? 
                                    ORDER BY l.tgl_pinjam DESC");
        $stmt->execute([$siswaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Peminjaman berdasarkan tanggal pinjam
     */
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
    
    /**
     * Peminjaman berdasarkan ID siswa atau NIS
     */
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
    
    /**
     * Ambil satu peminjaman berdasarkan ID loan
     */
    public function getLoanById($id) {
        $stmt = $this->db->prepare("SELECT * FROM loans WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Cek apakah siswa memiliki peminjaman terlambat
     */
    public function checkLateLoan($siswaId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM loans 
                                    WHERE id_siswa = ? AND status = 'dipinjam' AND batas_waktu < NOW()");
        $stmt->execute([$siswaId]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res['total'] > 0;
    }
    
    /**
     * Hapus data peminjaman (digunakan oleh admin setelah approve request hapus)
     */
    public function deleteLoan($loanId) {
        $loan = $this->getLoanById($loanId);
        if (!$loan) return false;
        if ($loan['status'] == 'dipinjam') {
            $itemObj = new Item();
            $itemObj->tambahStok($loan['id_item'], 1);
        }
        $stmt = $this->db->prepare("DELETE FROM loans WHERE id = ?");
        return $stmt->execute([$loanId]);
    }
    
    /**
     * Proses pengembalian barang (dipanggil oleh admin setelah approve dari petugas)
     */
    public function returnLoan($loanId, $statusReturn, $dendaTambahan = 0) {
        $loan = $this->getLoanById($loanId);
        if (!$loan) return false;
        
        $tgl_kembali = date('Y-m-d H:i:s');
        $telat_hari = 0;
        if (strtotime($tgl_kembali) > strtotime($loan['batas_waktu'])) {
            $telat_hari = floor((strtotime($tgl_kembali) - strtotime($loan['batas_waktu'])) / 86400);
        }
        $dendaTelat = $telat_hari * self::DENDA_PER_HARI;
        
        $dendaKondisi = 0;
        if ($statusReturn == 'rusak') $dendaKondisi = self::DENDA_RUSAK;
        elseif ($statusReturn == 'hilang') $dendaKondisi = self::DENDA_HILANG;
        
        $totalDenda = $dendaTelat + $dendaKondisi + $dendaTambahan;
        
        $stmt = $this->db->prepare("UPDATE loans SET tgl_kembali = ?, denda = ?, status = ? WHERE id = ?");
        $result = $stmt->execute([$tgl_kembali, $totalDenda, $statusReturn, $loanId]);
        
        if ($result) {
            $itemObj = new Item();
            if ($statusReturn == 'kembali') {
                $itemObj->tambahStok($loan['id_item'], 1);
            } elseif ($statusReturn == 'rusak') {
                $itemObj->syncStatus($loan['id_item'], false);
            } elseif ($statusReturn == 'hilang') {
                $itemObj->syncStatus($loan['id_item'], true);
            }
        }
        return $result;
    }
    
    /**
     * Laporan berdasarkan jenis status (dipinjam, kembali, rusak, hilang)
     */
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