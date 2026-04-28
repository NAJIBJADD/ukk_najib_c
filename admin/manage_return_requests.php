<?php
require_once '../includes/autoload.php';
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
$rr = new ReturnRequest();
if (isset($_GET['approve'])) {
    $rr->approveRequest((int)$_GET['approve'], $_SESSION['user_id']);
    $_SESSION['success'] = "Pengembalian disetujui.";
    header("Location: manage_return_requests.php");
    exit;
}
if (isset($_GET['reject'])) {
    $rr->rejectRequest((int)$_GET['reject']);
    $_SESSION['success'] = "Permintaan ditolak.";
    header("Location: manage_return_requests.php");
    exit;
}
$requests = $rr->getPendingRequests();
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4>Persetujuan Pengembalian (Verifikasi Barang Rusak/Hilang)</h4>
        </div>
        <div class="card-body">
            <?php if (empty($requests)): ?>
                <div class="alert alert-info">Tidak ada permintaan pengembalian pending.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr><th>ID</th><th>Siswa</th><th>Barang</th><th>Kondisi Diajukan</th><th>Denda Tambahan</th><th>Petugas</th><th>Tgl Request</th><th>Catatan</th><th>Aksi</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($requests as $req): ?>
                            <tr>
                                <td><?= $req['id'] ?></td>
                                <td><?= htmlspecialchars($req['siswa']) ?></td>
                                <td><?= htmlspecialchars($req['nama_item']) ?></td>
                                <td><?= $req['status_return'] ?></td>
                                <td>Rp <?= number_format($req['denda_tambahan'],0,',','.') ?></td>
                                <td><?= htmlspecialchars($req['petugas']) ?></td>
                                <td><?= time_elapsed_string($req['tgl_request']) ?></td>
                                <td><?= htmlspecialchars($req['catatan'] ?: '-') ?></td>
                                <td>
                                    <a href="?approve=<?= $req['id'] ?>" class="btn btn-success btn-sm" onclick="return confirm('Setujui pengembalian ini? Denda akan dihitung otomatis.')">Setujui</a>
                                    <a href="?reject=<?= $req['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tolak permintaan?')">Tolak</a>
                                 </span>
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