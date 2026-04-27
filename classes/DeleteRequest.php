<?php
class DeleteRequest {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function createRequest($loanId, $petugasId, $alasan = '') {
        $stmt = $this->db->prepare("INSERT INTO delete_requests (loan_id, id_petugas, alasan, tgl_request, status) VALUES (?, ?, ?, NOW(), 'pending')");
        return $stmt->execute([$loanId, $petugasId, $alasan]);
    }
    
    public function getAllRequests() {
        $stmt = $this->db->query("SELECT dr.*, l.id_siswa, u.nama_lengkap AS siswa, i.nama_item, p.nama_lengkap AS petugas 
                                  FROM delete_requests dr
                                  JOIN loans l ON dr.loan_id = l.id
                                  JOIN users u ON l.id_siswa = u.id
                                  JOIN items i ON l.id_item = i.id
                                  JOIN users p ON dr.id_petugas = p.id
                                  ORDER BY dr.tgl_request DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function approveRequest($id) {
        $stmt = $this->db->prepare("UPDATE delete_requests SET status = 'approved' WHERE id = ?");
        $result = $stmt->execute([$id]);
        if ($result) {
            // Ambil loan_id dari request
            $req = $this->getRequestById($id);
            if ($req) {
                // Hapus loan
                $loan = new Loan();
                $loan->deleteLoan($req['loan_id']); // perlu buat method deleteLoan di Loan
            }
        }
        return $result;
    }
    
    public function rejectRequest($id) {
        $stmt = $this->db->prepare("UPDATE delete_requests SET status = 'rejected' WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function getRequestById($id) {
        $stmt = $this->db->prepare("SELECT * FROM delete_requests WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>