<?php
require_once '../includes/autoload.php';
if ($_SESSION['role'] !== 'petugas') {
    header("Location: ../login.php");
    exit;
}

$loanId = (int)($_GET['loan_id'] ?? 0);
if (!$loanId) {
    $_SESSION['error'] = "ID peminjaman tidak valid";
    header("Location: search_loan.php");
    exit;
}

$loanManager = new Loan();
$loan = $loanManager->getLoanById($loanId);
if (!$loan || $loan['denda'] <= 0 || $loan['status'] == 'dipinjam') {
    $_SESSION['error'] = "Peminjaman tidak memiliki denda atau masih aktif";
    header("Location: search_loan.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $payment = new DendaPayment();
    $ok = $payment->createRequest($loanId, $_SESSION['user_id'], $loan['denda'], $_POST['note'] ?? '');
    if ($ok) {
        $_SESSION['success'] = "Permintaan pembayaran denda dikirim ke admin.";
    } else {
        $_SESSION['error'] = "Gagal mengirim permintaan.";
    }
    header("Location: search_loan.php");
    exit;
}
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-warning">
            <h5>Ajukan Pembayaran Denda</h5>
        </div>
        <div class="card-body">
            <p>Peminjaman: <strong><?= htmlspecialchars($loan['nama_item']) ?></strong></p>
            <p>Siswa: <strong><?= htmlspecialchars($loan['siswa']) ?></strong></p>
            <p>Denda yang harus dibayar: <strong>Rp <?= number_format($loan['denda'],0,',','.') ?></strong></p>
            <form method="POST">
                <div class="mb-3">
                    <label>Catatan (opsional)</label>
                    <textarea name="note" class="form-control" rows="2" placeholder="Bukti transfer, dll"></textarea>
                </div>
                <button type="submit" name="submit" class="btn btn-primary">Kirim Permintaan Pembayaran</button>
                <a href="search_loan.php" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>