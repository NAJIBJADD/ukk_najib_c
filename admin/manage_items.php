<?php
require_once '../includes/autoload.php';
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$itemManager = new Item();

function uploadGambar($file) {
    $targetDir = "../assets/uploads/items/";
    if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);
    $fileName = time() . '_' . basename($file['name']);
    $targetFile = $targetDir . $fileName;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    $allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array($imageFileType, $allowedTypes)) return false;
    if ($file['size'] > 2 * 1024 * 1024) return false;
    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        return 'assets/uploads/items/' . $fileName;
    }
    return false;
}

// Tambah barang
if (isset($_POST['add'])) {
    $nama      = $_POST['nama_item'];
    $kategori  = $_POST['kategori'] ?? $_POST['kategori_manual'] ?? 'Umum';
    $deskripsi = $_POST['deskripsi'];
    $stok      = (int) ($_POST['stok'] ?? 1);
    $gambar    = '';
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $gambar = uploadGambar($_FILES['gambar']);
        if (!$gambar) $_SESSION['error'] = "Gambar gagal diupload (format jpg/png/webp, max 2MB)";
    }
    $success = $itemManager->addItem($nama, $kategori, $deskripsi, $stok, $gambar);
    $_SESSION[$success ? 'success' : 'error'] = $success ? "Barang berhasil ditambahkan" : "Gagal menambahkan barang";
    header("Location: manage_items.php");
    exit;
}

// Hapus barang
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $item = $itemManager->getItemById($id);
    if ($item && $item['gambar'] && file_exists("../" . $item['gambar'])) {
        unlink("../" . $item['gambar']);
    }
    $success = $itemManager->deleteItem($id);
    $_SESSION[$success ? 'success' : 'error'] = $success ? "Barang dihapus" : "Gagal hapus barang";
    header("Location: manage_items.php");
    exit;
}

$items = $itemManager->getAllItems();
include '../includes/header.php';
include '../includes/navbar.php';
?>
<div class="container mt-4">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white border-bottom-0 d-flex flex-wrap justify-content-between align-items-center pt-3 pt-md-4 pb-2 px-3 px-md-4">
            <h5 class="mb-2 mb-md-0 fw-semibold text-primary"><i class="fas fa-box me-2"></i>Manajemen Barang</h5>
            <div class="d-flex flex-wrap gap-2">
                <a href="dashboard.php" class="btn btn-outline-secondary btn-sm rounded-pill"><i class="fas fa-arrow-left me-1"></i>Kembali</a>
                <button class="btn btn-primary btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fas fa-plus me-1"></i>Tambah Barang</button>
            </div>
        </div>
        <div class="card-body p-3 p-md-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr><th>No</th><th>Gambar</th><th>Nama</th><th>Kategori</th><th>Stok</th><th>Status</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; ?>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= $no++ ?> </span>
                            <td><?php if ($item['gambar'] && file_exists("../" . $item['gambar'])): ?><img src="../<?= $item['gambar'] ?>" width="45" height="45" class="rounded" style="object-fit: cover;"><?php else: ?><i class="fas fa-image fa-2x text-muted"></i><?php endif; ?></span>
                            <td><?= htmlspecialchars($item['nama_item']) ?> </span>
                            <td><?= htmlspecialchars($item['kategori'] ?? '-') ?> </span>
                            <td><?= $item['stok'] ?> </span>
                            <td><span class="badge <?= $item['status']=='tersedia'?'badge-soft-success':($item['status']=='dipinjam'?'badge-soft-warning':'badge-soft-danger') ?> rounded-pill px-3 py-1"><?= ucfirst($item['status']) ?></span></span>
                            <td><div class="d-flex flex-wrap gap-1"><a href="edit_item.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill"><i class="fas fa-edit"></i> Edit</a><button type="button" class="btn btn-sm btn-outline-info rounded-pill" data-bs-toggle="modal" data-bs-target="#viewItemModal" data-id="<?= $item['id'] ?>" data-name="<?= htmlspecialchars($item['nama_item']) ?>" data-kategori="<?= htmlspecialchars($item['kategori'] ?? '-') ?>" data-stok="<?= $item['stok'] ?>" data-status="<?= $item['status'] ?>" data-deskripsi="<?= htmlspecialchars($item['deskripsi']) ?>" data-gambar="<?= $item['gambar'] ?>"><i class="fas fa-eye"></i> Lihat</button><a href="?delete=<?= $item['id'] ?>" class="btn btn-sm btn-outline-danger rounded-pill" onclick="return confirm('Yakin hapus?')"><i class="fas fa-trash"></i> Hapus</a></div></span>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($items)): ?>
                            <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada barang</td></tr>
                        <?php endif; ?>
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
                <h5 class="modal-title fw-semibold"><i class="fas fa-plus-circle text-primary me-2"></i>Tambah Barang Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data" id="addItemForm">
                <div class="modal-body px-4 pb-2">
                    <div class="mb-3"><label class="form-label fw-semibold">Nama Barang *</label><input type="text" name="nama_item" class="form-control rounded-pill" required></div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Kategori *</label>
                        <select name="kategori" id="kategoriSelect" class="form-select rounded-pill">
                            <option value="Buku">Buku</option>
                            <option value="Alat Tulis">Alat Tulis</option>
                            <option value="Elektronik">Elektronik</option>
                            <option value="Perlengkapan">Perlengkapan</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                        <div id="kategoriLainnyaDiv" style="display: none; margin-top: 8px;">
                            <input type="text" name="kategori_manual" id="kategoriManual" class="form-control rounded-pill" placeholder="Masukkan kategori lain">
                        </div>
                    </div>
                    <div class="mb-3"><label class="form-label fw-semibold">Stok</label><input type="number" name="stok" class="form-control rounded-pill" value="1" min="1" required></div>
                    <div class="mb-3"><label class="form-label fw-semibold">Gambar Barang</label><input type="file" name="gambar" class="form-control rounded-pill" accept="image/jpeg,image/png,image/jpg,image/webp"><small class="text-muted">Format JPG, PNG, WebP (max 2MB)</small></div>
                    <div class="mb-3"><label class="form-label fw-semibold">Deskripsi</label><textarea name="deskripsi" class="form-control rounded-3" rows="2"></textarea></div>
                </div>
                <div class="modal-footer border-0 bg-white pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="add" class="btn btn-primary rounded-pill px-4">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Tampilkan input kategori manual jika pilih "Lainnya"
    const kategoriSelect = document.getElementById('kategoriSelect');
    const kategoriLainnyaDiv = document.getElementById('kategoriLainnyaDiv');
    kategoriSelect.addEventListener('change', function() {
        if (this.value === 'Lainnya') {
            kategoriLainnyaDiv.style.display = 'block';
        } else {
            kategoriLainnyaDiv.style.display = 'none';
        }
    });

    // Saat form submit, pastikan kategori_manual digunakan jika diisi
    document.getElementById('addItemForm').addEventListener('submit', function(e) {
        const select = document.getElementById('kategoriSelect');
        const manual = document.getElementById('kategoriManual');
        if (manual.value.trim() !== '') {
            select.disabled = true;
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'kategori';
            hidden.value = manual.value.trim();
            this.appendChild(hidden);
        }
    });
