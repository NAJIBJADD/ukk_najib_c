<?php
require_once '../includes/autoload.php';
if ($_SESSION['role'] !== 'petugas') {
    header("Location: ../login.php");
    exit;
}

$pdo = Database::getInstance()->getConnection();
// ambil peminjaman yang sudah selesai dan denda >0
$sql = "SELECT l.*, u.nama_lengkap AS siswa, i.nama_item 
        FROM loans l 
        JOIN users u ON l.id_siswa = u.id 
        JOIN items i ON l.id_item = i.id 
        WHERE l.denda > 0 AND l.status IN ('kembali','rusak','hilang')
        ORDER BY l.id DESC";
$stmt = $pdo->query($sql);
$loans = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-warning text-dark">
            <h5><i class="fas fa-money-bill"></i> Tagihan Denda (Belum Lunas)</h5>
        </div>
        <div class="card-body">
            <?php if (empty($loans)): ?>
                <div class="alert alert-success">Tidak ada denda yang perlu ditagih.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr><th>ID Pinjam</th><th>Siswa</th><th>Barang</th><th>Denda</th><th>Status</th><th>Aksi</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($loans as $loan): ?>
                            <tr>
                                <td><?= $loan['id'] ?></td>
                                <td><?= htmlspecialchars($loan['siswa']) ?></td>
                                <td><?= htmlspecialchars($loan['nama_item']) ?></td>
                                <td>Rp <?= number_format($loan['denda'],0,',','.') ?></td>
                                <td><?= $loan['status'] ?></td>
                                <td>
                                    <a href="request_payment.php?loan_id=<?= $loan['id'] ?>" class="btn btn-sm btn-primary">Ajukan Pembayaran</a>
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