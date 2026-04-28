<?php
require_once '../includes/autoload.php';
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$pdo = Database::getInstance()->getConnection();
$report_type = isset($_GET['type']) ? $_GET['type'] : 'dipinjam';

// Query tanpa i.harga
switch ($report_type) {
    case 'dipinjam':
        $sql = "SELECT l.*, u.nama_lengkap AS siswa, i.nama_item 
                FROM loans l 
                JOIN users u ON l.id_siswa = u.id 
                JOIN items i ON l.id_item = i.id 
                WHERE l.status IN ('dipinjam', 'telat')
                ORDER BY l.batas_waktu ASC";
        break;
    case 'kembali':
        $sql = "SELECT l.*, u.nama_lengkap AS siswa, i.nama_item 
                FROM loans l 
                JOIN users u ON l.id_siswa = u.id 
                JOIN items i ON l.id_item = i.id 
                WHERE l.status = 'kembali'
                ORDER BY l.tgl_kembali DESC";
        break;
    case 'rusak':
        $sql = "SELECT l.*, u.nama_lengkap AS siswa, i.nama_item 
                FROM loans l 
                JOIN users u ON l.id_siswa = u.id 
                JOIN items i ON l.id_item = i.id 
                WHERE l.status = 'rusak'
                ORDER BY l.tgl_kembali DESC";
        break;
    case 'hilang':
        $sql = "SELECT l.*, u.nama_lengkap AS siswa, i.nama_item 
                FROM loans l 
                JOIN users u ON l.id_siswa = u.id 
                JOIN items i ON l.id_item = i.id 
                WHERE l.status = 'hilang'
                ORDER BY l.tgl_kembali DESC";
        break;
    default:
        $sql = "SELECT l.*, u.nama_lengkap AS siswa, i.nama_item 
                FROM loans l 
                JOIN users u ON l.id_siswa = u.id 
                JOIN items i ON l.id_item = i.id 
                WHERE l.status = '$report_type'";
        break;
}

$stmt = $pdo->query($sql);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="fas fa-chart-line"></i> Laporan Perpustakaan</h4>
            <a href="dashboard.php" class="btn btn-sm btn-light">Kembali</a>
        </div>
        <div class="card-body">
            <ul class="nav nav-tabs mb-3">
                <li class="nav-item"><a class="nav-link <?= $report_type == 'dipinjam' ? 'active' : '' ?>" href="?type=dipinjam">Barang Dipinjam</a></li>
                <li class="nav-item"><a class="nav-link <?= $report_type == 'kembali' ? 'active' : '' ?>" href="?type=kembali">Barang Kembali</a></li>
                <li class="nav-item"><a class="nav-link <?= $report_type == 'rusak' ? 'active' : '' ?>" href="?type=rusak">Barang Rusak</a></li>
                <li class="nav-item"><a class="nav-link <?= $report_type == 'hilang' ? 'active' : '' ?>" href="?type=hilang">Barang Hilang</a></li>
            </ul>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Siswa</th>
                            <th>Barang</th>
                            <th>Tgl Pinjam</th>
                            <th>Batas Waktu</th>
                            <th>Tgl Kembali</th>
                            <th>Denda</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['siswa']) ?></td>
                            <td><?= htmlspecialchars($row['nama_item']) ?></td>
                            <td><?= format_tanggal_indonesia($row['tgl_pinjam']) ?></td>
                            <td><?= format_tanggal_indonesia($row['batas_waktu']) ?></td>
                            <td><?= $row['tgl_kembali'] ? format_tanggal_indonesia($row['tgl_kembali']) : '-' ?></td>
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