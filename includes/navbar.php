<?php
if (!isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit;
}
$role = $_SESSION['role'];
?>
<nav class="navbar navbar-expand-lg" style="background: #ffffff; box-shadow: 0 2px 12px rgba(0,0,0,0.02); border-bottom: 1px solid #eef2fa;">
    <div class="container">
        <a class="navbar-brand" href="<?= $role ?>/dashboard.php" style="font-weight: 800; font-size: 1.3rem; background: linear-gradient(135deg, #1e3a8a, #2a6df4); -webkit-background-clip: text; background-clip: text; color: transparent;">
            <i class="fas fa-book-open me-2"></i> Perpustakaan Digital
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" style="border: none;">
            <span class="navbar-toggler-icon" style="filter: invert(0.3);"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <?php if($role == 'admin'): ?>
                    <li class="nav-item"><a class="nav-link" href="../admin/dashboard.php"><i class="fas fa-tachometer-alt me-1"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="../admin/manage_items.php"><i class="fas fa-box me-1"></i> Barang</a></li>
                    <li class="nav-item"><a class="nav-link" href="../admin/manage_loans.php"><i class="fas fa-hand-holding me-1"></i> Peminjaman</a></li>
                    <li class="nav-item"><a class="nav-link" href="../admin/manage_users.php"><i class="fas fa-users me-1"></i> Users</a></li>
                    <li class="nav-item"><a class="nav-link" href="../admin/report.php"><i class="fas fa-chart-line me-1"></i> Laporan</a></li>
                <?php elseif($role == 'petugas'): ?>
                    <li class="nav-item"><a class="nav-link" href="../petugas/dashboard.php"><i class="fas fa-tachometer-alt me-1"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="../petugas/search_loan.php"><i class="fas fa-search me-1"></i> Cari Pinjaman</a></li>
                    <li class="nav-item"><a class="nav-link" href="../petugas/scan_student.php"><i class="fas fa-id-card me-1"></i> Scan Siswa</a></li>
                <?php elseif($role == 'siswa'): ?>
                    <li class="nav-item"><a class="nav-link" href="../siswa/dashboard.php"><i class="fas fa-tachometer-alt me-1"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="../siswa/scan_loan.php"><i class="fas fa-qrcode me-1"></i> Pinjam Barang</a></li>
                <?php endif; ?>
                <li class="nav-item"><a class="nav-link text-danger" href="../logout.php" style="color: #dc3545 !important;"><i class="fas fa-sign-out-alt me-1"></i> Logout (<?= $_SESSION['username'] ?>)</a></li>
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