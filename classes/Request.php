<?php
class Request {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Buat permintaan peminjaman dengan tanggal kembali yang diinginkan siswa.
     */
    public function createRequest($siswaId, $itemId, $returnDate = null, $catatan = '') {
        $tgl_request = date('Y-m-d H:i:s');
        $stmt = $this->db->prepare("INSERT INTO requests (id_siswa, id_item, tgl_request, status, catatan, requested_return_date) VALUES (?, ?, ?, 'pending', ?, ?)");
        return $stmt->execute([$siswaId, $itemId, $tgl_request, $catatan, $returnDate]);
    }
    
    public function getAllRequests() {
        $stmt = $this->db->query("SELECT r.*, u.nama_lengkap AS siswa, i.nama_item 
                                  FROM requests r 
                                  JOIN users u ON r.id_siswa = u.id 
                                  JOIN items i ON r.id_item = i.id 
                                  ORDER BY r.tgl_request DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getRequestsByStudent($siswaId) {
        $stmt = $this->db->prepare("SELECT r.*, i.nama_item 
                                    FROM requests r 
                                    JOIN items i ON r.id_item = i.id 
                                    WHERE r.id_siswa = ? 
                                    ORDER BY r.tgl_request DESC");
        $stmt->execute([$siswaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Hapus request berdasarkan ID
     */
    public function deleteRequest($requestId) {
        $stmt = $this->db->prepare("DELETE FROM requests WHERE id = ?");
        return $stmt->execute([$requestId]);
    }
    
    /**
     * Update status request, jika disetujui maka buat peminjaman dengan batas waktu sesuai requested_return_date.
     * Setelah berhasil membuat peminjaman, hapus request.
     */
    public function updateRequestStatus($requestId, $status, $petugasId) {
        $stmt = $this->db->prepare("UPDATE requests SET status = ? WHERE id = ?");
        $result = $stmt->execute([$status, $requestId]);
        
        if ($result && $status == 'disetujui') {
            $req = $this->getRequestById($requestId);
            if ($req) {
                $loanObj = new Loan();
                // Gunakan requested_return_date jika ada, jika tidak default +7 hari
                if (!empty($req['requested_return_date'])) {
                    $batas_waktu = $req['requested_return_date'] . ' 23:59:59';
                } else {
                    $batas_waktu = date('Y-m-d H:i:s', strtotime('+7 days'));
                }
                $loanCreated = $loanObj->createLoanWithDueDate($req['id_siswa'], $req['id_item'], $batas_waktu);
                if ($loanCreated) {
                    // Hapus request karena sudah diproses menjadi peminjaman
                    $this->deleteRequest($requestId);
                    $log = new Log();
                    $log->add($petugasId, 'Setujui Request', "Request ID $requestId, siswa {$req['id_siswa']}, item {$req['id_item']}, batas waktu $batas_waktu");
                    return true;
                }
            }
            return false;
        }
        return $result;
    }
    
    public function getRequestById($id) {
        $stmt = $this->db->prepare("SELECT * FROM requests WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>