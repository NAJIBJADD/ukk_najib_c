<?php
require_once '../includes/autoload.php';
if ($_SESSION['role'] != 'admin') { header("Location: ../login.php"); exit; }

$itemObj = new Item();
$id = (int)$_GET['id'];
$item = $itemObj->getItemById($id);
if (!$item) { header("Location: manage_items.php"); exit; }

function uploadGambar($file) { /* sama seperti sebelumnya */ }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = trim($_POST['nama_item']);
    $kategori = $_POST['kategori'] ?? $_POST['kategori_manual'] ?? $item['kategori'];
    $deskripsi = trim($_POST['deskripsi']);
    $status = $_POST['status'];
    $stok = (int)$_POST['stok'];
    $gambar = $item['gambar'] ?? '';
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        if ($gambar && file_exists("../" . $gambar)) unlink("../" . $gambar);
        $upload = uploadGambar($_FILES['gambar']);
        if ($upload) $gambar = $upload;
    }
    if ($itemObj->updateItem($id, $nama, $kategori, $deskripsi, $status, $stok, $gambar)) {
        $isLost = ($status == 'hilang');
        $itemObj->syncStatus($id, $isLost);
        $_SESSION['success'] = "Barang diperbarui";
    } else {
        $_SESSION['error'] = "Gagal update";
    }
    header("Location: manage_items.php");
    exit;
}
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>
<div class="container mt-4">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-2 px-4 d-flex flex-wrap justify-content-between">
            <h5 class="mb-0 fw-semibold text-primary"><i class="fas fa-edit me-2"></i>Edit Barang</h5>
            <a href="manage_items.php" class="btn btn-outline-secondary btn-sm rounded-pill"><i class="fas fa-arrow-left me-1"></i>Kembali</a>
        </div>
        <div class="card-body p-4">
            <form method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3"><label>Nama Barang</label><input type="text" name="nama_item" class="form-control rounded-pill" value="<?= htmlspecialchars($item['nama_item']) ?>" required></div>
                        <div class="mb-3">
                            <label>Kategori</label>
                            <select name="kategori" class="form-select rounded-pill">
                                <option <?= $item['kategori']=='Buku'?'selected':'' ?>>Buku</option>
                                <option <?= $item['kategori']=='Alat Tulis'?'selected':'' ?>>Alat Tulis</option>
                                <option <?= $item['kategori']=='Elektronik'?'selected':'' ?>>Elektronik</option>
                                <option <?= $item['kategori']=='Perlengkapan'?'selected':'' ?>>Perlengkapan</option>
                                <option <?= ($item['kategori']!='Buku' && $item['kategori']!='Alat Tulis' && $item['kategori']!='Elektronik' && $item['kategori']!='Perlengkapan')?'selected':'' ?>>Lainnya</option>
                            </select>
                            <input type="text" name="kategori_manual" class="form-control rounded-pill mt-2" placeholder="Atau ketik kategori lain" value="<?= htmlspecialchars($item['kategori']) ?>">
                        </div>
                        <div class="mb-3"><label>Stok</label><input type="number" name="stok" class="form-control rounded-pill" value="<?= $item['stok'] ?>" min="0" required></div>
                        <div class="mb-3">
                            <label>Gambar Barang</label>
                            <?php if ($item['gambar'] && file_exists("../".$item['gambar'])): ?>
                                <div class="mb-2"><img src="../<?= $item['gambar'] ?>" width="80" height="80" style="object-fit: cover; border-radius: 8px;"></div>
                            <?php endif; ?>
                            <input type="file" name="gambar" class="form-control rounded-pill" accept="image/jpeg,image/png,image/jpg,image/webp">
                            <small class="text-muted">Kosongkan jika tidak ingin mengubah gambar.</small>
                        </div>
                        <div class="mb-3"><label>Deskripsi</label><textarea name="deskripsi" class="form-control rounded-3" rows="3"><?= htmlspecialchars($item['deskripsi']) ?></textarea></div>
                        <div class="mb-3">
                            <label>Status</label>
                            <select name="status" class="form-select rounded-pill">
                                <option <?= $item['status']=='tersedia'?'selected':'' ?>>tersedia</option>
                                <option <?= $item['status']=='dipinjam'?'selected':'' ?>>dipinjam</option>
                                <option <?= $item['status']=='rusak'?'selected':'' ?>>rusak</option>
                                <option <?= $item['status']=='hilang'?'selected':'' ?>>hilang</option>
                                <option <?= $item['status']=='habis'?'selected':'' ?>>habis</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Update</button>
                        <a href="manage_items.php" class="btn btn-secondary rounded-pill px-4">Batal</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>