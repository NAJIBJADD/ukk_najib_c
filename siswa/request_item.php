<?php
require_once '../includes/autoload.php';
if ($_SESSION['role'] != 'siswa') {
    header("Location: ../login.php");
    exit;
}

$itemObj = new Item();
$requestObj = new Request();

$error = '';
$success = '';

$today = date('Y-m-d');
$minDate = date('Y-m-d', strtotime('+1 day'));
$maxDate = date('Y-m-d', strtotime('+14 days'));

// Ambil semua barang yang tersedia (stok > 0 dan status = 'tersedia')
$items = $itemObj->getAllItems();
$availableItems = array_filter($items, function($item) {
    return $item['status'] == 'tersedia' && $item['stok'] > 0;
});

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['item_id'])) {
    $itemId = (int)$_POST['item_id'];
    $returnDate = $_POST['return_date'] ?? '';
    $siswaId = $_SESSION['user_id'];

    if (empty($returnDate) || $returnDate < $minDate || $returnDate > $maxDate) {
        $error = "⚠️ Tanggal pengembalian tidak valid. Pilih antara $minDate sampai $maxDate.";
    } else {
        $item = $itemObj->getItemById($itemId);
        if (!$item || $item['status'] != 'tersedia' || $item['stok'] <= 0) {
            $error = "❌ Barang tidak tersedia.";
        } else {
            // Cek apakah sudah ada request pending untuk barang ini
            $pending = false;
            $requests = $requestObj->getRequestsByStudent($siswaId);
            foreach ($requests as $req) {
                if ($req['id_item'] == $itemId && $req['status'] == 'pending') {
                    $pending = true;
                    break;
                }
            }
            if ($pending) {
                $error = "⚠️ Anda sudah memiliki permintaan pending untuk barang '{$item['nama_item']}'. Tunggu konfirmasi petugas.";
            } else {
                if ($requestObj->createRequest($siswaId, $itemId, $returnDate, "Permintaan via daftar barang")) {
                    $success = "✅ Permintaan peminjaman '{$item['nama_item']}' berhasil dikirim. Tanggal kembali diminta: $returnDate";
                } else {
                    $error = "❌ Gagal mengirim permintaan. Coba lagi.";
                }
            }
        }
    }
}
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="fas fa-list"></i> Ajukan Peminjaman Barang</h4>
            <a href="dashboard.php" class="btn btn-back"><i class="fas fa-arrow-left"></i> Kembali</a>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Pilih barang yang ingin dipinjam, tentukan tanggal pengembalian (minimal besok, maksimal 14 hari), lalu kirim permintaan. Petugas akan mengonfirmasi.
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-semibold">📅 Tanggal Pengembalian yang Diharapkan <span class="text-danger">*</span></label>
                    <input type="date" name="return_date" class="form-control rounded-pill" value="<?= $minDate ?>" min="<?= $minDate ?>" max="<?= $maxDate ?>" required>
                    <small class="text-muted">Minimal besok, maksimal 14 hari ke depan.</small>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Pilih</th>
                                <th>Gambar</th>
                                <th>Nama Barang</th>
                                <th>Kategori</th>
                                <th>Stok</th>
                                <th>Deskripsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($availableItems)): ?>
                                <tr><td colspan="6" class="text-center">Tidak ada barang yang tersedia saat ini.</td></td>
                            <?php else: ?>
                                <?php foreach ($availableItems as $item): ?>
                                    <tr>
                                        <td><input type="radio" name="item_id" value="<?= $item['id'] ?>" required> </span>
                                        <td>
                                            <?php if (!empty($item['gambar']) && file_exists("../" . $item['gambar'])): ?>
                                                <img src="../<?= $item['gambar'] ?>" width="50" height="50" style="object-fit: cover; border-radius: 8px;">
                                            <?php else: ?>
                                                <i class="fas fa-image fa-2x text-muted"></i>
                                            <?php endif; ?>
                                         </span>
                                        <td><?= htmlspecialchars($item['nama_item']) ?> </span>
                                        <td><?= htmlspecialchars($item['kategori'] ?? '-') ?> </span>
                                        <td><?= $item['stok'] ?> </span>
                                        <td><?= htmlspecialchars($item['deskripsi']) ?> </span>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if (!empty($availableItems)): ?>
                    <button type="submit" class="btn btn-primary mt-3">Kirim Permintaan</button>
                <?php endif; ?>
            </form>

            <hr>
            <h5>📋 Riwayat Permintaan Saya</h5>
            <?php
            $requests = $requestObj->getRequestsByStudent($_SESSION['user_id']);
            if (empty($requests)): ?>
                <p class="text-muted">Belum ada permintaan.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>Barang</th>
                                <th>Gambar</th>
                                <th>Tgl Request</th>
                                <th>Tanggal Kembali Diminta</th>
                                <th>Status</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($requests as $req): 
                                $item = $itemObj->getItemById($req['id_item']);
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($req['nama_item']) ?> </span>
                                    <td>
                                        <?php if (!empty($item['gambar']) && file_exists("../" . $item['gambar'])): ?>
                                            <img src="../<?= $item['gambar'] ?>" width="40" height="40" style="object-fit: cover; border-radius: 8px;">
                                        <?php else: ?>
                                            <i class="fas fa-image text-muted"></i>
                                        <?php endif; ?>
                                     </span>
                                    <td><?= time_elapsed_string($req['tgl_request']) ?></td>
                                    <td><?= $req['requested_return_date'] ?? '-' ?> </span>
                                    <td>
                                        <?php if ($req['status'] == 'pending'): ?>
                                            <span class="badge bg-warning">Menunggu</span>
                                        <?php elseif ($req['status'] == 'disetujui'): ?>
                                            <span class="badge bg-success">Disetujui</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Ditolak</span>
                                        <?php endif; ?>
                                     </span>
                                    <td><?= htmlspecialchars($req['catatan']) ?> </span>
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