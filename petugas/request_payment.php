<?php
require_once '../includes/autoload.php';
if ($_SESSION['role'] !== 'petugas') {
    header("Location: ../login.php");
    exit;
}

$loanId = (int)($_GET['loan_id'] ?? 0);
if (!$loanId) {
    $_SESSION['error'] = "ID peminjaman tidak valid";
    header("Location: fines_list.php");
    exit;
}

// Ambil data peminjaman lengkap dengan JOIN
$pdo = Database::getInstance()->getConnection();
$stmt = $pdo->prepare("
    SELECT l.*, i.nama_item, u.nama_lengkap AS siswa 
    FROM loans l 
    JOIN items i ON l.id_item = i.id 
    JOIN users u ON l.id_siswa = u.id 
    WHERE l.id = ?
");
$stmt->execute([$loanId]);
$loan = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$loan || $loan['denda'] <= 0) {
    $_SESSION['error'] = "Denda tidak valid untuk peminjaman ini.";
    header("Location: fines_list.php");
    exit;
}

$itemName = $loan['nama_item'];
$studentName = $loan['siswa'];
$fineAmount = $loan['denda'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment = new DendaPayment();
    $success = $payment->createRequest($loanId, $_SESSION['user_id'], $fineAmount, $_POST['note'] ?? '');
    $_SESSION[$success ? 'success' : 'error'] = $success 
        ? "Permintaan pembayaran denda dikirim ke admin." 
        : "Gagal mengirim permintaan.";
    header("Location: fines_list.php");
    exit;
}
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-warning text-dark">
            <h5>Ajukan Pembayaran Denda</h5>
        </div>
        <div class="card-body">
            <p><strong>Barang:</strong> <?= htmlspecialchars($itemName) ?></p>
            <p><strong>Siswa:</strong> <?= htmlspecialchars($studentName) ?></p>
            <p><strong>Denda:</strong> Rp <?= number_format($fineAmount,0,',','.') ?></p>
            <form method="POST">
                <div class="mb-3">
                    <label>Catatan (opsional)</label>
                    <textarea name="note" class="form-control" rows="2" placeholder="Bukti transfer, dll"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Kirim Permintaan</button>
                <a href="fines_list.php" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>