<?php
class DeleteRequest {
    private $db;
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    // Parameter $petugasId disimpan ke kolom 'id_petugas' (sesuaikan jika kolom berbeda)
    public function createRequest($loanId, $petugasId, $alasan = '') {
        $stmt = $this->db->prepare("INSERT INTO delete_requests (loan_id, id_petugas, alasan, tgl_request, status) VALUES (?, ?, ?, NOW(), 'pending')");
        return $stmt->execute([$loanId, $petugasId, $alasan]);
    }
    
    public function getPendingRequests() {
        // JOIN menggunakan dr.id_petugas = p.id
        $stmt = $this->db->query("SELECT dr.*, l.id_siswa, u.nama_lengkap AS siswa, i.nama_item, p.nama_lengkap AS petugas 
            FROM delete_requests dr 
            JOIN loans l ON dr.loan_id = l.id 
            JOIN users u ON l.id_siswa = u.id 
            JOIN items i ON l.id_item = i.id 
            JOIN users p ON dr.id_petugas = p.id 
            WHERE dr.status = 'pending' 
            ORDER BY dr.tgl_request ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function approveRequest($id) {
        $stmt = $this->db->prepare("UPDATE delete_requests SET status = 'approved' WHERE id = ?");
        $ok = $stmt->execute([$id]);
        if ($ok) {
            $req = $this->getRequestById($id);
            if ($req) {
                $loan = new Loan();
                $loan->deleteLoan($req['loan_id']);
                if (isset($_SESSION['user_id'])) {
                    (new Log())->add($_SESSION['user_id'], 'Setujui Hapus Peminjaman', "Loan ID {$req['loan_id']} dihapus");
                }
            }
        }
        return $ok;
    }
    
    public function rejectRequest($id) {
        $stmt = $this->db->prepare("UPDATE delete_requests SET status = 'rejected' WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    private function getRequestById($id) {
        $stmt = $this->db->prepare("SELECT * FROM delete_requests WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>