<?php
require_once '../includes/autoload.php';
if ($_SESSION['role'] != 'admin') { header("Location: ../login.php"); exit; }

$itemObj = new Item();
$id = (int)$_GET['id'];
$item = $itemObj->getItemById($id);
if (!$item) { header("Location: manage_items.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = trim($_POST['nama_item']);
    $kategori = $_POST['kategori'] ?? $_POST['kategori_manual'] ?? $item['kategori'];
    $deskripsi = trim($_POST['deskripsi']);
    $status = $_POST['status'];
    $stok = (int)$_POST['stok'];
    $harga = (int)$_POST['harga'];
    
    if ($itemObj->updateItem($id, $nama, $kategori, $deskripsi, $status, $stok, $harga)) {
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
        <div class="card-header bg-white border-bottom-0 pt-4 pb-2 px-4 d-flex justify-content-between">
            <h5 class="mb-0 fw-semibold text-primary"><i class="fas fa-edit me-2"></i>Edit Barang</h5>
            <a href="manage_items.php" class="btn btn-outline-secondary btn-sm rounded-pill"><i class="fas fa-arrow-left me-1"></i>Kembali</a>
        </div>
        <div class="card-body p-4">
            <div class="row">
                <div class="col-md-6">
                    <form method="POST">
                        <div class="mb-3">
                            <label>Nama Barang</label>
                            <input type="text" name="nama_item" class="form-control rounded-pill" value="<?= htmlspecialchars($item['nama_item']) ?>" required>
                        </div>
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
                        <div class="mb-3">
                            <label>Stok</label>
                            <input type="number" name="stok" class="form-control rounded-pill" value="<?= $item['stok'] ?>" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label>Harga (Rp) <span class="text-danger">*</span></label>
                            <input type="number" name="harga" class="form-control rounded-pill" value="<?= $item['harga'] ?>" min="0" required>
                            <small class="text-muted">Digunakan untuk perhitungan denda rusak (50%) dan hilang (100%).</small>
                        </div>
                        <div class="mb-3">
                            <label>Deskripsi</label>
                            <textarea name="deskripsi" class="form-control rounded-3" rows="3"><?= htmlspecialchars($item['deskripsi']) ?></textarea>
                        </div>
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
                    </form>
                </div>
                <div class="col-md-6 text-center">
                    <h6>Barcode Barang</h6>
                    <canvas id="barcodeCanvas" data-value="<?= $item['barcode'] ?>" style="border:1px solid #ddd; padding:10px; border-radius:12px;"></canvas>
                    <p class="mt-2 font-monospace"><?= $item['barcode'] ?></p>
                    <button class="btn btn-sm btn-outline-secondary rounded-pill" onclick="printBarcode()"><i class="fas fa-print"></i> Cetak Barcode</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
    let canvas = document.getElementById('barcodeCanvas');
    let value = canvas.getAttribute('data-value');
    JsBarcode(canvas, value, { format: "CODE128", width: 2, height: 60, displayValue: true });
    function printBarcode() {
        let win = window.open();
        win.document.write("<html><head><title>Cetak Barcode</title></head><body><div style='text-align:center; margin-top:50px;'>");
        win.document.write("<img src='" + canvas.toDataURL() + "'/><br>" + value);
        win.document.write("</div></body></html>");
        win.print();
        win.close();
    }
</script>
<?php include '../includes/footer.php'; ?>