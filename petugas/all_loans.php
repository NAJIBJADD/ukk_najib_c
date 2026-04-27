<?php
session_start();
if ($_SESSION['role'] != 'petugas') {
    header("Location: ../login.php");
    exit;
}
require_once '../includes/autoload.php';

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$pdo = Database::getInstance()->getConnection();

// Query untuk loans
$sqlLoans = "SELECT l.*, u.nama_lengkap AS siswa, u.nis, i.nama_item 
             FROM loans l 
             JOIN users u ON l.id_siswa = u.id 
             JOIN items i ON l.id_item = i.id";
$params = [];

if ($filter != 'all' && $filter != 'ditolak') {
    $sqlLoans .= " WHERE l.status = ?";
    $params[] = $filter;
}

$stmtLoans = $pdo->prepare($sqlLoans);
$stmtLoans->execute($params);
$loans = $stmtLoans->fetchAll(PDO::FETCH_ASSOC);

// Query untuk permintaan yang ditolak (status 'ditolak' dari tabel requests)
$rejectedRequests = [];
if ($filter == 'ditolak' || $filter == 'all') {
    $sqlRejected = "SELECT r.*, u.nama_lengkap AS siswa, u.nis, i.nama_item 
                    FROM requests r 
                    JOIN users u ON r.id_siswa = u.id 
                    JOIN items i ON r.id_item = i.id 
                    WHERE r.status = 'ditolak'
                    ORDER BY r.tgl_request DESC";
    $stmtRej = $pdo->query($sqlRejected);
    $rejectedRequests = $stmtRej->fetchAll(PDO::FETCH_ASSOC);
}
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="fas fa-list"></i> Semua Peminjaman & Permintaan</h4>
            <a href="dashboard.php" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
        </div>
        <div class="card-body">
            <!-- Filter Status -->
            <ul class="nav nav-pills mb-4">
                <li class="nav-item"><a class="nav-link <?= $filter == 'all' ? 'active' : '' ?>" href="?filter=all">Semua</a></li>
                <li class="nav-item"><a class="nav-link <?= $filter == 'dipinjam' ? 'active' : '' ?>" href="?filter=dipinjam">Dipinjam</a></li>
                <li class="nav-item"><a class="nav-link <?= $filter == 'kembali' ? 'active' : '' ?>" href="?filter=kembali">Kembali (Tuntas)</a></li>
                <li class="nav-item"><a class="nav-link <?= $filter == 'telat' ? 'active' : '' ?>" href="?filter=telat">Terlambat</a></li>
                <li class="nav-item"><a class="nav-link <?= $filter == 'rusak' ? 'active' : '' ?>" href="?filter=rusak">Rusak</a></li>
                <li class="nav-item"><a class="nav-link <?= $filter == 'hilang' ? 'active' : '' ?>" href="?filter=hilang">Hilang</a></li>
                <li class="nav-item"><a class="nav-link <?= $filter == 'ditolak' ? 'active' : '' ?>" href="?filter=ditolak">Ditolak</a></li>
            </ul>

            <!-- Tabel Peminjaman (Loans) -->
            <?php if (!empty($loans)): ?>
                <h5 class="mb-3">Data Peminjaman</h5>
                <div class="table-responsive mb-4">
                    <table class="table table-striped table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th><th>Siswa</th><th>NIS</th><th>Barang</th>
                                <th>Tgl Pinjam</th><th>Batas Waktu</th><th>Tgl Kembali</th><th>Status</th><th>Denda</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($loans as $loan): 
                                $is_late = ($loan['status'] == 'dipinjam' && strtotime($loan['batas_waktu']) < time());
                                $status_display = $is_late ? 'Terlambat' : $loan['status'];
                                $badge_class = $is_late ? 'danger' : ($loan['status'] == 'dipinjam' ? 'warning' : ($loan['status'] == 'kembali' ? 'success' : 'danger'));
                            ?>
                                <tr>
                                    <td><?= $loan['id'] ?> </span>
                                    <td><?= htmlspecialchars($loan['siswa']) ?> </span>
                                    <td><?= htmlspecialchars($loan['nis'] ?? '-') ?> </span>
                                    <td><?= htmlspecialchars($loan['nama_item']) ?> </span>
                                    <td><?= $loan['tgl_pinjam'] ?> </span>
                                    <td><?= $loan['batas_waktu'] ?> </span>
                                    <td><?= $loan['tgl_kembali'] ?? '-' ?> </span>
                                    <td><span class="badge bg-<?= $badge_class ?>"><?= ucfirst($status_display) ?></span></span>
                                    <td>Rp <?= number_format($loan['denda'], 0, ',', '.') ?> </span>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <!-- Tabel Permintaan Ditolak -->
            <?php if ($filter == 'all' || $filter == 'ditolak') : ?>
                <h5 class="mb-3 mt-4">Permintaan yang Ditolak</h5>
                <?php if (empty($rejectedRequests)): ?>
                    <div class="alert alert-info">Tidak ada permintaan yang ditolak.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead class="table-dark">
                                <tr><th>ID Request</th><th>Siswa</th><th>NIS</th><th>Barang</th><th>Tgl Request</th><th>Catatan</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rejectedRequests as $req): ?>
                                    <tr>
                                        <td><?= $req['id'] ?> </span>
                                        <td><?= htmlspecialchars($req['siswa']) ?> </span>
                                        <td><?= htmlspecialchars($req['nis'] ?? '-') ?> </span>
                                        <td><?= htmlspecialchars($req['nama_item']) ?> </span>
                                        <td><?= $req['tgl_request'] ?> </span>
                                        <td><?= htmlspecialchars($req['catatan'] ?: '-') ?> </span>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>