<?php
require_once '../includes/autoload.php';
if ($_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$itemObj = new Item();

// Tambah barang
if (isset($_POST['add'])) {
    $nama      = $_POST['nama_item'];
    $kategori  = $_POST['kategori'] ?? $_POST['kategori_manual'] ?? 'Umum';
    $deskripsi = $_POST['deskripsi'];
    $stok      = (int) ($_POST['stok'] ?? 1);

    if ($itemObj->addItem($nama, $kategori, $deskripsi, $stok)) {
        $_SESSION['success'] = "Barang berhasil ditambahkan";
    } else {
        $_SESSION['error'] = "Gagal menambahkan barang";
    }
    header("Location: manage_items.php");
    exit;
}

// Hapus barang
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    if ($itemObj->deleteItem($id)) {
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
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white border-bottom-0 d-flex justify-content-between align-items-center pt-4 pb-2 px-4">
            <h5 class="mb-0 fw-semibold text-primary">
                <i class="fas fa-box me-2"></i>Manajemen Barang
            </h5>
            <div>
                <a href="dashboard.php" class="btn btn-outline-secondary btn-sm me-2 rounded-pill">
                    <i class="fas fa-arrow-left me-1"></i>Kembali
                </a>
                <button class="btn btn-primary btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="fas fa-plus me-1"></i>Tambah Barang
                </button>
            </div>
        </div>

        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0">ID</th>
                            <th class="border-0">Barcode</th>
                            <th class="border-0">Gambar Barcode</th>
                            <th class="border-0">Nama</th>
                            <th class="border-0">Kategori</th>
                            <th class="border-0">Stok</th>
                            <th class="border-0">Status</th>
                            <th class="border-0">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr data-item-id="<?= $item['id'] ?>">
                                <td><?= $item['id'] ?></td>
                                <td><span class="font-monospace"><?= $item['barcode'] ?></span></td>
                                <td><canvas class="barcode-canvas-<?= $item['id'] ?>" data-value="<?= $item['barcode'] ?>"></canvas></td>
                                <td><?= htmlspecialchars($item['nama_item']) ?></td>
                                <td><?= htmlspecialchars($item['kategori'] ?? '-') ?></td>
                                <td><?= $item['stok'] ?></td>
                                <td>
                                    <?php
                                    $status = $item['status'];
                                    $badgeClass = '';
                                    if ($status == 'tersedia') $badgeClass = 'badge-soft-success';
                                    elseif ($status == 'dipinjam') $badgeClass = 'badge-soft-warning';
                                    else $badgeClass = 'badge-soft-danger';
                                    ?>
                                    <span class="badge <?= $badgeClass ?> rounded-pill px-3 py-1">
                                        <?= ucfirst($status) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="edit_item.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill me-1">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-info rounded-pill me-1 view-barcode" 
                                            data-id="<?= $item['id'] ?>" 
                                            data-barcode="<?= $item['barcode'] ?>" 
                                            data-name="<?= htmlspecialchars($item['nama_item']) ?>">
                                        <i class="fas fa-eye"></i> Lihat Barcode
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill me-1 download-barcode" 
                                            data-id="<?= $item['id'] ?>" 
                                            data-barcode="<?= $item['barcode'] ?>" 
                                            data-name="<?= htmlspecialchars($item['nama_item']) ?>">
                                        <i class="fas fa-download"></i> Download
                                    </button>
                                    <a href="?delete=<?= $item['id'] ?>" class="btn btn-sm btn-outline-danger rounded-pill" onclick="return confirm('Yakin hapus?')">
                                        <i class="fas fa-trash"></i> Hapus
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Barang -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 bg-white pt-4 px-4">
                <h5 class="modal-title fw-semibold">
                    <i class="fas fa-plus-circle text-primary me-2"></i>Tambah Barang Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body px-4 pb-2">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Barang <span class="text-danger">*</span></label>
                        <input type="text" name="nama_item" class="form-control rounded-pill" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Kategori <span class="text-danger">*</span></label>
                        <select name="kategori" class="form-select rounded-pill">
                            <option value="Buku">Buku</option>
                            <option value="Alat Tulis">Alat Tulis</option>
                            <option value="Elektronik">Elektronik</option>
                            <option value="Perlengkapan">Perlengkapan</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                        <input type="text" name="kategori_manual" class="form-control rounded-pill mt-2" placeholder="Atau ketik kategori lain">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Stok</label>
                        <input type="number" name="stok" class="form-control rounded-pill" value="1" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control rounded-3" rows="2"></textarea>
                    </div>
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>Barcode akan digenerate otomatis.
                    </small>
                </div>
                <div class="modal-footer border-0 bg-white pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="add" class="btn btn-primary rounded-pill px-4">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Lihat Barcode (besar) -->
<div class="modal fade" id="viewBarcodeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow text-center p-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold" id="viewBarcodeTitle">Barcode Barang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <canvas id="viewBarcodeCanvas" style="max-width:100%; height:auto; border:1px solid #eef2f8; border-radius:12px; padding:10px;"></canvas>
                <p class="mt-3 font-monospace" id="viewBarcodeText"></p>
                <p class="text-muted small">Scan barcode ini menggunakan scanner atau kamera</p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
    // Generate barcode untuk setiap canvas di tabel
    <?php foreach ($items as $item): ?>
        JsBarcode(".barcode-canvas-<?= $item['id'] ?>", "<?= $item['barcode'] ?>", {
            format: "CODE128",
            width: 1.5,
            height: 35,
            displayValue: true,
            fontSize: 10
        });
    <?php endforeach; ?>

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

    // Fungsi download barcode (canvas -> PNG) dengan nama barang
    document.querySelectorAll('.download-barcode').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const barcodeText = this.getAttribute('data-barcode');
            const itemName = this.getAttribute('data-name');
            const canvas = document.querySelector(`.barcode-canvas-${id}`);
            if (!canvas) return;

            const downloadCanvas = document.createElement('canvas');
            const ctx = downloadCanvas.getContext('2d');
            downloadCanvas.width = 300;
            downloadCanvas.height = 130;
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, downloadCanvas.width, downloadCanvas.height);
            const scale = downloadCanvas.width / canvas.width;
            ctx.drawImage(canvas, 0, 0, canvas.width * scale, canvas.height * scale);
            ctx.font = 'bold 14px "Inter", sans-serif';
            ctx.fillStyle = '#1a344d';
            ctx.textAlign = 'center';
            ctx.fillText(itemName, downloadCanvas.width / 2, downloadCanvas.height - 15);
            const link = document.createElement('a');
            link.download = `barcode_${barcodeText}.png`;
            link.href = downloadCanvas.toDataURL('image/png');
            link.click();
        });
    });

    // Fungsi lihat barcode (modal besar)
    const viewModal = new bootstrap.Modal(document.getElementById('viewBarcodeModal'));
    document.querySelectorAll('.view-barcode').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const barcodeText = this.getAttribute('data-barcode');
            const itemName = this.getAttribute('data-name');
            const canvas = document.querySelector(`.barcode-canvas-${id}`);
            if (!canvas) return;

            // Buat canvas baru di modal dengan ukuran besar (400x? proporsional)
            const viewCanvas = document.getElementById('viewBarcodeCanvas');
            const ctx = viewCanvas.getContext('2d');
            const targetWidth = 400;
            const scale = targetWidth / canvas.width;
            viewCanvas.width = targetWidth;
            viewCanvas.height = canvas.height * scale + 40; // tambah ruang untuk teks
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, viewCanvas.width, viewCanvas.height);
            ctx.drawImage(canvas, 0, 0, canvas.width * scale, canvas.height * scale);
            ctx.font = 'bold 16px "Inter", sans-serif';
            ctx.fillStyle = '#1a344d';
            ctx.textAlign = 'center';
            ctx.fillText(itemName, viewCanvas.width / 2, viewCanvas.height - 15);
            document.getElementById('viewBarcodeText').innerText = barcodeText;
            document.getElementById('viewBarcodeTitle').innerHTML = `<i class="fas fa-barcode me-2"></i>${itemName}`;
            viewModal.show();
        });
    });
</script>

<style>
    .badge-soft-success { background-color: #e3f7ec; color: #0b5e42; }
    .badge-soft-warning { background-color: #feefd0; color: #b85c00; }
    .badge-soft-danger { background-color: #fee7e7; color: #b91c1c; }
    .table tbody tr:hover { background-color: #f9fbfe !important; }
    .table-light th { font-weight: 600; font-size: 0.8rem; letter-spacing: 0.3px; color: #1f4973; }
    .font-monospace { font-family: 'Courier New', monospace; font-size: 0.85rem; }
    #viewBarcodeCanvas { background: white; border-radius: 12px; }
</style>

<?php include '../includes/footer.php'; ?>