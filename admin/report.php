<?php
require_once '../includes/autoload.php';
if ($_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$pdo = Database::getInstance()->getConnection();
$report_type = isset($_GET['type']) ? $_GET['type'] : 'dipinjam';

// Ambil data dari tabel loans dengan join harga barang
$sql = "SELECT l.*, u.nama_lengkap AS siswa, i.nama_item, i.harga
        FROM loans l 
        JOIN users u ON l.id_siswa = u.id 
        JOIN items i ON l.id_item = i.id";

if ($report_type == 'dipinjam') {
    $sql .= " WHERE l.status IN ('dipinjam', 'telat') ORDER BY l.batas_waktu ASC";
} elseif ($report_type == 'kembali') {
    $sql .= " WHERE l.status = 'kembali' ORDER BY l.tgl_kembali DESC";
} elseif ($report_type == 'rusak') {
    $sql .= " WHERE l.status = 'rusak' ORDER BY l.tgl_kembali DESC";
} elseif ($report_type == 'hilang') {
    $sql .= " WHERE l.status = 'hilang' ORDER BY l.tgl_kembali DESC";
} else {
    $sql .= " WHERE l.status = '$report_type'";
}
$stmt = $pdo->query($sql);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

/**
 * Hitung denda keterlambatan (per hari 1000)
 * Denda kondisi hanya persentase (tanpa rupiah)
 */
function calculateFineDetails($loan) {
    $lateFine = 0;
    $lateDays = 0;
    
    // Denda keterlambatan: 1000 per hari
    $dueDate = $loan['batas_waktu'];
    $returnDate = $loan['tgl_kembali'] ?? date('Y-m-d H:i:s');
    if (strtotime($returnDate) > strtotime($dueDate)) {
        $lateDays = floor((strtotime($returnDate) - strtotime($dueDate)) / 86400);
        $lateFine = $lateDays * 1000;
    }
    
    // Persentase denda kondisi
    $conditionPercent = 0;
    if ($loan['status'] == 'rusak') {
        $conditionPercent = 50;
    } elseif ($loan['status'] == 'hilang') {
        $conditionPercent = 100;
    }
    
    return [
        'late_fine' => $lateFine,
        'late_days' => $lateDays,
        'condition_percent' => $conditionPercent
    ];
}
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
                <li class="nav-item"><a class="nav-link <?= $report_type=='dipinjam'?'active':'' ?>" href="?type=dipinjam">Barang Dipinjam</a></li>
                <li class="nav-item"><a class="nav-link <?= $report_type=='kembali'?'active':'' ?>" href="?type=kembali">Barang Kembali</a></li>
                <li class="nav-item"><a class="nav-link <?= $report_type=='rusak'?'active':'' ?>" href="?type=rusak">Barang Rusak</a></li>
                <li class="nav-item"><a class="nav-link <?= $report_type=='hilang'?'active':'' ?>" href="?type=hilang">Barang Hilang</a></li>
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
                            <th>Denda Keterlambatan</th>
                            <th>Denda Kondisi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data as $row): 
                            $fine = calculateFineDetails($row);
                            $conditionText = '';
                            if ($row['status'] == 'rusak') {
                                $conditionText = 'Rusak (50%)';
                            } elseif ($row['status'] == 'hilang') {
                                $conditionText = 'Hilang (100%)';
                            } else {
                                $conditionText = '-';
                            }
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($row['siswa']) ?></td>
                                <td><?= htmlspecialchars($row['nama_item']) ?></td>
                                <td><?= $row['tgl_pinjam'] ?></td>
                                <td><?= $row['batas_waktu'] ?></td>
                                <td><?= $row['tgl_kembali'] ?? '-' ?></td>
                                <td>Rp <?= number_format($fine['late_fine'], 0, ',', '.') ?> (<?= $fine['late_days'] ?> hari terlambat)</td>
                                <td><?= $conditionText ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($data)): ?>
                            <tr><td colspan="7" class="text-center">Tidak ada</span></td>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>