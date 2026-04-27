<?php

class DendaPayment {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Petugas membuat permintaan pembayaran denda.
     */
    public function createRequest(int $loanId, int $petugasId, int $amount, string $note = ''): bool {
        $stmt = $this->db->prepare(
            "INSERT INTO denda_payments (loan_id, petugas_id, jumlah_denda, tgl_request, status, catatan) 
             VALUES (?, ?, ?, NOW(), 'pending', ?)"
        );
        return $stmt->execute([$loanId, $petugasId, $amount, $note]);
    }

    /**
     * Admin mendapatkan semua permintaan pending.
     */
    public function getPendingRequests(): array {
        $stmt = $this->db->query(
            "SELECT dp.*, l.id_siswa, u.nama_lengkap AS siswa, i.nama_item, p.nama_lengkap AS petugas
             FROM denda_payments dp
             JOIN loans l ON dp.loan_id = l.id
             JOIN users u ON l.id_siswa = u.id
             JOIN items i ON l.id_item = i.id
             JOIN users p ON dp.petugas_id = p.id
             WHERE dp.status = 'pending'
             ORDER BY dp.tgl_request ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Admin menyetujui permintaan pembayaran.
     */
    public function approveRequest(int $paymentId, int $adminId): bool {
        $stmt = $this->db->prepare("UPDATE denda_payments SET status = 'approved' WHERE id = ?");
        $ok = $stmt->execute([$paymentId]);
        if ($ok) {
            // Ambil loan_id dan jumlah denda
            $pay = $this->getPaymentById($paymentId);
            if ($pay) {
                // Set denda menjadi 0 di loans (telah dibayar)
                $upd = $this->db->prepare("UPDATE loans SET denda = 0 WHERE id = ?");
                $upd->execute([$pay['loan_id']]);
                // Catat log
                $log = new Log();
                $log->add($adminId, 'Setujui Pembayaran Denda', "Payment ID $paymentId, Loan {$pay['loan_id']}");
            }
        }
        return $ok;
    }

    /**
     * Admin menolak permintaan.
     */
    public function rejectRequest(int $paymentId): bool {
        $stmt = $this->db->prepare("UPDATE denda_payments SET status = 'rejected' WHERE id = ?");
        return $stmt->execute([$paymentId]);
    }

    private function getPaymentById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM denda_payments WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}