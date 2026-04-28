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

// Ambil data loan dengan detail barang
$pdo = Database::getInstance()->getConnection();
$stmt = $pdo->prepare("
    SELECT l.*, i.nama_item, i.harga, i.gambar as gambar_barang, u.nama_lengkap AS siswa 
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

// Proses upload gambar
function uploadBukti($file) {
    $targetDir = "../assets/uploads/bukti/";
    if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);
    $fileName = time() . '_' . basename($file['name']);
    $targetFile = $targetDir . $fileName;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    $allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array($imageFileType, $allowedTypes)) return false;
    if ($file['size'] > 2 * 1024 * 1024) return false;
    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        return 'assets/uploads/bukti/' . $fileName;
    }
    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gambar = '';
    if (isset($_FILES['bukti']) && $_FILES['bukti']['error'] == 0) {
        $upload = uploadBukti($_FILES['bukti']);
        if ($upload) $gambar = $upload;
        else $_SESSION['error'] = "Gagal upload bukti pembayaran (max 2MB, jpg/png/webp)";
    }
    $payment = new DendaPayment();
    $success = $payment->createRequest($loanId, $_SESSION['user_id'], $loan['denda'], $_POST['note'] ?? '', $gambar);
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
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Barang:</strong> <?= htmlspecialchars($loan['nama_item']) ?></p>
                    <p><strong>Siswa:</strong> <?= htmlspecialchars($loan['siswa']) ?></p>
                    <p><strong>Denda:</strong> Rp <?= number_format($loan['denda'],0,',','.') ?></p>
                    <?php if ($loan['gambar_barang'] && file_exists("../".$loan['gambar_barang'])): ?>
                        <p><strong>Gambar Barang:</strong><br>
                        <img src="../<?= $loan['gambar_barang'] ?>" width="100" class="rounded shadow-sm"></p>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Catatan (opsional)</label>
                            <textarea name="note" class="form-control" rows="2" placeholder="Keterangan tambahan"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Upload Bukti Pembayaran (Foto, Screenshot)</label>
                            <input type="file" name="bukti" class="form-control" accept="image/jpeg,image/png,image/jpg,image/webp">
                            <small class="text-muted">Format JPG, PNG, WebP, max 2MB</small>
                        </div>
                        <button type="submit" class="btn btn-primary">Kirim Permintaan</button>
                        <a href="fines_list.php" class="btn btn-secondary">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>