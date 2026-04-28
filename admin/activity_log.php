<?php
require_once '../includes/autoload.php';
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$logManager = new Log();

if (isset($_POST['clear_logs']) && $_POST['clear_logs'] === 'yes') {
    if ($logManager->clearAllLogs()) {
        $_SESSION['success'] = "Semua aktivitas berhasil dibersihkan.";
    } else {
        $_SESSION['error'] = "Gagal membersihkan aktivitas.";
    }
    header("Location: activity_log.php");
    exit;
}

$logs = $logManager->getAllLogs();
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>
<div class="container mt-4">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white border-bottom-0 d-flex justify-content-between align-items-center pt-4 pb-2 px-4">
            <h5 class="mb-0 fw-semibold text-primary"><i class="fas fa-history me-2"></i> Log Aktivitas</h5>
            <div>
                <a href="dashboard.php" class="btn btn-sm btn-outline-secondary rounded-pill me-2">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
                <form method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus SEMUA log aktivitas? Tindakan ini tidak dapat dibatalkan.')">
                    <input type="hidden" name="clear_logs" value="yes">
                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill">
                        <i class="fas fa-trash-alt me-1"></i> Bersihkan Semua
                    </button>
                </form>
            </div>
        </div>
        <div class="card-body p-4">
            <?php if (empty($logs)): ?>
                <div class="alert alert-info">Belum ada aktivitas tercatat.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Kegiatan</th>
                                <th>Keterangan</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?= $log['id'] ?></td>
                                    <td><?= htmlspecialchars($log['nama_lengkap']) ?> (ID: <?= $log['user_id'] ?>)</span>
                                    <td><?= htmlspecialchars($log['kegiatan']) ?> </span>
                                    <td><?= htmlspecialchars($log['keterangan'] ?: '-') ?> </span>
                                    <td><?= $log['timestamp'] ?> </span>
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