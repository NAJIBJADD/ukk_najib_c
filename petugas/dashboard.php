<?php
session_start();
if ($_SESSION['role'] != 'petugas') {
    header("Location: ../login.php");
    exit;
}
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-info text-white">
            <h4><i class="fas fa-user-check"></i> Dashboard Petugas</h4>
        </div>
        <div class="card-body">
            <p>Selamat datang, <?= $_SESSION['nama'] ?>.</p>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <a href="search_loan.php" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Cari Peminjaman
                    </a>
                </div>
                <div class="col-md-6 mb-3">
                    <a href="scan_student.php" class="btn btn-success w-100">
                        <i class="fas fa-id-card"></i> Scan Barcode Siswa
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>