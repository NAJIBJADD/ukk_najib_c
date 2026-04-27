<?php
class ReturnRequest {
    private PDO $db;
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    public function createRequest($loanId, $petugasId, $statusReturn, $dendaTambahan = 0, $catatan = '') {
        $stmt = $this->db->prepare("INSERT INTO return_requests (loan_id, petugas_id, status_return, denda_tambahan, tgl_request, status, catatan) VALUES (?, ?, ?, ?, NOW(), 'pending', ?)");
        return $stmt->execute([$loanId, $petugasId, $statusReturn, $dendaTambahan, $catatan]);
    }
    public function getPendingRequests() {
        $stmt = $this->db->query("SELECT rr.*, l.id_siswa, u.nama_lengkap AS siswa, i.nama_item, p.nama_lengkap AS petugas
            FROM return_requests rr
            JOIN loans l ON rr.loan_id = l.id
            JOIN users u ON l.id_siswa = u.id
            JOIN items i ON l.id_item = i.id
            JOIN users p ON rr.petugas_id = p.id
            WHERE rr.status = 'pending'
            ORDER BY rr.tgl_request ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function approveRequest($id, $adminId) {
        $stmt = $this->db->prepare("UPDATE return_requests SET status = 'approved' WHERE id = ?");
        $ok = $stmt->execute([$id]);
        if ($ok) {
            $req = $this->getRequestById($id);
            if ($req) {
                $loan = new Loan();
                $loan->returnLoan($req['loan_id'], $req['status_return'], $req['denda_tambahan']);
                (new Log())->add($adminId, 'Setujui Pengembalian', "Return request ID $id, loan {$req['loan_id']}, status {$req['status_return']}");
            }
        }
        return $ok;
    }
    public function rejectRequest($id) {
        $stmt = $this->db->prepare("UPDATE return_requests SET status = 'rejected' WHERE id = ?");
        return $stmt->execute([$id]);
    }
    private function getRequestById($id) {
        $stmt = $this->db->prepare("SELECT * FROM return_requests WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>