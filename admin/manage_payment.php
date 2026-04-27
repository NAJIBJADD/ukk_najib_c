<?php
require_once '../includes/autoload.php';
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$paymentManager = new DendaPayment();

if (isset($_GET['approve'])) {
    $paymentManager->approveRequest((int)$_GET['approve'], $_SESSION['user_id']);
    $_SESSION['success'] = "Pembayaran denda disetujui.";
    header("Location: manage_payments.php");
    exit;
}
if (isset($_GET['reject'])) {
    $paymentManager->rejectRequest((int)$_GET['reject']);
    $_SESSION['success'] = "Permintaan pembayaran ditolak.";
    header("Location: manage_payments.php");
    exit;
}

$requests = $paymentManager->getPendingRequests();
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4>Persetujuan Pembayaran Denda</h4>
        </div>
        <div class="card-body">
            <?php if (empty($requests)): ?>
                <div class="alert alert-info">Tidak ada permintaan pembayaran pending.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr><th>ID</th><th>Siswa</th><th>Barang</th><th>Denda</th><th>Petugas</th><th>Tgl Request</th><th>Catatan</th><th>Aksi</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($requests as $req): ?>
                                <tr>
                                    <td><?= $req['id'] ?></td>
                                    <td><?= htmlspecialchars($req['siswa']) ?></td>
                                    <td><?= htmlspecialchars($req['nama_item']) ?></td>
                                    <td>Rp <?= number_format($req['jumlah_denda'],0,',','.') ?></td>
                                    <td><?= htmlspecialchars($req['petugas']) ?></td>
                                    <td><?= $req['tgl_request'] ?></td>
                                    <td><?= htmlspecialchars($req['catatan'] ?: '-') ?></td>
                                    <td>
                                        <a href="?approve=<?= $req['id'] ?>" class="btn btn-sm btn-success" onclick="return confirm('Setujui pembayaran denda?')">Setujui</a>
                                        <a href="?reject=<?= $req['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tolak permintaan?')">Tolak</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>