<?php
require_once '../includes/autoload.php';
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$itemObj = new Item();
$loanObj = new Loan();
$userObj = new User();
$logManager = new Log();

$total_items = count($itemObj->getAllItems());
$total_loans = count($loanObj->getAllLoans());
$total_users = count($userObj->getAllUsers());

// Activity log terbaru
$recentLogs = $logManager->getLatestLogs(5);
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3" style="color: #1a344d;"><i class="fas fa-tachometer-alt me-2"></i> Dashboard Admin</h1>
    </div>
    <div class="row g-4">
        <!-- Total Barang -->
        <div class="col-md-4">
            <a href="manage_items.php" class="text-decoration-none">
                <div class="stat-card text-center" style="background: linear-gradient(145deg, #eef2ff, #ffffff); border-top: 4px solid #2a6df4;">
                    <div class="icon" style="color: #2a6df4;"><i class="fas fa-box fa-2x"></i></div>
                    <p class="mt-2 mb-1 text-uppercase fw-semibold">Total Barang</p>
                    <h2 class="fw-bold"><?= $total_items ?></h2>
                </div>
            </a>
        </div>
        <!-- Laporan Peminjaman -->
        <div class="col-md-4">
            <a href="report.php" class="text-decoration-none">
                <div class="stat-card text-center" style="background: linear-gradient(145deg, #e6f7ec, #ffffff); border-top: 4px solid #10b981;">
                    <div class="icon" style="color: #10b981;"><i class="fas fa-hand-holding fa-2x"></i></div>
                    <p class="mt-2 mb-1 text-uppercase fw-semibold">Laporan Peminjaman</p>
                    <h2 class="fw-bold"><?= $total_loans ?></h2>
                </div>
            </a>
        </div>
        <!-- Total Pengguna -->
        <div class="col-md-4">
            <a href="manage_users.php" class="text-decoration-none">
                <div class="stat-card text-center" style="background: linear-gradient(145deg, #fffbeb, #ffffff); border-top: 4px solid #f59e0b;">
                    <div class="icon" style="color: #f59e0b;"><i class="fas fa-users fa-2x"></i></div>
                    <p class="mt-2 mb-1 text-uppercase fw-semibold">Total Pengguna</p>
                    <h2 class="fw-bold"><?= $total_users ?></h2>
                </div>
            </a>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-12">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white border-bottom-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-semibold"><i class="fas fa-history me-2"></i>Aktivitas Terbaru</h5>
                    <a href="activity_log.php" class="btn btn-sm btn-link">Lihat Semua</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recentLogs)): ?>
                        <p class="text-muted">Belum ada aktivitas tercatat.</p>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recentLogs as $log): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?= htmlspecialchars($log['nama_lengkap']) ?></strong><br>
                                        <small><?= htmlspecialchars($log['kegiatan']) ?> – <?= htmlspecialchars($log['keterangan'] ?: '-') ?></small>
                                    </div>
                                    <small class="text-muted"><?= $log['timestamp'] ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>