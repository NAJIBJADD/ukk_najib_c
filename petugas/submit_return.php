<?php
require_once '../includes/autoload.php';
if ($_SESSION['role'] !== 'petugas') {
    header("Location: ../login.php");
    exit;
}
$loanId = (int)($_GET['loan_id'] ?? 0);
if (!$loanId) {
    $_SESSION['error'] = "ID peminjaman tidak valid.";
    header("Location: search_loan.php");
    exit;
}
$loanManager = new Loan();
$loan = $loanManager->getLoanById($loanId);
if (!$loan || $loan['status'] !== 'dipinjam') {
    $_SESSION['error'] = "Peminjaman tidak aktif atau sudah kembali.";
    header("Location: search_loan.php");
    exit;
}
// Ambil detail barang dan siswa
$pdo = Database::getInstance()->getConnection();
$stmt = $pdo->prepare("SELECT l.*, i.nama_item, i.harga, u.nama_lengkap AS siswa FROM loans l JOIN items i ON l.id_item = i.id JOIN users u ON l.id_siswa = u.id WHERE l.id = ?");
$stmt->execute([$loanId]);
$detail = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $statusReturn = $_POST['status'];
    $dendaTambahan = (int)($_POST['denda_tambahan'] ?? 0);
    $catatan = trim($_POST['catatan'] ?? '');
    $rr = new ReturnRequest();
    $ok = $rr->createRequest($loanId, $_SESSION['user_id'], $statusReturn, $dendaTambahan, $catatan);
    $_SESSION[$ok ? 'success' : 'error'] = $ok ? "Permintaan pengembalian dikirim ke admin." : "Gagal mengirim permintaan.";
    header("Location: search_loan.php");
    exit;
}
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-warning text-dark">
            <h5>Ajukan Pengembalian Barang</h5>
        </div>
        <div class="card-body">
            <p><strong>Barang:</strong> <?= htmlspecialchars($detail['nama_item']) ?></p>
            <p><strong>Siswa:</strong> <?= htmlspecialchars($detail['siswa']) ?></p>
            <p><strong>Tanggal Pinjam:</strong> <?= $detail['tgl_pinjam'] ?></p>
            <p><strong>Batas Waktu:</strong> <?= $detail['batas_waktu'] ?></p>
            <p><strong>Harga Barang:</strong> Rp <?= number_format($detail['harga'], 0, ',', '.') ?></p>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Kondisi Barang <span class="text-danger">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="kembali">Baik (kembali) – tidak ada denda tambahan</option>
                        <option value="rusak">Rusak – denda 50% dari harga barang</option>
                        <option value="hilang">Hilang – denda 100% dari harga barang (ganti rugi)</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Denda Tambahan (opsional)</label>
                    <input type="number" name="denda_tambahan" class="form-control" value="0" min="0">
                    <small class="text-muted">Misal biaya administrasi, keterlambatan sudah dihitung otomatis.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Catatan</label>
                    <textarea name="catatan" class="form-control" rows="2" placeholder="Alasan kerusakan, kelengkapan, dll"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Kirim Permintaan</button>
                <a href="search_loan.php" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>