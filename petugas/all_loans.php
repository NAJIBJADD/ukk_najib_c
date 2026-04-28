<?php
session_start();
if ($_SESSION['role'] != 'petugas') {
    header("Location: ../login.php");
    exit;
}
require_once '../includes/autoload.php';

$pdo = Database::getInstance()->getConnection();
$requestObj = new Request();

// Hitung denda keterlambatan (tanpa kondisi rupiah karena harga sudah dihapus)
function calculateLateFine($loan) {
    $lateFine = 0;
    $dueDate = $loan['batas_waktu'];
    $returnDate = $loan['tgl_kembali'] ?? date('Y-m-d H:i:s');
    if (strtotime($returnDate) > strtotime($dueDate)) {
        $lateDays = floor((strtotime($returnDate) - strtotime($dueDate)) / 86400);
        $lateFine = $lateDays * 1000; // Rp1.000/hari
    }
    return $lateFine;
}

// Proses setujui/tolak request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && isset($_POST['request_id'])) {
    $action = $_POST['action'];
    $requestId = (int)$_POST['request_id'];
    $status = ($action == 'approve') ? 'disetujui' : 'ditolak';
    if ($requestObj->updateRequestStatus($requestId, $status, $_SESSION['user_id'])) {
        $_SESSION['success'] = "Permintaan berhasil " . ($status == 'disetujui' ? 'disetujui' : 'ditolak');
    } else {
        $_SESSION['error'] = "Gagal memproses permintaan.";
    }
    header("Location: all_loans.php");
    exit;
}

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// --- Ambil data peminjaman (loans) tanpa harga ---
$loanStatuses = ['dipinjam', 'kembali', 'rusak', 'hilang'];
$showLoans = in_array($filter, $loanStatuses) || $filter == 'all' || $filter == 'telat';

$loans = [];
if ($showLoans) {
    $sqlLoans = "SELECT l.*, u.nama_lengkap AS siswa, u.nis, i.nama_item 
                 FROM loans l 
                 JOIN users u ON l.id_siswa = u.id 
                 JOIN items i ON l.id_item = i.id";
    $params = [];

    if ($filter == 'telat') {
        $sqlLoans .= " WHERE l.status = 'dipinjam' AND l.batas_waktu < NOW()";
    } elseif ($filter != 'all') {
        $sqlLoans .= " WHERE l.status = ?";
        $params[] = $filter;
    }

    $stmtLoans = $pdo->prepare($sqlLoans);
    $stmtLoans->execute($params);
    $loans = $stmtLoans->fetchAll(PDO::FETCH_ASSOC);
}

// --- Ambil permintaan pending ---
$pendingRequests = [];
if ($filter == 'all' || $filter == 'pending') {
    $sqlPending = "SELECT r.*, u.nama_lengkap AS siswa, u.nis, i.nama_item, r.id_siswa
                   FROM requests r 
                   JOIN users u ON r.id_siswa = u.id 
                   JOIN items i ON r.id_item = i.id 
                   WHERE r.status = 'pending'
                   ORDER BY r.tgl_request ASC";
    $stmtPending = $pdo->query($sqlPending);
    $pendingRequests = $stmtPending->fetchAll(PDO::FETCH_ASSOC);
}