</script>
<!-- MODAL LIHAT DETAIL (tidak ada masalah scroll) -->
<div class="modal fade" id="viewItemModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 bg-white">
                <h5 class="modal-title fw-semibold">Detail Barang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-3"><img id="viewItemImage" src="" width="100" height="100" class="rounded border" style="object-fit: cover;"></div>
                <div class="table-responsive"><table class="table table-borderless"><tr><th style="width: 35%;">Nama Barang</th><td id="viewItemName"></td></tr><tr><th>Kategori</th><td id="viewItemCategory"></td></tr><tr><th>Stok</th><td id="viewItemStock"></td></tr><tr><th>Status</th><td id="viewItemStatus"></td></tr><tr><th>Deskripsi</th><td id="viewItemDesc"></td></tr></table></div>
            </div>
            <div class="modal-footer border-0 bg-white pb-4"><button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button></div>
        </div>
    </div>
</div>

<script>
    const viewModal = document.getElementById('viewItemModal');
    viewModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        document.getElementById('viewItemName').innerText = button.getAttribute('data-name');
        document.getElementById('viewItemCategory').innerText = button.getAttribute('data-kategori');
        document.getElementById('viewItemStock').innerText = button.getAttribute('data-stok');
        document.getElementById('viewItemDesc').innerText = button.getAttribute('data-deskripsi');
        let status = button.getAttribute('data-status');
        let badgeClass = status=='tersedia'?'badge-soft-success':(status=='dipinjam'?'badge-soft-warning':'badge-soft-danger');
        document.getElementById('viewItemStatus').innerHTML = `<span class="badge ${badgeClass} px-3 py-1">${status.charAt(0).toUpperCase() + status.slice(1)}</span>`;
        let gambar = button.getAttribute('data-gambar');
        document.getElementById('viewItemImage').src = (gambar && gambar !== '') ? '../' + gambar : 'https://placehold.co/100x100?text=No+Image';
    });
</script>

<style>
    .badge-soft-success { background-color: #e3f7ec; color: #0b5e42; }
    .badge-soft-warning { background-color: #feefd0; color: #b85c00; }
    .badge-soft-danger { background-color: #fee7e7; color: #b91c1c; }
    /* Pastikan modal body benar-benar scrollable di semua ukuran layar */
    .modal-dialog-scrollable .modal-body {
        overflow-y: auto;
    }
    @media (max-width: 768px) {
        .table td, .table th { padding: 0.5rem; }
        .btn-sm { padding: 0.2rem 0.5rem; font-size: 0.7rem; }
        .modal-body { padding: 1rem; }
    }
</style>
<?php include '../includes/footer.php'; ?>