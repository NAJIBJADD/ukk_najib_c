<?php
require_once '../includes/autoload.php';
if ($_SESSION['role'] != 'admin') { header("Location: ../login.php"); exit; }

$loanObj = new Loan();
$itemObj = new Item();
$logObj = new Log();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['return_loan'])) {
    $loanId = $_POST['loan_id'];
    $statusReturn = $_POST['status'];
    $dendaTambahan = (int)$_POST['denda_tambahan'];
    
    if ($loanObj->returnLoan($loanId, $statusReturn, $dendaTambahan)) {
        $logObj->add($_SESSION['user_id'], 'Pengembalian', "Loan ID $loanId, status $statusReturn");
        $_SESSION['success'] = "Pengembalian berhasil diproses.";
    } else {
        $_SESSION['error'] = "Gagal memproses pengembalian.";
    }
    header("Location: manage_loans.php");
    exit;
}

$loans = $loanObj->getAllLoans();
$item = new Item();
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4>Manajemen Peminjaman & Denda</h4>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr><th>ID</th><th>Siswa</th><th>Barang</th><th>Tgl Pinjam</th><th>Batas Waktu</th><th>Status</th><th>Denda</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                <?php foreach($loans as $loan): 
                    $is_late = ($loan['status']=='dipinjam' && strtotime($loan['batas_waktu']) < time());
                ?>
                    <tr class="<?= $is_late ? 'table-danger' : '' ?>">
                        <td><?= $loan['id'] ?></td>
                        <td><?= htmlspecialchars($loan['siswa']) ?></td>
                        <td><?= htmlspecialchars($loan['nama_item']) ?></td>
                        <td><?= $loan['tgl_pinjam'] ?></td>
                        <td><?= $loan['batas_waktu'] ?></td>
                        <td><?= $is_late ? '<span class="badge bg-danger">TERLAMBAT</span>' : $loan['status'] ?></td>
                        <td>Rp <?= number_format($loan['denda'],0,',','.') ?></td>
                        <td>
                            <?php if($loan['status'] == 'dipinjam'): ?>
                                <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#returnModal<?= $loan['id'] ?>">Kembalikan</button>
                                <!-- Modal pengembalian dengan konfirmasi -->
                                <div class="modal fade" id="returnModal<?= $loan['id'] ?>">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header bg-success text-white">
                                                <h5>Pengembalian: <?= htmlspecialchars($loan['nama_item']) ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST">
                                                <div class="modal-body">
                                                    <input type="hidden" name="loan_id" value="<?= $loan['id'] ?>">
                                                    <div class="mb-2">
                                                        <label>Kondisi barang dikembalikan</label>
                                                        <select name="status" class="form-control" required>
                                                            <option value="kembali">Baik (kembali)</option>
                                                            <option value="rusak">Rusak (denda Rp<?= number_format(Loan::DENDA_RUSAK,0,',','.') ?>)</option>
                                                            <option value="hilang">Hilang (denda Rp<?= number_format(Loan::DENDA_HILANG,0,',','.') ?>)</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-2">
                                                        <label>Denda tambahan</label>
                                                        <input type="number" name="denda_tambahan" class="form-control" value="0" min="0">
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="confirm<?= $loan['id'] ?>" required>
                                                        <label class="form-check-label">Saya konfirmasi barang yang dikembalikan adalah <?= htmlspecialchars($loan['nama_item']) ?></label>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" name="return_loan" class="btn btn-primary" id="submitBtn<?= $loan['id'] ?>" disabled>Proses</button>
                                                </div>
                                            </form>
                                            <script>
                                                document.getElementById('confirm<?= $loan['id'] ?>').addEventListener('change', function(e) {
                                                    document.getElementById('submitBtn<?= $loan['id'] ?>').disabled = !this.checked;
                                                });
                                            </script>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <button class="btn btn-sm btn-secondary" disabled>Selesai</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>