// --- Permintaan ditolak ---
$rejectedRequests = [];
if ($filter == 'ditolak' || $filter == 'all') {
    $sqlRejected = "SELECT r.*, u.nama_lengkap AS siswa, u.nis, i.nama_item, r.id_siswa
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
            <h4 class="mb-0"><i class="fas fa-list"></i> Manajemen Peminjaman & Permintaan</h4>
            <a href="dashboard.php" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
        </div>
        <div class="card-body">
            <!-- Filter Status -->
            <ul class="nav nav-pills mb-4">
                <li class="nav-item"><a class="nav-link <?= $filter == 'all' ? 'active' : '' ?>" href="?filter=all">Semua</a></li>
                <li class="nav-item"><a class="nav-link <?= $filter == 'pending' ? 'active' : '' ?>" href="?filter=pending">Permintaan Pending</a></li>
                <li class="nav-item"><a class="nav-link <?= $filter == 'dipinjam' ? 'active' : '' ?>" href="?filter=dipinjam">Dipinjam</a></li>
                <li class="nav-item"><a class="nav-link <?= $filter == 'kembali' ? 'active' : '' ?>" href="?filter=kembali">Kembali (Tuntas)</a></li>
                <li class="nav-item"><a class="nav-link <?= $filter == 'telat' ? 'active' : '' ?>" href="?filter=telat">Terlambat</a></li>
                <li class="nav-item"><a class="nav-link <?= $filter == 'rusak' ? 'active' : '' ?>" href="?filter=rusak">Rusak</a></li>
                <li class="nav-item"><a class="nav-link <?= $filter == 'hilang' ? 'active' : '' ?>" href="?filter=hilang">Hilang</a></li>
                <li class="nav-item"><a class="nav-link <?= $filter == 'ditolak' ? 'active' : '' ?>" href="?filter=ditolak">Ditolak</a></li>
            </ul>

            <!-- Tabel Permintaan Pending -->
            <?php if (($filter == 'all' || $filter == 'pending') && !empty($pendingRequests)): ?>
                <h5 class="mb-3">📋 Permintaan Peminjaman (Menunggu Persetujuan)</h5>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>ID Siswa</th><th>Siswa</th><th>NIS</th><th>Barang</th>
                                <th>Tgl Request</th><th>Tanggal Kembali Diminta</th><th>Status</th><th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingRequests as $req): ?>
                                <tr>
                                    <td><?= $req['id_siswa'] ?></td>
                                    <td><?= htmlspecialchars($req['siswa']) ?></td>
                                    <td><?= htmlspecialchars($req['nis'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($req['nama_item']) ?></td>
                                    <td><?= $req['tgl_request'] ?></td>
                                    <td><?= $req['requested_return_date'] ?? '-' ?></td>
                                    <td><span class="badge bg-warning" style="font-weight: bold; color: #000;">PENDING</span></td>
                                    <td>
                                        <form method="POST" style="display:inline-block"><input type="hidden" name="request_id" value="<?= $req['id'] ?>"><input type="hidden" name="action" value="approve"><button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Setujui?')">Setujui</button></form>
                                        <form method="POST" style="display:inline-block"><input type="hidden" name="request_id" value="<?= $req['id'] ?>"><input type="hidden" name="action" value="reject"><button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Tolak?')">Tolak</button></form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <!-- Tabel Peminjaman (Loans) – tanpa kolom harga -->
            <?php if ($showLoans && !empty($loans)): ?>
                <h5 class="mb-3">📚 Data Peminjaman</h5>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>ID Siswa</th><th>Siswa</th><th>NIS</th><th>Barang</th>
                                <th>Tgl Pinjam</th><th>Batas Waktu</th><th>Tgl Kembali</th><th>Status</th>
                                <th>Denda Keterlambatan</th><th>Denda Kondisi</th><th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($loans as $loan): 
                                $is_late = ($loan['status'] == 'dipinjam' && strtotime($loan['batas_waktu']) < time());
                                $status_display = $is_late ? 'Terlambat' : $loan['status'];
                                $badge_class = $is_late ? 'danger' : ($loan['status'] == 'dipinjam' ? 'warning' : ($loan['status'] == 'kembali' ? 'success' : 'danger'));
                                $lateFine = calculateLateFine($loan);
                                $conditionText = '';
                                if ($loan['status'] == 'rusak') $conditionText = 'Rusak (50.000)';
                                elseif ($loan['status'] == 'hilang') $conditionText = 'Hilang (200.000)';
                                else $conditionText = '-';
                            ?>
                                <tr>
                                    <td><?= $loan['id_siswa'] ?></td>
                                    <td><?= htmlspecialchars($loan['siswa']) ?></td>
                                    <td><?= htmlspecialchars($loan['nis'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($loan['nama_item']) ?></td>
                                    <td><?= $loan['tgl_pinjam'] ?></td>
                                    <td><?= $loan['batas_waktu'] ?></td>
                                    <td><?= $loan['tgl_kembali'] ?? '-' ?></td>
                                    <td><span class="badge bg-<?= $badge_class ?>" style="font-weight: bold; color: #000;"><?= strtoupper($status_display) ?></span></td>
                                    <td>
                                        <?php if ($lateFine > 0): ?>
                                            Rp <?= number_format($lateFine,0,',','.') ?> (<?= $lateFine/1000 ?> hari)
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $conditionText ?></td>
                                    <td>
                                        <?php if ($loan['status'] == 'dipinjam'): ?>
                                            <a href="submit_return.php?loan_id=<?= $loan['id'] ?>" class="btn btn-sm btn-warning">Ajukan Pengembalian</a>
                                        <?php elseif (in_array($loan['status'], ['kembali', 'rusak', 'hilang'])): ?>
                                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $loan['id'] ?>">Ajukan Hapus</button>
                                            <!-- Modal Ajukan Hapus (sama seperti sebelumnya) -->
                                            <div class="modal fade" id="deleteModal<?= $loan['id'] ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header bg-danger text-white">
                                                            <h5 class="modal-title">Ajukan Penghapusan Data</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form method="POST" action="request_delete.php">
                                                            <input type="hidden" name="loan_id" value="<?= $loan['id'] ?>">
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Alasan penghapusan</label>
                                                                    <textarea name="alasan" class="form-control" rows="3" required placeholder="Contoh: Data duplikat, salah input, dll"></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                                <button type="submit" class="btn btn-danger">Kirim Permintaan</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <!-- Tabel Permintaan Ditolak -->
            <?php if (($filter == 'all' || $filter == 'ditolak') && !empty($rejectedRequests)): ?>
                <h5 class="mb-3 mt-4">❌ Permintaan yang Ditolak</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>ID Siswa</th><th>Siswa</th><th>NIS</th><th>Barang</th>
                                <th>Tgl Request</th><th>Tanggal Kembali Diminta</th><th>Status</th><th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rejectedRequests as $req): ?>
                                <tr>
                                    <td><?= $req['id_siswa'] ?></td>
                                    <td><?= htmlspecialchars($req['siswa']) ?></td>
                                    <td><?= htmlspecialchars($req['nis'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($req['nama_item']) ?></td>
                                    <td><?= $req['tgl_request'] ?></td>
                                    <td><?= $req['requested_return_date'] ?? '-' ?></td>
                                    <td><span class="badge bg-danger" style="font-weight: bold; color: #000;">DITOLAK</span></td>
                                    <td><?= htmlspecialchars($req['catatan'] ?: '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <!-- Pesan kosong -->
            <?php if (empty($loans) && empty($pendingRequests) && empty($rejectedRequests) && $filter != 'pending' && $filter != 'ditolak'): ?>
                <div class="alert alert-info">Tidak ada data peminjaman atau permintaan.</div>
            <?php endif; ?>
            <?php if ($filter == 'pending' && empty($pendingRequests)): ?>
                <div class="alert alert-info">Tidak ada permintaan pending.</div>
            <?php endif; ?>
            <?php if ($filter == 'ditolak' && empty($rejectedRequests)): ?>
                <div class="alert alert-info">Tidak ada permintaan yang ditolak.</div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>