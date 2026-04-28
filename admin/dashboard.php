<?php
require_once '../includes/autoload.php';
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$itemManager = new Item();
$loanManager = new Loan();
$userManager = new User();
$logManager = new Log();

$totalItems = count($itemManager->getAllItems());
$totalLoans = count($loanManager->getAllLoans());
$totalUsers = count($userManager->getAllUsers());

// Ambil 5 log terbaru untuk ditampilkan di dashboard
$recentLogs = $logManager->getLatestLogs(5);
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3" style="color: #1a344d;"><i class="fas fa-tachometer-alt me-2"></i> Dashboard Admin</h1>
    </div>

    <!-- Statistik Cards (tanpa keterlambatan) -->
    <div class="row g-4">
        <div class="col-md-4">
            <a href="manage_items.php" class="text-decoration-none">
                <div class="stat-card text-center p-3 bg-white rounded-4 shadow-sm">
                    <i class="fas fa-box fa-2x text-primary"></i>
                    <p class="mt-2 mb-0 text-muted">Total Barang</p>
                    <h2 class="fw-bold"><?= $totalItems ?></h2>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="manage_loans.php" class="text-decoration-none">
                <div class="stat-card text-center p-3 bg-white rounded-4 shadow-sm">
                    <i class="fas fa-hand-holding fa-2x text-success"></i>
                    <p class="mt-2 mb-0 text-muted">Total Peminjaman</p>
                    <h2 class="fw-bold"><?= $totalLoans ?></h2>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="manage_users.php" class="text-decoration-none">
                <div class="stat-card text-center p-3 bg-white rounded-4 shadow-sm">
                    <i class="fas fa-users fa-2x text-warning"></i>
                    <p class="mt-2 mb-0 text-muted">Total Pengguna</p>
                    <h2 class="fw-bold"><?= $totalUsers ?></h2>
                </div>
            </a>
        </div>
    </div>

    <!-- Log Aktivitas Terbaru (dengan diff for human) -->
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
                                    <small class="text-muted"><?= time_elapsed_string($log['timestamp']) ?></small>
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