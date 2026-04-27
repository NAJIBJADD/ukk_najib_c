<?php
require_once '../includes/autoload.php';
if ($_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$loanObj = new Loan();
$report_type = isset($_GET['type']) ? $_GET['type'] : 'dipinjam';
$data = $loanObj->getReport($report_type);
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-info text-white">
            <h4><i class="fas fa-chart-line"></i> Laporan Perpustakaan</h4>
        </div>
        <div class="card-body">
            <ul class="nav nav-tabs mb-3">
                <li class="nav-item"><a class="nav-link <?= $report_type=='dipinjam'?'active':'' ?>" href="?type=dipinjam">Barang Dipinjam</a></li>
                <li class="nav-item"><a class="nav-link <?= $report_type=='kembali'?'active':'' ?>" href="?type=kembali">Barang Kembali</a></li>
                <li class="nav-item"><a class="nav-link <?= $report_type=='rusak'?'active':'' ?>" href="?type=rusak">Barang Rusak</a></li>
                <li class="nav-item"><a class="nav-link <?= $report_type=='hilang'?'active':'' ?>" href="?type=hilang">Barang Hilang</a></li>
            </ul>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr><th>Siswa</th><th>Barang</th><th>Tgl Pinjam</th><th>Batas Waktu</th><th>Tgl Kembali</th><th>Denda</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['siswa']) ?></td>
                            <td><?= htmlspecialchars($row['nama_item']) ?></td>
                            <td><?= $row['tgl_pinjam'] ?></td>
                            <td><?= $row['batas_waktu'] ?></td>
                            <td><?= $row['tgl_kembali'] ?? '-' ?></td>
                            <td>Rp <?= number_format($row['denda'], 0, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($data)): ?>
                            <tr><td colspan="6" class="text-center">Tidak ada data</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>