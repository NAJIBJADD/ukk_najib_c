<?php
if (!isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit;
}
$role = $_SESSION['role'];
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="<?= $role ?>/dashboard.php"><i class="fas fa-book"></i> Perpustakaan Digital</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <?php if($role == 'admin'): ?>
                    <li class="nav-item"><a class="nav-link" href="../admin/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="../admin/manage_items.php"><i class="fas fa-box"></i> Barang</a></li>
                    <li class="nav-item"><a class="nav-link" href="../admin/manage_loans.php"><i class="fas fa-hand-holding"></i> Peminjaman</a></li>
                    <li class="nav-item"><a class="nav-link" href="../admin/manage_users.php"><i class="fas fa-users"></i> Users</a></li>
                    <li class="nav-item"><a class="nav-link" href="../admin/report.php"><i class="fas fa-chart-line"></i> Laporan</a></li>
                <?php elseif($role == 'petugas'): ?>
                    <li class="nav-item"><a class="nav-link" href="../petugas/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="../petugas/search_loan.php"><i class="fas fa-search"></i> Cari Pinjaman (ID/NIS/Tanggal)</a></li>
                    <li class="nav-item"><a class="nav-link" href="../petugas/scan_student.php"><i class="fas fa-id-card"></i> Scan Siswa & Persetujuan</a></li>
                <?php elseif($role == 'siswa'): ?>
                    <li class="nav-item"><a class="nav-link" href="../siswa/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="../siswa/scan_loan.php"><i class="fas fa-qrcode"></i> Pinjam Barang (Scan)</a></li>
                <?php endif; ?>
                <li class="nav-item"><a class="nav-link text-danger" href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout (<?= $_SESSION['username'] ?>)</a></li>
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