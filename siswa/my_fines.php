<?php
require_once '../includes/autoload.php';
if ($_SESSION['role'] !== 'siswa') {
    header("Location: ../login.php");
    exit;
}

$pdo = Database::getInstance()->getConnection();
$studentId = $_SESSION['user_id'];

// Ambil peminjaman yang sudah selesai dan memiliki denda (atau denda 0)
$sql = "SELECT l.*, i.nama_item 
        FROM loans l 
        JOIN items i ON l.id_item = i.id 
        WHERE l.id_siswa = ? AND l.status IN ('kembali','rusak','hilang')
        ORDER BY l.tgl_pinjam DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$studentId]);
$loans = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-money-bill-wave"></i> Riwayat Denda Saya</h5>
            <a href="dashboard.php" class="btn btn-sm btn-light rounded-pill">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
        <div class="card-body">
            <?php if (empty($loans)): ?>
                <div class="alert alert-info">Belum ada riwayat peminjaman selesai.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr><th>Barang</th><th>Tgl Pinjam</th><th>Tgl Kembali</th><th>Status</th><th>Denda</th><th>Status Denda</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($loans as $loan): 
                                $denda = $loan['denda'];
                                $statusDenda = ($denda == 0) 
                                    ? '<span class="badge bg-success">Lunas</span>' 
                                    : '<span class="badge bg-danger">Belum Lunas</span>';
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($loan['nama_item']) ?></td>
                                <td><?= time_elapsed_string($loan['tgl_pinjam']) ?></td>
                                <td><?= $loan['tgl_kembali'] ? time_elapsed_string($loan['tgl_kembali']) : '-' ?></td>
                                <td><?= $loan['status'] ?></td>
                                <td>Rp <?= number_format($denda,0,',','.') ?></td>
                                <td><?= $statusDenda ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <p class="text-muted mt-2">* Jika denda belum lunas, silakan hubungi petugas untuk melakukan pembayaran.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>