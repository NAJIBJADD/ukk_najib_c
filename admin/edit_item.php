<?php
require_once '../includes/autoload.php';
if ($_SESSION['role'] != 'admin') { header("Location: ../login.php"); exit; }

$itemObj = new Item();
$id = $_GET['id'];
$item = $itemObj->getItemById($id);
if(!$item) { header("Location: manage_items.php"); exit; }

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama_item'];
    $kategori = $_POST['kategori'] ?? $_POST['kategori_manual'] ?? $item['kategori'];
    $deskripsi = $_POST['deskripsi'];
    $status = $_POST['status'];
    if($itemObj->updateItem($id, $nama, $kategori, $deskripsi, $status)) {
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
    <div class="card shadow">
        <div class="card-header bg-warning">
            <h4>Edit Barang</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <form method="POST">
                        <div class="mb-2"><label>Nama Barang</label><input type="text" name="nama_item" class="form-control" value="<?= htmlspecialchars($item['nama_item']) ?>" required></div>
                        <div class="mb-2">
                            <label>Kategori</label>
                            <select name="kategori" class="form-control">
                                <option <?= $item['kategori']=='Buku'?'selected':'' ?>>Buku</option>
                                <option <?= $item['kategori']=='Alat Tulis'?'selected':'' ?>>Alat Tulis</option>
                                <option <?= $item['kategori']=='Elektronik'?'selected':'' ?>>Elektronik</option>
                                <option <?= $item['kategori']=='Perlengkapan'?'selected':'' ?>>Perlengkapan</option>
                                <option <?= ($item['kategori']!='Buku' && $item['kategori']!='Alat Tulis' && $item['kategori']!='Elektronik' && $item['kategori']!='Perlengkapan')?'selected':'' ?>>Lainnya</option>
                            </select>
                            <input type="text" name="kategori_manual" class="form-control mt-1" placeholder="Atau kategori lain" value="<?= htmlspecialchars($item['kategori']) ?>">
                        </div>
                        <div class="mb-2"><label>Deskripsi</label><textarea name="deskripsi" class="form-control"><?= htmlspecialchars($item['deskripsi']) ?></textarea></div>
                        <div class="mb-2"><label>Status</label><select name="status" class="form-control">
                            <option <?= $item['status']=='tersedia'?'selected':'' ?>>tersedia</option>
                            <option <?= $item['status']=='dipinjam'?'selected':'' ?>>dipinjam</option>
                            <option <?= $item['status']=='rusak'?'selected':'' ?>>rusak</option>
                            <option <?= $item['status']=='hilang'?'selected':'' ?>>hilang</option>
                        </select></div>
                        <button type="submit" class="btn btn-primary">Update</button>
                        <a href="manage_items.php" class="btn btn-secondary">Batal</a>
                    </form>
                </div>
                <div class="col-md-6 text-center">
                    <h5>Barcode Barang</h5>
                    <canvas id="barcodeCanvas" data-value="<?= $item['barcode'] ?>" style="border:1px solid #ddd; padding:10px;"></canvas>
                    <p class="mt-2"><?= $item['barcode'] ?></p>
                    <button class="btn btn-sm btn-secondary" onclick="printBarcode()"><i class="fas fa-print"></i> Cetak Barcode</button>
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
    // Override kategori manual
    document.querySelector('form').addEventListener('submit', function(e) {
        let select = document.querySelector('select[name="kategori"]');
        let manual = document.querySelector('input[name="kategori_manual"]');
        if (manual.value.trim() !== '') {
            select.disabled = true;
            let hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'kategori';
            hidden.value = manual.value.trim();
            this.appendChild(hidden);
        }
    });
</script>
<?php include '../includes/footer.php'; ?>