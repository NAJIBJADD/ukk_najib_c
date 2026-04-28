<?php
session_start();
if ($_SESSION['role'] != 'petugas') {
    header("Location: ../login.php");
    exit;
}
require_once '../includes/autoload.php';

$loanObj = new Loan();
$loans = [];
$search_type = '';
$search_value = '';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Jika parameter 'all' ada, tampilkan semua peminjaman
    if (isset($_GET['all']) && $_GET['all'] == 1) {
        $search_type = 'all';
        $loans = $loanObj->getAllLoans();
    } elseif (isset($_GET['search_date']) && !empty($_GET['search_date'])) {
        $search_type = 'date';
        $search_value = $_GET['search_date'];
        $loans = $loanObj->getLoansByDate($search_value);
    } elseif (isset($_GET['search_student']) && !empty($_GET['search_student'])) {
        $search_type = 'student';
        $search_value = $_GET['search_student'];
        $loans = $loanObj->getLoansByStudentIdOrNis($search_value);
    }
}
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="fas fa-search"></i> Cari Peminjaman</h4>
            <a href="dashboard.php" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>
        <div class="card-body">
            <!-- Tombol Lihat Semua Peminjaman -->
            <div class="mb-3">
                <a href="?all=1" class="btn btn-info <?= $search_type == 'all' ? 'active' : '' ?>">
                    <i class="fas fa-list me-1"></i> Lihat Semua Peminjaman
                </a>
            </div>

            <ul class="nav nav-tabs mb-3">
                <li class="nav-item"><button class="nav-link <?= $search_type == 'date' ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#dateTab">📅 Berdasarkan Tanggal</button></li>
                <li class="nav-item"><button class="nav-link <?= $search_type == 'student' ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#studentTab">👨‍🎓 Berdasarkan ID/NIS Siswa</button></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane fade <?= $search_type == 'date' ? 'show active' : '' ?>" id="dateTab">
                    <form method="GET" class="row g-3">
                        <div class="col-md-6">
                            <label>Tanggal Pinjam</label>
                            <input type="date" name="search_date" class="form-control" value="<?= $search_value ?>" required>
                        </div>
                        <div class="col-md-2 align-self-end">
                            <button type="submit" class="btn btn-primary">Cari</button>
                        </div>
                    </form>
                </div>
                <div class="tab-pane fade <?= $search_type == 'student' ? 'show active' : '' ?>" id="studentTab">
                    <form method="GET" class="row g-3">
                        <div class="col-md-6">
                            <label>ID Siswa atau NIS</label>
                            <input type="text" name="search_student" class="form-control" placeholder="Masukkan ID atau NIS" value="<?= htmlspecialchars($search_value) ?>" required>
                        </div>
                        <div class="col-md-2 align-self-end">
                            <button type="submit" class="btn btn-primary">Cari</button>
                        </div>
                    </form>
                </div>
            </div>
            <hr>
            <?php if (($search_type == 'all' && !empty($loans)) || ($search_value && !empty($loans))): ?>
                <h5>Hasil Pencarian: 
                    <?php if ($search_type == 'all'): ?>
                        Semua Peminjaman
                    <?php elseif ($search_type == 'date'): ?>
                        Tanggal <?= $search_value ?>
                    <?php else: ?>
                        ID/NIS <?= htmlspecialchars($search_value) ?>
                    <?php endif; ?>
                </h5>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>Siswa</th><th>NIS</th><th>Barang</th><th>Tgl Pinjam</th><th>Batas Waktu</th><th>Status</th><th>Denda</th><th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($loans as $row): 
                                $is_late = ($row['status'] == 'dipinjam' && strtotime($row['batas_waktu']) < time());
                            ?>
                                <tr class="<?= $is_late ? 'table-danger' : '' ?>">
                                    <td><?= htmlspecialchars($row['siswa']) ?></td>
                                    <td><?= htmlspecialchars($row['nis'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($row['nama_item']) ?></td>
                                    <td><?= time_elapsed_string($row['tgl_pinjam']) ?></td>
                                    <td><?= time_elapsed_string($row['batas_waktu']) ?></td>
                                    <td><?= $row['status'] ?></td>
                                    <td>Rp <?= number_format($row['denda'],0,',','.') ?></td>
                                    <td>
                                        <?php if ($row['status'] == 'dipinjam'): ?>
                                            <a href="submit_return.php?loan_id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">
                                                <i class="fas fa-undo-alt"></i> Ajukan Pengembalian
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php elseif ($search_value): ?>
                <div class="alert alert-warning">Tidak ada peminjaman yang ditemukan.</div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>