<?php
require_once '../includes/autoload.php';
if ($_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}
$deleteReq = new DeleteRequest();
if (isset($_GET['approve'])) {
    $deleteReq->approveRequest((int)$_GET['approve']);
    $_SESSION['success'] = "Permintaan penghapusan disetujui.";
    header("Location: manage_delete_requests.php");
    exit;
}
if (isset($_GET['reject'])) {
    $deleteReq->rejectRequest((int)$_GET['reject']);
    $_SESSION['success'] = "Permintaan penghapusan ditolak.";
    header("Location: manage_delete_requests.php");
    exit;
}
$requests = $deleteReq->getPendingRequests();
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-danger text-white d-flex justify-content-between">
            <h4>Permintaan Hapus Data Peminjaman</h4>
            <a href="dashboard.php" class="btn btn-sm btn-light">Kembali</a>
        </div>
        <div class="card-body">
            <?php if (empty($requests)): ?>
                <div class="alert alert-info">Tidak ada permintaan hapus pending.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr><th>ID</th><th>Siswa</th><th>Barang</th><th>Petugas</th><th>Alasan</th><th>Tgl Request</th><th>Aksi</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($requests as $req): ?>
                            <tr>
                                <td><?= $req['id'] ?></td>
                                <td><?= htmlspecialchars($req['siswa']) ?> (ID: <?= $req['id_siswa'] ?>)</span>
                                <td><?= htmlspecialchars($req['nama_item']) ?> </span>
                                <td><?= htmlspecialchars($req['petugas']) ?> </span>
                                <td><?= nl2br(htmlspecialchars($req['alasan'])) ?> </span>
                                <td><?= $req['tgl_request'] ?> </span>
                                <td>
                                    <a href="?approve=<?= $req['id'] ?>" class="btn btn-sm btn-success" onclick="return confirm('Setujui penghapusan?')">Setujui</a>
                                    <a href="?reject=<?= $req['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tolak penghapusan?')">Tolak</a>
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