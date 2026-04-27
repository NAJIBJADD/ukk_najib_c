<?php
class Request {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function createRequest($siswaId, $itemId, $catatan = '') {
        $tgl_request = date('Y-m-d H:i:s');
        $stmt = $this->db->prepare("INSERT INTO requests (id_siswa, id_item, tgl_request, status, catatan) VALUES (?, ?, ?, 'pending', ?)");
        return $stmt->execute([$siswaId, $itemId, $tgl_request, $catatan]);
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
    
    public function updateRequestStatus($requestId, $status, $petugasId) {
        $stmt = $this->db->prepare("UPDATE requests SET status = ? WHERE id = ?");
        $result = $stmt->execute([$status, $requestId]);
        
        if ($result && $status == 'disetujui') {
            $req = $this->getRequestById($requestId);
            if ($req) {
                $loanObj = new Loan();
                $tgl_pinjam = date('Y-m-d H:i:s');
                $batas_waktu = date('Y-m-d H:i:s', strtotime('+7 days'));
                $insertLoan = $this->db->prepare("INSERT INTO loans (id_siswa, id_item, tgl_pinjam, batas_waktu, status) VALUES (?, ?, ?, ?, 'dipinjam')");
                $loanCreated = $insertLoan->execute([$req['id_siswa'], $req['id_item'], $tgl_pinjam, $batas_waktu]);
                if ($loanCreated) {
                    $itemObj = new Item();
                    $itemObj->updateStatus($req['id_item'], 'dipinjam');
                    $log = new Log();
                    $log->add($petugasId, 'Setujui Request', "Request ID $requestId, siswa {$req['id_siswa']}, item {$req['id_item']}");
                }
                return $loanCreated;
            }
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