<?php
if (!isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit;
}
$role = $_SESSION['role'];
?>
<nav class="navbar bg-white" style="box-shadow: 0 2px 12px rgba(0,0,0,0.02); border-bottom: 1px solid #eef2fa;">
    <div class="container">
        <a class="navbar-brand" href="<?= $role ?>/dashboard.php" style="font-weight: 800; font-size: 1.3rem; background: linear-gradient(135deg, #1e3a8a, #2a6df4); -webkit-background-clip: text; background-clip: text; color: transparent;">
            <i class="fas fa-book-open me-2"></i> Perpustakaan Digital
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <?php if($role == 'admin'): ?>
                    <li class="nav-item"><a class="nav-link" href="../admin/dashboard.php"><i class="fas fa-tachometer-alt me-1"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="../admin/manage_items.php"><i class="fas fa-box me-1"></i> Barang</a></li>
                    <li class="nav-item"><a class="nav-link" href="../admin/manage_users.php"><i class="fas fa-users me-1"></i> Users</a></li>
                    <li class="nav-item"><a class="nav-link" href="../admin/report.php"><i class="fas fa-chart-line me-1"></i> Laporan</a></li>
                    <li class="nav-item"><a class="nav-link" href="../admin/manage_payments.php"><i class="fas fa-hand-holding-usd me-1"></i> Persetujuan Denda</a></li>
                    <li class="nav-item"><a class="nav-link" href="../admin/manage_return_requests.php"><i class="fas fa-undo-alt me-1"></i> Persetujuan Pengembalian</a></li>
                <?php elseif($role == 'petugas'): ?>
                    <li class="nav-item"><a class="nav-link" href="../petugas/dashboard.php"><i class="fas fa-tachometer-alt me-1"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="../petugas/search_loan.php"><i class="fas fa-search me-1"></i> Cari Pinjaman</a></li>
                    <li class="nav-item"><a class="nav-link" href="../petugas/scan_student.php"><i class="fas fa-id-card me-1"></i> Scan Siswa</a></li>
                    <li class="nav-item"><a class="nav-link" href="../petugas/fines_list.php"><i class="fas fa-money-bill me-1"></i> Tagihan Denda</a></li>
                    <li class="nav-item"><a class="nav-link" href="../petugas/all_loans.php"><i class="fas fa-list me-1"></i> Semua Peminjaman</a></li>
                <?php elseif($role == 'siswa'): ?>
                    <li class="nav-item"><a class="nav-link" href="../siswa/dashboard.php"><i class="fas fa-tachometer-alt me-1"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="../siswa/request_item.php"><i class="fas fa-hand-holding me-1"></i> Ajukan Pinjam</a></li>
                    <li class="nav-item"><a class="nav-link" href="../siswa/my_fines.php"><i class="fas fa-money-bill-wave me-1"></i> Denda</a></li>
                <?php endif; ?>
                <li class="nav-item"><a class="nav-link text-danger" href="../logout.php"><i class="fas fa-sign-out-alt me-1"></i> Logout (<?= $_SESSION['username'] ?>)</a></li>
            </ul>
        </div>
    </div>
</nav>
<div class="container mt-3">
    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
</div>