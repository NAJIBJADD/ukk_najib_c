<?php
require_once '../includes/autoload.php';
if ($_SESSION['role'] != 'admin') { header("Location: ../login.php"); exit; }

$itemObj = new Item();

// Tambah barang
if(isset($_POST['add'])) {
    $nama = $_POST['nama_item'];
    $kategori = $_POST['kategori'] ?? $_POST['kategori_manual'] ?? 'Umum';
    $deskripsi = $_POST['deskripsi'];
    if($itemObj->addItem($nama, $kategori, $deskripsi)) {
        $_SESSION['success'] = "Barang berhasil ditambahkan";
    } else {
        $_SESSION['error'] = "Gagal menambahkan barang";
    }
    header("Location: manage_items.php");
    exit;
}

// Hapus barang
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    if($itemObj->deleteItem($id)) {
        $_SESSION['success'] = "Barang dihapus";
    } else {
        $_SESSION['error'] = "Gagal hapus barang";
    }
    header("Location: manage_items.php");
    exit;
}

$items = $itemObj->getAllItems();
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-dark text-white d-flex justify-content-between">
            <h4><i class="fas fa-box"></i> Manajemen Barang</h4>
            <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fas fa-plus"></i> Tambah Barang</button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr><th>ID</th><th>Barcode</th><th>Gambar Barcode</th><th>Nama Barang</th><th>Kategori</th><th>Status</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($items as $item): ?>
                        <tr>
                            <td><?= $item['id'] ?></td>
                            <td><?= $item['barcode'] ?></td>
                            <td><canvas class="barcode-canvas" data-value="<?= $item['barcode'] ?>"></canvas></td>
                            <td><?= htmlspecialchars($item['nama_item']) ?></td>
                            <td><?= htmlspecialchars($item['kategori'] ?? '-') ?></td>
                            <td><span class="badge bg-<?= $item['status']=='tersedia'?'success':($item['status']=='dipinjam'?'warning':'danger') ?>"><?= $item['status'] ?></span></td>
                            <td>
                                <a href="edit_item.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Edit</a>
                                <a href="?delete=<?= $item['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus?')"><i class="fas fa-trash"></i> Hapus</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Barang dengan Kategori -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-plus"></i> Tambah Barang Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-2">
                        <label>Nama Barang <span class="text-danger">*</span></label>
                        <input type="text" name="nama_item" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label>Kategori <span class="text-danger">*</span></label>
                        <select name="kategori" class="form-control">
                            <option value="Buku">Buku</option>
                            <option value="Alat Tulis">Alat Tulis</option>
                            <option value="Elektronik">Elektronik</option>
                            <option value="Perlengkapan">Perlengkapan</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                        <input type="text" name="kategori_manual" class="form-control mt-1" placeholder="Atau ketik kategori lain">
                    </div>
                    <div class="mb-2">
                        <label>Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="3"></textarea>
                    </div>
                    <small class="text-muted">Barcode akan digenerate otomatis.</small>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="add" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
    // Generate barcode untuk semua canvas
    document.querySelectorAll('.barcode-canvas').forEach(canvas => {
        let value = canvas.getAttribute('data-value');
        JsBarcode(canvas, value, { format: "CODE128", width: 2, height: 40, displayValue: true });
    });
    
    // Override kategori manual jika diisi
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