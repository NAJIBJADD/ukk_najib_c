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
                <i class="fas fa-box me-2"></i>Manajemen Barang (QR Code)
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
                            <th class="border-0">QR Code</th>
                            <th class="border-0">Nama Barang</th>
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
                                <td>
                                    <div id="qrcode-<?= $item['id'] ?>" style="width: 100px; height: 100px;"></div>
                                    <div class="small text-muted mt-1"><?= $item['barcode'] ?></div>
                                </td>
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
                                    <button type="button" class="btn btn-sm btn-outline-info rounded-pill me-1 view-qrcode" 
                                            data-id="<?= $item['id'] ?>" 
                                            data-barcode="<?= $item['barcode'] ?>" 
                                            data-name="<?= htmlspecialchars($item['nama_item']) ?>">
                                        <i class="fas fa-eye"></i> Lihat QR
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill me-1 download-qrcode" 
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
                        <i class="fas fa-info-circle me-1"></i>QR Code akan digenerate otomatis dari barcode.
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

<!-- Modal Lihat QR Code (besar) -->
<div class="modal fade" id="viewQRModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content rounded-4 border-0 shadow text-center p-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold" id="viewQRTitle">QR Code Barang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="viewQRContainer" style="display: flex; justify-content: center;"></div>
                <p class="mt-3 font-monospace" id="viewQRText"></p>
                <p class="text-muted small">Scan QR code ini menggunakan kamera siswa</p>
                <button id="downloadQRBtn" class="btn btn-primary rounded-pill mt-2"><i class="fas fa-download"></i> Unduh QR (PNG)</button>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
    // Generate QR code untuk setiap item
    <?php foreach ($items as $item): ?>
        new QRCode(document.getElementById("qrcode-<?= $item['id'] ?>"), {
            text: "<?= $item['barcode'] ?>",
            width: 100,
            height: 100,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
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

    // Fungsi download QR code (ukuran besar 300x300)
    document.querySelectorAll('.download-qrcode').forEach(btn => {
        btn.addEventListener('click', function() {
            const barcodeText = this.getAttribute('data-barcode');
            const itemName = this.getAttribute('data-name');
            
            // Buat container sementara
            const tempDiv = document.createElement('div');
            const qr = new QRCode(tempDiv, {
                text: barcodeText,
                width: 300,
                height: 300,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });
            // Tunggu QR selesai
            setTimeout(() => {
                const img = tempDiv.querySelector('img');
                if (img) {
                    const link = document.createElement('a');
                    link.download = `qr_${barcodeText}.png`;
                    link.href = img.src;
                    link.click();
                }
            }, 100);
        });
    });

    // Modal lihat QR
    const viewModal = new bootstrap.Modal(document.getElementById('viewQRModal'));
    let currentQRImageSrc = null;
    document.querySelectorAll('.view-qrcode').forEach(btn => {
        btn.addEventListener('click', function() {
            const barcodeText = this.getAttribute('data-barcode');
            const itemName = this.getAttribute('data-name');
            const container = document.getElementById('viewQRContainer');
            container.innerHTML = '';
            const qr = new QRCode(container, {
                text: barcodeText,
                width: 200,
                height: 200,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });
            document.getElementById('viewQRText').innerText = barcodeText;
            document.getElementById('viewQRTitle').innerHTML = `<i class="fas fa-qrcode me-2"></i>${itemName}`;
            setTimeout(() => {
                const img = container.querySelector('img');
                if (img) currentQRImageSrc = img.src;
            }, 100);
            viewModal.show();
        });
    });

    // Download QR dari modal
    document.getElementById('downloadQRBtn').addEventListener('click', function() {
        if (currentQRImageSrc) {
            const link = document.createElement('a');
            link.download = 'qr_code.png';
            link.href = currentQRImageSrc;
            link.click();
        }
    });
</script>

<style>
    .badge-soft-success { background-color: #e3f7ec; color: #0b5e42; }
    .badge-soft-warning { background-color: #feefd0; color: #b85c00; }
    .badge-soft-danger { background-color: #fee7e7; color: #b91c1c; }
    .table tbody tr:hover { background-color: #f9fbfe !important; }
    .table-light th { font-weight: 600; font-size: 0.8rem; letter-spacing: 0.3px; color: #1f4973; }
</style>

<?php include '../includes/footer.php'; ?>